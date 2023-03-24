<?php
/**
 * Author : Danish Naseem <dani.115115@gmail.com>
 */


// Posting new Purchase Invoice  - POST Request
$info=array (
    'supplier_id' => 2,
    'OrderDate' => '10/10/2021',
    'ref' => '001',
    'due_date' => '10/10/2021',
    'supp_ref' => '001',
    'dimension' => 0,
    'dimension2' => 0,
    'StkLocation' => 'DEF',
    'delivery_address' => 'N/A',
    '_stock_id_edit' => '',
    'qty' => '1',
    'cash_account' => '',
    'Comments' => substr('Comment should be limited to 255 characters only. As this is setup in the database field size',0,255),
    'Commit' => 'Process Invoice',
    'sales_type' => '1',
    'curr_code' => 'USD',
    'orig_order_date' => '10/10/2021',
    'tax_included' => 0,
    'Location' => 'DEF',
    'line_items' =>[
        [
            'line_no' => 0,
            'po_detail_rec' => NULL,
            'grn_item_id' => 0,
            'stock_id' => 'stock_001',
            'item_description' => '',
            'price' => 10.5,
            'units' => 'each',
            'req_del_date' => '',
            'tax_type' => '0',
            'tax_type_name' => 'Regular',
            'quantity' => 1,
            'qty_inv' => 0,
            'receive_qty' => 0,
            'qty_received' => 0,
            'standard_cost' => 0,
            'descr_editable' => '0',
        ]
    ]

);


// Deleting / Voiding Purchase Invoice - POST Request
$info=array (
    'trans_no' => 3,
    'date_' => '10/10/2021',
    'memo_' => substr('Comment should be limited to 255 characters only. As this is setup in the database field size',0,255),
);

class Purchase_Test extends Crud_Base
{

}

