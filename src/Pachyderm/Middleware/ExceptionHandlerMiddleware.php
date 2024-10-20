<?php

namespace Pachyderm\Middleware;

use Pachyderm\Exceptions\AbstractHTTPException;
use Pachyderm\Exchange\Response;
use Pachyderm\Middleware\MiddlewareInterface;

/**
 * Class ExceptionHandlerMiddleware
 *
 * This middleware handles exceptions that occur during the execution of the application.
 * It catches exceptions of type AbstractHTTPException and returns a structured response.
 */
class ExceptionHandlerMiddleware implements MiddlewareInterface
{
  /**
   * Handle the request and catch any AbstractHTTPException.
   *
   * @param \Closure $next The next middleware or request handler.
   * @return array The response containing the error code and message if an exception is caught.
   */
  public function handle(\Closure $next)
  {
    try {
      // Attempt to execute the next middleware or request handler.
      return $next();
    } catch (AbstractHTTPException $e) {
      // Catch the exception and return a structured error response.
      return new Response($e->getCode(), ['success' => false, 'message' => $e->getMessage(), 'errors' => $e->getErrors()]);
    }
  }
}
