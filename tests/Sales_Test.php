<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');

class SalesTest extends PHPUnit_Framework_TestCase
{

	public function testSale_CreateUpdateReadVoid_Ok()
	{
// 		$client = TestEnvironment::client();
// 		$response = $client->get('/modules/api/bankaccounts/', array(
// 			'headers' => TestEnvironment::headers()
// 		));
// 		$this->assertEquals('200', $response->getStatusCode());
// 		$result = $response->getBody();
// 		$result = json_decode($result);
// 		var_dump($result);
// 		$this->assertTrue(false);
	}

}
