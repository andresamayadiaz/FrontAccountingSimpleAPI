<?php
/**
 * Author : Danish Naseem <dani.115115@gmail.com>
 */


// Posting new Customer Payment Invoice  - POST Request
$info=array (
    'customer_id' => 2,
    'BranchID' => 3,
    'bank_account' => '1',
    'trans_no' => '',
    'DateBanked' => '10/10/2021',
    'ref' => '001',
    'charge' => '',
    'dimension_id' => '0',
    'dimension2_id' => '0',
    'TotalNumberOfAllocs' => '0',
    'discount' => '',
    'amount' => 10,
    'memo_' => substr('Comment should be limited to 255 characters only. As this is setup in the database field size.',0,255),
    'AddPaymentItem' => 'Add Payment',
);


// Deleting / Voiding Customer Payment Invoice - POST Request
$info=array (
    'trans_no' => 3,
    'date_' => '10/10/2021',
    'memo_' => substr('Comment should be limited to 255 characters only. As this is setup in the database field size',0,255),
);

class CustomerPayment_Test extends Crud_Base
{

}

