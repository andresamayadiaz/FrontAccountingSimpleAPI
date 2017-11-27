<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');
require_once(TEST_PATH . '/Crud_Base.php');

const CUSTOMER_POST_DATA = array(
	'name' => 'custname',
	'debtor_ref' => 'debtor_ref',
	'address' => 'address',
	'tax_id' => 'tax_id',
	'curr_code' => 'USD',
	'credit_status' => '1',
	'payment_terms' => '1',
	'discount' => '0',
	'pymt_discount' => '0',
	'credit_limit' => '1000',
	'sales_type' => '1',
	'notes' => 'notes',
	'dimension_id' => '0',
	'dimension2_id' => '0',
	'inactive' => '0',
);

const CUSTOMER_PUT_DATA = array(
	'name' => 'new custname',
	'debtor_ref' => 'new debtor_ref',
	'address' => 'new address',
	'tax_id' => 'new tax_id',
	'curr_code' => 'NZD',
	'credit_status' => '2',
	'payment_terms' => '2',
	'discount' => '1',
	'pymt_discount' => '1',
	'credit_limit' => '2000',
	'sales_type' => '2',
	'notes' => 'new notes',
	'dimension_id' => '0',  // Not yet implemented
	'dimension2_id' => '0', // Not yet implemented
	'inactive' => '0',      // Not yet implemented
);

class CustomerTest extends Crud_Base
{

	private $postData = CUSTOMER_POST_DATA;
	
	private $putData = CUSTOMER_PUT_DATA;
	
	public function __construct()
	{
		parent::__construct(
			'/modules/api/customers/',
			'debtor_no',
			$this->postData,
			$this->putData
		);
	}

	// 	public function testCRUD_Ok();
}

// class CustomerJsonTest extends Crud_Base
// {

// 	private $postData = CUSTOMER_POST_DATA;
	
// 	private $putData = CUSTOMER_PUT_DATA;
	
// 	public function __construct()
// 	{
// 		parent::__construct(
// 			'/modules/api/customers/',
// 			'debtor_no',
// 			$this->postData,
// 			$this->putData
// 		);
// 		$this->method = Crud_Base::JSON;
// 	}

// 	// 	public function testCRUD_Ok();
// }
