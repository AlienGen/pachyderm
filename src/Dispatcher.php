<?php

namespace Pachyderm;

class Dispatcher
{
	protected $_baseURL = '/api';
	protected $_routes = array(
        'GET' => array(),
        'POST' => array(),
        'PUT' => array(),
        'DELETE' => array()
	);

	public function __construct($_baseURL)
	{
		$this->_baseURL = $_baseURL;
	}

	/**
	 * Declare a new GET endpoint.
	 */
	public function get($endpoint, $action, $auth = TRUE)
	{
		$endpoint = $this->_baseURL . $endpoint;
		$this->_routes['GET'][$endpoint] = ['auth' => $auth, 'action' => $action, 'endpoint' => $endpoint];
	}

	/**
	 * Declare a new POST endpoint.
	 */
	public function post($endpoint, $action, $auth = TRUE)
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
		$method = $_SERVER['REQUEST_METHOD'];
		if($method == 'OPTIONS')
		{
			return $this->handle(function() {
				$response = array('options' => 'Defined!');
				return [200, $response];
			});
		}

		$uri = $_SERVER['REQUEST_URI'];

		$end = strpos($uri, '?');
		$path = substr($uri, 0, $end ? $end : strlen($uri));

		$action = NULL;
		// Match directly, we skip it.
		if(!empty($this->_routes[$method][$path]))
		{
			$action = $this->_routes[$method][$path];
		}

		$argumentsList = array();

		if($action === NULL)
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
			foreach($this->_routes[$method] AS $endpoint => $actionParam)
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
				$action = $actionParam;
				foreach($arguments AS $name)
				{
					$argumentsList[$name] = $args[$name];
				}

				break;
			}
		}

		if(empty($action))
		{
			return $this->handle(function() use($method, $path) {
				$response = array('error' => 'Not found!', 'method' => $method, 'route' => $path);
				return [404, $response];
			});
		}

		if(!$this->checkAuthentication($action))
		{
			return $this->handle(function() use($action) {
				return [401, 'Access denied!'];
			});
		}

		return $this->handle($action['action'], $argumentsList);
	}

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

        try {
        	$db = Db::getInstance()->mysql();
            $db->begin_transaction();

            $arguments['data'] = $bodyParams;
			$start = microtime(true);

            $response = call_user_func_array($action, $arguments);

            $end = microtime(true);
            if(is_array($response[1])) {
                $response[1]['time'] = array('start' => $start, 'end' => $end, 'time' => $end-$start);
            }

            if($response[0] < 300)
                $db->commit();
            else
                $db->rollBack();
        } catch(\Exception $e) {
			$response = [400, ['error' => $e->getMessage()]];
            $db->rollBack();
		}

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

