<?php
namespace FAAPI;

$path_to_root = "../..";

include_once ($path_to_root . "/inventory/includes/inventory_db.inc");
include_once ($path_to_root . "/inventory/includes/db/items_codes_db.inc");
include_once ($path_to_root . "/inventory/includes/db/items_locations_db.inc");
include_once ($path_to_root . "/gl/includes/gl_db.inc");
include_once ($path_to_root . "/includes/ui/items_cart.inc");

include_once (API_ROOT . "/inventory.inc");

class Inventory
{
	// Get Items
	public function get($rest)
	{
		$req = $rest->request();

		$page = $req->get("page");

		if ($page == null) {
			$this->inventory_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			$this->inventory_all($from);
		}
	}

	// Get Specific Item by Stock Id
	public function getById($rest, $id)
	{
		$this->inventory_get($id);
	}
	// Add Item
	public function post($rest)
	{
		$this->inventory_add();
	}
	// Edit Specific Item
	public function put($rest, $id)
	{
		$this->inventory_edit($id);
	}
	// Delete Specific Item
	public function delete($rest, $id)
	{
		$this->inventory_delete($id);
	}

	function inventory_all($from = null)
	{
		if ($from == null)
			$from = 0;

		$sql = "SELECT * FROM " . TB_PREF . "stock_master LIMIT " . $from . ", " . RESULTS_PER_PAGE;

		$query = db_query($sql, "error");

		$info = array();

		while ($data = db_fetch($query, "error")) {
			$info[] = array(
				'stock_id' => $data['stock_id'],
				'category_id' => $data['category_id'],
				'tax_type_id' => $data['tax_type_id'],
				'description' => $data['description'],
				'long_description' => $data['long_description'],
				'units' => $data['units'],
				'mb_flag' => $data['mb_flag'],
				'sales_account' => $data['sales_account'],
				'cogs_account' => $data['cogs_account'],
				'inventory_account' => $data['inventory_account'],
				'adjustment_account' => $data['adjustment_account'],
				'wip_account' => $data['wip_account'],
				'purchase_cost' => $data['purchase_cost'],
				'last_cost' => $data['last_cost'],
				'material_cost' => $data['material_cost'],
				'labour_cost' => $data['labour_cost'],
				'overhead_cost' => $data['overhead_cost'],
				'inactive' => $data['inactive'],
				'no_sale' => $data['no_sale'],
				'no_purchase' => $data['no_purchase']
			);
		}

		api_success_response(json_encode($info));
	}

	function inventory_get($id)
	{
		$item = get_item($id);
		if (! $item)
			$item = array();
		api_success_response(json_encode($item));
	}

	function inventory_add()
	{
		$app = \Slim\Slim::getInstance('SASYS');
		$req = $app->request();
		$info = $req->post();

		// Validate Required Fields
		if (! isset($info['stock_id'])) {
			api_error(412, 'Stock Id is required');
		}
		if (! isset($info['description'])) {
			api_error(412, 'Description is required');
		}
		if (! isset($info['long_description'])) {
			$info['long_description'] = $info['description'];
		}
		if (! isset($info['category_id'])) {
			api_error(412, 'Category Id is required');
		}
		if (! isset($info['tax_type_id'])) {
			api_error(412, 'Tax Type is required');
		}
		if (! isset($info['units'])) {
			api_error(412, 'Units is required');
		}
		if (! isset($info['mb_flag'])) {
			api_error(412, 'MB Flag is required');
		}
		if (! isset($info['sales_account'])) {
			api_error(412, 'Sales Account is required');
		}
		if (! isset($info['cogs_account'])) {
			api_error(412, 'Cogs Account is required');
		}
		if (! isset($info['adjustment_account'])) {
			api_error(412, 'Adjustment Account is required');
		}
		if (! isset($info['wip_account'])) {
			api_error(412, 'Assembly Account is required');
		}

		// TODO Validate Stock Id is Unique

		/*
		 * $stock_id, $description, $long_description, $category_id, $tax_type_id, $units, $mb_flag,	$sales_account,
		 * $inventory_account, $cogs_account, $adjustment_account,	$wip_account, $dimension_id, $dimension2_id,
		 * $no_sale, $editable
		 */
		add_item(
			$info['stock_id'],
			$info['description'],
			$info['long_description'],
			$info['category_id'],
			$info['tax_type_id'],
			$info['units'],
			$info['mb_flag'],
			$info['sales_account'],
			$info['inventory_account'],
			$info['cogs_account'],
			$info['adjustment_account'],
			$info['wip_account'],
			0, // dimension 1
			0, // dimension2
			0, // no sale
			1, // editable
			0  // no purchase
		);

		$itm = get_item($info['stock_id']);

		if ($itm != null) {
			api_create_response(json_encode($itm));
		} else {
			api_error(500, 'Could Not Save to Database');
		}
	}

	function inventory_edit($id)
	{
		$app = \Slim\Slim::getInstance('SASYS');
		$req = $app->request();
		$info = $req->post();

		$itm = get_item($id);
		if ($itm == null) {
			api_error(400, 'Invalid Stock Id');
		}

		// Validate Required Fields
		if (! isset($info['description'])) {
			api_error(412, 'Description is required');
		}
		if (! isset($info['long_description'])) {
			$info['long_description'] = $info['description'];
		}
		if (! isset($info['category_id'])) {
			api_error(412, 'Category Id is required');
		}
		if (! isset($info['tax_type_id'])) {
			api_error(412, 'Tax Type is required');
		}
		if (! isset($info['units'])) {
			api_error(412, 'Units is required');
		}
		if (! isset($info['mb_flag'])) {
			api_error(412, 'MB Flag is required');
		}
		if (! isset($info['sales_account'])) {
			api_error(412, 'Sales Account is required');
		}
		if (! isset($info['cogs_account'])) {
			api_error(412, 'Cogs Account is required');
		}
		if (! isset($info['adjustment_account'])) {
			api_error(412, 'Adjustment Account is required');
		}
		if (! isset($info['wip_account'])) {
			api_error(412, 'WIP Account is required');
		}

		/*
		 * $stock_id, $description, $long_description, $category_id, $tax_type_id, $units='', $mb_flag='',
		 * $sales_account, $inventory_account, $cogs_account, 	$adjustment_account, $wip_account, $dimension_id,
		 * $dimension2_id, $no_sale, $editable
		 */
		update_item(
			$info['stock_id'],
			$info['description'],
			$info['long_description'],
			$info['category_id'],
			$info['tax_type_id'],
			$info['units'],
			$info['mb_flag'],
			$info['sales_account'],
			$info['inventory_account'],
			$info['cogs_account'],
			$info['adjustment_account'],
			$info['wip_account'],
			0, // dimension 1
			0, // dimension2
			0, // no sale
			1, // editable
			0  // no purchase
		);

		api_success_response("Item has been updated");
	}

	function inventory_delete($id)
	{
		$app = \Slim\Slim::getInstance('SASYS');
		$req = $app->request();
		$info = $req->post();

		$itm = get_item($id);
		if ($itm == null) {
			api_error(400, 'Invalid Stock Id');
		}

		delete_item($id);

		$itm = get_item($id);

		if ($itm != null) {
			api_error(500, 'Could Not Delete from Database');
		} else {
			api_success_response("Item has been deleted");
		}
	}


}