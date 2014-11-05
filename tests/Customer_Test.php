<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');

class CustomerTest extends PHPUnit_Framework_TestCase
{

	public function testCRUD_Ok()
	{
		$client = TestEnvironment::client();

		// List
		$response = $client->get('/modules/api/customers/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count0 = count($result);
		$this->assertEquals(0, $count0);

		// Add
// 		$id = TestEnvironment::createId();
		$response = $client->post('/modules/api/customers/', array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'custname' => 'custname',
				'cust_ref' => 'cust_ref',
				'address' => 'address',
				'tax_id' => 'tax_id',
				'curr_code' => 'USD',
				'credit_status' => '1',
				'payment_terms' => '1',
				'discount' => '0',
				'pymt_discount' => '0',
				'credit_limit' => '1000',
				'sales_type' => '1',
				'notes' => 'notes'
			)
		));
		$this->assertEquals('201', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);
		$id = $result->debtor_no;

		$expected = new stdClass();
		$expected->debtor_no = '1';
		$expected->name = 'custname';
		$expected->debtor_ref = 'cust_ref';
		$expected->address = 'address';
		$expected->tax_id = 'tax_id';
		$expected->curr_code = 'USD';
		$expected->sales_type = '1';
		$expected->dimension_id = '0';
		$expected->dimension2_id = '0';
		$expected->credit_status = '1';
		$expected->payment_terms = '1';
		$expected->discount = '0';
		$expected->pymt_discount = '0';
		$expected->credit_limit = '1000';
		$expected->notes = 'notes';
		$expected->inactive = '0';

		$expected->{ '0' } = '1';
		$expected->{ '1' } = 'custname';
		$expected->{ '2' }  = 'cust_ref';
		$expected->{ '3' }  = 'address';
		$expected->{ '4' }  = 'tax_id';
		$expected->{ '5' }  = 'USD';
		$expected->{ '6' }  = '1';
		$expected->{ '7' }  = '0';
		$expected->{ '8' }  = '0';
		$expected->{ '9' }  = '1';
		$expected->{ '10' }  = '1';
		$expected->{ '11' }  = '0';
		$expected->{ '12' }  = '0';
		$expected->{ '13' }  = '1000';
		$expected->{ '14' }  = 'notes';
		$expected->{ '15' }  = '0';

		$this->assertEquals($expected, $result);

		// List again
		$response = $client->get('/modules/api/customers/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count1 = count($result);
		$this->assertEquals($count0 + 1, $count1);

		// Get by id
		$response = $client->get('/modules/api/customers/' . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$this->assertEquals($expected, $result);

		// Write back
		$response = $client->put('/modules/api/customers/' . $id, array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'custname' => 'new custname',
				'cust_ref' => 'new cust_ref',
				'address' => 'new address',
				'tax_id' => 'new tax_id',
				'curr_code' => 'NZD',
				'credit_status' => '2',
				'payment_terms' => '2',
				'discount' => '1',
				'pymt_discount' => '1',
				'credit_limit' => '2000',
				'sales_type' => '2',
				'notes' => 'new notes'
			)
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		// Get by id to read back
		$response = $client->get('/modules/api/customers/' . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$expected->name = 'new custname';
		$expected->debtor_ref = 'new cust_ref';
		$expected->address = 'new address';
		$expected->tax_id = 'new tax_id';
		$expected->curr_code = 'NZD';
		$expected->sales_type = '2';
// 		$expected->dimension_id = '0';
// 		$expected->dimension2_id = '0';
		$expected->credit_status = '2';
		$expected->payment_terms = '2';
		$expected->discount = '1';
		$expected->pymt_discount = '1';
		$expected->credit_limit = '2000';
		$expected->notes = 'new notes';
// 		$expected->inactive = '0';

		$expected->{ '0' } = '1';
		$expected->{ '1' } = 'new custname';
		$expected->{ '2' }  = 'new cust_ref';
		$expected->{ '3' }  = 'new address';
		$expected->{ '4' }  = 'new tax_id';
		$expected->{ '5' }  = 'NZD';
		$expected->{ '6' }  = '2';
		$expected->{ '7' }  = '0';
		$expected->{ '8' }  = '0';
		$expected->{ '9' }  = '2';
		$expected->{ '10' }  = '2';
		$expected->{ '11' }  = '1';
		$expected->{ '12' }  = '1';
		$expected->{ '13' }  = '2000';
		$expected->{ '14' }  = 'new notes';
		$expected->{ '15' }  = '0';

		$this->assertEquals($expected, $result);

		// List again
		$response = $client->get('/modules/api/customers/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count1 = count($result);
		$this->assertEquals($count0 + 1, $count1);

		$this->assertEquals($id, $result[$count1 - 1]->debtor_no);

		// Delete
		$response = $client->delete('/modules/api/customers/' . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		// List again
		$response = $client->get('/modules/api/customers/', array(
			'headers' => TestEnvironment::headers()
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count2 = count($result);
		$this->assertEquals($count0, $count2);

	}

}
