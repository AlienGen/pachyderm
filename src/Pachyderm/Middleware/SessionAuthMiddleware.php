<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;
use Pachyderm\Auth;

class SessionAuthMiddleware implements MiddlewareInterface {
  public function handle(\Closure $next) {
    $user = isset($_SESSION['PACHYDERM_USER']) ? $_SESSION['PACHYDERM_USER'] : NULL;

    if ( !$user ) {
      return [401, array('status' => 'Unauthorized')];
    }

    Auth::setInstanceUser($user);

    return $next();
  }
}
