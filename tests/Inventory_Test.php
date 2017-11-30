<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');
require_once(TEST_PATH . '/Crud_Base.php');

const INVENTORY_POST_DATA = array(
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
	'wip_account' => '1'
);

const INVENTORY_GET_DATA = array(
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
	'wip_account' => '1',
	'dimension_id' => '0',
	'dimension2_id' => '0',
	'purchase_cost' => '0',
	'last_cost' => '0',
	'material_cost' => '0',
	'labour_cost' => '0',
	'overhead_cost' => '0',
	'inactive' => '0',
	'no_sale' => '0',
	'no_purchase' => '0',
	'editable' => '1',
	'depreciation_method' => 'D',
	'depreciation_rate' => '100',
	'depreciation_factor' => '1',
	'depreciation_start' => '0000-00-00',
	'depreciation_date' => '0000-00-00',
	'fa_class_id' => '',
	'tax_type_name' => 'Regular',
);

class InventoryTest extends Crud_Base
{

	private $postData = INVENTORY_POST_DATA;
	
	private $putData;
	
	public function __construct()
	{
		// Note: The primary key needs to be provided by the client.
		// It is not an auto-increment property.
		$this->postData['stock_id'] = TestEnvironment::createId();
		$this->putData = $this->postData;
		$this->putData['description'] = 'new description';
		$this->putData['long_description'] = 'new long description';

		parent::__construct(
			'/modules/api/inventory/',
			'stock_id',
			$this->postData,
			$this->putData,
			INVENTORY_GET_DATA
		);
	}

	protected function checkGetAfterPost($result)
	{
		$expected = INVENTORY_GET_DATA;
		$expected['stock_id'] = $this->postData['stock_id'];
		$expected = $this->fixExpectedType($expected, $result);
		// $result = $this->removeKeyProperty($result);
		$this->assertEquals($expected, $result, 'Failed GET after POST');
	}

	protected function checkGetAfterPut($result)
	{
		$expected = INVENTORY_GET_DATA;
		$expected['stock_id'] = $this->putData['stock_id'];
		// Update the expected with the modifications that we PUT
		foreach ($this->putData as $key => $value) {
			if (isset($expected[$key])) {
				$expected[$key] = $value;
			}
		}
		$expected = $this->fixExpectedType($expected, $result);
		// $result = $this->removeKeyProperty($result);
		$this->assertEquals($expected, $result, 'Failed GET after PUT');
	}

	// 	public function testCRUD_Ok();


}

class InventoryOtherTest extends PHPUnit_Framework_TestCase
{
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
			'form_params' => array(
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
			'form_params' => array(
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
				'wip_account' => '1'
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
		$expected->unit_cost = '0';

		$this->assertEquals($expected, $result);

		// Write Item Cost
		$response = $client->put('/modules/api/itemcosts/' . $id, array(
			'headers' => TestEnvironment::headers(),
			'form_params' => array(
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
		$expected->unit_cost = '1';

		$this->assertEquals($expected, $result);

	}

}
