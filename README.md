# Pachyderm - A Micro PHP Framework for APIs

Pachyderm is a lightweight PHP framework designed for building APIs with ease. It provides a simple and flexible way to manage routes, middleware, and HTTP requests.

## Introduction

### Motivation

Pachyderm began as an internal training project at AlienGen, aimed at deepening our understanding of framework fundamentals and showcasing PHP's capabilities. As we developed numerous microservices, we recognized the need for a more robust solution than single-file scripts for main endpoints. Pachyderm emerged as a micro framework designed to be both simple and user-friendly, offering a lightweight alternative to larger frameworks like Laravel or Symfony.

We adhere to the KISS (Keep It Simple, Stupid) principle, ensuring our code remains straightforward and comprehensible. The framework's components are extensible, allowing for customization to meet specific needs. By minimizing external dependencies, Pachyderm remains compact and tailored.

Our development approach aligns with the 12-factor app principles, ensuring the framework is built to support these best practices.

### Goals

- **Developer Experience**: Prioritize ease of use, enabling developers to concentrate on business logic with minimal code.
- **Simplicity**: Maintain clear and understandable code for developers.
- **Extensibility**: Allow customization to suit individual project requirements.
- **Lightweight**: Core framework avoids external dependencies, though projects can incorporate any necessary libraries.

### Features

- Routing
- Service Container
- Validation
- Middleware
- Exception Handling
- Response Management

## Getting Started

### Installation

To install Pachyderm, use Composer:

```bash
composer require aliengen/pachyderm
```

### Usage

#### Simple Example

```php
use Pachyderm\Dispatcher;
use Pachyderm\Exchange\Response;

$dispatcher = new Dispatcher();

// Declare a new GET endpoint
$dispatcher->get('/my_endpoint', function() {
    return Response::success(['success' => true]);
});

// Dispatch the request
$dispatcher->dispatch();
```

#### Setting Up a Controller

To create a controller, you need to set up a dispatcher and register your middleware and routes.

```php
use Pachyderm\Dispatcher;
use Pachyderm\Middleware\MiddlewareManager;
use Pachyderm\Middleware\PreflightRequestMiddleware;
use Pachyderm\Middleware\TimerMiddleware;
use Pachyderm\Middleware\DbSessionMiddleware;
use Pachyderm\Middleware\SessionMiddleware;
use Pachyderm\Middleware\SessionAuthMiddleware;
use Pachyderm\Middleware\JSONEncoderMiddleware;
use Pachyderm\Exchange\Response;

/*
 * Instantiate the dispatcher with a base URL and middleware manager.
 */
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
    return Response::success(['success' => true]);
});

$dispatcher->post('/my_post_endpoint', function($data) {
    return Response::success(['success' => true]);
});

/**
 * Dispatch the request.
 */
$dispatcher->dispatch();
```

### Middleware

Middleware in Pachyderm allows you to process requests and responses. You can register global middleware or specific middleware for individual routes.

#### Registering Global Middleware

Example of registering global middleware:

```php
$dispatcher->registerMiddlewares([
    JSONEncoderMiddleware::class,
    PreflightRequestMiddleware::class,
    // Add more middleware as needed
]);
```

#### Adding and Removing Middleware for Specific Routes

You can add middleware that should only be applied to specific routes, or remove global middleware from specific routes.

Example of adding and removing middleware for a route:

```php
$dispatcher->get('/my_endpoint', function() {
    return Response::success(['success' => true]);
}, 
[
    // Local middleware to be applied only to this route
    CustomMiddleware::class
], 
[
    // Global middleware to be excluded from this route
    JSONEncoderMiddleware::class
]);
```

- **Local Middleware**: Specify middleware that should only be applied to the route by passing an array of middleware classes as the third parameter.
  
- **Blacklist Middleware**: Specify global middleware that should be excluded from the route by passing an array of middleware classes as the fourth parameter.

### Routes

Pachyderm supports various HTTP methods such as GET, POST, PUT, DELETE, etc. You can define routes and their corresponding handlers.

#### Supported HTTP Methods

- **GET**: Used to retrieve data from the server.
  ```php
  $dispatcher->get('/example', function() {
      return Response::success(['message' => 'Hello, World!']);
  });
  ```

- **POST**: Used to send data to the server.
  ```php
  $dispatcher->post('/submit', function($data) {
      // Process $data
      return Response::success(['status' => 'Data submitted successfully']);
  });
  ```

- **PUT**: Used to update existing data on the server.
  ```php
  $dispatcher->put('/update/{id}', function($id, $data) {
      // Update data with $id
      return Response::success(['status' => 'Data updated successfully']);
  });
  ```

- **DELETE**: Used to delete data from the server.
  ```php
  $dispatcher->delete('/delete/{id}', function($id) {
      // Delete data with $id
      return Response::success(['status' => 'Data deleted successfully']);
  });
  ```

- **OPTIONS**: Used to describe the communication options for the target resource.
  ```php
  $dispatcher->request('OPTIONS', '/options', function() {
      return Response::success(['methods' => 'GET, POST, PUT, DELETE']);
  });
  ```

- **HEAD**: Used to retrieve the headers of a resource.
  ```php
  $dispatcher->request('HEAD', '/headers', function() {
      return Response::success(['methods' => 'GET, POST, PUT, DELETE']);
  });
  ```

### Services

Pachyderm provides a simple service container for managing service instances. You can register services as closures and retrieve them by name.

#### Registering a Service

To register a service, use the `Service::set` method. For example, to register the `Db` class as a service:

```php
use Pachyderm\Service;
use Pachyderm\Db;

Service::set('db', function() {
    return new Db([
        'host' => 'your_db_host',
        'username' => 'your_db_username',
        'password' => 'your_db_password',
        'database' => 'your_db_name'
    ]);
});
```

#### Using a Registered Service

To retrieve and use a registered service, use the `Service::get` method. For example, to use the `Db` service:

```php
$db = Service::get('db');
$results = $db->findAll('users');
```

#### Direct Instantiation of Db

If you prefer to create a new instance of the `Db` class directly, you can do so as follows:

```php
use Pachyderm\Db;

// Define your database configuration parameters
$parameters = [
    'host' => 'your_db_host',
    'username' => 'your_db_username',
    'password' => 'your_db_password',
    'database' => 'your_db_name'
];

// Create a new instance of the Db class
$db = new Db($parameters);

// Use the $db instance to perform database operations
$results = $db->findAll('users');
```

This approach allows you to have multiple instances of the `Db` class with different configurations if needed.

### Exception Handling

Pachyderm provides a way to handle exceptions that occur during the execution of your application using the `ExceptionHandlerMiddleware`. This middleware is designed to catch exceptions of type `AbstractHTTPException` and return a structured response.

#### Using ExceptionHandlerMiddleware

To use the `ExceptionHandlerMiddleware`, you need to register it with your dispatcher. This middleware will catch any exceptions that occur during the request processing and handle them appropriately.

Here's an example of how to register and use the `ExceptionHandlerMiddleware`:

```php
use Pachyderm\Dispatcher;
use Pachyderm\Middleware\MiddlewareManager;
use Pachyderm\Middleware\ExceptionHandlerMiddleware;

$dispatcher = new Dispatcher('/api', new MiddlewareManager());

// Register the ExceptionHandlerMiddleware
$dispatcher->registerMiddlewares([
    ExceptionHandlerMiddleware::class,
    // Other middlewares...
]);

// Define a route that throws a BadRequestException
$dispatcher->get('/example', function() {
    // Simulate a condition that causes a bad request
    $condition = false;
    if (!$condition) {
        throw new BadRequestException('Invalid request parameters.');
    }
    return Response::success(['success' => true]);
});

// Dispatch the request
$dispatcher->dispatch();
```

#### Existing Exceptions

The following exceptions implement the `AbstractHTTPException` and can be used to handle specific HTTP error scenarios:

- **BadRequestException**: Represents a 400 Bad Request error.
- **UnauthenticatedException**: Represents a 401 Unauthorized error.
- **NotFoundException**: Represents a 404 Not Found error.

These exceptions can be thrown within your route handlers to trigger the `ExceptionHandlerMiddleware` and return appropriate HTTP error responses.

## Additional Information

- **Configuration**: Use the `Config` class to manage application configurations.
- **Database**: The `Db` class provides methods for database interactions, including transactions and queries.
- **Error Handling**: Custom exceptions are available for handling different error scenarios.

For more detailed documentation, please refer to the source code and comments within the files.

## License

Pachyderm is licensed under the MIT License. See the LICENSE file for more details.
