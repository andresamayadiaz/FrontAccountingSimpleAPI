<?php
namespace FAAPI;

class Suppliers
{
	// Get Items
	public function get($rest)
	{
		$req = $rest->request();
		include_once (API_ROOT . "/suppliers.inc");

		$page = $req->get("page");

		supplier_all($page);
	}

	// Get Specific Item by Stock Id
	public function getById($rest, $id)
	{
		include_once (API_ROOT . "/suppliers.inc");
		supplier_get($id);
	}
	// Add Item
	public function post($rest)
	{
		include_once (API_ROOT . "/suppliers.inc");
		supplier_add();
	}
	// Edit Specific Item
	public function put($rest, $id)
	{
		include_once (API_ROOT . "/suppliers.inc");
		supplier_edit($id);
	}
	// Delete Specific Item
	public function delete($rest, $id)
	{
		include_once (API_ROOT . "/suppliers.inc");
		supplier_delete($id);
	}

	public function getContacts($rest, $id)
	{
		include_once (API_ROOT . "/suppliers.inc");
		supplier_contacts_get($id);
	}
}