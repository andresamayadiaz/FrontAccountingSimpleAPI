<?php
namespace FAAPI;

class Currencies
{
	// Get Items
	public function get($rest)
	{
		$req = $rest->request();
		include_once (API_ROOT . "/currencies.inc");

		$page = $req->get("page");

		if ($page == null) {
			currencies_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			currencies_all($from);
		}
	}

	// Get Specific Item by Stock Id
	public function getById($rest, $id)
	{
		include_once (API_ROOT . "/currencies.inc");
		currencies_get($id);
	}

	public function getLastExchangeRate($rest, $currencyCode)
	{
		include_once (API_ROOT . "/currencies.inc");
		currencies_last_exrate($currencyCode);
	}
}