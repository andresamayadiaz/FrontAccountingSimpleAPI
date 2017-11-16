<?php
namespace FAAPI;

$path_to_root = "../..";

include_once($path_to_root . "/includes/db/inventory_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_trans_db.inc");

class InventoryCosts
{
	// Get Specific Item by Stock Id
	public function getById($rest, $id)
	{
		$cost = get_unit_cost($id);
		api_success_response(json_encode( array('stock_id' => $id, 'unit_cost' => $cost) ));
	}
	// Edit Specific Item
	public function put($rest, $id)
	{
		$req = $rest->request();
		$info = $req->post();

		$old_cost = get_unit_cost($id);

		$update_no = stock_cost_update(
			$id,
			$info['material_cost'],
			$info['labour_cost'],
			$info['overhead_cost'],
			$old_cost,
			'', // Ref lines
			''  // Memo
		);

		api_success_response(json_encode( array('stock_id' => $id)));
	}
}