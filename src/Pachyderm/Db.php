<?php

namespace Pachyderm;

use Pachyderm\Exceptions\ConfigurationException;

class DuplicateException extends \Exception {};

class Db
{
    protected static array $_instance = [];
    protected $_mysql = NULL;

    protected $_last_query = '';

    public function __construct(array $parameters = NULL)
    {
        if($parameters === NULL) {
            $parameters = [
                'host' => DB_HOST,
                'username' => DB_USER,
                'password' => DB_PASSWORD,
                'database' => DB_NAME
            ];
        }

        $this->_mysql = new \MySQLi($parameters['host'], $parameters['username'], $parameters['password'], $parameters['database']);
    }

    public static function getInstance(string $config = NULL): Db {
        // If not specific configuration is provided, set the default "database" configuration.
        if($config === NULL) {
            $config = 'database';
        }

        if(empty(self::$_instance[$config])) {
            $parameters = NULL;
            try {
                $parameters = Config::get($config);
            } catch(ConfigurationException $e) {
                // Fallback to the "environment" defined configuration.
                $parameters = NULL;
            }
            self::$_instance[$config] = new Db($parameters);
        }

        return self::$_instance[$config];
    }

    public static function query(string $query) {
        $db = self::getInstance();
        return $db->_query($query);
    }

    public static function escape(string $field = NULL): string|null {
        if(is_null($field)) {
            return null;
        }
        $db = self::getInstance();
        return $db->mysql()->real_escape_string($field);
    }

    public function mysql() {
        return $this->_mysql;
    }

    public function _query(string $query) {
        $this->_last_query = $query;
        $result = $this->_mysql->query($query);

        $this->checkDbError();
        return $result;
    }

    public function getInsertedId(): int|null
    {
        if($id = $this->_mysql->insert_id)
        {
            return $id;
        }
        return FALSE;
    }

    public function getAffectedRows(): int
    {
        return $this->_mysql->affected_rows;
    }

    protected function checkDbError(): void
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
    }

    /**
     * @param $table String Table name
     * @param $where Array Where condition
     * @param $order Array Order by
     * @param $offset integer Offset
     * @param $limit integer Limit
     * @return array Return array of objects
     */
    public static function findAll(string $table, array $where = NULL, array $order = NULL, int $offset = 0, int $limit = 50): array {
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
    public static function findOne(string $table, string|array $key, string|array $value): array {
        $query = 'SELECT * FROM `' . $table . '` WHERE ';
        if (!is_array($key) && !is_array($value)) {
            $query .= $key . '="' . Db::escape($value) . '"';
        } else {
            $keyLength = count($key);
            for ($i = 0; $i < $keyLength; $i++) {
                $query .= $key[$i] . '="' . Db::escape($value[$i]) . '"';

                if ($i != $keyLength - 1)
                    $query .= ' AND ';
            }
        }

        $result = Db::query($query);
        return $result->fetch_assoc();
    }

    /**
     * @param $table String Table name
     * @param array $payload Data to insert
     * @return false|integer Return new inserted id if success, false otherwise
     */
    public static function insert(string $table, array $content): int|false {
        $sql = 'INSERT INTO ' . $table;
        if(empty($content)) {
            $sql .= ' VALUES ()';
        }
        else {
            $sql .= ' SET ' . self::formatColumns($content);
        }

        self::query($sql);
        return self::getInstance()->getInsertedId();
    }

    /**
     * @param $table String table name
     * @param array $content Data to insert
     * @param $where String Where condition
     */
    public static function update(string $table, array $content, array $where = NULL): void {
        $sql = 'UPDATE '.$table.' SET ';

        $sql .= self::formatColumns($content);

        if(!empty($where)) {
            $sql .= ' WHERE ';
            $sql .= self::parseWhere($where);
        }
        self::query($sql);
    }

    private static function formatColumns(array $columns): string {
        $cols = array();
        foreach ($columns as $column => $value) {
            if($value === NULL) {
                $cols[] = '`' . $column . '` = NULL';
            }
            elseif ($value === true) {
                $cols[] = '`' . $column . '` = 1';
            }
            elseif ($value === false) {
                $cols[] = '`' . $column . '` = 0';
            }
            else
                $cols[] = '`' . $column . '` = "'.self::escape($value).'"';
        }
        $sql = join(',', $cols);

        return $sql;
    }

    /**
     * @param $table String Table name
     * @param $key Primary Key
     * @param $value Value
     * @return false|true Return true if success, false otherwise
     */
    public static function delete(string $table, string|array $key, string|array $value): bool {
        if (!is_array($key) && !is_array($value)) {
            return self::query('DELETE FROM `' . $table . '` WHERE `' . $key . '`="' . Db::escape($value) . '"');
        }

        $query = 'DELETE FROM `' . $table . '` WHERE ';
        for ($i = 0; $i < count($key); $i++) {
            $query .= '`' . $key[$i] . '`="' . Db::escape($value[$i]) . '"';

            if ($i != count($key) - 1)
                $query .= ' AND ';
        }

        return self::query($query);
    }

    private static function parseWhere(array $array): string {
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
