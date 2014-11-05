<?php
/**********************************************
Author: Andres Amaya
Name: Tax Groups
Free software under GNU GPL
***********************************************/

$path_to_root = "../..";

include_once($path_to_root . "/taxes/db/tax_groups_db.inc");

function taxgroups_all($from = null){
	if ($from == null) $from = 0;

	$sql = "SELECT * FROM ".TB_PREF."tax_groups WHERE !inactive LIMIT ".$from.", ".RESULTS_PER_PAGE;

	$query = db_query($sql, "error");

	$info = array();

	while($data = db_fetch($query, "error")) {
		$info[] = array('id' => $data['id'], 'name' => $data['name'], 'tax_shipping' => $data['tax_shipping']);
	}

	api_success_response(json_encode($info));
}

?>