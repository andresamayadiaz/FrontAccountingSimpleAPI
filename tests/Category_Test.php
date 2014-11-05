<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');

class CategoryTest extends PHPUnit_Framework_TestCase
{

	public function testCRUD_Ok()
	{
		$client = TestEnvironment::client();

		// List
		$response = $client->get('/modules/api/category/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count0 = count($result);
		$this->assertGreaterThan(1, $count0);

		// Add
		$response = $client->post('/modules/api/category/', array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'description' => 'description',
				'tax_type_id' => '1',
				'units' => 'each',
				'mb_flag' => 'D',
				'sales_account' => '4010',
				'cogs_account' => '5010',
				'adjustment_account' => '5040',
				'assembly_account' => '1530',
				'inventory_account' => '1510',
			)
		));
		$this->assertEquals('201', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);
		$id = $result->category_id;

		$this->assertNotNull($id);

		// List again
		$response = $client->get('/modules/api/category/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count1 = count($result);
		$this->assertEquals($count0 + 1, $count1);

		// Get by id
		$response = $client->get('/modules/api/category/' . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);
		$this->assertEquals($id, $result->category_id);

		// Write back
		$response = $client->put('/modules/api/category/' . $id, array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'description' => 'other description',
				'tax_type_id' => '1',
				'units' => 'month',
				'mb_flag' => 'D',
				'sales_account' => '4010',
				'cogs_account' => '5010',
				'adjustment_account' => '5040',
				'assembly_account' => '1530',
				'inventory_account' => '1510',
			)
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		// List again
		$response = $client->get('/modules/api/category/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count1 = count($result);
		$this->assertEquals($count0 + 1, $count1);

		$this->assertEquals($id, $result[$count1 - 1]->category_id);
		$this->assertEquals('other description', $result[$count1 - 1]->description);

		// Delete
		$response = $client->delete('/modules/api/category/' . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		// List again
		$response = $client->get('/modules/api/category/', array(
			'headers' => TestEnvironment::headers()
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$count2 = count($result);
		$this->assertEquals($count0, $count2);

	}

}
