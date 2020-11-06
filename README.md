# Pachyderm - A micro PHP framework for APIs

A micro PHP framework for building API.

## Getting started

### Install

```bash
composer require aliengen/pachyderm
```

### Usage

#### Controller

```php
use Pachyderm\Dispatcher;
use Pachyderm\Middleware\MiddlewareManager;

$dispatcher = new Dispatcher('/api',  new MiddlewareManager());

/* Declaration of the middleware. */
$dispatcher->registerMiddlewares([
    JSONEncoderMiddleware::class,
    PreflightRequestMiddleware::class,
    SessionMiddleware::class,
    SessionAuthMiddleware::class,
    TimerMiddleware::class,
    DbSessionMiddleware::class
]);

/**
 * Declaration of the routes.
 */

$dispatcher->get('/my_endpoint', function() {
    return [200, ['success' => true]];
});

$dispatcher->post('/my_post_endpoint', function($data) {
    return [200, ['success' => true]];
});

/**
 * Dispatch the request.
 */
$dispatcher->dispatch();
```

## License

See the LICENSE file (MIT)

