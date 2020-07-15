<?php

namespace Pachyderm;

use Pachyderm\Exceptions\MiddlewareException;

class MiddlewareManager {
  private $middlewares = [];

  public function __construct() {}

  private function executeChain ($middlewareMethod) {
    foreach($this->middlewares as $m) {
      if ( !method_exists($m->{$middlewareMethod}) ) continue;

      try {
        $m->{$middlewareMethod}();
      } catch (\Exception $e) {
        throw new MiddlewareException('Error in middleware ' .get_class($m)); 
      }
    }
  } 

  public function executeChainBeforeRequest() {
    $this->executeChain($handleBeforeRequest);
  }

  public function executeChainAfterRequest() {
    $this->executeChain($handleAfterRequest);
  }
}