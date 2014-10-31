<?php
namespace FAAPI;

class InventoryLocations
{
	// Get Items
	public function get($rest)
	{
		include_once (API_ROOT . "/inventory.inc");
		inventory_locations_all();

	}

	// Add Item
	public function post($rest)
	{
		include_once (API_ROOT . "/inventory.inc");
		inventory_location_add();
	}

}