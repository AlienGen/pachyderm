<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;

/**
 * Class SessionMiddleware
 *
 * This middleware is responsible for starting a session before
 * passing the request to the next middleware or handler.
 */
class SessionMiddleware implements MiddlewareInterface {
  
  /**
   * Handle the request and start a session.
   *
   * @param \Closure $next The next middleware or handler in the stack.
   * @return mixed The response from the next middleware or handler.
   */
  public function handle(\Closure $next) {
    // Start the session
    session_start();
    
    // Pass the request to the next middleware or handler
    return $next();
  }
}
