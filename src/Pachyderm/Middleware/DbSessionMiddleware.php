<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;
use Pachyderm\Db;

class DbSessionMiddleware implements MiddlewareInterface
{
    public function __construct()
    {
    }

    public function handle(\Closure $next)
    {
        try {
            $db = Db::getInstance()->mysql();
            $db->begin_transaction();

            $response = $next();

            if ($response[0] < 300) {
                $db->commit();
            } else {
                $db->rollBack();
            }

            return $response;
        } catch (\Exception $e) {
            $response = [500, ['error' => $e->getMessage()]];
            if (!empty($db)) {
                $db->rollBack();
            }

            // Throw exception in CLI.
            if (PHP_SAPI === 'cli') {
                throw $e;
            }

            // Return a 500 error in HTTP.
            return $response;
        }
    }
}
