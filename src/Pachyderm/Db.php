<?php

namespace Pachyderm;

class DuplicateException extends \Exception {};

class Db
{
    protected static $_instance = NULL;
    protected $_mysql = NULL;

    protected $_last_query = '';

    public function __construct()
    {
        $this->_mysql = new \MySQLi(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    }

    public static function getInstance() {
        if(!self::$_instance) {
            self::$_instance = new Db();
        }
        return self::$_instance;
    }

    public static function query($query) {
        $db = self::getInstance();
        return $db->_query($query);
    }

    public static function escape($field) {
        if(is_null($field)) {
            return null;
        }
        $db = self::getInstance();
        return $db->mysql()->real_escape_string($field);
    }

    public function mysql() {
        return $this->_mysql;
    }

    public function _query($query) {
        $this->_last_query = $query;
        $result = $this->_mysql->query($query);

        $this->checkDbError();
        return $result;
    }

    public function getInsertedId()
    {
        if($id = $this->_mysql->insert_id)
        {
            return $id;
        }
        return FALSE;
    }

    public function getAffectedRows()
    {
        return $this->_mysql->affected_rows;
    }

    protected function checkDbError()
    {
        if(!empty($this->_mysql->error))
        {
            switch($this->_mysql->errno)
            {
                case 1062:
                    throw new DuplicateException($this->_mysql->error);
                default:
                    throw new \Exception('SQL Error: ' . $this->_mysql->error . ' Last Query:(' . $this->_last_query . ')');
            }
        }
        if($this->_mysql->warning_count != 0)
        {
            $message = '';
            if ($result = $this->_mysql->query("SHOW WARNINGS"))
            {
                while($row = $result->fetch_row())
                {
                    $message .= $row[0] . ' (' . $row[1] . '): ' . $row[2] . PHP_EOL;
                }
                $result->close();
            }
            throw new \Exception('SQL Warning: ' . $message . ' Last Query:(' . $this->_last_query . ')');
        }
        return TRUE;
    }

    /**
     * @param $table String Table name
     * @param $where Array Where condition
     * @param $order Array Order by
     * @param $offset integer Offset
     * @param $limit integer Limit
     * @return array Return array of objects
     */
    public static function findAll($table, $where = NULL, $order = NULL, $offset = 0, $limit = 50) {
        $sql = 'SELECT * FROM `' . $table . '`';

        if(!empty($where)) {
            $sql .= ' WHERE ';
            $sql .= self::parseWhere($where);
        }

        if(!empty($order)) {
            $orders = [];
            foreach($order AS $k => $v) {
                $orders[] = '`' . $k . '` ' . $v;
            }
            $sql .= ' ORDER BY ' . join(', ', $orders);
        }

        $sql .= ' LIMIT ' . $offset . ', ' . $limit;

        $results = self::query($sql);

        $items = [];
        while($item = $results->fetch_assoc()) {
            $items[] = $item;
        }
        return $items;
    }

    /**
     * @param $table String Table name
     * @param $key Primary Key
     * @param $value Value
     * @return false|array Return element if success, false otherwise
     */
    public static function findOne($table, $key, $value) {
        $result = Db::query('SELECT * FROM `' . $table . '` WHERE `' . $key . '`="' . Db::escape($value) . '"');
        $data = $result->fetch_assoc();
        return $data;
    }

    /**
     * @param $table String Table name
     * @param array $payload Data to insert
     * @return false|integer Return new inserted id if success, false otherwise
     */
    public static function insert($table, array $content) {
        $sql = 'INSERT INTO ' . $table . ' SET ';
        $cols = array();
        foreach ($content as $column => $value) {
            if($value === NULL) {
                $cols[] = $column.' = NULL';
            }
            else
                $cols[] = $column.' = "'.self::escape($value).'"';
        }
        $sql .= join(',', $cols);

        self::query($sql);
        return self::getInstance()->getInsertedId();
    }

    /**
     * @param $table String table name
     * @param array $content Data to insert
     * @param $where String Where condition
     */
    public static function update($table, array $content, $where) {
        $sql = 'UPDATE '.$table.' SET ';
        $cols = array();
        foreach ($content as $column => $value) {
            if($value === NULL) {
                $cols[] = $column.' = NULL';
            }
            else
                $cols[] = $column.' = "'.self::escape($value).'"';
        }
        $sql .= join(',', $cols);

        if(!empty($where)) {
            $sql .= ' WHERE ';
            $sql .= self::parseWhere($where);
        }
        self::query($sql);
    }

    /**
     * @param $table String Table name
     * @param $key Primary Key
     * @param $value Value
     * @return false|true Return true if success, false otherwise
     */
    public static function delete($table, $key, $value) {
        return self::query('DELETE FROM `' . $table . '` WHERE `' . $key . '`="' . Db::escape($value) . '"');
    }

    private function parseWhere($array) {
        $op = array_key_first($array);
        $values = $array[$op];
        $arraySize = count($values);

        switch ($op) {
            case 'AND':
            case 'OR':
                $sql = '(';
                for ($i = 0; $i < $arraySize; $i++) {
                    if ($i === $arraySize - 1){
                        $sql .= self::parseWhere($values[$i]);
                    break;
                    }
                    $sql .= self::parseWhere($values[$i]) . ' ' .$op. ' ';
                }
                $sql .= ')';
            break;

            default:
                if($arraySize > 1) {
                    $sql = $values[0] . ' ' . $op .' "' . self::escape($values[1]) . '"';
                    break;
                }
                $sql = $values[0] . ' ' . $op;
            break;
        }
        return $sql;
    }
}

