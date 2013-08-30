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
  if ($tax_types != "") {
    $arr = split(',', $tax_types);
    $join = join(',', $arr);

    /*$sql = "select id from ".TB_PREF."item_tax_types as tb0, ".TB_PREF."item_tax_type_exemptions as tb1 where tb0.id = tb1.item_tax_type_id and tb1.tax_type_id not in (".$join.") group by tb0.id;";

    $query = db_query($sql, "error");

    $result = array();

    while($item = db_fetch_assoc($query)) {
      $result = array("item_tax_type_id" => $item['id']);
      break;
    }

    api_success_response(json_encode($result));*/

    // ----------------------------------------------------------------

    $sql = "select t0.id from ".TB_PREF."item_tax_types t0 ";

    for ($i = 1; $i < sizeof($arr)+1; $i++) {
      $sql .= "inner join ".TB_PREF."item_tax_type_exemptions t".$i." on t".$i.".item_tax_type_id=t0.id ";
    }

    for ($i = 0; $i < sizeof($arr); $i++) {
      if ($i == 0) {
        $sql .= "where t".($i+1).".tax_type_id!=".$arr[$i]." ";
      }

      if ($i > 0) {
        $sql .= "and t".($i+1).".tax_type_id!=".$arr[$i]." ";
      }
    }

    $sql .= "group by t0.id";

    //echo $sql;

    $query = db_query($sql, "error");

    $result = array();

    while($item = db_fetch($query, "error")) {
      $tax_sql   = "select count(*) as count from ".TB_PREF."item_tax_type_exemptions where item_tax_type_id=".$item['id'];
      $tax_query = db_query($tax_sql, "error");
      $tax_fetch = db_fetch($tax_query, "error");

      echo $tax_fetch['count']. " ";

      if (sizeof($arr) == 1) {
        $result = array("item_tax_type_id" => $item['id']);
        break;
      }

      if (sizeof($arr) == 2) {
        $result = array("item_tax_type_id" => $item['id']);
        break;
      }
    }

    api_success_response(json_encode($result));
  } else { // Se devuelve el id que tenga todos los impuestos
    $sql = "select id from ".TB_PREF."item_tax_types where id not in (select item_tax_type_id from ".TB_PREF."item_tax_type_exemptions) and exempt != 1 limit 1";
    $query = db_query($sql, "error");

    $result = array();

    while($item = db_fetch_assoc($query)) {
      $result = array("item_tax_type_id" => $item['id']);
      break;
    }

    api_success_response(json_encode($result));
  }
}

?>