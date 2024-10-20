<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;
use Pachyderm\Container;

class LoggerMiddleware extends Container implements MiddlewareInterface
{
  public function handle(\Closure $next)
  {
    $this->logger->debug('Request', ['request' => $_REQUEST]);
    $response = $next();
    $this->logger->debug('Response', ['response' => $response]);
    return $response;
  }
}