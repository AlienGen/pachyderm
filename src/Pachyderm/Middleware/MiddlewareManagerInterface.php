<?php

namespace Pachyderm\Middleware;

interface MiddlewareManagerInterface
{
    public function executeChain(\Closure $action, array $handler): array;
    public function registerMiddleware(MiddlewareInterface $middleware);
}
