<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;

class SessionMiddleware implements MiddlewareInterface {
  public function handle(\Closure $next) {
    session_start();
    return $next();
  }
}
