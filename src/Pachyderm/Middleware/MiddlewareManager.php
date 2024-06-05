<?php

namespace Pachyderm\Middleware;

use Pachyderm\Exceptions\MiddlewareException;
use Pachyderm\Middleware\MiddlewareInterface;
use Pachyderm\Middleware\MiddlewareManagerInterface;

class MiddlewareManager implements MiddlewareManagerInterface
{
    private $middlewares = [];

    private function mergeMiddleware(array $additional, array $blacklist): array
    {
        // remove blacklist
        $middlewares = $this->middlewares;
        foreach ($middlewares as $key => $candidateForRemoval) {
            $candidateClass = get_class($candidateForRemoval);
            foreach ($blacklist as $toRemove) {
                if ($candidateClass == $toRemove) {
                    unset($middlewares[$key]);
                }
            }
        }

        // merge additional
        foreach ($additional as $toAdd) {
            $middlewares[] = $toAdd;
        }

        return $middlewares;
    }

    public function executeChain(\Closure $action, array $handler): array
    {
        $additional = $handler['localMiddleware'] ?? [];
        $blacklist = $handler['blacklistMiddleware'] ?? [];

        $middlewares = array_reverse(
            $this->mergeMiddleware($additional, $blacklist)
        );
        $next = $action;

        foreach ($middlewares as $m) {
            $next = function () use ($m, $next, $handler) {
                $m->handler = $handler;
                return $m->handle($next, $handler);
            };
        }

        return $next();
    }

    public function registerMiddleware(MiddlewareInterface $middleware): void
    {
        try {
            $this->middlewares[] = $middleware;
        } catch (\Exception $e) {
            throw new MiddlewareException('Error registering middledware' . get_class($middleware));
        }
    }
}
