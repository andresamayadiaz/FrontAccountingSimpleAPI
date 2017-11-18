<?php
namespace FAAPI;

$path_to_root = "../..";

include_once ($path_to_root . "/inventory/includes/db/items_category_db.inc");

class Category
{
	// Get Items
	public function get($rest)
	{
		$req = $rest->request();

		$page = $req->get("page");

		if ($page == null) {
			$this->category_all(null);
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			$this->category_all($from);
		}
	}

	// Get Specific Item by Stock Id
	public function getById($rest, $id)
	{
		$catego = get_item_category($id);
		if(!$catego) $catego = array();
		api_success_response(json_encode($catego));
	}
	// Add Item
	public function post($rest)
	{
		$req = $rest->request();
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
		if(!isset($info['wip_account'])){
			api_error(412, 'WIP Account is required');
		}
		if(!isset($info['inventory_account'])){
			api_error(412, 'Inventory Account is required');
		}

		/*
		$description, $tax_type_id, $sales_account,
		$cogs_account, $inventory_account, $adjustment_account, $wip_account,
		$units, $mb_flag, $dim1, $dim2, $no_sale
		*/
		add_item_category($info['description'], $info['tax_type_id'],
			$info['sales_account'],
			$info['cogs_account'],
			$info['inventory_account'],
			$info['adjustment_account'],
			$info['wip_account'],
			$info['units'],
			$info['mb_flag'],
			0, // dimension 1
			0, // dimension2
			0, // no sale
			0  // no purchase
		);

		$id = db_insert_id();
		$catego = get_item_category($id);

		if($catego != null){
			api_create_response(json_encode($catego));
		}else {
			api_error(500, 'Could Not Save to Database');
		}
	}
	// Edit Specific Item
	public function put($rest, $id)
	{
		$req = $rest->request();
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
		if(!isset($info['wip_account'])){
			api_error(412, 'Assembly Account is required');
		}
		if(!isset($info['inventory_account'])){
			api_error(412, 'Inventory Account is required');
		}

		/*
		$id, $description, $tax_type_id,
		$sales_account, $cogs_account, $inventory_account, $adjustment_account,
		$wip_account, $units, $mb_flag, $dim1, $dim2, $no_sale
		*/
		update_item_category($id, $info['description'], $info['tax_type_id'],
			$info['sales_account'],
			$info['cogs_account'],
			$info['inventory_account'],
			$info['adjustment_account'],
			$info['wip_account'],
			$info['units'],
			$info['mb_flag'],
			0, // dimension 1
			0, // dimension2
			0, // no sale
			0  // no purchase
		);

		api_success_response("Category has been updated");
	}
	// Delete Specific Item
	public function delete($rest, $id)
	{
		$req = $rest->request();
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

	private function category_all($from = null)
	{
		$sql = "SELECT c.*, t.name as tax_name FROM "
			. TB_PREF . "stock_category c, "
			. TB_PREF . "item_tax_types t WHERE c.dflt_tax_type=t.id";
		if ($from !== null) {
			$sql .= "LIMIT " . $from . ", " . RESULTS_PER_PAGE;
		}

		$query = db_query($sql, "error");

		$info = array();

		while ($data = db_fetch($query, "error")) {
			$info[] = array(
				'category_id' => $data['category_id'],
				'description' => $data['description'],
				'dflt_tax_type' => $data['dflt_tax_type'],
				'dflt_units' => $data['dflt_units'],
				'dflt_mb_flag' => $data['dflt_mb_flag'],
				'dflt_sales_act' => $data['dflt_sales_act'],
				'dflt_cogs_act' => $data['dflt_cogs_act'],
				'dflt_inventory_act' => $data['dflt_inventory_act'],
				'dflt_adjustment_act' => $data['dflt_adjustment_act'],
				'dflt_wip_act' => $data['dflt_wip_act'],
				'dflt_no_sale' => $data['dflt_no_sale']
			);
		}

		api_success_response(json_encode($info));
	}
}