<?php
/**********************************************
Author: Andres Amaya
Name: Currencies API
Free software under GNU GPL
***********************************************/

$path_to_root = "../..";

include_once($path_to_root . "/gl/includes/db/gl_db_currencies.inc");
//include_once($path_to_root . "/gl/includes/db/gl_db_rates.inc");
include_once($path_to_root . "/includes/banking.inc");

function currencies_all($from = null) {
	if ($from == null) $from = 0;
	
	$sql = "SELECT * FROM ".TB_PREF."currencies WHERE !inactive LIMIT ".$from.", ".RESULTS_PER_PAGE;

	$query = db_query($sql, "error");

	$info = array();

	while($data = db_fetch($query, "error")) {
		$info[] = array('curr_abrev' => $data['curr_abrev'], 'currency' => $data['currency'], 'curr_symbol' => $data['curr_symbol'], 'country' => $data['country'], 'hundreds_name' => $data['hundreds_name']);
	}

	api_success_response(json_encode($info));
}

function currencies_get($id){
	
	$curr = get_currency($id);
	if(!$curr) $curr = array();
	api_success_response(json_encode($curr));
	
}

function currencies_last_exrate($id){
	
	$date = date2sql(Today());
	
	$sql = "SELECT rate_buy, max(date_) as date_ FROM ".TB_PREF."exchange_rates WHERE curr_code = ".db_escape($id)."
				AND date_ <= '$date' GROUP BY rate_buy ORDER BY date_ Desc LIMIT 1";
	
	$result = db_query($sql, "could not query exchange rates");
	
	if (db_num_rows($result) == 0)
	{
		// no stored exchange rate, just return 0
		api_success_response(json_encode( array('curr_abrev' => $id, 'rate' => 0, 'date' => $date) ));
	}
	
	$myrow = db_fetch_row($result);
	
	api_success_response(json_encode( array('curr_abrev' => $id, 'rate' => $myrow[0], 'date' => $myrow[1]) ));
	
}

?>