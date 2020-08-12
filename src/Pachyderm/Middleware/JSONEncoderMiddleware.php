<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;

class JSONEncoderMiddleware implements MiddlewareInterface {
  public function __construct() {}

  public function handle(\Closure $next) {
    $response = $next();

    header('Content-Type: application/json');

    if(count($response) != 2 && is_integer($response[0]) && !is_array($response[1])) {
        $response[1] = json_encode(array('error' => 'Invalid response format!'));
        return $response;
    }

    $response[1] = json_encode($response[1]);
    return $response;
  }
}
