<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');
require_once(TEST_PATH . '/Crud_Base.php');

const EXCHANGERATES_POST_DATA = array(
    'currency' => 'GBP',
    'rateBuy' => '1.1',
    'rateSell' => '1.2',
    'date' => '2018-06-25'
);

class ExchangeRatesTest extends Crud_Base
{
    private $postData = EXCHANGERATES_POST_DATA;
    
    private $putData;
    
    public function __construct()
    {
        $this->putData = $this->postData;
        $this->putData['rateBuy'] = '2.1';
        $this->putData['rateSell'] = '2.2';
        $this->putData['date'] = '2018-06-26';

        parent::__construct(
            '/modules/api/exchangerates/gbp/',
            'id',
            $this->postData,
            $this->putData
        );
    }

    // 	public function testCRUD_Ok();
}
