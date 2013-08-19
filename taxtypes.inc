<?php
/**********************************************
Author: Andres Amaya
Name: Tax Types API
Free software under GNU GPL
***********************************************/

$path_to_root = "../..";

include_once($path_to_root . "/taxes/includes/db/item_tax_types_db.inc");

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

function taxtypes_get($tax_types) {
  $arr = split(',', $tax_types);
  $join = join(',', $arr);

  $sql = "select id from ".TB_PREF."item_tax_types as tb0, ".TB_PREF."item_tax_type_exemptions as tb1 where tb0.id = tb1.item_tax_type_id and tb1.tax_type_id not in (".$join.") group by tb0.id;";

  $query = db_query($sql, "error");

  $result = array();

  while($item = db_fetch_assoc($query)) {
    $result = array("item_tax_type_id" => $item['id']);
    break;
  }

  api_success_response(json_encode($result));
}

?>