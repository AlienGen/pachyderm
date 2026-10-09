<?php

namespace Pachyderm\Middleware;

use Pachyderm\Exchange\Response;
use Pachyderm\Middleware\MiddlewareInterface;

/**
 * TimerMiddleware class
 *
 * This middleware measures the execution time of a request.
 * It adds timing information to the response body when it is an array.
 *
 * Note: Response implements ArrayAccess for middleware compatibility.
 * Nested writes like `$response[1]['time'] = ...` are indirect modifications
 * of overloaded elements and have no effect — copy the body, mutate, assign back.
 */
class TimerMiddleware implements MiddlewareInterface {

  public function handle(\Closure $next) {
    // Record the start time
    $start = microtime(true);

    // Execute the next middleware or request handler
    $response = $next();

    // Record the end time
    $end = microtime(true);

    $timing = array('start' => $start, 'end' => $end, 'time' => $end - $start);

    if ($response instanceof Response) {
      $body = $response->body();
      if (is_array($body)) {
        $body['time'] = $timing;
        $response[1] = $body;
      }
      return $response;
    }

    // Legacy array responses: [status, body, headers]
    if (is_array($response) && isset($response[1]) && is_array($response[1])) {
      $response[1]['time'] = $timing;
    }

    return $response;
  }
}
