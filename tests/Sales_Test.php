<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');

$path_to_root = SRC_PATH;
require_once(SRC_PATH . '/includes/types.inc');

class SalesTest extends PHPUnit_Framework_TestCase
{

	public function testCRUD_Ok()
	{
		$client = TestEnvironment::client();

		TestEnvironment::createCustomer($client, 'TEST_CUST', 'Test Customer');
		TestEnvironment::createItem($client, 'TEST_ITEM', 'Test Item');

		// List
		$response = $client->get('/modules/api/sales/' . ST_SALESINVOICE, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count0 = count($result);
		$this->assertEquals(0, $count0);

		// Add
		$ref = TestEnvironment::createId();
		//?XDEBUG_SESSION_START=cambell
		$response = $client->post('/modules/api/sales/', array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'trans_type' => ST_SALESINVOICE,
				'ref' => $ref, // TODO Ideally the api would default this and return.
				'comments' => 'comments',
				'order_date' => '01/02/2013',

				'delivery_date' => '03/04/2013',
				'cust_ref' => 'cust_ref',
				'deliver_to' => 'deliver_to',
				'delivery_address' => 'delivery_address',
				'phone' => 'phone',
				'ship_via' => 'ship_via',
				'location' => 'DEF',
				'freight_cost' => '0',
				'customer_id' => '2',
				'branch_id' => '2',
				'sales_type' => '1',
				'dimension_id' => '0',
				'dimension2_id' => '0',

				'items' => array(
					0 => array(
						'stock_id' => 'TEST_ITEM',
						'qty' => '1',
						'price' => '2',
						'discount' => '0',
						'description' => 'description'
					)
				),
			)
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		// List again
		$response = $client->get('/modules/api/sales/' . ST_SALESINVOICE .'/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count1 = count($result);
		$this->assertEquals($count0 + 1, $count1);

		$id = $result[0]->trans_no;

		// Get by id
		$response = $client->get('/modules/api/sales/' . $id . '/' . ST_SALESINVOICE, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$expected = new stdClass();
		$expected->ref = $ref;
		$expected->comments = "comments";
		$expected->order_date = "01/02/2013";
		$expected->payment = "0";
		$expected->payment_terms = false;
		$expected->due_date =  "03/04/2013";
		$expected->phone = "";
		$expected->cust_ref = "cust_ref";
		$expected->delivery_address = "delivery_address";
		$expected->ship_via = "0";
		$expected->deliver_to = "Test Customer";
		$expected->delivery_date = "03/04/2013";
		$expected->location = NULL;
		$expected->freight_cost = "0";
		$expected->email = "";
		$expected->customer_id = "2";
		$expected->branch_id = "2";
		$expected->sales_type = "1";
		$expected->dimension_id = "0";
		$expected->dimension2_id = "0";
		$item = new stdClass();
		$item->id = "2";
		$item->stock_id = "TEST_ITEM";
		$item->qty = 1;
		$item->units = "ea";
		$item->price = "2";
		$item->discount = "0";
		$item->description = "description";
		$expected->line_items = array($item);
		$expected->sub_total = 2;
		$expected->display_total = 2;

		$this->assertEquals($expected, $result);

		// Write back
		$response = $client->put('/modules/api/sales/' . $id . '/' . ST_SALESINVOICE, array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'trans_type' => ST_SALESINVOICE,
				'ref' => $ref, // TODO Ideally the api would default this and return.
				'comments' => 'new comments',
				'order_date' => '02/03/2013',

				'delivery_date' => '04/05/2013',
				'cust_ref' => 'cust_ref',
				'deliver_to' => 'new deliver_to',
				'delivery_address' => 'new delivery_address',
				'phone' => 'new phone',
				'ship_via' => 'new ship_via',
				'location' => 'DEF',
				'freight_cost' => '0',
				'customer_id' => '2',
				'branch_id' => '2',
				'sales_type' => '1',
				'dimension_id' => '0',
				'dimension2_id' => '0',

// 				'items' => array(
// 					0 => array(
// 						'stock_id' => 'TEST_ITEM',
// 						'qty' => '2',
// 						'price' => '3',
// 						'discount' => '0',
// 						'description' => 'new description'
// 					)
// 				),
			)
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		// Get by id
		$response = $client->get('/modules/api/sales/' . $id . '/' . ST_SALESINVOICE, array(
		    'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$expected = new stdClass();
		$expected->ref = $ref;
		$expected->comments = "new comments";
		$expected->order_date = "02/03/2013";
		$expected->payment = "0";
		$expected->payment_terms = false;
		$expected->due_date =  "04/05/2013";
		$expected->phone = "";
		$expected->cust_ref = "cust_ref";
		$expected->delivery_address = "delivery_address";
		$expected->ship_via = "0";
		$expected->deliver_to = "Test Customer";
		$expected->delivery_date = "04/05/2013";
		$expected->location = NULL;
		$expected->freight_cost = "0";
		$expected->email = "";
		$expected->customer_id = "2";
		$expected->branch_id = "2";
		$expected->sales_type = "1";
		$expected->dimension_id = "0";
		$expected->dimension2_id = "0";
		$item = new stdClass();
		$item->id = "2";
		$item->stock_id = "TEST_ITEM";
		$item->qty = 1;
		$item->units = "ea";
		$item->price = "2";
		$item->discount = "0";
		$item->description = "description";
		$expected->line_items = array($item);
		$expected->sub_total = 2;
		$expected->display_total = 2;

		$this->assertEquals($expected, $result);

		/* Delete is currently untested, and not implemented with standard FA
		// Delete
		$response = $client->delete('/modules/api/sales/' . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		// List again
		$response = $client->get('/modules/api/sales/', array(
			'headers' => TestEnvironment::headers()
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count2 = count($result);
		$this->assertEquals($count0, $count2);
		*/

	}

}
