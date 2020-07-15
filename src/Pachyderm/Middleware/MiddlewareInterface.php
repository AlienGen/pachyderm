<?php

namespace Pachyderm\Middleware;

interface MiddlewareInterface {
  public function handle(\Closure $next);
}