<?php

namespace Pachyderm;

use Pachyderm\Exceptions\ConfigurationException;

class Config {
    public static $configurations = [];

    /**
     * Set a new configuration.
     */
    public static function set(string $config, mixed $value) : void{
        self::$configurations[$config] = $value;
    }

    /**
     * Retrieve a specific configuration.
     */
    public static function get(string $config): mixed {
        if(!isset(self::$configurations[$config])) {
            throw new ConfigurationException('No configuration set for "' . $config . '"');
        }
        return self::$configurations[$config];
    }
}
