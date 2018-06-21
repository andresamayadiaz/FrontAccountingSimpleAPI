<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');
// require_once(TEST_PATH . '/Crud_Base.php');

const STOCK_POST_DATA = array(
    'stock_id' => 'TEST_ITEM',
    'location' => 'DEF',
    'date' => '1/2/2013',
    'reference' => '1',
    'quantity' => '1',
    'standard_cost' => '0',
    'memo' => 'Some Memo'
);

class StockAdjustTest extends PHPUnit_Framework_TestCase
{
    public function testStockAdjust_Ok()
    {
        $client = TestEnvironment::client();

        // Adjust
        $response = $client->post('/modules/api/stock/', array(
            'headers' => TestEnvironment::headers(),
            'form_params' => STOCK_POST_DATA
        ));
        $this->assertEquals('201', $response->getStatusCode());
        $result = $response->getBody();
        $result = json_decode($result);

        $this->assertEquals("Stock Adjustment has been added", $result->msg);
        
        // TODO Not sure if there is an API to verify the stock adjust, possibly inventory CP 2018-06
    }
}
