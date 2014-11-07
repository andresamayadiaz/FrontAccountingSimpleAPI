<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');

class InventoryTest extends PHPUnit_Framework_TestCase
{

	public function testCRUD_Ok()
	{
		$client = TestEnvironment::client();

		// List
		$response = $client->get('/modules/api/inventory/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count0 = count($result);

		// Add
		$id = TestEnvironment::createId();
		$response = $client->post('/modules/api/inventory/', array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'stock_id' => $id,
				'description' => 'description',
				'long_description' => 'long description',
				'category_id' => '1',
				'tax_type_id' => '1',
				'units' => 'ea',
				'mb_flag' => '0',
				'sales_account' => '1',
				'inventory_account' => '1',
				'cogs_account' => '1',
				'adjustment_account' => '1',
				'assembly_account' => '1'
			)
		));
		$this->assertEquals('201', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$this->assertEquals($id, $result->stock_id);
// 		var_dump($result);

		// List again
		$response = $client->get('/modules/api/inventory/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count1 = count($result);
		$this->assertEquals($count0 + 1, $count1);

		// Get by id
		$response = $client->get('/modules/api/inventory/' . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		// Write back
		$response = $client->put('/modules/api/inventory/' . $id, array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'stock_id' => $id, // TODO This shouldn't be required for an edit CP 2014-10
				'description' => 'changed',
				'long_description' => 'changed long description',
				'category_id' => '1',
				'tax_type_id' => '1',
				'units' => 'ea',
				'mb_flag' => '0',
				'sales_account' => '1',
				'inventory_account' => '1',
				'cogs_account' => '1',
				'adjustment_account' => '1',
				'assembly_account' => '1'
			)
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		// List again
		$response = $client->get('/modules/api/inventory/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count1 = count($result);
		$this->assertEquals($count0 + 1, $count1);

		$this->assertEquals($id, $result[0]->stock_id);
		$this->assertEquals('changed', $result[0]->description);
		$this->assertEquals('changed long description', $result[0]->long_description);

		// Delete
		$response = $client->delete('/modules/api/inventory/' . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		// List again
		$response = $client->get('/modules/api/inventory/', array(
			'headers' => TestEnvironment::headers()
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count2 = count($result);
		$this->assertEquals($count0, $count2);

	}

	public function testMovementTypes_Ok()
	{
		$client = TestEnvironment::client();

		// List
		$response = $client->get('/modules/api/movementtypes/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$this->assertEquals(1, count($result));
		$this->assertEquals('Adjustment', $result[0]->name);

	}

	public function testLocations_Ok()
	{
		$client = TestEnvironment::client();

		// List
		$response = $client->get('/modules/api/locations/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count0 = count($result);

		// Add
		$id = 'LOC';
		$response = $client->post('/modules/api/locations/', array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'loc_code' => $id,
				'location_name' => 'Location Name'
			)
		));
		$this->assertEquals('201', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$this->assertEquals($id, $result->loc_code);
		$this->assertEquals('Location Name', $result->location_name);

		// List again
		$response = $client->get('/modules/api/locations/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count1 = count($result);
		$this->assertEquals($count0 + 1, $count1);
	}

	public function testItemCosts_Ok()
	{
		$client = TestEnvironment::client();

		// Add
		$id = TestEnvironment::createId();
		$response = $client->post('/modules/api/inventory/', array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'stock_id' => $id,
				'description' => 'description',
				'long_description' => 'long description',
				'category_id' => '1',
				'tax_type_id' => '1',
				'units' => 'ea',
				'mb_flag' => '0',
				'sales_account' => '1',
				'inventory_account' => '1',
				'cogs_account' => '1',
				'adjustment_account' => '1',
				'assembly_account' => '1'
			)
		));
		$this->assertEquals('201', $response->getStatusCode());

		// Read Item Cost
		$response = $client->get('/modules/api/itemcosts/' . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$expected = new stdClass();
		$expected->stock_id = $id;
		$expected->standard_cost = '0';

		$this->assertEquals($expected, $result);

		// Write Item Cost
		$response = $client->put('/modules/api/itemcosts/' . $id, array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'material_cost' => '1',
				'labour_cost' => '2',
				'overhead_cost' => '3'
			)
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$expected = new stdClass();
		$expected->stock_id = $id;

		$this->assertEquals($expected, $result);

		// Read Item Cost again
		$response = $client->get('/modules/api/itemcosts/' . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$expected = new stdClass();
		$expected->stock_id = $id;
		$expected->standard_cost = '6';

		$this->assertEquals($expected, $result);

	}

}
