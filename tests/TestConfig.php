<?php

ini_set('xdebug.show_exception_trace', 0);

$apiPath = realpath(__DIR__ . '/..');
define('API_PATH', $apiPath);
define('SRC_PATH', realpath(API_PATH . '/../..'));
define('TEST_PATH', $apiPath . '/tests');

