<?php
namespace FAAPI;

$path_to_root = "../..";

include_once ($path_to_root . "/taxes/db/tax_groups_db.inc");

class TaxGroups
{
	// Get Items
	public function get($rest)
	{
		$req = $rest->request();

		$page = $req->get("page");

		if ($page == null) {
			$this->taxgroups_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			$this->taxgroups_all($from);
		}
	}

	private function taxgroups_all($from = null)
	{
		if ($from == null)
			$from = 0;

		$sql = "SELECT * FROM " . TB_PREF . "tax_groups WHERE !inactive LIMIT " . $from . ", " . RESULTS_PER_PAGE;

		$query = db_query($sql, "error");

		$info = array();

		while ($data = db_fetch($query, "error")) {
			$info[] = array(
				'id' => $data['id'],
				'name' => $data['name'],
				'inactive' => $data['inactive']
			);
		}

		api_success_response(json_encode($info));
	}
}