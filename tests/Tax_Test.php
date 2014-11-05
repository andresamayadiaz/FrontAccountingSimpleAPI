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
        $expected->id = 1;
        $expected->name = 'Tax';
        $expected->tax_shipping = 0;
        $this->assertEquals($expected, $result[0]);
    }
}
