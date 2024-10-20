<?php

namespace Pachyderm\Middleware;

use Pachyderm\Exchange\Response;

interface MiddlewareManagerInterface
{
    public function executeChain(\Closure $action, array $handler): array|Response;
    public function registerMiddleware(MiddlewareInterface $middleware);
}
