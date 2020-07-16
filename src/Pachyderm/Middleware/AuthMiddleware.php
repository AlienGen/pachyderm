<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;
use Pachyderm\Auth;

class AuthMiddleware implements MiddlewareInterface {
  public function __construct() {}

  public function handle(\Closure $next) {
    if (!Auth::getUser()) return [401, array('status' => 'Unauthorized')];
    return $next();
  }
}