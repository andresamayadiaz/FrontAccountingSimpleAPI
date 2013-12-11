<?php

/*
= Christian Estrella
= 08/2013
= API destinada a emision con Autofactura.
*/

include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/db/cust_trans_details_db.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");
include_once($path_to_root . "/admin/db/attachments_db.inc");
include_once($path_to_root . "/modules/timbrado/includes/db/comprobantes_db.php.inc");
include_once($path_to_root . "/modules/timbrado/includes/db/llavero_db.php.inc");
include_once($path_to_root . "/modules/timbrado/includes/generate/AutoFacturaCore.php.inc");
include_once($path_to_root . "/modules/timbrado/includes/db/configuraciones_db.php.inc");
include_once($path_to_root . '/sales/includes/db/customers_db.inc');
include_once($path_to_root . '/includes/ui/simple_crud_class.inc');
include_once($path_to_root . "/includes/db/crm_contacts_db.inc");
include_once($path_to_root . "/includes/db/countries_db.php.inc");


function cfdi_series_all($branch_key) {
  $AutoFactura          = new AutoFacturaCore();
  $AutoFactura->userkey = get_cofig("Llave de Usuario", "");
  $AutoFactura->suckey  = $branch_key;
  $AutoFactura->url     = get_cofig("Servidor", "");
  $response             = $AutoFactura->series();

  $result = array();

  foreach($response as $item) {
    $result[] = array('id' => $item['id'], 'name' => $item['nombre']);
  }

  api_success_response(json_encode($result));
}

function cfdi_branches_all($from = null) {
  if ($from == null) $from = 0;

  $sql = "select * from ".TB_PREF."timbrado_llaves where status = 1 limit ".$from.", ".RESULTS_PER_PAGE;
  $query = db_query($sql, "error");

  $result = array();

  while($item = db_fetch($query, "error")) {
    $result[] = array('id' => $item['id'], 'name' => $item['nombre'], 'key' => $item['llave']);
  }

  api_success_response(json_encode($result));
}

function cfdi_taxtypes_get() {
  $sql    = "select id, name, rate from ".TB_PREF."tax_types";
  $query  = db_query($sql, "error");
  $result = array();

  while($tax_type = db_fetch($query, "error")) {
    array_push($result, array("id" => $tax_type['id'], "name" => $tax_type['name'], "rate" => $tax_type['rate']));
  }

  api_success_response(json_encode($result)); 
}

function cfdi_taxgroup_get() {
  $sql   = "select id from ".TB_PREF."tax_types";
  $query = db_query($sql, "error");
  $arr   = array();

  while($tax_type = db_fetch($query, "error")) {
    array_push($arr, $tax_type['id']);
  }

  $sql             = null;
  $query           = null;
  $tax_type        = null;

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

/*function cfdi_item_taxtype_includes_all_get() {
  $sql = "select id from ".TB_PREF."item_tax_types where id not in (select item_tax_type_id from ".TB_PREF."item_tax_type_exemptions) and exempt != 1 limit 1";
  $query = db_query($sql, "error");

  $result = array();

  while($item = db_fetch_assoc($query)) {
    $result = array("item_tax_type_id" => $item['id']);
    break;
  }

  api_success_response(json_encode($result));
}*/

function cfdi_item_taxtype_exempt_get() {
  $sql = "select id from ".TB_PREF."item_tax_types where exempt = 1 limit 1";
  $query = db_query($sql, "error");

  $result = array();

  while($item = db_fetch_assoc($query)) {
    $result = array("item_tax_type_id" => $item['id']);
    break;
  }

  api_success_response(json_encode($result));
}

function cfdi_item_taxtypes_get($tax_types) {
  $arr  = explode(',', $tax_types);
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
      $sql .= "where t".($i+1).".tax_type_id=".$arr[$i]." ";
    }

    if ($i > 0) {
      $sql .= "and t".($i+1).".tax_type_id=".$arr[$i]." ";
    }
  }

  $sql .= "group by t0.id";

  $query = db_query($sql, "error");

  $result = array();

  while($item = db_fetch($query, "error")) {
    $tax_sql   = "select count(*) as count from ".TB_PREF."item_tax_type_exemptions where item_tax_type_id=".$item['id'];
    $tax_query = db_query($tax_sql, "error");
    $tax_fetch = db_fetch($tax_query, "error");

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
}

function cfdi_customer_get_by_id($id) {
  $sql = "select dm.debtor_no, dm.name, dm.debtor_ref, cb.branch_code, cb.br_name, cb.branch_ref, cb.br_address, cb.tax_group_id, cb.default_location, cb.default_ship_via from ".TB_PREF."debtors_master as dm inner join ".TB_PREF."cust_branch as cb on cb.debtor_no = dm.debtor_no where dm.debtor_no = ".db_escape($id);
  $query = db_query($sql, "error");

  $result = array();

  while($customer = db_fetch($query, "error")) {
    if (sizeof($result) < 1) {
      $result = array("no" => $customer['debtor_no'], "name" => $customer['name'], "ref" => $customer['debtor_ref']);
      $result['cust_branches'][] = array("code" => $customer['branch_code'], "name" => $customer['br_name'], "ref" => $customer['branch_ref'], "address" => $customer['br_address'], "tax_group_id" => $customer['tax_group_id'], "default_location" => $customer['default_location'], "default_ship_via" => $customer['default_ship_via'], "user_key" => get_cofig("Llave de Usuario", ""));
    } else {
      $result['cust_branches'][] = array("code" => $customer['branch_code'], "name" => $customer['br_name'], "ref" => $customer['branch_ref'], "address" => $customer['br_address'], "tax_group_id" => $customer['tax_group_id'], "default_location" => $customer['default_location'], "default_ship_via" => $customer['default_ship_via'], "user_key" => get_cofig("Llave de Usuario", ""));
    }
  }

  if (sizeof($result) == 0)
  {
    api_error(422, _('Could not get data.'));
    return false;
  }
  
  api_success_response(json_encode($result)); 
}

function cfdi_customer_get_by_rfc($rfc) {
  $sql = "select dm.debtor_no, dm.name, dm.debtor_ref, cb.branch_code, cb.br_name, cb.branch_ref, cb.br_address, cb.tax_group_id, cb.default_location, cb.default_ship_via from ".TB_PREF."debtors_master as dm inner join ".TB_PREF."cust_branch as cb on cb.debtor_no = dm.debtor_no where dm.tax_id = ".db_escape($rfc);
  $query = db_query($sql, "error");

  $result = array();

  while($customer = db_fetch($query, "error")) {
    if (sizeof($result) < 1) {
      $result = array("no" => $customer['debtor_no'], "name" => $customer['name'], "ref" => $customer['debtor_ref'], "user_key" => get_cofig("Llave de Usuario", ""));
      $result['cust_branches'][] = array("code" => $customer['branch_code'], "name" => $customer['br_name'], "ref" => $customer['branch_ref'], "address" => $customer['br_address'], "tax_group_id" => $customer['tax_group_id'], "default_location" => $customer['default_location'], "default_ship_via" => $customer['default_ship_via']);
    } else {
      $result['cust_branches'][] = array("code" => $customer['branch_code'], "name" => $customer['br_name'], "ref" => $customer['branch_ref'], "address" => $customer['br_address'], "tax_group_id" => $customer['tax_group_id'], "default_location" => $customer['default_location'], "default_ship_via" => $customer['default_ship_via']);
    }
  }

  if (sizeof($result) == 0)
  {
    api_error(422, _('Could not get data.'));
    return false;
  }
  
  api_success_response(json_encode($result)); 
}

function cfdi_customer_get_by_client_no($client_no) {
  $sql = "select dm.debtor_no, dm.name, dm.debtor_ref, cb.branch_code, cb.br_name, cb.branch_ref, cb.br_address, cb.tax_group_id, cb.default_location, cb.default_ship_via from ".TB_PREF."debtors_master as dm inner join ".TB_PREF."cust_branch as cb on cb.debtor_no = dm.debtor_no where dm.client_no = ".db_escape($client_no);
  $query = db_query($sql, "error");

  $result = array();

  while($customer = db_fetch($query, "error")) {
    if (sizeof($result) < 1) {
      $result = array("no" => $customer['debtor_no'], "name" => $customer['name'], "ref" => $customer['debtor_ref'], "user_key" => get_cofig("Llave de Usuario", ""));
      $result['cust_branches'][] = array("code" => $customer['branch_code'], "name" => $customer['br_name'], "ref" => $customer['branch_ref'], "address" => $customer['br_address'], "tax_group_id" => $customer['tax_group_id'], "default_location" => $customer['default_location'], "default_ship_via" => $customer['default_ship_via']);
    } else {
      $result['cust_branches'][] = array("code" => $customer['branch_code'], "name" => $customer['br_name'], "ref" => $customer['branch_ref'], "address" => $customer['br_address'], "tax_group_id" => $customer['tax_group_id'], "default_location" => $customer['default_location'], "default_ship_via" => $customer['default_ship_via']);
    }
  }

  if (sizeof($result) == 0)
  {
    api_error(422, _('Could not get data.'));
    return false;
  }
  
  api_success_response(json_encode($result)); 
}

function cfdi_pdf_get($uuid) {
  $sql = "select pdf_url from ".TB_PREF."timbrado_digital where uuid_sat = ".db_escape($uuid)." limit 1";
  $query = db_query($sql, "error");
  $fetch = db_fetch($query, "error");

  api_success_response(json_encode(array("url" => get_cofig("Servidor", "").$fetch['pdf_url'])));
}

function cfdi_xml_get($uuid) {
  $sql = "select xml_url from ".TB_PREF."timbrado_digital where uuid_sat = ".db_escape($uuid)." limit 1";
  $query = db_query($sql, "error");
  $fetch = db_fetch($query, "error");

  api_success_response(json_encode(array("url" => get_cofig("Servidor", "").$fetch['xml_url'])));
}

function cfdi_customer_add() {
  try {
    $app  = \Slim\Slim::getInstance('SASYS');
    $req  = $app->request();
    $info = $req->post();

    error_log("\r\n1=>".json_encode($info), 3, "/var/tmp/sasys.log");

    if (strlen($info['name']) == 0) 
    {
      api_error(422, _('The customer name cannot be empty.'));
      return false;
    } 

    if (strlen($info['debtor_ref']) == 0) 
    {
      api_error(422, _('The customer short name cannot be empty.'));
      return false;
    }

    if (strlen($info['cfdi_street']) == 0) {
      api_error(422, _('The street cannot be empty.'));
      return false;
    }

    if (strlen($info['cfdi_street_number']) == 0) {
      api_error(422, _('The street number cannot be empty.'));
      return false;
    }

    if (strlen($info['cfdi_district']) == 0) {
      api_error(422, _('The district cannot be empty.'));
      return false;
    }

    if (!is_numeric($info['cfdi_postal_code'])) {
      api_error(422, _('The postal code is only numbers.'));
      return false;
    }

    if (strlen($info['cfdi_postal_code']) != 5) {
      api_error(422, _('The postal code cannot be less or bigger than 5.'));
      return false;
    }

    if (strlen($info['cfdi_city']) == 0) {
      api_error(422, _('The city cannot be empty.'));
      return false;
    }

    if (strlen($info['cfdi_state']) == 0) {
      api_error(422, _('The state cannot be empty.'));
      return false;
    }

    if (strlen($info['cfdi_country']) == 0) {
      api_error(422, _('The country cannot be empty.'));
      return false;
    }

    if (empty($info['suite_number']) == false) {
     $info['address'] = $info['cfdi_street'].' #'.$info['cfdi_street_number'].' int '.$info['cfdi_suite_number'].', '.$info['cfdi_district'].', '.$info['cfdi_postal_code'].', '.$info['cfdi_city'].', '.$info['cfdi_state'].', '.$info['cfdi_country'];
    } else {
      $info['address'] = $info['cfdi_street'].' #'.$info['cfdi_street_number'].', '.$info['cfdi_district'].', '.$info['cfdi_postal_code'].', '.$info['cfdi_city'].', '.$info['cfdi_state'].', '.$info['cfdi_country'];
    }

    begin_transaction();

    // Cliente
    add_customer($info['name'], $info['debtor_ref'], $info['address'], $info['tax_id'], $info['curr_code'], null, null, $info['credit_status'], $info['payment_terms'], $info['discount'] / 100, $info['pymt_discount'] / 100, $info['credit_limit'], $info['sales_type'], $info['notes'], $info['cfdi_street'], $info['cfdi_street_number'], $info['cfdi_suite_number'], $info['cfdi_district'], $info['cfdi_postal_code'], $info['cfdi_city'], $info['cfdi_state'], $info['cfdi_country'], $info['client_no']);

    $customer_id = db_insert_id();

    // Sucursal
    add_branch($customer_id, $info['name'], $info['debtor_ref'], $info['address'], $info['salesman_id'], $info['area_id'], $info['tax_group_id'], '', get_company_pref('default_sales_discount_act'), get_company_pref('debtors_act'), get_company_pref('default_prompt_payment_act'), $info['location_id'], $info['address'], 0, 0, $info['ship_via_id'], $info['notes']);

    $selected_branch = db_insert_id();

    // CRM
    add_crm_person($info['name'], $info['debtor_ref'], '', $info['address'], '', '', '', '', '', '');

    add_crm_contact('cust_branch', 'general', $selected_branch, db_insert_id());

    commit_transaction();

    $sql = "select dm.debtor_no, dm.name, dm.debtor_ref, cb.branch_code, cb.br_name, cb.branch_ref, cb.br_address, cb.tax_group_id, cb.default_location, cb.default_ship_via from ".TB_PREF."debtors_master as dm inner join ".TB_PREF."cust_branch as cb on cb.debtor_no = dm.debtor_no where dm.debtor_no = ".db_escape($customer_id);
    $query = db_query($sql, "error");

    $result = array();

    while($customer = db_fetch($query, "error")) {
      if (sizeof($result) < 1) {
        $result = array("no" => $customer['debtor_no'], "name" => $customer['name'], "ref" => $customer['debtor_ref'], "user_key" => get_cofig("Llave de Usuario", ""));
        $result['cust_branches'][] = array("code" => $customer['branch_code'], "name" => $customer['br_name'], "ref" => $customer['branch_ref'], "address" => $customer['br_address'], "tax_group_id" => $customer['tax_group_id'], "default_location" => $customer['default_location'], "default_ship_via" => $customer['default_ship_via']);
      } else {
        $result['cust_branches'][] = array("code" => $customer['branch_code'], "name" => $customer['br_name'], "ref" => $customer['branch_ref'], "address" => $customer['br_address'], "tax_group_id" => $customer['tax_group_id'], "default_location" => $customer['default_location'], "default_ship_via" => $customer['default_ship_via']);
      }
    }

    if (sizeof($result) == 0)
    {
      api_error(422, _('Could not get data.'));
      return false;
    }
    
    api_success_response(json_encode($result));
  } catch (Exception $e) {
    api_error(422, _('Could not get data'));
  }
}

function cfdi_cust_branch_add() {
  try {
    $app  = \Slim\Slim::getInstance('SASYS');
    $req  = $app->request();
    $info = $req->post();

    error_log("\r\n1=>".json_encode($info), 3, "/var/tmp/sasys.log");

    $sql = "select name, debtor_ref, address, tax_id, curr_code, credit_status, payment_terms, discount, pymt_discount, credit_limit, sales_type, notes, cfdi_street, cfdi_street_number, cfdi_suite_number, cfdi_district, cfdi_postal_code, cfdi_city, cfdi_state, cfdi_country_id from ".TB_PREF."debtors_master where debtor_no=".$info['customer_id']." limit 1";
    $query = db_query($sql, "error");
    $fetch = db_fetch($query, "error");

    error_log("\r\n2=>".$sql, 3, "/var/tmp/sasys.log");
    error_log("\r\n3=>".json_encode($fetch), 3, "/var/tmp/sasys.log");

    begin_transaction();

    $customer_id = $info['customer_id'];

    error_log("\r\n4=>".$customer_id, 3, "/var/tmp/sasys.log");

    // Sucursal
    add_branch($customer_id, $fetch['name'], $fetch['debtor_ref'], $fetch['address'], $info['salesman_id'], $info['area_id'], $info['tax_group_id'], '', get_company_pref('default_sales_discount_act'), get_company_pref('debtors_act'), get_company_pref('default_prompt_payment_act'), $info['location_id'], $fetch['address'], 0, 0, $info['ship_via_id'], $info['notes']);

    $selected_branch = db_insert_id();

    error_log("\r\n5=>".$selected_branch, 3, "/var/tmp/sasys.log");

    // CRM
    add_crm_person($fetch['name'], $fetch['debtor_ref'], '', $fetch['address'], '', '', '', '', '', '');

    add_crm_contact('cust_branch', 'general', $selected_branch, db_insert_id());

    commit_transaction();

    $sql = null;
    $query = null;
    $fetch = null;

    $sql = "select branch_code, br_name, branch_ref, br_address, tax_group_id, default_location, default_ship_via from ".TB_PREF."cust_branch where branch_code = ".db_escape($selected_branch)." limit 1";
    $query = db_query($sql, "error");
    $fetch = db_fetch($query, "error");

    api_success_response(json_encode(array("code" => $fetch['branch_code'], "name" => $fetch['br_name'], "ref" => $fetch['branch_ref'], "address" => $fetch['br_address'],  "tax_group_id" => $fetch['tax_group_id'], "default_location" => $fetch['default_location'], "default_ship_via" => $fetch['default_ship_via'])));
  } catch (Exception $e) {
    api_error(404, _('Could not get data'));
  }
}

function cfdi_sales_add() {
  $app  = \Slim\Slim::getInstance('SASYS');
  $req  = $app->request();
  $info = $req->post();

  error_log("\r\n/modules/api/cfdi.inc -- POST\r\n".json_encode($info), 3, "/var/tmp/sasys.log");
  
  $cart                 = null;
  $cart                 = new Cart($info['trans_type'], 0);
  $cart->reference      = $info['ref'];
  $cart->Comments       = $info['comments'];
  $cart->order_date     = date("Y-m-d");
  $cart->document_date  = $cart->order_date;

  if (isset($info['payment']) && ($cart->payment != $info['payment'])) {
    $cart->payment        = $info['payment'];
    $cart->payment_terms  = get_payment_terms($info['payment']);
    $newpayment           = true;
  }

  if ($cart->payment_terms['cash_sale']) {
    if ($newpayment) {
      $cart->due_date   = $cart->document_date;
      $cart->phone      = $cart->cust_ref = $cart->delivery_address = '';
      $cart->ship_via   = 0;
      $cart->deliver_to = '';
    }
  } else {
    $cart->due_date         = $info['delivery_date'];
    $cart->cust_ref         = $info['cust_ref'];
    $cart->deliver_to       = $info['deliver_to'];
    $cart->delivery_address = $info['delivery_address'];
    $cart->phone            = $info['phone'];
    $cart->ship_via         = $info['ship_via'];
  }

  $cart->Location     = $info['location'];
  $cart->freight_cost = input_num('freight_cost');

  if (isset($info['email'])) {
    $cart->email = $info['email'];
  } else {
    $cart->email = '';
  }

  $cart->customer_id  = $info['customer_id'];
  $cart->Branch       = $info['branch_id'];
  $cart->sales_type   = $info['sales_type'];

  if ($cart->trans_type != ST_SALESORDER && $cart->trans_type != ST_SALESQUOTE) { // 2008-11-12 Joe Hunt
    $cart->dimension_id   = $info['dimension_id'];
    $cart->dimension2_id  = $info['dimension2_id'];
  }

  foreach($info['items'] as $item) {
    if (!isset($item['tax_type_id'])) {
      api_error(412, sprintf(_("Item '%s' not contains tax type id."), $item['description']));
      return;
    }

    $stock_id = cfdi_get_item_stock_id($item['tax_type_id']);

    if (!isset($stock_id)) {
      api_error(412, sprintf(_("Could not locate the stock id for item '%s'."), $item['description']));
      return;
    }

    $add_to_cart = $cart->add_to_cart_for_api(count($cart->line_items), $stock_id, $item['qty'], $item['price'], $item['discount'], 0, 0, $item['description']);

    if ($add_to_cart == 0) {
      api_error(412, _("You have to enter valid stock code or nonempty description"));
      return;
    }
  }

  error_log("\r\n/modules/api/cfdi.inc -- CART\r\n".json_encode($cart), 3, "/var/tmp/sasys.log");

  $can_process = cfdi_sales_can_process($info, $cart);

  error_log("\r\n/modules/api/cfdi.inc -- CAN PROCESS\r\n".json_encode($can_process), 3, "/var/tmp/sasys.log");

  if ($can_process['passed'] == false) {
    return api_error(412, $can_process['message']);
  }

  $result = $cart->write(1);

  error_log("\r\n/modules/api/cfdi.inc -- WRITE RESULT\r\n".json_encode($result), 3, "/var/tmp/sasys.log");

  if ($result == -1 || $result == null) {
    api_error(500, _('Could Not Save to Database'));
    return;
  }

  if ($cart->trans_type == ST_SALESINVOICE || $cart->trans_type == ST_CUSTCREDIT) {
    $cfdi_response = "";

    try {
      // branch_cfdi_id
      // serie_cfdi_id
      // form_payment_cfdi
      // payment_method_cfdi
      // account_number_cfi
      $data = array(
          'serie'             => $info['cfdi_serie_id'],
          'formaDePago'       => $info['cfdi_form_payment'],
          'metodoDePago'      => $info['cfdi_method_payment'],
          'numerocta'         => $info['cfdi_account_number'],
          'sucursal'          => $info['cfdi_branch_id'],
          'transid'           => key($cart->trans_no),
          'transtype'         => $cart->trans_type,
          'addenda'           => base64_encode($info['cfdi_addenda']),
          // NULL
          'condicionesDePago' => null,
          'tipoCambio'        => null,
          'moneda'            => null,
          'tipocomprobante'   => null,
      );

      $bill = emitir($data);
      $cfdi_error = false;

      error_log("\r\n/modules/api/cfdi.inc -- EMITIR\r\n".json_encode($bill), 3, "/var/tmp/sasys.log");

      if (isset($bill['error'])) {
        cfdi_delete_sale(key($cart->trans_no));
        $cfdi_response = $bill['error'];
        $cfdi_error = true;
      } else if (is_null($bill)) {
        cfdi_delete_sale(key($cart->trans_no));
        $cfdi_response = _("Can't issue CFDi invoice.");
        $cfdi_error = true;
      } else {
          $AutoFactura          = new AutoFacturaCore();
          $AutoFactura->userkey = get_cofig("Llave de Usuario", "");
          $AutoFactura->suckey  = $info['cfdi_branch_id'];
          $AutoFactura->url     = get_cofig("Servidor", "");
          
          $response = $AutoFactura->emitir($bill);

          error_log("\r\n/modules/api/cfdi.inc -- AUTOFACTURA\r\n".json_encode($response), 3, "/var/tmp/sasys.log");

          if($response['exito'] == 1){
            set_sql_digital($cart->trans_type, key($cart->trans_no), $info['cfdi_branch_id'], $response);

            // XML
            $response_xml = get_xml_digital(key($cart->trans_no));
            $xml_unique_name = uniqid('');
            $xml_file = company_path()."/attachments/".$xml_unique_name;
            $xml_url = str_replace("@llaveusuario@", get_cofig("Llave de Usuario", ""), $response_xml['xml_url']);
            copy($AutoFactura->url.$xml_url, $xml_file);
            add_attachment($cart->trans_type, key($cart->trans_no), "XML", $xml_file, $xml_unique_name, filesize($xml_file), mime_content_type($xml_file));

            // PDF
            $response_pdf = get_pdf_digital(key($cart->trans_no));
            $pdf_unique_name = uniqid('');
            $pdf_file = company_path()."/attachments/".$pdf_unique_name;
            copy($AutoFactura->url.$response_pdf['pdf_url'], $pdf_file);
            add_attachment($cart->trans_type, key($cart->trans_no), "PDF", $pdf_file, $pdf_unique_name, filesize($pdf_file), mime_content_type($pdf_file));
          } else {
            cfdi_delete_sale(key($cart->trans_no));
            $cfdi_response = _("Can't issue CFDi invoice. ").$response['mensaje'];
            $cfdi_error = true;
          }
      }
    } catch (Exception $e) {
      cfdi_delete_sale(key($cart->trans_no));
      $cfdi_response = $e->getMessage();
      $cfdi_error = true;
    }
  }

  switch($cart->trans_type) {
    case ST_SALESQUOTE:
      if (!$cfdi_error) {
        api_success_response(json_encode(array("success" => true, "msg" => sprintf(_("Quotation # %d has been entered."), key($cart->trans_no)))));
      } else {
        api_success_response(json_encode(array("success" => false, "msg" => sprintf(_("Quotation # %d has not been entered and rollback performed."), key($cart->trans_no)))));
      }
      return;
      break;
    case ST_SALESORDER:
      if (!$cfdi_error) {
        api_success_response(json_encode(array("success" => true, "msg" => sprintf(_("Order # %d has been entered."), key($cart->trans_no)))));
      } else {
        api_success_response(json_encode(array("success" => false, "msg" => sprintf(_("Order # %d has been entered and rollback performed."), key($cart->trans_no)))));
      }
      return;
      break;
    case ST_SALESINVOICE:
      if (!$cfdi_error) {
        $feeback_response = sprintf(_("Invoice # %d has been entered."), key($cart->trans_no));
      } else {
        $feeback_response = sprintf(_("Invoice # %d has been entered and rollback performed."), key($cart->trans_no));
      }
      
      if ($cfdi_response != "") {
        $feeback_response .= "\r\n".$cfdi_response;
      }

      if (!$cfdi_error) {
        #api_success_response(json_encode(array("success" => true, "msg" => $feeback_response, "extra" => $response['uuid'])));
        api_success_response(json_encode(array(
           "success" => true,
           "msg" => $feeback_response,
           "uuid" => $response['uuid'],
           "serie_folio" => $response['Folio'],
           "no_certificate" => $response['NoSerieCertificado'],
           "no_certificate_sat" => $response['NoSerieCertificadoSAT'],
           "digital_stamp" => $response['selloDigital'],
           "digital_stamp_sat" => $response['selloDigitalSAT'],
           "original_string" => $response['CadenaOriginal'],
           "original_string_sat" => $response['CadenaOriginalSAT'],
           "date_stamped" => $response['fechatimbrado'],
          )
        ));
      } else {
        api_success_response(json_encode(array("success" => false, "msg" => $feeback_response, "extra" => $response['uuid'])));
      }
      return;
      break;
    case ST_CUSTCREDIT:
      if (!$cfdi_error) {
        $feeback_response = sprintf(_("Credit Note # %d has been processed."), key($cart->trans_no));
      } else {
        $feeback_response = sprintf(_("Credit Note # %d has been processed and rollback performed."), key($cart->trans_no));
      }
      
      if ($cfdi_response != "") {
        $feeback_response .= "\r\n".$cfdi_response;
      }

      if (!$cfdi_error) {
        #api_success_response(json_encode(array("success" => true, "msg" => $feeback_response, "extra" => $response['uuid'])));
        api_success_response(json_encode(array(
           "success" => true,
           "msg" => $feeback_response,
           "uuid" => $response['uuid'],
           "serie_folio" => $response['Folio'],
           "no_certificate" => $response['NoSerieCertificado'],
           "no_certificate_sat" => $response['NoSerieCertificadoSAT'],
           "digital_stamp" => $response['selloDigital'],
           "digital_stamp_sat" => $response['selloDigitalSAT'],
           "original_string" => $response['CadenaOriginal'],
           "original_string_sat" => $response['CadenaOriginalSAT'],
           "date_stamped" => $response['fechatimbrado'],
          )
        ));
      } else {
        api_success_response(json_encode(array("success" => false, "msg" => $feeback_response, "extra" => $response['uuid'])));
      }
      return;
      break;
    case ST_CUSTDELIVERY:
      if (!$cfdi_error) {
        api_success_response(json_encode(array("success" => true, "msg" => sprintf(_("Delivery # %d has been entered."), key($cart->trans_no)))));
      } else {
        api_success_response(json_encode(array("success" => false, "msg" => sprintf(_("Delivery # %d has been entered and rollback performed."), key($cart->trans_no)))));
      }
      return;
      break;
  }

  api_success_response(_("Ok"));
}

function cfdi_sales_cancel($branch_key, $uuid) {
  $AutoFactura          = new AutoFacturaCore();
  $AutoFactura->userkey = get_cofig("Llave de Usuario", "");
  $AutoFactura->suckey  = $branch_key;
  $AutoFactura->url     = get_cofig("Servidor", "");
  $datos                = array('autofactura_id' => $uuid);
  $response             = $AutoFactura->cancelar($datos);
  
  try {
    if($response['exito'] == 1){
      set_sql_cancelar_digital($uuid, $response['url']);
      api_success_response(json_encode(array("success" => true, "msg" => _("The invoice was cancelled."))));
      return;
    } else {
      api_error(500, _('Could not cancel invoice.'));
      return;
    }
  } catch (Exception $e) {
    api_error(500, _('Could not cancel invoice. ').$e->getMessage()); 
    return;
  }
}

function cfdi_sales_can_process($info, $cart) {
  global $Refs;

  if (!$info['customer_id']) {
    return array("passed" => false, "message" => _("There is no customer selected."));
  }

  if (!$info['branch_id']) {
    return array("passed" => false, "message" => _("This customer has no branch defined."));
  }
  
  if (!is_date($cart->order_date)) {
    return array("passed" => false, "message" => _("The entered date is invalid."));
  }

  if ($cart->trans_type != ST_SALESORDER && $cart->trans_type != ST_SALESQUOTE && !is_date_in_fiscalyear($cart->order_date)) {
    return array("passed" => false, "message" => _("The entered date is not in fiscal year"));
  }

  if (count($cart->line_items) == 0)  {
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

    if (date1_greater_date2($cart->order_date, $info['delivery_date'])) {
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

  if (!$Refs->is_valid($info['ref'])) {
    return array("passed" => false, "message" => _("You must enter a reference."));
  }

  if (!db_has_currency_rates($cart->customer_currency, $cart->order_date)){
    return false;
  }
  
  if ($cart->get_items_total() < 0) {
    return array("passed" => false, "message" => _("Invoice total amount cannot be less than zero."));
  }

  return array("passed" => true);
}

function cfdi_delete_sale($trans_no) {
  $sql = "delete from ".TB_PREF."debtor_trans where type in (".ST_SALESINVOICE.", ".ST_CUSTCREDIT.", ".ST_CUSTDELIVERY.") and trans_no = ".$trans_no;
  db_query($sql, "error");
  $sql = "delete from ".TB_PREF."debtor_trans_details where debtor_trans_type in (".ST_SALESINVOICE.", ".ST_CUSTCREDIT.", ".ST_CUSTDELIVERY.") and debtor_trans_no = ".$trans_no;
  db_query($sql, "error");
  $sql = "delete from ".TB_PREF."trans_tax_details where trans_type in (".ST_SALESINVOICE.", ".ST_CUSTCREDIT.", ".ST_CUSTDELIVERY.") and trans_no = ".$trans_no;
  db_query($sql, "error");
  $sql = "delete from ".TB_PREF."sales_orders where trans_type in (".ST_SALESORDER.") and order_no = ".$trans_no;
  db_query($sql, "error");
  $sql = "delete from ".TB_PREF."sales_order_details where trans_type in (".ST_SALESORDER.") and order_no = ".$trans_no;
  db_query($sql, "error");
}

function cfdi_get_item_stock_id($tax_type_id) {
  $sql = "select stock_id from ".TB_PREF."stock_master where tax_type_id = ".$tax_type_id." limit 1";
  $query = db_query($sql, "error");
  $fetch = db_fetch($query, "error");

  if (isset($fetch)) {
    return $fetch['stock_id'];
  } else {
    return null;
  }
}

?>