<?php
/**********************************************
Author: Andres Amaya
Name: Bank Accounts API
Free software under GNU GPL
***********************************************/

$path_to_root = "../..";

include_once($path_to_root . "/gl/includes/db/gl_db_bank_accounts.inc");

function bankaccounts_all($from = null) {
	if ($from == null) $from = 0;
	$sql = "SELECT * FROM ".TB_PREF."bank_accounts LIMIT ".$from.", ".RESULTS_PER_PAGE;
	$query = db_query($sql, "error");

	$info = array();

	while($data = db_fetch($query, "error")) {
		$info[] = array("id" => $data["id"], "account_type" => $data["account_type"], "account_code" => $data["account_code"], "bank_account_name" => $data["bank_account_name"], "bank_name" => $data["bank_name"], "bank_account_number" => $data["bank_account_number"], "bank_curr_code" => $data["bank_curr_code"], "bank_address" => $data["bank_address"], "dflt_curr_act" => $data["dflt_curr_act"]);
	}

	api_success_response(json_encode($info));
}

function bankaccounts_get($id)
{
	
	$bank = get_bank_account($id);
	if(!$bank) $bank = array();
	api_success_response(json_encode($bank));
	
}

?>