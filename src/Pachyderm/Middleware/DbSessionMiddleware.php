<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;
use Pachyderm\Db;

class DbSessionMiddleware implements MiddlewareInterface
{
    public function handle(\Closure $next)
    {
        try {
            $db = Db::getInstance()->mysql();
            $db->begin_transaction();

            $response = $next();

            if ($response[0] < 400) {
                $db->commit();
            } else {
                $db->rollBack();
            }

            return $response;
        } catch (\Exception $e) {
            if (!empty($db)) {
                $db->rollBack();
            }

            throw $e;
        }
    }
}
