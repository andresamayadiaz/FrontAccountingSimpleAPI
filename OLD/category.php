<?php
/**********************************************
Author: Andres Amaya
Name: Items Category API
Free software under GNU GPL
***********************************************/

$path_to_root="../..";

include_once($path_to_root . "/modules/api/api.php");

include_once($path_to_root . "/inventory/includes/db/items_category_db.inc");

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

function all(){

	$categos = get_item_categories(false);
	$ret = array();
	while($cat = db_fetch($categos)){
		
		$ret[] = array(
			'category_id' => $cat['category_id'],
			'description' => $cat['description'],
			'dflt_tax_type' => $cat['dflt_tax_type'],
			'dflt_units' => $cat['dflt_units'],
			'dflt_mb_flag' => $cat['dflt_mb_flag'],
			'dflt_sales_act' => $cat['dflt_sales_act'],
			'dflt_cogs_act' => $cat['dflt_cogs_act'],
			'dflt_inventory_act' => $cat['dflt_inventory_act'],
			'dflt_adjustment_act' => $cat['dflt_adjustment_act'],
			'dflt_assembly_act' => $cat['dflt_assembly_act'],
			'dflt_no_sale' => $cat['dflt_no_sale']
		);
		
	}
	echo json_encode($ret);
	
}

function get($info){
	
	if(!isset($info['category_id'])){
		api_sendError('400', 'Invalid Category Id', 'Category Id Not Found');
	}
	
	$id = $info['category_id'];
	$catego = get_item_category($id);
	echo json_encode($catego);
	
}

function add($info){
	
	if(isset($info['category_id'])){
		api_sendError('400', 'ID Received', 'Add Action does not support ID field');
	}
	
	// Validate Required Fields
	if(!isset($info['description'])){
		api_sendError(412, 'Description is required', 'Description is required');
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
		api_sendSuccess("Category has been added");
	}else {
		api_sendError(500, 'Could Not Save to Database', 'Could Not Save to Database');
	}
	
}

function edit($info){
	
	if(!isset($info['category_id'])){
		api_sendError('400', 'Invalid Category Id', 'Category Id Not Found');
	}
	
	// Validate Required Fields
	if(!isset($info['description'])){
		api_sendError(412, 'Description is required', 'Description is required');
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
	$id, $description, $tax_type_id, 
	$sales_account, $cogs_account, $inventory_account, $adjustment_account, 
	$assembly_account, $units, $mb_flag, $dim1, $dim2, $no_sale
	*/
	update_item_category($info['category_id'], $info['description'], $info['tax_type_id'], 
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
	
	api_sendSuccess("Category has been updated");
	
}

function delete($info){
	
	if(!isset($info['category_id'])){
		api_sendError('400', 'Invalid Category Id', 'Category Id Not Found');
	}
	
	$id = $info['category_id'];
	delete_item($id);
	
	$catego = get_item_category($id);
	
	if($catego != null){
		api_sendError(500, 'Could Not Delete from Database', 'Could Not Delete from Database');
	}else {
		api_sendSuccess("Category has been deleted");
	}
	
}


?>