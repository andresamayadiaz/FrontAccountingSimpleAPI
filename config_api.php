<?php

if (file_exists(__DIR__ . '/_frontaccounting')) {
	$rootPath = realpath(__DIR__ . '/_frontaccounting');
} else {
	$rootPath = realpath(__DIR__ . '/../..');
}

define('API_ROOT', $rootPath . '/modules/api');
define('FA_ROOT', $rootPath);

