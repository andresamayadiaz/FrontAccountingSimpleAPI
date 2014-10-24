<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');

class SupplierTest extends PHPUnit_Framework_TestCase
{

	public function testCRUD_Ok()
	{
		$client = TestEnvironment::client();

		// List
		$response = $client->get('/modules/api/suppliers/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count0 = count($result);
		$this->assertEquals(0, $count0);

		// Add
// 		$id = TestEnvironment::createId();
		$response = $client->post('/modules/api/suppliers/', array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'supp_name' => 'supp_name',
				'supp_ref' => 'supp_ref',
				'address' => 'address',
				'supp_address' => 'supp_address',
				'gst_no' => 'gst_no',
				'website' => 'website',
				'supp_account_no' => 'supp_account_no',
				'bank_account' => 'bank_account',
				'credit_limit' => '1000',
				'curr_code' => 'USD',
				'payment_terms' => '1',
				'payable_account' => '1010',
				'purchase_account' => '1020',
				'payment_discount_account' => '1030',
				'notes' => 'notes',
				'tax_group_id' => '1',
				'tax_included' => '1'
			)
		));
		$this->assertEquals('201', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

// 		var_dump($result);
		$id = $result->supplier_id;

		$expected = new stdClass();
		$expected->supplier_id = '1';
		$expected->supp_name = 'supp_name';
		$expected->supp_ref = 'supp_ref';
		$expected->address = 'address';
		$expected->supp_address = 'supp_address';
		$expected->gst_no = 'gst_no';
		$expected->website = 'website';
		$expected->supp_account_no = 'supp_account_no';
		$expected->bank_account = 'bank_account';
		$expected->credit_limit = '1000';
		$expected->curr_code = 'USD';
		$expected->payment_terms = '1';
		$expected->payable_account = '1010';
		$expected->purchase_account = '1020';
		$expected->payment_discount_account = '1030';
		$expected->notes = 'notes';
		$expected->tax_group_id = '1';
		$expected->tax_included = '1';
		$expected->contact = '';
		$expected->dimension_id = '0';
		$expected->dimension2_id = '0';
		$expected->inactive = '0';

		$expected->{ '0' } = '1';
		$expected->{ '1' } = 'supp_name';
		$expected->{ '2' }  = 'supp_ref';
		$expected->{ '3' }  = 'address';
		$expected->{ '4' }  = 'supp_address';
		$expected->{ '5' }  = 'gst_no';
		$expected->{ '6' }  = '';
		$expected->{ '7' }  = 'supp_account_no';
		$expected->{ '8' }  = 'website';
		$expected->{ '9' }  = 'bank_account';
		$expected->{ '10' }  = 'USD';
		$expected->{ '11' }  = '1';
		$expected->{ '12' }  = '1';
		$expected->{ '13' }  = '0';
		$expected->{ '14' }  = '0';
		$expected->{ '15' }  = '1';
		$expected->{ '16' }  = '1000';
		$expected->{ '17' }  = '1020';
		$expected->{ '18' }  = '1010';
		$expected->{ '19' }  = '1030';
		$expected->{ '20' }  = 'notes';
		$expected->{ '21' }  = '0';

		$this->assertEquals($expected, $result);

		// List again
		$response = $client->get('/modules/api/suppliers/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count1 = count($result);
		$this->assertEquals($count0 + 1, $count1);

		// Get by id
		$response = $client->get('/modules/api/suppliers/' . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$this->assertEquals($expected, $result);

		// Write back
		$response = $client->put('/modules/api/suppliers/' . $id, array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'supp_name' => 'new supp_name',
				'supp_ref' => 'new supp_ref',
				'address' => 'new address',
				'supp_address' => 'new supp_address',
				'gst_no' => 'new gst_no',
				'website' => 'new website',
				'supp_account_no' => 'new supp_account_no',
				'bank_account' => 'new bank_account',
				'credit_limit' => '2000',
				'curr_code' => 'NZD',
				'payment_terms' => '2',
				'payable_account' => '2010',
				'purchase_account' => '2020',
				'payment_discount_account' => '2030',
				'notes' => 'new notes',
				'tax_group_id' => '2',
				'tax_included' => '2'
			)
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		// Get by id to read back
		$response = $client->get('/modules/api/suppliers/' . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$expected->supp_name = 'new supp_name';
		$expected->supp_ref = 'new supp_ref';
		$expected->address = 'new address';
		$expected->supp_address = 'new supp_address';
		$expected->gst_no = 'new gst_no';
		$expected->website = 'new website';
		$expected->supp_account_no = 'new supp_account_no';
		$expected->bank_account = 'new bank_account';
		$expected->credit_limit = '2000';
		$expected->curr_code = 'NZD';
		$expected->payment_terms = '2';
		$expected->payable_account = '2010';
		$expected->purchase_account = '2020';
		$expected->payment_discount_account = '2030';
		$expected->notes = 'new notes';
		$expected->tax_group_id = '2';
		$expected->tax_included = '2';
		$expected->contact = '';
		$expected->dimension_id = '0';
		$expected->dimension2_id = '0';
		$expected->inactive = '0';

		$expected->{ '0' } = '1';
		$expected->{ '1' } = 'new supp_name';
		$expected->{ '2' }  = 'new supp_ref';
		$expected->{ '3' }  = 'new address';
		$expected->{ '4' }  = 'new supp_address';
		$expected->{ '5' }  = 'new gst_no';
		$expected->{ '6' }  = '';
		$expected->{ '7' }  = 'new supp_account_no';
		$expected->{ '8' }  = 'new website';
		$expected->{ '9' }  = 'new bank_account';
		$expected->{ '10' }  = 'NZD';
		$expected->{ '11' }  = '2';
		$expected->{ '12' }  = '2';
		$expected->{ '13' }  = '0';
		$expected->{ '14' }  = '0';
		$expected->{ '15' }  = '2';
		$expected->{ '16' }  = '2000';
		$expected->{ '17' }  = '2020';
		$expected->{ '18' }  = '2010';
		$expected->{ '19' }  = '2030';
		$expected->{ '20' }  = 'new notes';
		$expected->{ '21' }  = '0';

		$this->assertEquals($expected, $result);

		// List again
		$response = $client->get('/modules/api/suppliers/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count1 = count($result);
		$this->assertEquals($count0 + 1, $count1);

		$this->assertEquals($id, $result[$count1 - 1]->supplier_id);

		// Delete
		$response = $client->delete('/modules/api/suppliers/' . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		// List again
		$response = $client->get('/modules/api/suppliers/', array(
			'headers' => TestEnvironment::headers()
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count2 = count($result);
		$this->assertEquals($count0, $count2);

	}

}
