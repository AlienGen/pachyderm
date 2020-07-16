<?php

require_once('../vendor/autoload.php');

use Pachyderm\Dispatcher;
use Pachyderm\Auth;
use Pachyderm\Middleware\MiddlewareManager;
use Pachyderm\Middleware\PreflightRequestMiddleware;
use Pachyderm\Middleware\TimerMiddleware;
use Pachyderm\Middleware\DbSessionMiddleware;
use Pachyderm\Middleware\SessionMiddleware;
use Pachyderm\Middleware\SessionAuthMiddleware;
use Pachyderm\Middleware\JSONEncoderMiddleware;



define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '12345678');
define('DB_NAME', 'pachyderm');

$dispatcher = new Dispatcher('/api',  new MiddlewareManager());
$dispatcher->registerMiddlewares([
    JSONEncoderMiddleware::class,
    PreflightRequestMiddleware::class,
    SessionMiddleware::class,
    SessionAuthMiddleware::class,
    TimerMiddleware::class,
    DbSessionMiddleware::class
]);

/**
 * Unprotected route
 * - Blacklists the Auth middleware registered globally above
 */
$dispatcher->get('/login', function() {
    $_SESSION['PACHYDERM_USER'] = [ 'name' => 'Alan Turing', 'id' => 0];
    return [200, ['success' => true]];
}, [], [SessionAuthMiddleware::class]);

/**
 * Protected route
 * - All middleware is included
 */
$dispatcher->get('/protected', function() {
    return [200, ['success' => true]];
}, [], []);

$dispatcher->dispatch();
