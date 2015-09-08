<?php
namespace FAAPI;

class Sales
{
	// Get Items
	public function get($rest, $trans_type)
	{
		$req = $rest->request();
		include_once (API_ROOT . "/sales.inc");

		$page = $req->get("page");

		if ($page == null) {
			sales_all($trans_type);
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			sales_all($trans_type, $from);
		}
	}

	// Get Specific Item by Stock Id
	public function getById($rest, $trans_no, $trans_type)
	{
		include_once (API_ROOT . "/sales.inc");
		sales_get($trans_no, $trans_type);
	}
	// Add Item
	public function post($rest)
	{
		include_once (API_ROOT . "/sales.inc");
		sales_add();
	}
	// Edit Specific Item
	public function put($rest, $trans_no, $trans_type)
	{
		include_once (API_ROOT . "/sales.inc");
		sales_edit($trans_no, $trans_type);
	}
	// Delete Specific Item
	public function delete($rest, $branch_id, $uuid)
	{
		include_once (API_ROOT . "/sales.inc");
		sales_cancel($branch_id, $uuid);
	}
}