<?php
$middlewares = [
  // Authentication middleware
	function($next) {
		// return [401, 'Unauthorized'];
		$response = $next();
		return $response;
	},
	// DB middleware
	function($next) {
		$db = new MySQLi();
		$response = $next();
		$db->close();
		return $response;
	},
	// JSON Encode middleware
	function($next) {
		$response =  $next();
		$response[1] = json_encode($response[1]);
		return $response;
	}
];

function action() {
	return [200, ['success' => 'Youpi!']];
};

$action = function() {
	return action();
};

$next = $action;
// $middlewares = array_reverse($middlewares);

foreach($middlewares AS $m) {
	$next = function() use($m, $next) {
		return $m($next);
  };
}  

$response = $next();
echo 'HTTP Code: ', $response[0], PHP_EOL;
echo 'Body: ', print_r($response[1], true), PHP_EOL;