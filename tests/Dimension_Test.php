<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');
require_once(TEST_PATH . '/Crud_Base.php');

const DIMENSION_POST_DATA = array(
    'reference' => 'DIM1',
    'name' => 'Dimension 1',
    'closed' => '0',
    'type_' => '0',
    'date_' => '0000-00-00',
    'due_date' => '0000-00-00'
);

class DimensionTest extends Crud_Base
{
    private $postData = DIMENSION_POST_DATA;
    
    private $putData;
    
    public function __construct()
    {
        $this->putData = $this->postData;
        $this->putData['name'] = 'Dimension 1 Edited';

        parent::__construct(
            '/modules/api/dimensions/',
            'id',
            $this->postData,
            $this->putData
        );
    }

    // 	public function testCRUD_Ok();
}
