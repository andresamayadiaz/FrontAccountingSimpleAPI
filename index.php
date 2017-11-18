<?php
use FAAPI\Inventory;
use FAAPI\InventoryLocations;
use FAAPI\Category;
use FAAPI\TaxTypes;
use FAAPI\TaxGroups;
use FAAPI\Customers;
use FAAPI\Suppliers;
use FAAPI\BankAccounts;
use FAAPI\GLAccounts;
use FAAPI\Currencies;
use FAAPI\InventoryCosts;
use FAAPI\Sales;
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
$rest->container->singleton('inventory', function() {
	return new Inventory();
});
$rest->group('/inventory', function () use($rest)
{
	// Get Items
	$rest->get('/', function() use($rest) {
		$rest->inventory->get($rest);
	});
	// Get Specific Item by Stock Id
	$rest->get('/:id', function ($id) use($rest)
	{
		$rest->inventory->getById($rest, $id);
	});
	// Add Item
	$rest->post('/', function () use($rest)
	{
		$rest->inventory->post($rest);
	});
	// Edit Specific Item
	$rest->put('/:id', function ($id) use($rest)
	{
		$rest->inventory->put($rest, $id);
	});
	// Delete Specific Item
	$rest->delete('/:id', function ($id) use($rest)
	{
		$rest->inventory->delete($rest, $id);
	});
});
// ------------------------------- Items -------------------------------

// ------------------------------- Inventory Locations -------------------------------
$rest->container->singleton('inventoryLocations', function() {
	return new InventoryLocations();
});
$rest->group('/locations', function () use($rest)
{
	// Get Locations
	$rest->get('/', function () use($rest)
	{
		$rest->inventoryLocations->get($rest);
	});

	// Add Location, added by Richard Vinke
	$rest->post('/', function () use($rest)
	{
		$rest->inventoryLocations->post($rest);
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
$rest->container->singleton('category', function() {
	return new Category();
});
$rest->group('/category', function () use($rest)
{
	// Get Items Categories
	$rest->get('/', function () use($rest)
	{
		$rest->category->get($rest);
	});
	// Get Specific Item Category
	$rest->get('/:id', function ($id) use($rest)
	{
		$rest->category->getById($rest, $id);
	});
	// Add Item Category
	$rest->post('/', function () use($rest)
	{
		$rest->category->post($rest);
	});
	// Edit Item Category
	$rest->put('/:id', function ($id) use($rest)
	{
		$rest->category->put($rest, $id);
	});
	// Delete Item Category
	$rest->delete('/:id', function ($id) use($rest)
	{
		$rest->category->delete($rest, $id);
	});
});
// ------------------------------- Item Categories -------------------------------

// ------------------------------- Tax Types -------------------------------
// Tax Types
$rest->container->singleton('taxTypes', function() {
	return new TaxTypes();
});

// Get All Item Tax Types
$rest->get('/taxtypes/', function () use($rest)
{
	$rest->taxTypes->get($rest);
});
// ------------------------------- Tax Types -------------------------------

// ------------------------------- Tax Groups -------------------------------
// Tax Groups
$rest->container->singleton('taxGroups', function() {
	return new TaxGroups();
});

// Get All Tax Groups
$rest->get('/taxgroups/', function () use($rest)
{
	$rest->taxGroups->get($rest);
});
// ------------------------------- Tax Groups -------------------------------

// ------------------------------- Customers -------------------------------
$rest->container->singleton('customers', function() {
	return new Customers();
});
$rest->group('/customers', function () use($rest)
{
	// Get Customer General Info
	$rest->get('/:id', function ($id) use($rest)
	{
		$rest->customers->getById($rest, $id);
	});
	// All Customers
	$rest->get('/', function () use($rest)
	{
		$rest->customers->get($rest);
	});
	// Add Customer
	$rest->post('/', function () use($rest)
	{
		$rest->customers->post($rest);
	});
	// Edit Customer
	$rest->put('/:id', function ($id) use($rest)
	{
		$rest->customers->put($rest, $id);
	});
	// Delete Customer
	$rest->delete('/:id', function ($id) use($rest)
	{
		$rest->customers->delete($rest, $id);
	});
	// Get Customer Branches
	$rest->get('/:id/branches/', function ($id) use($rest)
	{
		$rest->customers->getBranches($rest, $id);
	});
});
// ------------------------------- Customers -------------------------------

// ------------------------------- Suppliers -------------------------------
$rest->container->singleton('suppliers', function() {
	return new Suppliers();
});
$rest->group('/suppliers', function () use($rest)
{
	// All Suppliers
	$rest->get('/', function () use($rest)
	{
		$rest->suppliers->get($rest);
	});
	// Get Supplier General Info
	$rest->get('/:id', function ($id) use($rest)
	{
		$rest->suppliers->getById($rest, $id);
	});
	// Add Supplier
	$rest->post('/', function () use($rest)
	{
		$rest->suppliers->post($rest);
	});
	// Edit Supplier
	$rest->put('/:id', function ($id) use($rest)
	{
		$rest->suppliers->put($rest, $id);
	});
	// Delete Supplier
	$rest->delete('/:id', function ($id) use($rest)
	{
		$rest->suppliers->delete($rest, $id);
	});
	// Get Supplier Contacts
	$rest->get('/:id/contacts/', function ($id) use($rest)
	{
		$rest->suppliers->getContacts($rest, $id);
	});
});
// ------------------------------- Suppliers -------------------------------

// ------------------------------- Bank Accounts -------------------------------
$rest->container->singleton('bankAccounts', function() {
	return new BankAccounts();
});
$rest->group('/bankaccounts', function () use($rest)
{
	// Get All Bank Accounts
	$rest->get('/', function () use($rest)
	{
		$rest->bankAccounts->get($rest);
	});
	// Get Specific Bank Account
	$rest->get('/:id', function ($id) use($rest)
	{
		$rest->bankAccounts->getById($rest, $id);
	});
});
// ------------------------------- Bank Accounts -------------------------------

// ------------------------------- GL Accounts -------------------------------
$rest->container->singleton('glAccounts', function() {
	return new GLAccounts();
});
$rest->group('/glaccounts', function () use($rest)
{
	// Get GL Accounts
	$rest->get('/', function () use($rest)
	{
		$rest->glAccounts->get($rest);
	});
	// Get Specific GL Account
	$rest->get('/:id', function ($id) use($rest)
	{
		$rest->glAccounts->getById($rest, $id);
	});
});
// Get GL Account Types
$rest->get('/glaccounttypes/', function () use($rest)
{
	$rest->glAccounts->getTypes($rest);
});
// ------------------------------- GL Accounts -------------------------------

// ------------------------------- Currencies -------------------------------
$rest->container->singleton('currencies', function() {
	return new Currencies();
});
$rest->group('/currencies', function () use($rest)
{
	// Get All Currencies
	$rest->get('/', function () use($rest)
	{
		$rest->currencies->get($rest);
	});
	// Get Specific Currency
	$rest->get('/:id', function ($id) use($rest)
	{
		$rest->currencies->getById($rest, $id);
	});
});
// Get Last Exchange Rate
$rest->get('/exrates/:curr_abrev', function ($curr_abrev) use($rest)
{
	$rest->currencies->getLastExchangeRate($rest, $curr_abrev);
});
// ------------------------------- Currencies -------------------------------

// ------------------------------- Inventory Costs -------------------------------
$rest->container->singleton('inventoryCosts', function() {
	return new InventoryCosts();
});
$rest->group('/itemcosts', function () use($rest)
{
	// Get Item Cost
	$rest->get('/:id', function ($id) use($rest)
	{
		$rest->inventoryCosts->getById($rest, $id);
	});
	// Update Item Cost
	$rest->put('/:id', function ($id) use($rest)
	{
		$rest->inventoryCosts->put($rest, $id);
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
$rest->container->singleton('sales', function() {
	return new Sales();
});
$rest->group('/sales', function () use($rest)
{
	// Get Sales Header and Details
	$rest->get('/:trans_no/:trans_type', function ($trans_no, $trans_type) use($rest)
	{
		$rest->sales->getById($rest, $trans_no, $trans_type);
	});
	// Insert Sales
	$rest->post('/', function () use($rest)
	{
		$rest->sales->post($rest);
	});
	// Edit Sales
	$rest->put('/:trans_no/:trans_type', function ($trans_no, $trans_type) use($rest)
	{
		$rest->sales->put($rest, $trans_no, $trans_type);
	});
	// Cancel Sales
	$rest->delete('/:branch_id/:uuid', function ($branch_id, $uuid) use($rest)
	{
		$rest->sales->delete($rest, $branch_id, $uuid);
	});
	// All Sales
	$rest->get('/:trans_type/', function ($trans_type) use($rest)
	{
		$rest->sales->get($rest, $trans_type);
	});
});
// ------------------------------- Sales --------------------------------

// Init API
$rest->run();

?>