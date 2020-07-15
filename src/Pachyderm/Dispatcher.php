<?php

namespace Pachyderm;

use Pachyderm\Middleware\MiddlewareInterface;
use Pachyderm\Middleware\MiddlewareManagerInterface;

class Dispatcher
{
	protected $_baseURL = '/api';
	protected $_routes = array(
        'GET' => array(),
        'POST' => array(),
        'PUT' => array(),
				'DELETE' => array(),
				'OPTIONS' => array()
	);

	protected $_middlewareManager;

	public function __construct($_baseURL, MiddlewareManagerInterface $middlewareManager)
	{
		$this->_baseURL = $_baseURL;
		$this->_middlewareManager = $middlewareManager;
	}

	public function registerMiddlewares($middlewares) {
		if ( is_array($middlewares) ) {
			foreach($middlewares as $m) {
				$this->_middlewareManager->registerMiddleware(new $m());
			}
		} 
	}



	private function registerRoute($method, $endpoint, $action, $localMiddleware, $excludeMiddleware) {
		$endpoint = $this->_baseURL . $endpoint;
		$this->_routes[$method][$endpoint] = ['action' => $action, 'endpoint' => $endpoint, 'middleware' => $localMiddleware, 'exclude' => $excludeMiddleware];
	}

	/**
	 * Declare a new GET endpoint.
	 */
	public function get($endpoint, \Closure $action, $auth = TRUE, $localMiddleware =[], $excludeMiddleware = [])
	{
		$this->registerRoute('GET', $endpoint, $action, $localMiddleware, $excludeMiddleware);
	}

	/**
	 * Declare a new POST endpoint.
	 */
	public function post($endpoint, \Closure $action, $auth = TRUE)
	{
	    $endpoint = $this->_baseURL . $endpoint;
		$this->_routes['POST'][$endpoint] = ['auth' => $auth, 'action' => $action, 'endpoint' => $endpoint];
	}

	/**
	 * Declare a new PUT endpoint.
	 */
	public function put($endpoint, $action, $auth = TRUE)
	{
	    $endpoint = $this->_baseURL . $endpoint;
		$this->_routes['PUT'][$endpoint] = ['auth' => $auth, 'action' => $action, 'endpoint' => $endpoint];
	}

	/**
	 * Declare a new DELETE endpoint.
	 */
	public function delete($endpoint, $action, $auth = TRUE)
	{
	    $endpoint = $this->_baseURL . $endpoint;
		$this->_routes['DELETE'][$endpoint] = ['auth' => $auth, 'action' => $action, 'endpoint' => $endpoint];
	}

	/**
	 * Dispatch the request and execute the right action.
	 */
	public function dispatch()
	{
		/**
		 * GET SERVER VARIABLES FOR RESOLVING ACTION
		 */
		$method = $_SERVER['REQUEST_METHOD'];
		$uri = $_SERVER['REQUEST_URI'];

		/**
		 * BEGIN HANDLER MATHCHING AND ARGUMENT EXTRACTION
		 */
		$matchedHandler = NULL;
		$argumentsList = array();
		$end = strpos($uri, '?');
		$path = substr($uri, 0, $end ? $end : strlen($uri));

		// Attempt to match path directly
		if(!empty($this->_routes[$method][$path]))
		{
			$matchedHandler = $this->_routes[$method][$path];
		}

		// Can't match directly, so try to match route against parameters
		if($matchedHandler === NULL)
		{
			/**
			 * Match URL
			 *
			 * WARNING: Known bug, the first endpoint with params to match will be choosen!
			 *          Example for the path: /order/125/update
			 *            - /order
			 *            - /order/{id}   <-- This endpoint will be choosen
			 *            - /order/{id}/update
			 */
			foreach($this->_routes[$method] AS $endpoint => $handler)
			{
				/**
				 * Retrieve the params from the endpoint.
				 */
				$paramRegex = '/{[^}]*}/';
				preg_match_all($paramRegex, $endpoint, $params);

				/* The endpoint wasn't requiring any param, it should be matched before, we skip it. */
				if(empty($params[0]))
				{
					continue;
				}

				/**
				 * Generate the regex for URL matching
				 */
				$arguments = array();
				foreach($params[0] AS $param)
				{
					$param_name = substr($param, 1, -1);
					$regexp = '(?P<' . $param_name . '>[^/]+)';
					$endpoint = str_replace($param, $regexp, $endpoint);
					$arguments[] = $param_name;
				}
				$endpoint_matcher = '@^' . $endpoint . '|/?$@';

				/**
				 * Match the endpoint.
				 */
				preg_match($endpoint_matcher, $path, $args);

				/**
				 * Endpoint doesn't match.
				 */
				if(empty($args[0]))
				{
					continue;
				}

				/**
				 * Set action and retrieve the list of values.
				 */
				$matchedHandler = $handler;
				foreach($arguments AS $name)
				{
					$argumentsList[$name] = $args[$name];
				}

				break;
			}
		}

		// reject if no action provided
		if(empty($matchedHandler))
		{
			$matchedHandler['action'] = function() use($method, $path) {
				$response = array('error' => 'Not found!', 'method' => $method, 'route' => $path);
				return [404, $response];
			};
		}

		// check auth for action
		// if(!$this->checkAuthentication($action))
		// {
		// 	return $this->handle(function() use($action) {
		// 		return [401, 'Access denied!'];
		// 	});
		// }

		return $this->handle($matchedHandler['action'], $argumentsList);
	}

	// TODO: export to middleware
	protected function checkAuthentication($action)
	{
		if($action['auth'] == FALSE)
		{
			return TRUE;
		}

		if(!Auth::getUser()) {
			return FALSE;
		}

		return TRUE;
	}

	

	/**
	 * Set data, execute the action and return the json_encoded value.
	 */
	protected function handle($action, $arguments = array())
	{
		$bodyParams = json_decode(file_get_contents('php://input'), true);
		$arguments['data'] = $bodyParams;

		$response = [200, NULL]; // empty default response
		$requestClosure = function() use ($action, $arguments) {
			return call_user_func_array($action, $arguments);
		};

		$response =	$this->_middlewareManager->executeChain($requestClosure);

    //     try {
    //     	$db = Db::getInstance()->mysql();
    //         $db->begin_transaction();

    //         $arguments['data'] = $bodyParams;


    //         if($response[0] < 300)
    //             $db->commit();
    //         else
    //             $db->rollBack();
    //     } catch(\Exception $e) {
		// 	$response = [400, ['error' => $e->getMessage()]];
    //         $db->rollBack();
		// }

		header('Content-Type: application/json');
		if(count($response) != 2 && is_integer($response[0]) && !is_array($response[1]))
		{
			echo json_encode(array('error' => 'Invalid response format!'));
			return false;
		}
		header('HTTP/1.1 ' . $response[0] . ' ' . $this->httpCode($response[0]));
		echo json_encode($response[1]);
		return true;
	}

	protected function httpCode($code)
	{
		switch($code)
		{
			case 200: return 'OK';
			case 201: return 'Created';
			case 400: return 'Bad Request';
			case 401: return 'Unauthorized';
			case 403: return 'Forbidden';
			case 404: return 'Not found';
			case 412: return 'Precondition Failed';
			case 500: return 'Internal error';
		}
		return 'Undefined';
	}

	
};

