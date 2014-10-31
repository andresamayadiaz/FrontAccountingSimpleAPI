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

--> 6-Sept-2014
- Several bug fixes and additions Thanks to Cambell Prince
***********************************************/
ini_set('html_errors', false);
ini_set('xdebug.show_exception_trace', 0);
// ini_set('xdebug.auto_trace', 2);

include_once ('config_api.php');

global $security_areas, $security_groups, $security_headings, $path_to_root, $db, $db_connections;

$page_security = 'SA_API';

include_once (API_ROOT . "/session-custom.inc");
include_once (API_ROOT . "/vendor/autoload.php");

include_once (API_ROOT . "/util.php");

include_once (FA_ROOT . "/includes/date_functions.inc");
include_once (FA_ROOT . "/includes/data_checks.inc");

// echo "sales quote => ".ST_SALESQUOTE;
// echo "sales order => ".ST_SALESORDER;
// echo "sales invoice => ".ST_SALESINVOICE;
// echo "cust delivery => ".ST_CUSTDELIVERY;
// echo "cust credit => ".ST_CUSTCREDIT;

$rest = new \Slim\Slim(array(
	'log.enabled' => true,
	'mode' => 'debug',
	'debug' => true
));
$rest->setName('SASYS');

// API Login Hook
api_login();

$req = $rest->request();

define("RESULTS_PER_PAGE", 2);

// API Routes
// ------------------------------- Items -------------------------------
$rest->group('/inventory', function () use($rest)
{
	// Get Items
	$rest->get('/', function () use($rest)
	{

		global $req;
		include_once (API_ROOT . "/inventory.inc");

		$page = $req->get("page");

		if ($page == null) {
			inventory_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			inventory_all($from);
		}
	});
	// Get Specific Item by Stock Id
	$rest->get('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/inventory.inc");
		inventory_get($id);
	});
	// Add Item
	$rest->post('/', function () use($rest)
	{
		include_once (API_ROOT . "/inventory.inc");
		inventory_add();
	});
	// Edit Specific Item
	$rest->put('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/inventory.inc");
		inventory_edit($id);
	});
	// Delete Specific Item
	$rest->delete('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/inventory.inc");
		inventory_delete($id);
	});
});
// ------------------------------- Items -------------------------------

// ------------------------------- Inventory Movements -------------------------------
// Get Inventory Movement Types
$rest->get('/movementtypes/', function () use($rest)
{
	include_once (API_ROOT . "/inventory.inc");
	inventory_movementstype_all();
});
// ------------------------------- Inventory Movements -------------------------------

// ------------------------------- Inventory Locations -------------------------------
$rest->group('/locations', function () use($rest)
{
	// Get Locations
	$rest->get('/', function () use($rest)
	{
		include_once (API_ROOT . "/inventory.inc");
		inventory_locations_all();
	});

	// Add Location, added by Richard Vinke
	$rest->post('/', function () use($rest)
	{
		include_once (API_ROOT . "/inventory.inc");
		inventory_location_add();
	});
});
// ------------------------------- Inventory Locations -------------------------------

// ------------------------------- Stock Adjustments -------------------------------
// Add Stock Adjustment
$rest->post('/stock/', function () use($rest)
{
	include_once (API_ROOT . "/inventory.inc");
	stock_adjustment_add();
});
// ------------------------------- Stock Adjustments -------------------------------

// ------------------------------- Item Categories -------------------------------
$rest->group('/category', function () use($rest)
{
	// Get Items Categories
	$rest->get('/', function () use($rest)
	{
		global $req;
		include_once (API_ROOT . "/category.inc");

		$page = $req->get("page");

		if ($page == null) {
			category_all(null);
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			category_all($from);
		}
	});
	// Get Specific Item Category
	$rest->get('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/category.inc");
		category_get($id);
	});
	// Add Item Category
	$rest->post('/', function () use($rest)
	{
		include_once (API_ROOT . "/category.inc");
		category_add();
	});
	// Edit Item Category
	$rest->put('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/category.inc");
		category_edit($id);
	});
	// Delete Item Category
	$rest->delete('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/category.inc");
		category_delete($id);
	});
});
// ------------------------------- Item Categories -------------------------------

// ------------------------------- Tax Types -------------------------------
// Tax Types
// Get All Item Tax Types
$rest->get('/taxtypes/', function () use($rest)
{
	global $req;
	include_once (API_ROOT . "/taxtypes.inc");

	$page = $req->get("page");

	if ($page == null) {
		taxtypes_all();
	} else {
		// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
		$from = -- $page * RESULTS_PER_PAGE;
		taxtypes_all($from);
	}
});
// ------------------------------- Tax Types -------------------------------

// ------------------------------- Tax Groups -------------------------------
// Tax Groups
// Get All Tax Groups
$rest->get('/taxgroups/', function () use($rest)
{
	global $req;
	include_once (API_ROOT . "/taxgroups.inc");

	$page = $req->get("page");

	if ($page == null) {
		taxgroups_all();
	} else {
		// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
		$from = -- $page * RESULTS_PER_PAGE;
		taxgroups_all($from);
	}
});
// ------------------------------- Tax Groups -------------------------------

// ------------------------------- Customers -------------------------------
$rest->group('/customers', function () use($rest)
{
	// Get Customer General Info
	$rest->get('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/customers.inc");
		customer_get($id);
	});
	// All Customers
	$rest->get('/', function () use($rest)
	{
		global $req;
		include_once (API_ROOT . "/customers.inc");

		$page = $req->get("page");

		if ($page == null) {
			customer_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			customer_all($from);
		}
	});
	// Add Customer
	$rest->post('/', function () use($rest)
	{
		include_once (API_ROOT . "/customers.inc");
		customer_add();
	});
	// Edit Customer
	$rest->put('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/customers.inc");
		customer_edit($id);
	});
	// Delete Customer
	$rest->delete('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/customers.inc");
		customer_delete($id);
	});
	// Get Customer Branches
	$rest->get('/:id/branches/', function ($id) use($rest)
	{
		include_once (API_ROOT . "/customers.inc");
		customer_branches_get($id);
	});
});
// ------------------------------- Customers -------------------------------

// ------------------------------- Suppliers -------------------------------
$rest->group('/suppliers', function () use($rest)
{
	// All Suppliers
	$rest->get('/', function () use($rest)
	{
		global $req;
		include_once (API_ROOT . "/suppliers.inc");

		$page = $req->get("page");

		supplier_all($page);
	});
	// Get Supplier General Info
	$rest->get('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/suppliers.inc");
		supplier_get($id);
	});
	// Add Supplier
	$rest->post('/', function () use($rest)
	{
		include_once (API_ROOT . "/suppliers.inc");
		supplier_add();
	});
	// Edit Supplier
	$rest->put('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/suppliers.inc");
		supplier_edit($id);
	});
	// Delete Supplier
	$rest->delete('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/suppliers.inc");
		supplier_delete($id);
	});
	// Get Supplier Contacts
	$rest->get('/:id/contacts/', function ($id) use($rest)
	{
		include_once (API_ROOT . "/suppliers.inc");
		supplier_contacts_get($id);
	});
});
// ------------------------------- Suppliers -------------------------------

// ------------------------------- Bank Accounts -------------------------------
$rest->group('/bankaccounts', function () use($rest)
{
	// Get All Bank Accounts
	$rest->get('/', function () use($rest)
	{
		global $req;
		include_once (API_ROOT . "/bankaccounts.inc");

		$page = $req->get("page");

		if ($page == null) {
			bankaccounts_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			bankaccounts_all($from);
		}
	});
	// Get Specific Bank Account
	$rest->get('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/bankaccounts.inc");
		bankaccounts_get($id);
	});
});
// ------------------------------- Bank Accounts -------------------------------

// ------------------------------- GL Accounts -------------------------------
$rest->group('/glaccounts', function () use($rest)
{
	// Get GL Accounts
	$rest->get('/', function () use($rest)
	{
		global $req;
		include_once (API_ROOT . "/glaccounts.inc");

		$page = $req->get("page");

		if ($page == null) {
			glaccounts_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			glaccounts_all($from);
		}
	});
	// Get Specific GL Account
	$rest->get('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/glaccounts.inc");
		glaccounts_get($id);
	});
});
// Get GL Account Types
$rest->get('/glaccounttypes/', function () use($rest)
{
	include_once (API_ROOT . "/glaccounts.inc");
	glaccounttypes_all();
});
// ------------------------------- GL Accounts -------------------------------

// ------------------------------- Currencies -------------------------------
$rest->group('/currencies', function () use($rest)
{
	// Get All Currencies
	$rest->get('/', function () use($rest)
	{
		global $req;
		include_once (API_ROOT . "/currencies.inc");

		$page = $req->get("page");

		if ($page == null) {
			currencies_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			currencies_all($from);
		}
	});
	// Get Specific Currency
	$rest->get('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/currencies.inc");
		currencies_get($id);
	});
});
// Get Last Exchange Rate
$rest->get('/exrates/:curr_abrev', function ($curr_abrev) use($rest)
{
	include_once (API_ROOT . "/currencies.inc");
	currencies_last_exrate($curr_abrev);
});
// ------------------------------- Currencies -------------------------------

// ------------------------------- Inventory Costs -------------------------------
$rest->group('/itemcosts', function () use($rest)
{
	// Get Item Cost
	$rest->get('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/items.inc");
		itemcosts_get($id);
	});
	// Update Item Cost
	$rest->put('/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/items.inc");
		itemcosts_update($id);
	});
});
// ------------------------------- Inventory Costs -------------------------------

// ------------------------------- Assets -------------------------------
// Fixed Assets
function assets_supported()
{
	global $path_to_root;
	return file_exists($path_to_root . '/modules/asset_register');
}
if (assets_supported()) {
	// Get Fixed Asset
	$rest->get('/assets/:id', function ($id) use($rest)
	{
		include_once (API_ROOT . "/assets.inc");
		assets_get($id);
	});
	// Insert Fixed Asset
	$rest->post('/assets/', function () use($rest)
	{
		include_once (API_ROOT . "/assets.inc");
		assets_add();
	});
	// Get Asset Types
	$rest->get('/assettypes/', function () use($rest)
	{
		global $req;
		include_once (API_ROOT . "/assets.inc");

		$page = $req->get("page");

		if ($page == null) {
			assettypes_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			assettypes_all($from);
		}
	});
}
// ------------------------------- Assets -------------------------------

// ------------------------------- Sales --------------------------------
$rest->group('/sales', function () use($rest)
{
	// Get Sales Header and Details
	$rest->get('/:trans_no/:trans_type', function ($trans_no, $trans_type) use($rest)
	{
		include_once (API_ROOT . "/sales.inc");
		sales_get($trans_no, $trans_type);
	});
	// Insert Sales
	$rest->post('/', function () use($rest)
	{
		include_once (API_ROOT . "/sales.inc");
		sales_add();
	});
	// Edit Sales
	$rest->put('/:trans_no/:trans_type', function ($trans_no, $trans_type) use($rest)
	{
		include_once (API_ROOT . "/sales.inc");
		sales_edit($trans_no, $trans_type);
	});
	// Cancel Sales
	$rest->delete('/:branch_id/:uuid', function ($branch_id, $uuid) use($rest)
	{
		include_once (API_ROOT . "/sales.inc");
		sales_cancel($branch_id, $uuid);
	});
	// All Sales
	$rest->get('/:trans_type/', function ($trans_type) use($rest)
	{
		global $req;
		include_once (API_ROOT . "/sales.inc");

		$page = $req->get("page");

		if ($page == null) {
			sales_all($trans_type);
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			sales_all($trans_type, $from);
		}
	});
});
// ------------------------------- Sales --------------------------------

// Init API
$rest->run();

?>