<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;

class SessionMiddleware extends MiddlewareInterface {
  public function __construct() {}

  public function handleBeforeRequest() {
    session_start();
  }

  public function handleAfterRequest() {}
}