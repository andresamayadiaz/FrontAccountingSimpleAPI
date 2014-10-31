<?php
namespace FAAPI;

class TaxTypes
{
	// Get Items
	public function get($rest)
	{
		$req = $rest->request();
		include_once (API_ROOT . "/taxtypes.inc");

		$page = $req->get("page");

		if ($page == null) {
			taxtypes_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			taxtypes_all($from);
		}
	}

}