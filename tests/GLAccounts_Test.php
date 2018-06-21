<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');
require_once(TEST_PATH . '/Crud_Base.php');

const GL_POST_DATA = array(
    'account_code' => '123456',
    'account_code2' => '',
    'account_name' => 'GL Test Account',
    'account_type' => '1',
    'inactive' => '0'
);

class GLAccountsTest extends Crud_Base
{
    private $postData = GL_POST_DATA;
    
    private $putData;
    
    public function __construct()
    {
        $this->putData = $this->postData;
        $this->putData['account_name'] = 'GL Test Account Edited';

        parent::__construct(
            '/modules/api/glaccounts/',
            'account_code',
            $this->postData,
            $this->putData
        );
    }

    protected function checkGetAfterPost($result)
    {
        $expected = $this->fixExpectedType($this->postData, $result);
        $this->assertEquals($expected, $result, 'Failed GET after POST');
    }

    protected function checkGetAfterPut($result)
    {
        $expected = $this->fixExpectedType($this->putData, $result);
        $this->assertEquals($expected, $result, 'Failed GET after PUT');
    }

    // 	public function testCRUD_Ok();
}

class GLOtherTest extends PHPUnit_Framework_TestCase
{
    public function testAccountTypes_Ok()
    {
        $client = TestEnvironment::client();
        $response = $client->get('/modules/api/glaccounttypes', array(
            'headers' => TestEnvironment::headers()
        ));

        $this->assertEquals('200', $response->getStatusCode());
        $result = $response->getBody();
        $result = json_decode($result);

        $count = count($result);
        $this->assertTrue($count > 0, 'Count > 0');
        $expected = new stdClass();
        $expected->id = '1';
        $expected->name = 'Current Assets';
        $expected->class_id = '1';
        $expected->parent = '';
        $this->assertEquals($expected, $result[0]);
    }
}
