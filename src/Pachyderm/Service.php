<?php

namespace Pachyderm;

use Closure;
use Pachyderm\Exceptions\ServiceException;

/**
 * Class Service
 * 
 * This class provides a simple service container for managing service instances.
 * It allows setting services as closures and retrieving them by name.
 */
class Service {
    private static array $_services;  // Stores service closures
    private static array $_instances; // Stores instantiated services

    /**
     * Retrieves a service instance by name.
     * 
     * @param string $name The name of the service.
     * @return mixed The service instance.
     * @throws ServiceException If the service is not declared.
     */
    public static function get(string $name): mixed {
        if(!isset(self::$_instances[$name])) {
            if(empty(self::$_services[$name])) {
                throw new ServiceException('Service "' . $name . '" not declared!');
            }

            $service = self::$_services[$name];
            self::$_instances[$name] = $service(); // Instantiate the service
        }
        return self::$_instances[$name];
    }

    /**
     * Sets a service closure by name.
     * 
     * @param string $name The name of the service.
     * @param Closure $service The service closure.
     */
    public static function set(string $name, Closure $service): void {
        self::$_services[$name] = $service; // Store the service closure
    }
}
