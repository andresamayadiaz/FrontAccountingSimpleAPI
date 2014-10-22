<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');

class GLTest extends PHPUnit_Framework_TestCase
{

	public function testAccountList_Ok()
	{
		$client = TestEnvironment::client();
		$response = $client->get('/modules/api/glaccounts/', array(
			'headers' => TestEnvironment::headers()
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$this->assertEquals(2, count($result));

		$expected = array();
		$expected[] = new stdClass();
		$expected[0]->account_code = '1060';
		$expected[0]->account_name = 'Checking Account';
		$expected[0]->account_type = '1';
		$expected[0]->account_code2 = '';
		$expected[] = new stdClass();
		$expected[1]->account_code = '1065';
		$expected[1]->account_name = 'Petty Cash';
		$expected[1]->account_type = '1';
		$expected[1]->account_code2 = '';

		$this->assertEquals($expected, $result);
	}

	public function testAccountGetById_Ok()
	{
		$client = TestEnvironment::client();
		$response = $client->get('/modules/api/glaccounts/1060', array(
			'headers' => TestEnvironment::headers()
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$expected = new stdClass();
		$expected->account_code = '1060';
		$expected->account_code2 = '';
		$expected->account_name = 'Checking Account';
		$expected->account_type = '1';
		$expected->inactive = '0';

		$expected->{ '0' } = '1060';
		$expected->{ '1' } = '';
		$expected->{ '2' } = 'Checking Account';
		$expected->{ '3' } = '1';
		$expected->{ '4' } = '0';

		$this->assertEquals($expected, $result);
	}

	public function testAccountTypes_Ok()
	{
		$client = TestEnvironment::client();
		$response = $client->get('/modules/api/glaccounttypes', array(
			'headers' => TestEnvironment::headers()
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);
	}

}
