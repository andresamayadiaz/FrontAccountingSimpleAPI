<?php
/**********************************************
Author: Andres Amaya
Name: Inventory REST API
Free software under GNU GPL
***********************************************/
$path_to_root = "../..";

include_once ($path_to_root . "/inventory/includes/inventory_db.inc");
include_once ($path_to_root . "/inventory/includes/db/items_codes_db.inc");
include_once ($path_to_root . "/inventory/includes/db/items_locations_db.inc");
include_once ($path_to_root . "/gl/includes/gl_db.inc");
include_once ($path_to_root . "/includes/ui/items_cart.inc");

function stock_adjustment_add($info)
{
	$app = \Slim\Slim::getInstance('SASYS');
	$req = $app->request();
	$info = $req->post();

	// Example
	// $ex = array('stock_id' => 'PLUMA', 'location' => 'DEF', 'date' => today(), 'type' => 1, 'reference' =>
	// '123qwe', 'quantity' => 555, 'standard_cost' => 10, 'increase' => 0, 'memo' => 'Proof of API 2');
	// echo base64_encode(json_encode($ex));
	// print_r($info);

	// Validate Required Fields
	if (! isset($info['stock_id'])) {
		api_error(412, 'Stock Id is required');
	}
	if (! isset($info['location'])) {
		api_error(412, 'Location is required');
	}
	if (! isset($info['date'])) {
		api_error(412, 'Date is required');
	}
	if (! isset($info['reference'])) {
		api_error(412, 'Reference is required');
	}
	if (! isset($info['quantity'])) {
		api_error(412, 'Quantity is required');
	}
	if (! isset($info['standard_cost'])) {
		$info['standard_cost'] = 0;
	}
	if (! isset($info['memo'])) {
		api_error(412, 'Memo is required');
	}

	// Create Adjustment Order
	unset($_SESSION['adj_items']);
	$_SESSION['adj_items'] = new items_cart(ST_INVADJUST);
	$_SESSION['gl_items'] = new items_cart(0);

	$info['date'] = today();
	if (! is_date_in_fiscalyear($info['date']))
		$info['date'] = end_fiscalyear();

	$_SESSION['adj_items']->tran_date = $info['date'];
	$_SESSION['gl_items']->tran_date = $info['date'];
	// This should never happen
	if ($_SESSION['adj_items']->find_cart_item($info['stock_id']))
		api_error(500, 'Item Already Exists In Cart');
	else {
		$_SESSION['adj_items']->add_to_cart(count($_SESSION['adj_items']->line_items), $info['stock_id'], $info['quantity'], $info['standard_cost']);
		// $code_id, $dimension_id, $dimension2_id, $amount, $reference, $description=null
		// TODO Obtain accounts against which to make seats
		// Product Inventory
		$_SESSION['gl_items']->add_gl_item('1510', 0, 0, ($info['quantity'] * $info['standard_cost'] * - 1), $info['memo']);
		// Configured for Absolute, Own
		$_SESSION['gl_items']->add_gl_item('4010', 0, 0, ($info['quantity'] * $info['standard_cost']), $info['memo']);
		$_SESSION['gl_items']->order_id = 0;
		$_SESSION['gl_items']->reference = 666;
	}

	/*
	 * print_r($_SESSION['adj_items']); echo "----------- GL -------"; print_r($_SESSION['gl_items']); echo "------
	 * DEBIT: " . $_SESSION['gl_items']->gl_items_total_debit(); echo "------ CREDIT: " .
	 * $_SESSION['gl_items']->gl_items_total_credit();
	 */
	// Process Order
	$trans_no = add_stock_adjustment($_SESSION['adj_items']->line_items, $info['location'], $info['date'], $info['reference'], $info['memo']);

	$gl_trans_no = write_journal_entries($_SESSION['gl_items'], false);

	new_doc_date($info['date']);
	$_SESSION['adj_items']->clear_items();
	unset($_SESSION['adj_items']);

	$_SESSION['gl_items']->clear_items();
	unset($_SESSION['gl_items']);

	api_create_response("Stock Adjustment has been added");
}
?>