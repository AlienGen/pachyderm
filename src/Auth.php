<?php

namespace Pachyderm;

class Auth
{
    protected static $_instance = NULL;
    protected $_user = NULL;

    public function __construct() {
        session_start();
        $this->_user = isset($_SESSION['PACHYDERM_USER']) ? $_SESSION['PACHYDERM_USER'] : NULL;
    }

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

    public function setUser($user) {
        $this->_user = $user;
        $_SESSION['PACHYDERM_USER'] = $user;
    }
}
