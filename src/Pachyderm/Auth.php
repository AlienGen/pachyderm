<?php

namespace Pachyderm;

class Auth
{
    protected static $_instance = NULL;
    protected $_user = NULL;

    public static function getInstance() {
        if(!self::$_instance) {
            self::$_instance = new Auth();
        }
        return self::$_instance;
    }

    public static function getUser() {
        return self::getInstance()->user();
    }

    public function user() {
        return $this->_user;
    }

    public static function setInstanceUser($user) {
        self::getInstance()->setUser($user);
    }

    public function setUser($user) {
        $this->_user = $user;
        
    }
}
