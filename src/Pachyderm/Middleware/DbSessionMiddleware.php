<?php

namespace Pachyderm\Middleware;

use Pachyderm\Middleware\MiddlewareInterface;
use Pachyderm\Db;
use Pachyderm\Exchange\Response;

/**
 * This middleware manages database transactions for each request.
 * It starts a transaction before the request is processed and commits it if the response is successful.
 * If an error occurs or an exception is thrown, it rolls back the transaction.
 */
class DbSessionMiddleware implements MiddlewareInterface
{
    public function handle(\Closure $next)
    {
        try {
            // Get the database instance and start a transaction
            $db = Db::getInstance()->mysql();
            $db->begin_transaction();

            // Execute the next middleware or request handler
            $response = $next();

            // Commit the transaction if the response status is less than 400
            if (!Response::hasError($response)) {
                $db->commit();
            } else {
                // Rollback the transaction if the response status is 400 or greater
                $db->rollBack();
            }

            // Return the response
            return $response;
        } catch (\Exception $e) {
            // Rollback the transaction in case of an exception
            if (!empty($db)) {
                $db->rollBack();
            }

            // Rethrow the exception
            throw $e;
        }
    }
}
