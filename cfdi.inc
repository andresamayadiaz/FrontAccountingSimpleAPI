<?php

/*
= Christian Estrella
= 08/2013
= API destinada a emision con Autofactura.
*/

include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/sales/includes/sales_ui.inc");
include_once($path_to_root . "/sales/includes/ui/sales_order_ui.inc");
include_once($path_to_root . "/sales/includes/db/cust_trans_details_db.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/sales/includes/db/sales_types_db.inc");
include_once($path_to_root . "/admin/db/attachments_db.inc");
include_once($path_to_root . "/modules/timbrado/includes/db/comprobantes_db.php.inc");
include_once($path_to_root . "/modules/timbrado/includes/db/llavero_db.php.inc");
include_once($path_to_root . "/modules/timbrado/includes/generate/AutoFacturaCore.php.inc");
include_once($path_to_root . "/modules/timbrado/includes/db/configuraciones_db.php.inc");

function cfdi_series_all($branch_key) {
  $AutoFactura          = new AutoFacturaCore();
  $AutoFactura->userkey = get_cofig("Llave de Usuario", "");
  $AutoFactura->suckey  = $branch_key;
  $AutoFactura->url     = get_cofig("Servidor", "");
  $response = $AutoFactura->series();
  api_success_response(json_encode($response));
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

function cfdi_sales_add() {
  $app  = \Slim\Slim::getInstance('SASYS');
  $req  = $app->request();
  $info = $req->post();
  
  $cart                 = null;
  $cart                 = new Cart($info['trans_type'], 0);
  $cart->reference      = $info['ref'];
  $cart->Comments       = $info['comments'];
  $cart->document_date  = $info['order_date'];

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
    add_to_order($cart, $item['stock_id'], $item['qty'], $item['price'], $item['discount'], $item['description']);
  }

  $can_process = cfdi_sales_can_process($info, $cart);

  if ($can_process == false) {
    api_error(412, $can_process['message']);
  }

  $result = $cart->write(1);

  if ($result == -1) {
    //return array("passed" => false, "message" => _("The entered reference is already in use."));
    api_error(500, 'Could Not Save to Database');
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
          // NULL
          'condicionesDePago' => null,
          'tipoCambio'        => null,
          'moneda'            => null,
          'tipocomprobante'   => null,
      );

      $bill = emitir($data);

      if (isset($bill['error'])) {
        $cfdi_response = $bill['error'];
      } else if (is_null($bill)) {
        $cfdi_response = _('But for your invoice unidentified error has not yet been digitized CFDi. Please, do it manually.');
      } else {
          $AutoFactura          = new AutoFacturaCore();
          $AutoFactura->userkey = get_cofig("Llave de Usuario", "");
          $AutoFactura->suckey  = $info['cfdi_branch_id'];
          $AutoFactura->url     = get_cofig("Servidor", "");
          
          $response = $AutoFactura->emitir($bill);

          if($response['exito'] == 1){
            set_sql_digital(key($cart->trans_no), $info['cfdi_branch_id'], $response);

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
            $cfdi_response = _('But for your invoice unidentified error has not yet been digitized CFDi. Please, do it manually. ').$response['mensaje'];
          }
      }
    } catch (Exception $e) {
      $cfdi_response = $e->getMessage();
    }
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
      
      if ($cfdi_response != "") {
        $feeback_response .= "\r\n".$cfdi_response;
      }

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
      api_success_response(_("The invoice was cancelled."));
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
  
  if (!is_date($info['order_date'])) {
    return array("passed" => false, "message" => _("The entered date is invalid."));
  }

  if ($cart->trans_type != ST_SALESORDER && $cart->trans_type != ST_SALESQUOTE && !is_date_in_fiscalyear($info['order_date'])) {
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

    if (!check_num('freight_cost', 0)) {
      return array("passed" => false, "message" => _("The shipping cost entered is expected to be numeric."));
    }

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

  if (!$Refs->is_valid($info['ref'])) {
    return array("passed" => false, "message" => _("You must enter a reference."));
  }

  if (!db_has_currency_rates($cart->customer_currency, $info['order_date'])){
    return false;
  }
  
  if ($cart->get_items_total() < 0) {
    return array("passed" => false, "message" => _("Invoice total amount cannot be less than zero."));
  }

  return true;
}

?>