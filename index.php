<?php
/**********************************************
Author: Andres Amaya
Name: Inventory REST API
Free software under GNU GPL
***********************************************/
set_include_path(dirname(__FILE__) . '/../' . PATH_SEPARATOR . get_include_path());

global $security_areas, $security_groups, $security_headings, $path_to_root, $db, $db_connections;

$path_to_root = "../..";

$page_security = 'SA_API';
ini_set('xdebug.auto_trace', 2);
include_once ($path_to_root . "/modules/api/session-custom.inc");
include_once ($path_to_root . "/modules/api/Slim/Slim.php");
\Slim\Slim::registerAutoloader();

include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");

include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_codes_db.inc");
include_once($path_to_root . "/inventory/includes/db/movement_types_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_locations_db.inc");
include_once($path_to_root . "/includes/ui/items_cart.inc");

/*
add_access_extensions();
$app = & $_SESSION["App"];
if (isset ($_GET['application']))
	$app->selected_application = $_GET['application'];
*/

$rest = new \Slim\Slim(array(
    	'debug' => true
    ));

$db = set_global_connection($company);
$succeed = $_SESSION["wa_current_user"]->login(0,
				'admin', 'ceis12345');

/**********************************************
Model: Inventory
Description: Inventory REST API
***********************************************/
$rest->get('/inventory', function () {
    $items = get_items();
	$ret = array();
	while($itm = db_fetch($items)){
		
		$ret[] = array(
			'stock_id' => $itm['stock_id'],
			'category_id' => $itm['category_id'],
			'tax_type_id' => $itm['tax_type_id'],
			'description' => $itm['description'],
			'long_description' => $itm['long_description'],
			'units' => $itm['units'],
			'mb_flag' => $itm['mb_flag'],
			'sales_account' => $itm['sales_account'],
			'cogs_account' => $itm['cogs_account'],
			'inventory_account' => $itm['inventory_account'],
			'sales_account' => $itm['sales_account'],
			'adjustment_account' => $itm['adjustment_account'],
			'actual_cost' => $itm['actual_cost'],
			'material_cost' => $itm['material_cost'],
			'labour_cost' => $itm['labour_cost'],
			'overhead_cost' => $itm['overhead_cost'],
			'inactive' => $itm['inactive'],
			'no_sale' => $itm['no_sale']
		);
		
	}
	echo json_encode($ret);
});

$rest->run();

?>