<?php
/**********************************************
Author: Andres Amaya
Name: Sales Quotes API
Free software under GNU GPL
***********************************************/

$path_to_root = "../..";

include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/ui/sales_order_ui.inc");
include_once($path_to_root . "/sales/includes/db/cust_trans_details_db.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");
include_once($path_to_root . "/admin/db/attachments_db.inc");
// include_once($path_to_root . "/modules/timbrado/includes/db/comprobantes_db.php.inc");
// include_once($path_to_root . "/modules/timbrado/includes/db/llavero_db.php.inc");
// include_once($path_to_root . "/modules/timbrado/includes/generate/AutoFacturaCore.php.inc");
// include_once($path_to_root . "/modules/timbrado/includes/db/configuraciones_db.php.inc");

/*function salesquote_get($id){

	try{
		$doc = new Cart(ST_SALESQUOTE, $id);

		$sub_tot = $doc->get_items_total() + $doc->freight_cost;
		$display_sub_tot = price_format($sub_tot);

		$display_total = $doc->get_trans_total();

		$ret = array();
		$ret['trans_type'] = $doc->trans_type;
		$ret['trans_no'] = $id;
		$ret['line_items'] = $doc->line_items;
		$ret['document_date'] = $doc->document_date;
		$ret['due_date'] = $doc->due_date;
		$ret['sales_type'] = $doc->sales_type;
		$ret['sales_type_name'] = $doc->sales_type_name;
		$ret['tax_included'] = $doc->tax_included;
		$ret['customer_currency'] = $doc->customer_currency;
		$ret['default_discount'] = $doc->default_discount;
		$ret['customer_name'] = $doc->customer_name;
		$ret['customer_id'] = $doc->customer_id;
		$ret['Branch'] = $doc->Branch;
		$ret['email'] = $doc->email;
		$ret['deliver_to'] = $doc->deliver_to;
		$ret['delivery_address'] = $doc->delivery_address;
		$ret['phone'] = $doc->phone;
		$ret['cust_ref'] = $doc->cust_ref;
		$ret['reference'] = $doc->reference;
		$ret['Comments'] = $doc->Comments;
		$ret['Location'] = $doc->Location;
		$ret['location_name'] = $doc->location_name;
		$ret['order_no'] = $doc->order_no;
		$ret['ship_via'] = $doc->ship_via;
		$ret['freight_cost'] = $doc->freight_cost;
		$ret['tax_group_id'] = $doc->tax_group_id;
		$ret['tax_group_name'] = $doc->tax_group_name;
		$ret['tax_group_array'] = $doc->tax_group_array;
		$ret['price_factor'] = $doc->price_factor;
		$ret['pos'] = $doc->pos;
		$ret['cash_discount'] = $doc->cash_discount;
		$ret['dimension_id'] = $doc->dimension_id;
		$ret['dimension2_id'] = $doc->dimension2_id;
		$ret['payment'] = $doc->payment;
		$ret['payment_terms'] = $doc->payment_terms;
		$ret['credit'] = $doc->credit;
		$ret['sub_tot'] = $sub_tot;
		$ret['display_total'] = $display_total;
	} catch(Exception $e){
		api_error(404, $e->getMessage());
	}

	api_success_response(json_encode($ret));

}*/

function sales_all($trans_type, $from = null) {
	if ($from == null) $from = 0;

	$info = array();
	$sale_index = 0;

	if ($trans_type == ST_SALESINVOICE || $trans_type == ST_CUSTCREDIT) {
		$sql = "SELECT trans_no, type FROM ".TB_PREF."debtor_trans WHERE type=".db_escape($trans_type)." LIMIT ".$from.", ".RESULTS_PER_PAGE;
		$query = db_query($sql, "error");

		while($sale = db_fetch($query, "error")) {
			try {
				$trans					= get_customer_trans($sale['trans_no'], $sale['type']);
				$trans_details	= get_customer_trans_details($sale['type'], $trans['trans_no']);

				$info[$sale_index]['trans_no']				= $trans['trans_no'];
				$info[$sale_index]['type']						= $trans['type'];
				$info[$sale_index]['version']					= $trans['version'];
				$info[$sale_index]['debtor_no']				= $trans['debtor_no'];
				$info[$sale_index]['branch_code']			= $trans['branch_code'];
				$info[$sale_index]['tran_date']				= $trans['tran_date'];
				$info[$sale_index]['due_date']				= $trans['due_date'];
				$info[$sale_index]['reference']				= $trans['reference'];
				$info[$sale_index]['tpe']							= $trans['tpe'];
				$info[$sale_index]['order_']					= $trans['order_'];
				$info[$sale_index]['ov_amount']				= $trans['ov_amount'];
				$info[$sale_index]['ov_gst']					= $trans['ov_gst'];
				$info[$sale_index]['ov_freight']			= $trans['ov_freight'];
				$info[$sale_index]['ov_freight_tax']	= $trans['ov_freight_tax'];
				$info[$sale_index]['ov_discount']			= $trans['ov_discount'];
				$info[$sale_index]['ov_discount']			= $trans['Total'];
				$info[$sale_index]['alloc']						= $trans['alloc'];
				$info[$sale_index]['rate']						= $trans['rate'];
				$info[$sale_index]['ship_via']				= $trans['ship_via'];
				$info[$sale_index]['dimension_id']		= $trans['dimension_id'];
				$info[$sale_index]['dimension2_id']		= $trans['dimension2_id'];
				$info[$sale_index]['payment_terms']		= $trans['payment_terms'];
				$info[$sale_index]['digital']					= $trans['digital'];

			 	$item_index = 0;

			 	if (db_num_rows($trans_details) > 0) {
					while ($trans_item = db_fetch($trans_details)) {
						$info[$sale_index]['line_items'][$item_index]['id']						= $trans_item['id'];
						$info[$sale_index]['line_items'][$item_index]['stock_id']			= $trans_item['stock_id'];
						$info[$sale_index]['line_items'][$item_index]['qty']					= $trans_item['quantity'];
						$info[$sale_index]['line_items'][$item_index]['units']				= $trans_item['units'];
						$info[$sale_index]['line_items'][$item_index]['price']				= $trans_item['unit_price'];
						$info[$sale_index]['line_items'][$item_index]['discount']			= $trans_item['discount_percent'];
						$info[$sale_index]['line_items'][$item_index]['description']	= $trans_item['StockDescription'];
				 		$item_index++;
					}
				}
			} catch (Exception $e) {
				//api_error(404, $e->getMessage());
			}

			$sale_index++;
		}
	} else if ($trans_type == ST_SALESORDER || $trans_type == ST_SALESQUOTE || $trans_type == ST_CUSTDELIVERY) {
		$sql = "SELECT order_no, trans_type FROM ".TB_PREF."sales_orders WHERE trans_type=".db_escape($trans_type)." LIMIT ".$from.", ".RESULTS_PER_PAGE;
		$query = db_query($sql, "error");

		while($sale = db_fetch($query, "error")) {
			try {
				$cart																		= null;
				$cart																		= new Cart($sale['trans_type'], $sale['order_no']);
				$info[$sale_index]['ref']								= $cart->reference;
				$info[$sale_index]['comments']					= $cart->Comments;
				$info[$sale_index]['order_date']				= $cart->document_date;
				$info[$sale_index]['payment']						= $cart->payment;
				$info[$sale_index]['payment_terms']			= $cart->payment_terms;
				$info[$sale_index]['due_date']					= $cart->due_date;
				$info[$sale_index]['phone']							= $cart->phone;
				$info[$sale_index]['cust_ref']					= $cart->cust_ref;
				$info[$sale_index]['delivery_address']	= $cart->delivery_address;
				$info[$sale_index]['ship_via']					= $cart->ship_via;
				$info[$sale_index]['deliver_to']				= $cart->deliver_to;
				$info[$sale_index]['delivery_date']			= $cart->due_date;
				$info[$sale_index]['location']					= $cart->Location;
				$info[$sale_index]['freight_cost']			= $cart->freight_cost;
				$info[$sale_index]['email']							= $cart->email;
				$info[$sale_index]['customer_id']				= $cart->customer_id;
				$info[$sale_index]['branch_id']					= $cart->Branch;
				$info[$sale_index]['sales_type']				= $cart->sales_type;
				$info[$sale_index]['dimension_id']			= $cart->dimension_id;
				$info[$sale_index]['dimension2_id']			= $cart->dimension2_id;

			 	$item_index = 0;

				foreach($cart->line_items as $item) {
					$info[$sale_index]['line_items'][$item_index]['id']						= $item->id;
					$info[$sale_index]['line_items'][$item_index]['stock_id']			= $item->stock_id;
					$info[$sale_index]['line_items'][$item_index]['qty']					= $item->quantity;
					$info[$sale_index]['line_items'][$item_index]['units']				= $item->units;
					$info[$sale_index]['line_items'][$item_index]['price']				= $item->price;
					$info[$sale_index]['line_items'][$item_index]['discount']			= $item->discount_percent;
					$info[$sale_index]['line_items'][$item_index]['description']	= $item->item_description;
			 		$item_index++;
				}

				$sub_total					= $cart->get_items_total() + $cart->freight_cost;
				$display_sub_total	= price_format($sub_total);
				$display_total			= $cart->get_trans_total();

				$info[$sale_index]['sub_total']			= $sub_total;
				$info[$sale_index]['display_total']	= $display_total;
			} catch (Exception $e) {
				//api_error(404, $e->getMessage());
			}

			$sale_index++;
		}
	}

	api_success_response(json_encode($info));
}

function sales_get($trans_no, $trans_type) {
	try {
		$cart											= null;
		$cart											= new Cart($trans_type, $trans_no);
		$info['ref']							= $cart->reference;
		$info['comments']					= $cart->Comments;
		$info['order_date']				= $cart->document_date;
		$info['payment']					= $cart->payment;
		$info['payment_terms']		= $cart->payment_terms;
		$info['due_date']					= $cart->due_date;
		$info['phone']						= $cart->phone;
		$info['cust_ref']					= $cart->cust_ref;
		$info['delivery_address']	= $cart->delivery_address;
		$info['ship_via']					= $cart->ship_via;
		$info['deliver_to']				= $cart->deliver_to;
		$info['delivery_date']		= $cart->due_date;
		$info['location']					= $cart->Location;
		$info['freight_cost']			= $cart->freight_cost;
		$info['email']						= $cart->email;
		$info['customer_id']			= $cart->customer_id;
		$info['branch_id']				= $cart->Branch;
		$info['sales_type']				= $cart->sales_type;
		$info['dimension_id']			= $cart->dimension_id;
		$info['dimension2_id']		= $cart->dimension2_id;

	 	$item_index = 0;
		foreach($cart->line_items as $item) {
	 		$info['line_items'][$item_index]['id']					= $item->id;
	 		$info['line_items'][$item_index]['stock_id']		= $item->stock_id;
	 		$info['line_items'][$item_index]['qty']					= $item->quantity;
	 		$info['line_items'][$item_index]['units']				= $item->units;
	 		$info['line_items'][$item_index]['price']				= $item->price;
	 		$info['line_items'][$item_index]['discount']		= $item->discount_percent;
	 		$info['line_items'][$item_index]['description']	= $item->item_description;
	 		$item_index++;
		}

		$sub_total					= $cart->get_items_total() + $cart->freight_cost;
		$display_sub_total	= price_format($sub_total);
		$display_total			= $cart->get_trans_total();

		$info['sub_total']			= $sub_total;
		$info['display_total']	= $display_total;
	} catch (Exception $e) {
		api_error(404, $e->getMessage());
	}

	api_success_response(json_encode($info));
}

function sales_add() {
	$app	= \Slim\Slim::getInstance('SASYS');
	$req	= $app->request();
	$info	= $req->post();

	$cart									= null;
	$cart									= new Cart($info['trans_type'], 0);
	$cart->reference			= $info['ref'];
	$cart->Comments				= $info['comments'];
	$cart->document_date	= $info['order_date'];

	if (isset($info['payment']) && ($cart->payment != $info['payment'])) {
		$cart->payment				= $info['payment'];
		$cart->payment_terms	= get_payment_terms($info['payment']);
		$newpayment						= true;
	}

	if ($cart->payment_terms['cash_sale']) {
		if ($newpayment) {
			$cart->due_date		= $cart->document_date;
			$cart->phone			= $cart->cust_ref = $cart->delivery_address = '';
			$cart->ship_via		= 0;
			$cart->deliver_to	= '';
		}
	} else {
		$cart->due_date					= $info['delivery_date'];
		$cart->cust_ref					= $info['cust_ref'];
		$cart->deliver_to				= $info['deliver_to'];
		$cart->delivery_address	= $info['delivery_address'];
		$cart->phone						= $info['phone'];
		$cart->ship_via					= $info['ship_via'];
	}

	$cart->Location			= $info['location'];
	$cart->freight_cost	= input_num('freight_cost');

	if (isset($info['email'])) {
		$cart->email = $info['email'];
	}	else {
		$cart->email = '';
	}

	$cart->customer_id	= $info['customer_id'];
	$cart->Branch				= $info['branch_id'];
	$cart->sales_type		= $info['sales_type'];

	if ($cart->trans_type != ST_SALESORDER && $cart->trans_type != ST_SALESQUOTE) { // 2008-11-12 Joe Hunt
		$cart->dimension_id		= $info['dimension_id'];
		$cart->dimension2_id	= $info['dimension2_id'];
	}

	foreach($info['items'] as $item) {
		add_to_order($cart, $item['stock_id'], $item['qty'], $item['price'], $item['discount'], $item['description']);
	}

	$can_process = can_process($info, $cart);

	if ($can_process['passed'] == false) {
		trigger_error(var_export($can_process, true));
		api_error(412, $can_process['message']);
		return;
	}

	$result = $cart->write(1);

	if ($result == -1) {
    //return array("passed" => false, "message" => _("The entered reference is already in use."));
    api_error(500, 'Could Not Save to Database');
    return;
  }

  switch($cart->trans_type) {
  	case ST_SALESQUOTE:
  		api_success_response(sprintf(_("Quotation # %d has been entered."), key($cart->trans_no)));
  		return;
  		break;
  	case ST_SALESORDER:
  		api_success_response(sprintf(_("Order # %d has been entered."), key($cart->trans_no)));
  		return;
  		break;
  	case ST_SALESINVOICE:
  		$feeback_response = sprintf(_("Invoice # %d has been entered."), key($cart->trans_no));

//   		if ($cfdi_response != "") {
//   			$feeback_response .= "\r\n".$cfdi_response;
//   		}

  		api_success_response($feeback_response);
  		return;
  		break;
  	case ST_CUSTCREDIT:
  		$feeback_response = sprintf(_("Credit Note # %d has been processed."), key($cart->trans_no));

  		if ($cfdi_response != "") {
  			$feeback_response .= "\r\n".$cfdi_response;
  		}

  		api_success_response($feeback_response);
  		return;
  		break;
  	case ST_CUSTDELIVERY:
  		api_success_response(sprintf(_("Delivery # %d has been entered."), key($cart->trans_no)));
  		return;
  		break;
  }

  api_success_response(_("Ok"));
}

function sales_edit($trans_no, $trans_type) {
	$app	= \Slim\Slim::getInstance('SASYS');
	$req	= $app->request();
	$info	= $req->put();

	$cart									= null;
	$cart									= new Cart($trans_type, $trans_no);
	if (isset($info["ref"]))
		$cart->reference			= $info['ref'];
	if (isset($info["comments"]))
		$cart->Comments				= $info['comments'];
	if (isset($info["order_date"]))
		$cart->document_date	= $info['order_date'];

	if (isset($info['payment']) && ($cart->payment != $info['payment'])) {
		if (isset($info["payment"]))
			$cart->payment				= $info['payment'];
		if (isset($info["payment"]))
			$cart->payment_terms	= get_payment_terms($info['payment']);
		//$newpayment						= true;
	}

	if ($cart->payment_terms['cash_sale']) {
		/*if ($newpayment) {
			$cart->due_date		= $cart->document_date;
			$cart->phone			= $cart->cust_ref = $cart->delivery_address = '';
			$cart->ship_via		= 0;
			$cart->deliver_to	= '';
		}*/
	} else {
		if (isset($info["delivery_date"]))
			$cart->due_date					= $info['delivery_date'];
		if (isset($info["cust_ref"]))
			$cart->cust_ref					= $info['cust_ref'];
		if (isset($info["deliver_to"]))
			$cart->deliver_to				= $info['deliver_to'];
		if (isset($info["delivery_address"]))
			$cart->delivery_address	= $info['delivery_address'];
		if (isset($info["phone"]))
			$cart->phone						= $info['phone'];
		if (isset($info["ship_via"]))
			$cart->ship_via					= $info['ship_via'];
	}

	if (isset($info["location"]))
		$cart->Location			= $info['location'];
	if (isset($info["freight_cost"]))
		$cart->freight_cost	= input_num('freight_cost');

	if (isset($info['email'])) {
		$cart->email = $info['email'];
	}	else {
		//$cart->email = '';
	}

	if (isset($info["customer_id"]))
		$cart->customer_id	= $info['customer_id'];
	if (isset($info["branch_id"]))
		$cart->Branch				= $info['branch_id'];
	if (isset($info["sales_type"]))
		$cart->sales_type		= $info['sales_type'];

	if ($cart->trans_type != ST_SALESORDER && $cart->trans_type != ST_SALESQUOTE) { // 2008-11-12 Joe Hunt
		if (isset($info["dimension_id"]))
			$cart->dimension_id		= $info['dimension_id'];
		if (isset($info["dimension2_id"]))
			$cart->dimension2_id	= $info['dimension2_id'];
	}

	if (isset($info["items"])) {
		foreach($info['items'] as $item) {
			add_to_order($cart, $item['stock_id'], $item['qty'], $item['price'], $item['discount'], $item['description']);
		}
	}

	$can_process = can_process($info, $cart);

	if ($can_process == false) {
		api_error(412, $can_process['message']);
	}

	$result = $cart->write(1);

	if ($result == -1) {
    //return array("passed" => false, "message" => _("The entered reference is already in use."));
    api_error(500, _('Could Not Save to Database'));
  }

  switch($cart->trans_type) {
  	case ST_SALESQUOTE:
  		api_success_response(sprintf(_("Quotation # %d has been updated."), $cart->trans_no));
  		return;
  		break;
  	case ST_SALESORDER:
  		api_success_response(sprintf(_("Order # %d has been updated."), $cart->trans_no));
  		return;
  		break;
  	case ST_SALESINVOICE:
  		api_success_response(sprintf(_("Invoice # %d has been updated."), $cart->trans_no));
  		return;
  		break;
  	case ST_CUSTCREDIT:
  		api_success_response(sprintf(_("Credit # %d has been updated."), $cart->trans_no));
  		return;
  		break;
  	case ST_CUSTDELIVERY:
  		api_success_response(sprintf(_("Delivery # %d has been updated."), $cart->trans_no));
  		return;
  		break;
  }

  api_success_response(_("Ok"));
}

function sales_cancel($branch_id, $uuid) {
	error_log("\r\n1", 3, "/var/tmp/sasys.log");
	$AutoFactura					= new AutoFacturaCore();
	$AutoFactura->userkey	= get_cofig("Llave de Usuario", "");
	$AutoFactura->suckey	= $branch_id;
	$AutoFactura->url			= get_cofig("Servidor", "");
	$datos								= array('autofactura_id' => $uuid);
	$response							= $AutoFactura->cancelar($datos);
	error_log("\r\n2=>".json_encode($response), 3, "/var/tmp/sasys.log");

	try {
		if($response['exito'] == 1){
			error_log("\r\n3", 3, "/var/tmp/sasys.log");
			set_sql_cancelar_digital($uuid, $response['url']);
			api_success_response(_("The invoice was cancelled."));
			return;
			error_log("\r\n4", 3, "/var/tmp/sasys.log");
		} else {
			error_log("\r\n5", 3, "/var/tmp/sasys.log");
			api_error(500, _('Could not cancel invoice.'));
			return;
		}
	} catch (Exception $e) {
		error_log("\r\n6", 3, "/var/tmp/sasys.log");
		api_error(500, _('Could not cancel invoice. ').$e->getMessage());
		return;
	}
}

function can_process($info, $cart) {
	global $Refs;

	if (!$info['customer_id']) {
		return array("passed" => false, "message" => _("There is no customer selected."));
	}

	if (!$info['branch_id']) {
		return array("passed" => false, "message" => _("This customer has no branch defined."));
	}

	if (!is_date($info['order_date'])) {
		return array("passed" => false, "message" => _("The entered date is invalid."));
	}

	if ($cart->trans_type != ST_SALESORDER && $cart->trans_type != ST_SALESQUOTE && !is_date_in_fiscalyear($info['order_date'])) {
		return array("passed" => false, "message" => _("The entered date is not in fiscal year"));
	}

	if (count($cart->line_items) == 0)	{
		return array("passed" => false, "message" => _("You must enter at least one non empty item line."));
	}

	if ($cart->payment_terms['cash_sale'] == 0) {
		if (strlen($info['deliver_to']) <= 1) {
			return array("passed" => false, "message" => _("You must enter the person or company to whom delivery should be made to."));
		}

		if ($cart->trans_type != ST_SALESQUOTE && strlen($info['delivery_address']) <= 1) {
			return array("passed" => false, "message" => _("You should enter the street address in the box provided. Orders cannot be accepted without a valid street address."));
		}

		if ($info['freight_cost'] == "") {
			$info['freight_cost'] = price_format(0);
		}

		/*if (!check_num('freight_cost', 0)) {
			return array("passed" => false, "message" => _("The shipping cost entered is expected to be numeric."));
		}*/

		if (!is_date($info['delivery_date'])) {
			if ($cart->trans_type == ST_SALESQUOTE) {
				return array("passed" => false, "message" => _("The Valid date is invalid."));
			} else {
				return array("passed" => false, "message" => _("The delivery date is invalid."));
			}
		}

		if (date1_greater_date2($info['order_date'], $info['delivery_date'])) {
			if ($cart->trans_type == ST_SALESQUOTE){
				return array("passed" => false, "message" => _("The requested valid date is before the date of the quotation."));
			} else {
				return array("passed" => false, "message" => _("The requested delivery date is before the date of the order."));
			}
		}
	} else {
		if (!db_has_cash_accounts()) {
			return array("passed" => false, "message" => _("You need to define a cash account for your Sales Point."));
		}
	}

	if (!$Refs->is_valid($info['ref'], $cart->trans_type)) {
		return array("passed" => false, "message" => _("You must enter a valid reference."));
	}

	if (!db_has_currency_rates($cart->customer_currency, $info['order_date'])){
		return false;
	}

	if ($cart->get_items_total() < 0) {
		return array("passed" => false, "message" => _("Invoice total amount cannot be less than zero."));
	}

	return array('passed' => true);
}

?>