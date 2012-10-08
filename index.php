<?php
/**********************************************
Author: Andres Amaya
Name: Inventory REST API
Free software under GNU GPL
***********************************************/

global $security_areas, $security_groups, $security_headings, $path_to_root, $db, $db_connections;

$path_to_root = "../..";

$page_security = 'SA_API';
ini_set('xdebug.auto_trace', 2);
include_once ($path_to_root . "/modules/api/session-custom.inc");
include_once ($path_to_root . "/modules/api/Slim/Slim.php");
\Slim\Slim::registerAutoloader();

include_once ($path_to_root . "/modules/api/util.php");

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

$rest = new \Slim\Slim(array(
		'log.enabled' => true,
    	'mode' => 'debug',
    	'debug' => true
    ));
$rest->setName('SASYS');

// API Login Hook
api_login();

// API Routes
$rest->get('/inventory/', function() use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_all();
	
});
$rest->get('/inventory/:id', function($id) use ($rest) {
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_get($id);

});
$rest->post('/inventory/', function() use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_add();
	
});
$rest->post('/inventory/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_edit($id);
	
});
$rest->delete('/inventory/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_delete($id);
	
});

$rest->get('/movementstype/', function() use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_movementstype_all();
	
});

$rest->get('/locations/', function() use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_locations_all();
	
});

// Init API
$rest->run();

?>