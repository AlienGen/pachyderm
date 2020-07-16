<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;

class SessionMiddleware implements MiddlewareInterface {
  public function __construct() {}

  public function handle(\Closure $next) {
    session_start();
    return $next();
  }

  public function handleAfterRequest() {}
}