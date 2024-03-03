<?php

namespace Pachyderm;

use Closure;

class Service {
    private static array $_services;
    private static array $_instances;

    public static function get(string $name): mixed {
        if(!isset(self::$_instances[$name])) {
            if(empty(self::$_services[$name])) {
                throw new \Exception('Service "' . $name . '" not declared!');
            }

            $service = self::$_services[$name];
            self::$_instances[$name] = $service();
        }
        return self::$_instances[$name];
    }

    public static function set(string $name, Closure $service): void {
        self::$_services[$name] = $service;
    }
}
