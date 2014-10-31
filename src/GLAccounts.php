<?php
namespace FAAPI;

class GLAccounts
{
	// Get Items
	public function get($rest)
	{
		$req = $rest->request();
		include_once (API_ROOT . "/glaccounts.inc");

		$page = $req->get("page");

		if ($page == null) {
			glaccounts_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			glaccounts_all($from);
		}
	}

	// Get Specific Item by Stock Id
	public function getById($rest, $id)
	{
		include_once (API_ROOT . "/glaccounts.inc");
		glaccounts_get($id);
	}

	public function getTypes($rest)
	{
		include_once (API_ROOT . "/glaccounts.inc");
		glaccounttypes_all();
	}

}