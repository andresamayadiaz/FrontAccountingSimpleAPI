<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');

class TaxTest extends PHPUnit_Framework_TestCase
{
    public function testTaxTypes_Ok()
    {
        $client = TestEnvironment::client();

        // List
        $response = $client->get('/modules/api/taxtypes/', array(
            'headers' => TestEnvironment::headers()
        ));
        $this->assertEquals('200', $response->getStatusCode());
        $result = $response->getBody();
        $result = json_decode($result);

        $this->assertEquals(1, count($result));
        $expected = new stdClass();
        $expected->id = 1;
        $expected->name = 'Regular';
        $expected->exempt = 0;
        $this->assertEquals($expected, $result[0]);

        // Get by ID
        $response = $client->get('/modules/api/taxtypes/' . $expected->id, array(
            'headers' => TestEnvironment::headers()
        ));
        $result = $response->getBody();
        $result = json_decode($result);
        $expected = new stdClass();
        $expected->id = 1;
        $expected->name = 'Tax';
        // $expected->exempt = 0; //?
        $expected->rate = '5';
        $expected->sales_gl_code = '2150';
        $expected->purchasing_gl_code = '2150';
        $expected->inactive = '0';
        $expected->SalesAccountName = 'Sales Tax';
        $expected->PurchasingAccountName = 'Sales Tax';
        $this->assertEquals($expected, $result);
    }

    public function testTaxGroups_Ok()
    {
        $client = TestEnvironment::client();

        // List
        $response = $client->get('/modules/api/taxgroups/', array(
            'headers' => TestEnvironment::headers()
        ));
        $this->assertEquals('200', $response->getStatusCode());
        $result = $response->getBody();
        $result = json_decode($result);

        $this->assertEquals(2, count($result));
        $expected = new stdClass();
        $expected->id = '1';
        $expected->name = 'Tax';
        $expected->inactive = '0';
        $this->assertEquals($expected, $result[0]);
    }
}
