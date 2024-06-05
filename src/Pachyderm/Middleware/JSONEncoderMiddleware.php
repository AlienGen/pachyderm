<?php

namespace Pachyderm\Middleware;

use Pachyderm\Exceptions\BodyContentException;
use Pachyderm\Middleware\MiddlewareInterface;

class JSONEncoderMiddleware implements MiddlewareInterface
{
    public function handle(\Closure $next)
    {
        $response = $next();

        if (PHP_SAPI !== 'cli') {
            header('Content-Type: application/json');
        }

        if (count($response) != 2 && is_integer($response[0]) && !is_array($response[1])) {
            $response[1] = json_encode(array('error' => 'Invalid response format!'));
            return $response;
        }

        if (empty($response[1])) {
            return $response;
        }

        $response[1] = json_encode($response[1]);
        if (empty($response[1])) {
            throw new BodyContentException('Unable to serialize the object!');
        }
        return $response;
    }
}
