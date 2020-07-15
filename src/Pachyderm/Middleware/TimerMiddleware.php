<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;

class TimerMiddleware implements MiddlewareInterface {
  public function __construct() {}

  public function handle(\Closure $next) {
    $start = microtime(true);
    $response = $next();
    $end = microtime(true);
      if(is_array($response[1])) {
        $response[1]['time'] = array('start' => $start, 'end' => $end, 'time' => $end-$start);
      }
    return $response;
  }
}