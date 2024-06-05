<?php

declare(strict_types=1);

namespace Pachyderm;

use Pachyderm\Exceptions\DispatcherException;
use Pachyderm\Http\HttpHandler;
use Pachyderm\Http\HttpInterface;
use Pachyderm\Middleware\MiddlewareManager;
use Pachyderm\Middleware\MiddlewareManagerInterface;
use ReflectionFunction;

class Dispatcher
{
    protected string $_baseURL = '';
    protected array $_routes = array(
        'GET' => array(),
        'POST' => array(),
        'PUT' => array(),
        'DELETE' => array(),
        'OPTIONS' => array(),
        'HEAD' => array()
    );

    protected HttpInterface $_httpInterface;
    protected MiddlewareManagerInterface $_middlewareManager;

    public function __construct(string $_baseURL = '', MiddlewareManagerInterface $middlewareManager = new MiddlewareManager(), HttpInterface $httpInterface = new HttpHandler())
    {
        $this->_baseURL = $_baseURL;
        $this->_middlewareManager = $middlewareManager;
        $this->_httpInterface = $httpInterface;
    }

    public function registerMiddlewares(array $middlewares)
    {
        if (is_array($middlewares)) {
            foreach ($middlewares as $m) {
                $this->_middlewareManager->registerMiddleware(new $m());
            }
        }
    }

    /**
     * List the routes registered in the dispatcher.
     */
    public function getRoutes(): array {
        $routes = [];
        foreach($this->_routes as $method => $route) {
            foreach($route as $endpoint => $action) {
                $routes[] = [
                    'method' => $method,
                    'endpoint' => $endpoint
                ];
            }
        }
        return $routes;
    }

    /**
     * Declare a new request endpoint.
     */
    public function request(string $method, string $endpoint, \Closure $action, array $localMiddleware = [], array $blacklistMiddleware = [])
    {
        $this->_routes[$method][$endpoint] = [
            'action' => $action,
            'method' => $method,
            'endpoint' => $endpoint,
            'localMiddleware' => $localMiddleware,
            'blacklistMiddleware' => $blacklistMiddleware
        ];
        return $this;
    }

    /**
     * Declare a new GET endpoint.
     */
    public function get(string $endpoint, \Closure $action, array $localMiddleware = [], array $blacklistMiddleware = []): Dispatcher
    {
        return $this->request('GET', $endpoint, $action, $localMiddleware, $blacklistMiddleware);
    }

    /**
     * Declare a new POST endpoint.
     */
    public function post(string $endpoint, \Closure $action, array $localMiddleware = [], array $blacklistMiddleware = []): Dispatcher
    {
        return $this->request('POST', $endpoint, $action, $localMiddleware, $blacklistMiddleware);
    }

    /**
     * Declare a new PUT endpoint.
     */
    public function put(string $endpoint, \Closure $action, array $localMiddleware = [], array $blacklistMiddleware = []): Dispatcher
    {
        return $this->request('PUT', $endpoint, $action, $localMiddleware, $blacklistMiddleware);
    }

    /**
     * Declare a new DELETE endpoint.
     */
    public function delete(string $endpoint, \Closure $action, array $localMiddleware = [], array $blacklistMiddleware = []): Dispatcher
    {
        return $this->request('DELETE', $endpoint, $action, $localMiddleware, $blacklistMiddleware);
    }

    /**
     * Dispatch the request:
     * - resolve path,
     * - resolve parameters,
     * - match an action,
     * - and execute
     */
    public function dispatch(): void
    {
        /**
         * GET SERVER VARIABLES FOR RESOLVING ACTION
         */
        $method = $this->_httpInterface->method();

        // Unrecognized method
        if (!isset($this->_routes[$method])) {
            // Stop there
            die();
        }

        /**
         * BEGIN HANDLER MATCHING AND ARGUMENT EXTRACTION
         */
        $matchedHandler = null;
        $pathParameters = array();
        $path = $this->_httpInterface->path();

        // Check if the path starts by the baseURL.
        if(strncmp($path, $this->_baseURL, strlen($this->_baseURL)) === 0) {
            // Remove the baseURL from the request path to get the endpoint path.
            $path = substr($path, strlen($this->_baseURL));

            // Attempt to match path directly
            if (!empty($this->_routes[$method][$path])) {
                $matchedHandler = $this->_routes[$method][$path];
            }

            // Can't match directly, so try to match route against parameters
            if ($matchedHandler === null) {
                /**
                 * Match URL
                 */
                foreach ($this->_routes[$method] as $endpoint => $handler) {
                    if ($this->isRouteMatching($endpoint, $path, $pathParameters)) {
                        /**
                         * Set action and retrieve the list of values.
                         */
                        $matchedHandler = $handler;
                        break;
                    }
                }
            }
        }

        // no action provided, provide default 404 action
        if (empty($matchedHandler)) {
            $matchedHandler = [
                'method' => $method,
                'action' => function () use ($method, $path) {
                    $response = array('error' => 'Not found!', 'method' => $method, 'route' => $path);
                    return [404, $response];
                }
            ];
        }

        // extract any body params for POST, PUT, or DELETE
        $body = $this->_httpInterface->bodyParams();

        // Handle the request
        $this->handle($matchedHandler, $pathParameters, $body);
    }

    /**
     * Set data, execute the action and return the json_encoded value.
     */
    protected function handle(array $handler, array $pathParameters = array(), mixed $body = null): void
    {
        $arguments = $pathParameters;

        $bodyParameterName = 'data';
        $bodyTypeParameter = null;

        // Analyze action parameters.
        $reflection = new ReflectionFunction($handler['action']);
        $actionParameters = $reflection->getParameters();
        $paramsLeft = $pathParameters;
        foreach ($actionParameters as $param) {
            $name = $param->getName();

            if ($param->hasType()) {
                $type = $param->getType();
                if (!$type->isBuiltin()) {
                    $bodyTypeParameter = $type->getName();
                    $bodyParameterName = $name;
                    continue;
                }
            }

            // If the body has no type, only "data" is allowed.
            if ($bodyParameterName === $name) {
                continue;
            }

            if (!isset($paramsLeft[$name])) {
                throw new DispatcherException('Parameter ' . $name . ' doesn\'t exists for the action!');
            }
            unset($paramsLeft[$name]);
        }

        // Build the body object if necessary
        if ($bodyTypeParameter === null) {
            $arguments[$bodyParameterName] = $body;
        } else {
            $arguments[$bodyParameterName] = new $bodyTypeParameter($body);
        }

        // wrap action in a closure
        $requestClosure = function () use ($handler, $arguments) {
            return call_user_func_array($handler['action'], array_values($arguments));
        };

        // ask middleware manager to execute middleware chain before the request
        $response = $this
            ->_middlewareManager
            ->executeChain(
                $requestClosure,
                $handler
            );

        // Set headers, body response and send
        $this->_httpInterface
            ->setStatusCode($response[0])
            ->setBody($response[1])
            ->send();
    }

    /**
     * Evaluate if the endpoint is matching the path from the request.
     * Extract the parameters if succeed.
     */
    private function isRouteMatching($endpoint, $path, &$pathParameters = array()): bool
    {
        /**
         * Retrieve the params from the endpoint.
         */
        $paramRegex = '/{[^}]*}/';
        preg_match_all($paramRegex, $endpoint, $params);

        /* The endpoint wasn't requiring any param, it should be matched before, we skip it. */
        if (empty($params[0])) {
            return false;
        }

        /**
         * Generate the regex for URL matching
         */
        $parameters = array();
        $matcher = $endpoint;
        foreach ($params[0] as $param) {
            $param_name = substr($param, 1, -1);
            $regexp = '(?P<' . $param_name . '>[^/]+)';
            $matcher = str_replace($param, $regexp, $matcher);
            $parameters[] = $param_name;
        }
        $endpoint_matcher = '@^' . $matcher . '|/?$@';

        /**
         * Match the endpoint.
         */
        preg_match($endpoint_matcher, $path, $args);

        /**
         * Endpoint doesn't match.
         */
        if (empty($args[0])) {
            return false;
        }

        /**
         * Check URL length match with endpoint length
         */
        $subfoldersA = explode('/', $path);
        $subfoldersB = explode('/', $endpoint);

        $length = count($subfoldersA);
        if ($length != count($subfoldersB)) {
            return false;
        }

        foreach ($parameters as $name) {
            $pathParameters[$name] = $args[$name];
        }
        return true;
    }
}
