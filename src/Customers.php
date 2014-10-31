<?php
namespace FAAPI;

class Customers
{
	// Get Items
	public function get($rest)
	{
		$req = $rest->request();
		include_once (API_ROOT . "/customers.inc");

		$page = $req->get("page");

		if ($page == null) {
			customer_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			customer_all($from);
		}
	}

	// Get Specific Item by Stock Id
	public function getById($rest, $id)
	{
		include_once (API_ROOT . "/customers.inc");
		customer_get($id);
	}
	// Add Item
	public function post($rest)
	{
		include_once (API_ROOT . "/customers.inc");
		customer_add();
	}
	// Edit Specific Item
	public function put($rest, $id)
	{
		include_once (API_ROOT . "/customers.inc");
		customer_edit($id);
	}
	// Delete Specific Item
	public function delete($rest, $id)
	{
		include_once (API_ROOT . "/customers.inc");
		customer_delete($id);
	}

	public function getBranches($rest, $id)
	{
		include_once (API_ROOT . "/customers.inc");
		customer_branches_get($id);
	}
}