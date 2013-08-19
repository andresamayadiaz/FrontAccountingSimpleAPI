<?php
/**********************************************
Author: Andres Amaya
Name: Tax Groups
Free software under GNU GPL
***********************************************/

$path_to_root = "../..";

//include_once($path_to_root . "/taxes/includes/db/tax_groups_db.inc");
include_once($path_to_root . "/taxes/includes/db/tax_groups_db.inc");
include_once($path_to_root . "/taxes/includes/db/tax_types_db.inc");

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

function taxgroups_get($tax_types) {
  $arr = split(',', $tax_types);

  $sql = "select t0.id from ".TB_PREF."tax_groups t0 ";

  for ($i = 1; $i < sizeof($arr)+1; $i++) {
    $sql .= "inner join ".TB_PREF."tax_group_items t".$i." on t".$i.".tax_group_id=t0.id ";
  }

  for ($i = 0; $i < sizeof($arr); $i++) {
    if ($i == 0) {
      $sql .= "where t".($i+1).".tax_type_id=".$arr[$i]." ";
    }

    if ($i > 0) {
      $sql .= "and t".($i+1).".tax_type_id=".$arr[$i]." ";
    }
  }

  $sql .= "group by t0.id";

  $query = db_query($sql, "error");

  $result = array();

  while($item = db_fetch_assoc($query)) {
    $tax_sql   = "select count(*) as count from ".TB_PREF."tax_group_items t0 where t0.tax_group_id=".$item['id'];
    $tax_query = db_query($tax_sql, "error");
    $tax_fetch = db_fetch($tax_query, "error");

    if ($tax_fetch['count'] == sizeof($arr)) {
      $result = array("tax_group_id" => $item['id']);
      break;
    }
  }

  api_success_response(json_encode($result));
}

?>