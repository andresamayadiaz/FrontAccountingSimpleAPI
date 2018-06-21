<?php
use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');
require_once(TEST_PATH . '/Crud_Base.php');

const BANKACCOUNT_POST_DATA = array(
    'account_code' => '123456',
    'account_type' => '0',
    'bank_account_name' => 'Bank Test Account',
    'bank_account_number' => '12-3456-789123-00',
    'bank_curr_code' => 'USD',
    'bank_name' => 'Bank Test Name',
    'bank_address' => 'Bank Test Address',
    'bank_charge_act' => '5690',
    'dflt_curr_act' => '0',
    'inactive' => '0'
);

class BankAccountsTest extends Crud_Base
{
    private $postData = BANKACCOUNT_POST_DATA;
    
    private $putData;
    
    public function __construct()
    {
        $this->putData = $this->postData;
        $this->putData['bank_account_name'] = 'Bank Test Account Edited';

        parent::__construct(
            '/modules/api/bankaccounts/',
            'id',
            $this->postData,
            $this->putData
        );
    }

    protected function checkGetAfterPost($result)
    {
        $expected = $this->fixExpectedType($this->postData, $result);
        $expected->last_reconciled_date = '0000-00-00 00:00:00';
        $expected->ending_reconcile_balance = '0';
        $result = $this->removeKeyProperty($result);
        $this->assertEquals($expected, $result, 'Failed GET after POST');
    }

    protected function checkGetAfterPut($result)
    {
        $expected = $this->fixExpectedType($this->putData, $result);
        $result = $this->removeKeyProperty($result);
        $expected->last_reconciled_date = '0000-00-00 00:00:00';
        $expected->ending_reconcile_balance = '0';
        $this->assertEquals($expected, $result, 'Failed GET after PUT');
    }

    // 	public function testCRUD_Ok();
}

class BankAccountsOtherTest extends PHPUnit_Framework_TestCase
{
    public function testBankAccount_ReadAll_Ok()
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

}
