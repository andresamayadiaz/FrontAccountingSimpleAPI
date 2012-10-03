<?php
/**********************************************
Author: Andres Amaya
Name: Inventory API
Free software under GNU GPL
***********************************************/

$path_to_root="../..";

include_once($path_to_root . "/modules/api/api.php");

include_once($path_to_root . "/inventory/includes/inventory_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_codes_db.inc");
include_once($path_to_root . "/dimensions/includes/dimensions_db.inc");

$data = null;
$data = api_initAPI();

switch($data['action']){
	
	case 'list':
		all();
		break;
	case 'get':
		get($data['info']);
		break;
	case 'add':
		add($data['info']);
		break;
	case 'edit':
		edit($data['info']);
		break;
	case 'delete':
		delete($data['info']);
		break;
	default:
		api_sendError('404', 'Invalid Action', 'Action Not Found');
		break;
}

function all()
{

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

}

function get($info)
{
	
	if(!isset($info['stock_id'])){
		api_sendError('400', 'Invalid Stock Id', 'Stock Id Not Found');
	}
	
	$id = $info['stock_id'];
	$item = get_item($id);
	echo json_encode($item);

}

function add($info)
{
	
	// Validate Required Fields
	if(!isset($info['stock_id'])){
		api_sendError(412, 'Stock Id is required', 'Stock Id is required');
	}
	if(!isset($info['description'])){
		api_sendError(412, 'Description is required', 'Description is required');
	}
	if(!isset($info['long_description'])){
		$info['long_description'] = $info['description'];
	}
	if(!isset($info['category_id'])){
		api_sendError(412, 'Category Id is required', 'Category Id is required');
	}
	if(!isset($info['tax_type_id'])){
		api_sendError(412, 'Tax Type is required', 'Tax Type is required');
	}
	if(!isset($info['units'])){
		api_sendError(412, 'Units is required', 'Units is required');
	}
	if(!isset($info['mb_flag'])){
		api_sendError(412, 'MB Flag is required', 'MB Flag is required');
	}
	if(!isset($info['sales_account'])){
		api_sendError(412, 'Sales Account is required', 'Sales Account is required');
	}
	if(!isset($info['cogs_account'])){
		api_sendError(412, 'Cogs Account is required', 'Cogs Accoount is required');
	}
	if(!isset($info['adjustment_account'])){
		api_sendError(412, 'Adjustment Account is required', 'Adjustment Account is required');
	}
	if(!isset($info['assembly_account'])){
		api_sendError(412, 'Assembly Account is required', 'Assembly Account is required');
	}
	
	// TODO Validate Stock Id is Unique
	
	/*
	$stock_id, $description, $long_description, $category_id, 
	$tax_type_id, $units, $mb_flag,	$sales_account, $inventory_account, 
	$cogs_account, $adjustment_account,	$assembly_account, $dimension_id, 
	$dimension2_id, $no_sale, $editable
	*/
	add_item($info['stock_id'], $info['description'], $info['long_description'], $info['category_id'], $info['tax_type_id'], 
		$info['units'],
		$info['mb_flag'],
		$info['sales_account'],
		$info['inventory_account'],
		$info['cogs_account'], 
		$info['adjustment_account'],
		$info['assembly_account'],
		0, // dimension 1
		0, // dimension2
		0, // no sale
		1 // editable
		);
	
	$itm = get_item($info['stock_id']);
	
	if($itm != null){
		api_sendSuccess("Item has been added");
	}else {
		api_sendError(500, 'Could Not Save to Database', 'Could Not Save to Database');
	}
	
}

function edit()
{

	if(!isset($info['stock_id'])){
		api_sendError('400', 'Invalid Stock Id', 'Stock Id Not Found');
	}
	
	// Validate Required Fields
	if(!isset($info['stock_id'])){
		api_sendError(412, 'Stock Id is required', 'Stock Id is required');
	}
	if(!isset($info['description'])){
		api_sendError(412, 'Description is required', 'Description is required');
	}
	if(!isset($info['long_description'])){
		$info['long_description'] = $info['description'];
	}
	if(!isset($info['category_id'])){
		api_sendError(412, 'Category Id is required', 'Category Id is required');
	}
	if(!isset($info['tax_type_id'])){
		api_sendError(412, 'Tax Type is required', 'Tax Type is required');
	}
	if(!isset($info['units'])){
		api_sendError(412, 'Units is required', 'Units is required');
	}
	if(!isset($info['mb_flag'])){
		api_sendError(412, 'MB Flag is required', 'MB Flag is required');
	}
	if(!isset($info['sales_account'])){
		api_sendError(412, 'Sales Account is required', 'Sales Account is required');
	}
	if(!isset($info['cogs_account'])){
		api_sendError(412, 'Cogs Account is required', 'Cogs Accoount is required');
	}
	if(!isset($info['adjustment_account'])){
		api_sendError(412, 'Adjustment Account is required', 'Adjustment Account is required');
	}
	if(!isset($info['assembly_account'])){
		api_sendError(412, 'Assembly Account is required', 'Assembly Account is required');
	}
	
	/*
	$stock_id, $description, $long_description, $category_id, 
	$tax_type_id, $units='', $mb_flag='', $sales_account, $inventory_account, 
	$cogs_account, 	$adjustment_account, $assembly_account, $dimension_id, 
	$dimension2_id, $no_sale, $editable
	*/
	update_item($info['stock_id'], $info['description'], $info['long_description'], $info['category_id'], $info['tax_type_id'], 
		$info['units'],
		$info['mb_flag'],
		$info['sales_account'],
		$info['inventory_account'],
		$info['cogs_account'], 
		$info['adjustment_account'],
		$info['assembly_account'],
		0, // dimension 1
		0, // dimension2
		0, // no sale
		1 // editable
		);
	
	api_sendSuccess("Item has been updated");

}

function delete()
{

	if(!isset($info['stock_id'])){
		api_sendError('400', 'Invalid ID', 'ID Not Found');
	}
	
	$id = $info['stock_id'];
	delete_item($id);
	
	$itm = get_item($info['stock_id']);
	
	if($itm != null){
		api_sendError(500, 'Could Not Delete from Database', 'Could Not Delete from Database');
	}else {
		api_sendSuccess("Item has been deleted");
	}

}

?>