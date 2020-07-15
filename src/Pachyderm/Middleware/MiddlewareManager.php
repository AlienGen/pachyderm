<?php

namespace Pachyderm\Middleware;

use Pachyderm\Exceptions\MiddlewareException;
use Pachyderm\Middleware\MiddlewareInterface;
use Pachyderm\Middleware\MiddlewareManagerInterface;

class MiddlewareManager implements MiddlewareManagerInterface {
  private $middlewares = [];

  public function __construct() {}

  public function executeChain (\Closure $action) {
    $next = $action;

    $middlewares = array_reverse($this->middlewares);
    
    foreach($middlewares AS $m) {
      $next = function() use($m, $next) {
        return $m->handle($next);
      };
    }   

    return $next();
  } 

  public function registerMiddleware(MiddlewareInterface $middleware) {
    try {
      $this->middlewares[] = $middleware;
    } catch (\Exception $e) {
      throw new MiddlewareException('Error registering middledware' . get_class($middleware));
    }
  }
}