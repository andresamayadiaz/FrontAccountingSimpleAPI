<?php
namespace FAAPI;

class InventoryCosts
{
	// Get Specific Item by Stock Id
	public function getById($rest, $id)
	{
		include_once (API_ROOT . "/items.inc");
		itemcosts_get($id);
	}
	// Edit Specific Item
	public function put($rest, $id)
	{
		include_once (API_ROOT . "/items.inc");
		itemcosts_update($id);
	}
}