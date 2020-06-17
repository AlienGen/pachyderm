<?php

require_once('./vendor/autoload.php');

use Pachyderm/Dispatcher;

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'pachyderm');

$dispatcher = new Dispatcher('/api');

$dispatcher->get('/', function() {
    return [200, ['success' => true]];
}, FALSE);

