<?php

namespace Pachyderm\Middleware;

interface MiddlewareManagerInterface
{
    public function executeChain(\Closure $action);
    public function registerMiddleware(MiddlewareInterface $middleware);
}
