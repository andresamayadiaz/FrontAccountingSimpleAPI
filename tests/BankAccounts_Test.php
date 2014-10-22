<?php
use GuzzleHttp\Client;

require_once (__DIR__ . '/TestConfig.php');

require_once (TEST_PATH . '/TestEnvironment.php');

class BankAccountsTest extends PHPUnit_Framework_TestCase
{

	public function testSale_CreateUpdateReadVoid_Ok()
	{
		$client = TestEnvironment::client();
		$response = $client->get('/modules/api/bankaccounts/', array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);

		$this->assertEquals(2, count($result));

		$expected = array();
		$expected[] = new stdClass();
		$expected[0]->account_code = '1060';
		$expected[0]->account_type = '0';
		$expected[0]->id = '1';
		$expected[0]->bank_account_name = 'Current account';
		$expected[0]->bank_name = 'N/A';
		$expected[0]->bank_account_number = 'N/A';
		$expected[0]->bank_curr_code = 'USD';
		$expected[0]->bank_address = '';
		$expected[0]->dflt_curr_act = '1';
		$expected[] = new stdClass();
		$expected[1]->account_code = '1065';
		$expected[1]->account_type = '3';
		$expected[1]->id = '2';
		$expected[1]->bank_account_name = 'Petty Cash account';
		$expected[1]->bank_name = 'N/A';
		$expected[1]->bank_account_number = 'N/A';
		$expected[1]->bank_curr_code = 'USD';
		$expected[1]->bank_address = '';
		$expected[1]->dflt_curr_act = '0';

		$this->assertEquals($expected, $result);
	}

	public function testAccountGetById_Ok()
	{
		$client = TestEnvironment::client();
		$response = $client->get('/modules/api/bankaccounts/1', array(
			'headers' => TestEnvironment::headers()
		));

		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();

		$result = json_decode($result);

		$expected = new stdClass();
		$expected->account_code = '1060';
		$expected->account_type = '0';
		$expected->id = '1';
		$expected->bank_account_name = 'Current account';
		$expected->bank_name = 'N/A';
		$expected->bank_account_number = 'N/A';
		$expected->bank_curr_code = 'USD';
		$expected->bank_address = '';
		$expected->dflt_curr_act = '1';
		$expected->last_reconciled_date = '0000-00-00 00:00:00';
		$expected->ending_reconcile_balance = '0';
		$expected->inactive = '0';

		$expected->{ '0' } = '1060';
		$expected->{ '1' } = '0';
		$expected->{ '2' } = 'Current account';
		$expected->{ '3' } = 'N/A';
		$expected->{ '4' } = 'N/A';
		$expected->{ '5' } = '';
		$expected->{ '6' } = 'USD';
		$expected->{ '7' } = '1';
		$expected->{ '8' } = '1';
		$expected->{ '9' } = '0000-00-00 00:00:00';
		$expected->{ '10' } = '0';
		$expected->{ '11' } = '0';

		$this->assertEquals($expected, $result);
	}
}
