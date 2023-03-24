<?php
/**
 * Author : Danish Naseem <dani.115115@gmail.com>
 */

$path_to_root = "../..";

$page_security = 'SA_PURCHASEORDER';
include_once($path_to_root . "/sales/includes/cart_class.inc");
include_once($path_to_root . "/purchasing/includes/po_class.inc");
//include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");
include_once($path_to_root . "/purchasing/includes/db/suppliers_db.inc");
include_once($path_to_root . "/reporting/includes/reporting.inc");
include_once($path_to_root . "/includes/types.inc");


function purchase_add() {

    $app	= \Slim\Slim::getInstance('SASYS');
    $req	= $app->request();
    $info	= $req->post();
    /*$cart	= null;
    $cart	= new Cart($info['trans_type'], 20);*/

    $purchase_obj=new purch_order();
    foreach ($info as $key => $value) {
        $purchase_obj->{$key} = $value;
    }
    $line_items=[];
    foreach ($purchase_obj->line_items as $k =>$v){
        $line_items[]=new po_line_details($v['line_no'], $v['stock_id'], $v['item_description'], $v['quantity'], $v['price'], $v['units'], $v['req_del_date'], $v['qty_inv'], $v['qty_received'], $v['grn_item_id']);
    }
    $purchase_obj->line_items=$line_items;
    $purchase_obj->orig_order_date=$info['orig_order_date'];
    $purchase_obj->tax_included=$info['tax_included'];
    $purchase_obj->trans_type=ST_SUPPINVOICE;
    $purchase_obj->reference=$info['ref'];

    $_SESSION['PO']=$purchase_obj;
    if (can_commit()) {
        $cart=$_SESSION['PO'];
        $trans_no = add_direct_supp_trans($cart);
        if($trans_no){
            api_success_response(sprintf(_("Invoice # %d has been entered."), $trans_no));
        }else{
            api_error(412, 'Failed to add invoice.');
        }
    }else{
        api_error(500, 'Invoice data is invalid.');
    }


}

function can_commit()
{
    if (!get_post('supplier_id'))
    {
        display_error(_("There is no supplier selected."));
        set_focus('supplier_id');
        return false;
    }

    if (!is_date($_POST['OrderDate']))
    {
        display_error(_("The entered order date is invalid."));
        set_focus('OrderDate');
        return false;
    }
    if (($_SESSION['PO']->trans_type == ST_SUPPRECEIVE || $_SESSION['PO']->trans_type == ST_SUPPINVOICE)
        && !is_date_in_fiscalyear($_POST['OrderDate'])) {
        display_error(_("The entered date is out of fiscal year or is closed for further data entry."));
        set_focus('OrderDate');
        return false;
    }

    if (($_SESSION['PO']->trans_type==ST_SUPPINVOICE) && !is_date($_POST['due_date']))
    {
        display_error(_("The entered due date is invalid."));
        set_focus('due_date');
        return false;
    }

    if (!$_SESSION['PO']->order_no)
    {
        if (!check_reference(get_post('ref'), $_SESSION['PO']->trans_type))
        {
            set_focus('ref');
            return false;
        }
    }

    if ($_SESSION['PO']->trans_type == ST_SUPPINVOICE && trim(get_post('supp_ref')) == false)
    {
        display_error(_("You must enter a supplier's invoice reference."));
        set_focus('supp_ref');
        return false;
    }
    if ($_SESSION['PO']->trans_type==ST_SUPPINVOICE
        && is_reference_already_there($_SESSION['PO']->supplier_id, get_post('supp_ref'), $_SESSION['PO']->order_no))
    {
        display_error(_("This invoice number has already been entered. It cannot be entered again.") . " (" . get_post('supp_ref') . ")");
        set_focus('supp_ref');
        return false;
    }
    if ($_SESSION['PO']->trans_type == ST_PURCHORDER && get_post('delivery_address') == '')
    {
        display_error(_("There is no delivery address specified."));
        set_focus('delivery_address');
        return false;
    }
    if (get_post('StkLocation') == '')
    {
        display_error(_("There is no location specified to move any items into."));
        set_focus('StkLocation');
        return false;
    }
    if (!db_has_currency_rates($_SESSION['PO']->curr_code, $_POST['OrderDate'], true))
        return false;
    if ($_SESSION['PO']->order_has_items() == false)
    {
        display_error (_("The order cannot be placed because there are no lines entered on this order."));
        return false;
    }
    if (floatcmp(input_num('prep_amount'), $_SESSION['PO']->get_trans_total()) > 0)
    {
        display_error(_("Required prepayment is greater than total invoice value."));
        set_focus('prep_amount');
        return false;
    }

    return true;
}

function purchase_delete($branch_id,$uuid){
    $resp=['status'=>true,'msg'=>''];
    try {
        $_POST['filterType']=ST_SUPPINVOICE;
        if (check_valid_entries()==true)
        {
            $void_entry = get_voided_entry($_POST['filterType'], $_POST['trans_no']);
            if ($void_entry != null)
            {
                $resp['status']=false;
                $resp['error_code']=500;
                $resp['msg']="The selected transaction has already been voided.";

            } else {
                $msg = void_transaction($_POST['filterType'], $_POST['trans_no'], $_POST['date_'], $_POST['memo_']);
                if (!$msg) {
                    $resp['status'] = true;
                } else {
                    $resp['status'] = false;
                    $resp['error_code'] = 500;
                    $resp['msg'] = $msg;
                }
            }
        }
    } catch (Exception $e) {
        error_log($e->getMessage(), 3, "/var/tmp/sales_cancel.log");
        $resp['msg']='Could not cancel invoice. ';
        return;
    }
    if($resp['status']){
        api_success_response($resp);
    }else{
        api_error($resp['error_code'], $resp['msg']);
    }

}

function check_valid_entries()
{
    if (is_closed_trans($_POST['filterType'],$_POST['trans_no']))
    {
        display_error(_("The selected transaction was closed for edition and cannot be voided."));
        set_focus('trans_no');
        return false;
    }
    if (!is_date($_POST['date_']))
    {
        display_error(_("The entered date is invalid."));
        set_focus('date_');
        return false;
    }
    if (!is_date_in_fiscalyear($_POST['date_']))
    {
        display_error(_("The entered date is out of fiscal year or is closed for further data entry."));
        set_focus('date_');
        return false;
    }

    if (!is_numeric($_POST['trans_no']) OR $_POST['trans_no'] <= 0)
    {
        display_error(_("The transaction number is expected to be numeric and greater than zero."));
        set_focus('trans_no');
        return false;
    }
    return true;
}


?>