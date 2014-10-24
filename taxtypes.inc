<?php
/**********************************************
Author: Andres Amaya
Name: Tax Types API
Free software under GNU GPL
***********************************************/

$path_to_root = "../..";

include_once($path_to_root . "/taxes/db/item_tax_types_db.inc");

function taxtypes_all($from = null){
	if ($from == null) $from = 0;

	$sql = "SELECT * FROM ".TB_PREF."item_tax_types LIMIT ".$from.", ".RESULTS_PER_PAGE;

	$query = db_query($sql, "error");

	$info = array();

	while($data = db_fetch($query, "error")) {
		$info[] = array('id' => $data['id'], 'name' => $data['name'], 'exempt' => $data['exempt']);
	}

	api_success_response(json_encode($info));
}

?>