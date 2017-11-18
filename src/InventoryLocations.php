<?php
namespace FAAPI;

$path_to_root = "../..";

include_once ($path_to_root . "/inventory/includes/inventory_db.inc");
include_once ($path_to_root . "/inventory/includes/db/items_codes_db.inc");
include_once ($path_to_root . "/inventory/includes/db/items_locations_db.inc");
include_once ($path_to_root . "/gl/includes/gl_db.inc");
include_once ($path_to_root . "/includes/ui/items_cart.inc");

class InventoryLocations
{
	// Get Items
	public function get($rest)
	{
		$locations = get_item_locations(false);
		$ret = array();
		while ($loc = db_fetch($locations)) {
			$ret[] = array(
				'loc_code' => $loc['loc_code'],
				'location_name' => $loc['location_name'],
				'delivery_address' => $loc['delivery_address'],
				'phone' => $loc['phone'],
				'phone2' => $loc['phone2'],
				'fax' => $loc['fax'],
				'email' => $loc['email'],
				'contact' => $loc['contact']
			);
		}
		api_success_response(json_encode($ret));
	}

	// Add Item
	public function post($rest)
	{
		// Originally added by Richard Vinke
		$req = $rest->request();
		$info = $req->post();

		// ToDo Check if loc_code already exists

		// Validate Required Fields
		if (! isset($info['loc_code'])) {
			api_error(412, 'Stock Id is required');
		}
		if (! isset($info['location_name'])) {
			api_error(412, 'Stock Id is required');
		}
		if (! isset($info['delivery_address'])) {
			$info['delivery_address'] = '';
		}
		if (! isset($info['phone'])) {
			$info['phone'] = '';
		}
		if (! isset($info['phone2'])) {
			$info['phone2'] = '';
		}
		if (! isset($info['fax'])) {
			$info['fax'] = '';
		}
		if (! isset($info['email'])) {
			$info['email'] = '';
		}
		if (! isset($info['contact'])) {
			$info['contact'] = '';
		}

		add_item_location($info['loc_code'], $info['location_name'], $info['delivery_address'], $info['phone'], $info['phone2'], $info['fax'], $info['email'], $info['contact']);

		$itm = get_item_location($info['loc_code']);

		if ($itm != null) {
			api_create_response(json_encode($itm));
		} else {
			api_error(500, 'Could Not Save to Database');
		}
	}

}