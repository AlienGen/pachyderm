<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;

class PreflightRequestMiddleware implements MiddlewareInterface{
  public function handle(\Closure $next) {
    $method = $_SERVER['REQUEST_METHOD'];
    if($method == 'OPTIONS') {
        return [200, array('options' => 'OK')];
    }
    $response = $next();
    return $response;
  }
}
