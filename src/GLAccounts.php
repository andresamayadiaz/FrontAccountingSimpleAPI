<?php
namespace FAAPI;

$path_to_root = "../..";

include_once ($path_to_root . "/gl/includes/db/gl_db_accounts.inc");
include_once ($path_to_root . "/gl/includes/db/gl_db_account_types.inc");

class GLAccounts
{
	// Get Items
	public function get($rest)
	{
		$req = $rest->request();

		$page = $req->get("page");

		if ($page == null) {
			$this->glaccounts_all();
		} else {
			// If page = 1 the value will be 0, if page = 2 the value will be 1, ...
			$from = -- $page * RESULTS_PER_PAGE;
			$this->glaccounts_all($from);
		}
	}

	// Get Specific Item by Stock Id
	public function getById($rest, $id)
	{
		$acct = get_gl_account($id);
		if (! $acct)
			$acct = array();
		api_success_response(json_encode($acct));
	}

	public function getTypes($rest)
	{
		$accttypes = get_account_types();
		$ret = array();
		while ($type = db_fetch($accttypes)) {
			$ret[] = array(
				'id' => $type['id'],
				'name' => $type['name'],
				'class_id' => $type['class_id'],
				'parent' => $type['parent']
			);
		}
		api_success_response(json_encode($ret));
	}

	private function glaccounts_all($from = null)
	{
		if ($from == null)
			$from = 0;

		$sql = "SELECT " . TB_PREF . "chart_master.*," . TB_PREF . "chart_types.name AS AccountTypeName FROM " . TB_PREF . "chart_master," . TB_PREF . "chart_types WHERE " . TB_PREF . "chart_master.account_type=" . TB_PREF . "chart_types.id ORDER BY account_code LIMIT " . $from . ", " . RESULTS_PER_PAGE;

		$query = db_query($sql, "error");

		$info = array();

		while ($data = db_fetch($query, "error")) {
			$info[] = array(
				'account_code' => $data['account_code'],
				'account_name' => $data['account_name'],
				'account_type' => $data['account_type'],
				'account_code2' => $data['account_code2']
			);
		}

		api_success_response(json_encode($info));
	}
}