<?php

namespace Pachyderm\Middleware;

use Pachyderm\Exceptions\MiddlewareException;
use Pachyderm\Middleware\MiddlewareInterface;
use Pachyderm\Middleware\MiddlewareManagerInterface;

class MiddlewareManager implements MiddlewareManagerInterface {
  private $middlewares = [];

  public function __construct() {}

  private function mergeMiddleware($additional, $blacklist) {
    // remove blacklist 
    $middlewares = $this->middlewares;
    foreach($middlewares as $key => $candidateForRemoval) {
      $candidateClass = get_class($candidateForRemoval);
      foreach ($blacklist as $toRemove) {
        if ( $candidateClass == $toRemove ) {
          unset($middlewares[$key]);
        }
      }
    }
    
    // merge additional
    foreach ($additional as $toAdd) {
      $middlewares[] = $toAdd;
    }

    return $middlewares;
  }

  public function executeChain (\Closure $action, $additional =[], $blacklist =[]) {
    $middlewares = array_reverse(
      $this->mergeMiddleware($additional, $blacklist)
    );
    $next = $action;
    
    foreach($middlewares as $m) {
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