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
// 		$this->assertEquals(0, $count0);

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

}
