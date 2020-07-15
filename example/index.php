<?php

require_once('../vendor/autoload.php');

use Pachyderm\Dispatcher;
use Pachyderm\Middleware\MiddlewareManager;
use Pachyderm\Middleware\PreflightRequestMiddleware;
use Pachyderm\Middleware\TimerMiddleware;
use Pachyderm\Middleware\DbSessionMiddleware;




define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '12345678');
define('DB_NAME', 'pachyderm');

$dispatcher = new Dispatcher('/api',  new MiddlewareManager());
$dispatcher->registerMiddlewares([
    PreflightRequestMiddleware::class,
    TimerMiddleware::class,
    DbSessionMiddleware::class
]);

$dispatcher->get('/', function() {
    return [200, ['success' => true]];
}, FALSE);

$dispatcher->dispatch();
