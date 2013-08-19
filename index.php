<?php
/**********************************************
Author: Andres Amaya
Name: SASYS REST API
Free software under GNU GPL

--> 15-July-2013:
- Added .htaccess
- GET with pagination
- Sales Methods

--> 14-June-2013:
- Added POST /locations/ To Add A Location Thanks to Richard Vinke
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

$req	= $rest->request();

define("RESULTS_PER_PAGE", 2);

// API Routes
// ------------------------------- Items -------------------------------
// Get Items
$rest->get('/inventory/', function() use ($rest){
	
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/inventory.inc");

	$page	= $req->get("page");

	if ($page == null) {
		inventory_all();
	} else {
		// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
		$from = --$page * RESULTS_PER_PAGE;
		inventory_all($from);
	}	
});
// Get Specific Item by Stock Id
$rest->get('/inventory/:id', function($id) use ($rest) {
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_get($id);

});
// Add Item
$rest->post('/inventory/', function() use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_add();
	
});
// Edit Specific Item
$rest->put('/inventory/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_edit($id);
	
});
// Delete Specific Item
$rest->delete('/inventory/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_delete($id);
	
});
// ------------------------------- Items -------------------------------

// ------------------------------- Inventory Movements -------------------------------
// Get Inventory Movement Types
$rest->get('/movementtypes/', function() use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_movementstype_all();
	
});
// ------------------------------- Inventory Movements -------------------------------

// ------------------------------- Inventory Locations -------------------------------
// Get Locations
$rest->get('/locations/', function() use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_locations_all();
	
});

// Add Location, added by Richard Vinke
$rest->post('/locations/', function() use ($rest){

	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	inventory_location_add();

});
// ------------------------------- Inventory Locations -------------------------------

// ------------------------------- Stock Adjustments -------------------------------
// Add Stock Adjustment
$rest->post('/stock/', function() use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/inventory.inc");
	stock_adjustment_add();
	
});
// ------------------------------- Stock Adjustments -------------------------------

// ------------------------------- Item Categories -------------------------------
// Get Items Categories
$rest->get('/category/', function() use ($rest){
	
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/category.inc");

	$page	= $req->get("page");

	if ($page == null) {
		category_all(null);
	} else {
		// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
		$from = --$page * RESULTS_PER_PAGE;
		category_all($from);
	}
});
// Get Specific Item Category
$rest->get('/category/:id', function($id) use ($rest) {
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/category.inc");
	category_get($id);

});
// Add Item Category
$rest->post('/category/', function() use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/category.inc");
	category_add();
	
});
// Edit Item Category
$rest->put('/category/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/category.inc");
	category_edit($id);
	
});
// Delete Item Category
$rest->delete('/category/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/category.inc");
	category_delete($id);
	
});
// ------------------------------- Item Categories -------------------------------

// ------------------------------- Tax Types -------------------------------
// Tax Types
// Get All Item Tax Types
$rest->get('/taxtypes/', function() use ($rest){
	
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/taxtypes.inc");

	$page	= $req->get("page");

	if ($page == null){
		taxtypes_all();
	}	else {
		// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
		$from = --$page * RESULTS_PER_PAGE;
		taxtypes_all($from);
	}
});
$rest->get('/taxtypes/find_by_taxtype', function() use ($rest){
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/taxtypes.inc");

	$tax_types = $req->get("tax_types");

	taxtypes_get($tax_types);
});
// ------------------------------- Tax Types -------------------------------

// ------------------------------- Tax Groups -------------------------------
// Tax Groups
// Get All Tax Groups
$rest->get('/taxgroups/', function() use ($rest){
	
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/taxgroups.inc");

	$page	= $req->get("page");

	if ($page == null) {
		taxgroups_all();
	} else {
		// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
		$from = --$page * RESULTS_PER_PAGE;
		taxgroups_all($from);
	}	
});
// Get Tax
$rest->get('/taxgroups/find_by_taxtype', function() use ($rest){
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/taxgroups.inc");

	$tax_types = $req->get("tax_types");

	taxgroups_get($tax_types);
});
// ------------------------------- Tax Groups -------------------------------

// ------------------------------- Customers -------------------------------
// Customers
// Get Customer General Info
$rest->get('/customers/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/customers.inc");
	customer_get($id);
	
});
// All Customers
$rest->get('/customers/', function() use ($rest){
	
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/customers.inc");

	$page	= $req->get("page");

	if ($page == null) {
		customer_all();
	} else {
		// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
		$from = --$page * RESULTS_PER_PAGE;
		customer_all($from);
	}	
});
// Add Customer
$rest->post('/customers/', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/customers.inc");
	customer_add();
	
});
// Edit Customer
$rest->put('/customers/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/customers.inc");
	customer_edit($id);
	
});
// Delete Customer
$rest->delete('/customers/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/suppliers.inc");
	customer_delete($id);
	
});
// Get Customer Branches
$rest->get('/customers/:id/branches/', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/customers.inc");
	customer_branches_get($id);
	
});
// ------------------------------- Customers -------------------------------

// ------------------------------- Suppliers -------------------------------
// Suppliers
// Get Supplier General Info
$rest->get('/suppliers/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/suppliers.inc");
	supplier_get($id);
	
});
// Add Supplier
$rest->post('/suppliers/', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/suppliers.inc");
	supplier_add();
	
});
// Edit Supplier
$rest->put('/suppliers/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/suppliers.inc");
	supplier_edit($id);
	
});
// Delete Supplier
$rest->delete('/suppliers/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/suppliers.inc");
	supplier_delete($id);
	
});
// Get Supplier Contacts
$rest->get('/suppliers/:id/contacts/', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/suppliers.inc");
	supplier_contacts_get($id);
	
});
// ------------------------------- Suppliers -------------------------------

// ------------------------------- Bank Accounts -------------------------------
// Bank Accounts
// Get All Bank Accounts
$rest->get('/bankaccounts/', function() use ($rest){
	
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/bankaccounts.inc");

	$page	= $req->get("page");

	if ($page == null) {
		bankaccounts_all();
	} else {
		// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
		$from = --$page * RESULTS_PER_PAGE;
		bankaccounts_all($from);
	}
});
// Get Specific Bank Account
$rest->get('/bankaccounts/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/bankaccounts.inc");
	bankaccounts_get($id);
	
});
// ------------------------------- Bank Accounts -------------------------------

// ------------------------------- GL Accounts -------------------------------
// GL Accounts
// Get GL Accounts
$rest->get('/glaccounts/', function() use ($rest){
	
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/glaccounts.inc");

	$page	= $req->get("page");

	if ($page == null) {
		glaccounts_all();
	} else {
		// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
		$from = --$page * RESULTS_PER_PAGE;
		glaccounts_all($from);
	}	
});
// Get Specific GL Account
$rest->get('/glaccounts/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/glaccounts.inc");
	glaccounts_get($id);
	
});
// Get GL Account Types
$rest->get('/glaccounttypes/', function() use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/glaccounts.inc");
	glaccounttypes_all();
	
});
// ------------------------------- GL Accounts -------------------------------

// ------------------------------- Currencies -------------------------------
// Currencies
// Get All Currencies
$rest->get('/currencies/', function() use ($rest){
	
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/currencies.inc");

	$page	= $req->get("page");

	if ($page == null) {
		currencies_all();
	} else {
		// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
		$from = --$page * RESULTS_PER_PAGE;
		currencies_all($from);
	}
});
// Get Specific Currency
$rest->get('/currencies/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/currencies.inc");
	currencies_get($id);
	
});
// Get Last Exchange Rate
$rest->get('/exrates/:curr_abrev', function($curr_abrev) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/currencies.inc");
	currencies_last_exrate($curr_abrev);
	
});
// ------------------------------- Currencies -------------------------------

// ------------------------------- Inventory Costs -------------------------------
// Inventory Costs
// Get Item Cots
$rest->get('/itemcosts/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/items.inc");
	itemcosts_get($id);
	
});
// Update Item Cost
$rest->put('/itemcosts/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/items.inc");
	itemcosts_update($id);
	
});
// ------------------------------- Inventory Costs -------------------------------

// ------------------------------- Assets -------------------------------
// Fixed Assets
// Get Fixed Asset
$rest->get('/assets/:id', function($id) use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/assets.inc");
	assets_get($id);
	
});
// Insert Fixed Asset
$rest->post('/assets/', function() use ($rest){
	
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/assets.inc");
	assets_add();
	
});
// Get Asset Types
$rest->get('/assettypes/', function() use ($rest){
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/assets.inc");

	$page	= $req->get("page");

	if ($page == null) {
		assettypes_all();
	} else {
		// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
		$from = --$page * RESULTS_PER_PAGE;
		assettypes_all($from);
	}
});
// ------------------------------- Assets -------------------------------

// ------------------------------- Sales Order Entry -------------------------------
// Sales
// Get Sales Header and Details
$rest->get('/sales/:trans_no/:trans_type', function($trans_no, $trans_type) use ($rest){
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/sales.inc");
	sales_get($trans_no, $trans_type);
});
// Insert Sales
$rest->post('/sales/', function() use ($rest){
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/sales.inc");
	sales_add();
	
});
// Edit Sales
$rest->put('/sales/:trans_no/:trans_type', function($trans_no, $trans_type) use ($rest){
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/sales.inc");
	sales_edit($trans_no, $trans_type);
	
});
// Cancel Sales
$rest->delete('/sales/:branch_id/:uuid', function($branch_id, $uuid) use ($rest) {
	global $path_to_root;
	include_once($path_to_root . "/modules/api/sales.inc");
	sales_cancel($branch_id, $uuid);
});
// All Sales
$rest->get('/sales/:trans_type/', function($trans_type) use ($rest){
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/sales.inc");

	$page	= $req->get("page");

	if ($page == null) {
		sales_all($trans_type);
	} else {
		// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
		$from = --$page * RESULTS_PER_PAGE;
		sales_all($trans_type, $from);
	}
});
// ------------------------------- Sales Order Entry -------------------------------

// ------------------------------- CFDI -------------------------------
// Get branches
$rest->get('/cfdi/branches/', function() use ($rest){
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/cfdi.inc");

	$page	= $req->get("page");

	if ($page == null) {
		cfdi_branches_all();
	} else {
		// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
		$from = --$page * RESULTS_PER_PAGE;
		cfdi_branches_all($from);
	}
});
// Get series
$rest->get('/cfdi/series/:branch_key', function($branch_key) use ($rest){
	global $path_to_root, $req;
	include_once ($path_to_root . "/modules/api/cfdi.inc");
	cfdi_series_all($branch_key);
});
// Insert Sales
$rest->post('/cfdi/sales/', function() use ($rest){
	global $path_to_root;
	include_once ($path_to_root . "/modules/api/cfdi.inc");
	cfdi_sales_add();
});
// Cancel Sales
$rest->delete('/cfdi/sales/:branch_key/:uuid', function($branch_key, $uuid) use ($rest) {
	global $path_to_root;
	include_once($path_to_root . "/modules/api/cfdi.inc");
	cfdi_sales_cancel($branch_key, $uuid);
});
// ------------------------------- CFDI -------------------------------

// Init API
$rest->run();

?>