<?php

namespace Pachyderm\Middleware;

interface ExtendedMiddlewareInterface
{
    public function handle(\Closure $next, array $handler);
}
