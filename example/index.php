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
use Pachyderm\Exchange\Response;
use Pachyderm\Service;
use Pachyderm\Db;

// Register the database service
Service::set('db', function() {
    return new Db([
        'host' => 'localhost',
        'username' => 'root',
        'password' => '12345678',
        'database' => 'pachyderm'
    ]);
});

// Initialize the dispatcher with the base API path and middleware manager
$dispatcher = new Dispatcher('/api', new MiddlewareManager());

// Register global middlewares
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
    // Set a session variable for the user
    $_SESSION['PACHYDERM_USER'] = ['name' => 'Alan Turing', 'id' => 0];
    // Return a success response using the Response class
    return Response::success(['success' => true]);
}, [], [SessionAuthMiddleware::class]);

/**
 * Protected route
 * - All middleware is included
 */
$dispatcher->get('/protected', function() {
    // Return a success response using the Response class
    return Response::success(['success' => true]);
}, [], []);

// Dispatch the request
$dispatcher->dispatch();
