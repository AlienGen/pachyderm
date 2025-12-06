<?php

namespace Pachyderm;

class Auth
{
    protected static Auth|null $_instance = null;
    protected mixed $_user = null;

    public static function getInstance(): Auth {
        if(!self::$_instance) {
            self::$_instance = new Auth();
        }
        return self::$_instance;
    }

    public static function getUser(): mixed {
        return self::getInstance()->user();
    }

    public function user(): mixed {
        return $this->_user;
    }

    public static function setInstanceUser(mixed $user): void {
        self::getInstance()->setUser($user);
    }

    public function setUser(mixed $user): void {
        $this->_user = $user;

    }
}
