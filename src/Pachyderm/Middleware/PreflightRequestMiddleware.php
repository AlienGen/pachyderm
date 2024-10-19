<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;

/**
 * Class PreflightRequestMiddleware
 *
 * This middleware handles HTTP OPTIONS requests, commonly known as preflight requests.
 * It checks if the request method is 'OPTIONS' and returns a 200 response with an 'options' key.
 * If the request method is not 'OPTIONS', it passes the request to the next middleware or handler.
 */
class PreflightRequestMiddleware implements MiddlewareInterface {
    
    /**
     * Handle the incoming request.
     *
     * @param \Closure $next The next middleware or request handler.
     * @return array The response array with status code and body.
     */
    public function handle(\Closure $next) {
        // Retrieve the request method from the server variables
        $method = $_SERVER['REQUEST_METHOD'];

        // Check if the request method is 'OPTIONS'
        if ($method == 'OPTIONS') {
            // Return a 200 response with an 'options' key
            return [200, array('options' => 'OK')];
        }

        // If not an OPTIONS request, pass the request to the next middleware or handler
        return $next();
    }
}
