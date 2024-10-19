<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;

/**
 * TimerMiddleware class
 *
 * This middleware measures the execution time of a request.
 * It adds timing information to the response if the response
 * is an array and contains a second element that is an array.
 */
class TimerMiddleware implements MiddlewareInterface {

  public function handle(\Closure $next) {
    // Record the start time
    $start = microtime(true);

    // Execute the next middleware or request handler
    $response = $next();

    // Record the end time
    $end = microtime(true);

    // Check if the response has the correct structure to insert new fields in the response.
    if(is_array($response[1])) {
        // Add timing information to the response
        $response[1]['time'] = array('start' => $start, 'end' => $end, 'time' => $end-$start);
    }

    return $response;
  }
}
