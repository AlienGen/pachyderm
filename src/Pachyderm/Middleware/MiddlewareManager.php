<?php

namespace Pachyderm\Middleware;

use Pachyderm\Exceptions\MiddlewareException;
use Pachyderm\Exchange\Response;
use Pachyderm\Middleware\MiddlewareInterface;
use Pachyderm\Middleware\MiddlewareManagerInterface;

/**
 * Class MiddlewareManager
 *
 * Manages a collection of middleware, allowing for registration,
 * execution, and dynamic modification of middleware chains.
 */
class MiddlewareManager implements MiddlewareManagerInterface
{
    private $middlewares = []; // Array to store registered middleware

    /**
     * Merges additional middleware with the existing ones, excluding any blacklisted middleware.
     *
     * @param array $additional Middleware to add.
     * @param array $blacklist Middleware to exclude.
     * @return array The merged list of middleware.
     */
    private function mergeMiddleware(array $additional, array $blacklist): array
    {
        // Start with the current middlewares
        $middlewares = $this->middlewares;

        // Remove blacklisted middleware
        foreach ($middlewares as $key => $candidateForRemoval) {
            $candidateClass = get_class($candidateForRemoval);
            foreach ($blacklist as $toRemove) {
                if ($candidateClass == $toRemove) {
                    unset($middlewares[$key]);
                }
            }
        }

        // Add additional middleware
        foreach ($additional as $toAdd) {
            $instance = new $toAdd();
            if(!$instance instanceof MiddlewareInterface) {
                throw new MiddlewareException('Middleware ' . $toAdd . ' must implement MiddlewareInterface');
            }
            $middlewares[] = $instance;
        }

        return $middlewares;
    }

    /**
     * Executes a chain of middleware, starting with the provided action.
     *
     * @param \Closure $action The initial action to execute.
     * @param array $handler Contains local and blacklist middleware.
     * @return array The result of the middleware chain execution.
     */
    public function executeChain(\Closure $action, array $handler): array|Response
    {
        $additional = $handler['localMiddleware'] ?? [];
        $blacklist = $handler['blacklistMiddleware'] ?? [];

        // Merge and reverse the middleware list
        $middlewares = array_reverse(
            $this->mergeMiddleware($additional, $blacklist)
        );
        $next = $action;

        // Wrap each middleware around the next action
        foreach ($middlewares as $m) {
            $next = function () use ($m, $next, $handler) {
                // $m->handler = $handler; // Set the handler for the middleware
                return $m->handle($next, $handler); // Execute the middleware
            };
        }

        return $next(); // Execute the final action
    }

    /**
     * Registers a new middleware to the manager.
     *
     * @param MiddlewareInterface $middleware The middleware to register.
     * @throws MiddlewareException If registration fails.
     */
    public function registerMiddleware(string $middleware): void
    {
        try {
            $instance = new $middleware();
            if(!$instance instanceof MiddlewareInterface) {
                throw new MiddlewareException('Middleware ' . $middleware . ' must implement MiddlewareInterface');
            }
            $this->middlewares[] = $instance; // Add middleware to the list
        } catch (\Exception $e) {
            throw new MiddlewareException('Error registering middleware ' . $middleware . ': ' . $e->getMessage());
        }
    }
}
