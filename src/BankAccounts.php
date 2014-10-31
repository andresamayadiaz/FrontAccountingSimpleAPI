<?php
namespace FAAPI;

class BankAccounts
{
	// Get Items
	public function get($rest)
	{
		$req = $rest->request();
		include_once (API_ROOT . "/bankaccounts.inc");

		$page = $req->get("page");

		if ($page == null) {
			bankaccounts_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			bankaccounts_all($from);
		}
	}

	// Get Specific Item by Stock Id
	public function getById($rest, $id)
	{
		include_once (API_ROOT . "/bankaccounts.inc");
		bankaccounts_get($id);
	}

}