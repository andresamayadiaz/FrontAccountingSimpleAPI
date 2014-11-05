<?php
/**********************************************
Author: Andres Amaya
Name: Items Category API
Free software under GNU GPL
***********************************************/

$path_to_root = "../..";

include_once($path_to_root . "/inventory/includes/db/items_category_db.inc");

function category_all($from = null) {
	$sql = "SELECT c.*, t.name as tax_name FROM ".TB_PREF."stock_category c, ".TB_PREF."item_tax_types t WHERE c.dflt_tax_type=t.id";
	if ($from !== null) {
		$sql .= "LIMIT " . $from . ", " . RESULTS_PER_PAGE;
	}

	$query = db_query($sql, "error");

	$info = array();

	while($data = db_fetch($query, "error")) {
		$info[] = array('category_id' => $data['category_id'], 'description' => $data['description'], 'dflt_tax_type' => $data['dflt_tax_type'], 'dflt_units' => $data['dflt_units'], 'dflt_mb_flag' => $data['dflt_mb_flag'], 'dflt_sales_act' => $data['dflt_sales_act'], 'dflt_cogs_act' => $data['dflt_cogs_act'], 'dflt_inventory_act' => $data['dflt_inventory_act'], 'dflt_adjustment_act' => $data['dflt_adjustment_act'], 'dflt_assembly_act' => $data['dflt_assembly_act'], 'dflt_no_sale' => $data['dflt_no_sale']);
	}

	api_success_response(json_encode($info));
}

function category_get($id)
{

	$catego = get_item_category($id);
	if(!$catego) $catego = array();
	api_success_response(json_encode($catego));

}

function category_add()
{

	$app = \Slim\Slim::getInstance('SASYS');
	$req = $app->request();
	$info = $req->post();

	// Validate Required Fields
	if(!isset($info['description'])){
		api_error(412, 'Description is required');
	}
	if(!isset($info['tax_type_id'])){
		api_error(412, 'Tax Type is required');
	}
	if(!isset($info['units'])){
		api_error(412, 'Units is required');
	}
	if(!isset($info['mb_flag'])){
		api_error(412, 'MB Flag is required');
	}
	if(!isset($info['sales_account'])){
		api_error(412, 'Sales Account is required');
	}
	if(!isset($info['cogs_account'])){
		api_error(412, 'Cogs Account is required');
	}
	if(!isset($info['adjustment_account'])){
		api_error(412, 'Adjustment Account is required');
	}
	if(!isset($info['assembly_account'])){
		api_error(412, 'Assembly Account is required');
	}
	if(!isset($info['inventory_account'])){
		api_error(412, 'Inventory Account is required');
	}

	/*
	$description, $tax_type_id, $sales_account,
	$cogs_account, $inventory_account, $adjustment_account, $assembly_account,
	$units, $mb_flag, $dim1, $dim2, $no_sale
	*/
	add_item_category($info['description'], $info['tax_type_id'],
		$info['sales_account'],
		$info['cogs_account'],
		$info['inventory_account'],
		$info['adjustment_account'],
		$info['assembly_account'],
		$info['units'],
		$info['mb_flag'],
		0, // dimension 1
		0, // dimension2
		0 // no sale
		);

	$id = db_insert_id();
	$catego = get_item_category($id);

	if($catego != null){
		api_create_response(json_encode($catego));
	}else {
		api_error(500, 'Could Not Save to Database');
	}

}

function category_edit($id){

	$app = \Slim\Slim::getInstance('SASYS');
	$req = $app->request();
	$info = $req->post();

	$catego = get_item_category($id);
	if($catego == null){
		api_error(400, 'Invalid Category ID');
	}

	// Validate Required Fields
	if(!isset($info['description'])){
		api_error(412, 'Description is required');
	}
	if(!isset($info['tax_type_id'])){
		api_error(412, 'Tax Type is required');
	}
	if(!isset($info['units'])){
		api_error(412, 'Units is required');
	}
	if(!isset($info['mb_flag'])){
		api_error(412, 'MB Flag is required');
	}
	if(!isset($info['sales_account'])){
		api_error(412, 'Sales Account is required');
	}
	if(!isset($info['cogs_account'])){
		api_error(412, 'Cogs Account is required');
	}
	if(!isset($info['adjustment_account'])){
		api_error(412, 'Adjustment Account is required');
	}
	if(!isset($info['assembly_account'])){
		api_error(412, 'Assembly Account is required');
	}
	if(!isset($info['inventory_account'])){
		api_error(412, 'Inventory Account is required');
	}

	/*
	$id, $description, $tax_type_id,
	$sales_account, $cogs_account, $inventory_account, $adjustment_account,
	$assembly_account, $units, $mb_flag, $dim1, $dim2, $no_sale
	*/
	update_item_category($id, $info['description'], $info['tax_type_id'],
		$info['sales_account'],
		$info['cogs_account'],
		$info['inventory_account'],
		$info['adjustment_account'],
		$info['assembly_account'],
		$info['units'],
		$info['mb_flag'],
		0, // dimension 1
		0, // dimension2
		0 // no sale
		);

	api_success_response("Category has been updated");

}

function category_delete($id){

	$app = \Slim\Slim::getInstance('SASYS');
	$req = $app->request();
	$info = $req->post();

	$catego = get_item_category($id);
	if($catego == null){
		api_error(400, 'Invalid Category ID');
	}

	delete_item_category($id);

	$catego = get_item_category($id);

	if($catego != null){
		api_error(500, 'Could Not Delete from Database: ');
	}else {
		api_success_response("Category has been deleted");
	}

}

?>