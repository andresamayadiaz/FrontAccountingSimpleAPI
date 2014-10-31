<?php
namespace FAAPI;

class TaxGroups
{
	// Get Items
	public function get($rest)
	{
		$req = $rest->request();
		include_once (API_ROOT . "/taxgroups.inc");

		$page = $req->get("page");

		if ($page == null) {
			taxgroups_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			taxgroups_all($from);
		}
	}

}