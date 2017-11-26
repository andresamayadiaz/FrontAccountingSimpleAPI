<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');
require_once(TEST_PATH . '/Crud_Base.php');

const CATEGORY_DATA = array(
	'description' => 'description',
	'dflt_tax_type' => '1',
	'dflt_units' => 'each',
	'dflt_mb_flag' => 'D',
	'dflt_sales_act' => '4010',
	'dflt_cogs_act' => '5010',
	'dflt_inventory_act' => '1510',
	'dflt_adjustment_act' => '5040',
	'dflt_wip_act' => '1530',
	'dflt_dim1' => '0',
	'dflt_dim2' => '0',
	'inactive' => '0',
	'dflt_no_sale' => '0',
	'dflt_no_purchase' => '0',
);

class CategoryTest extends Crud_Base
{
	private $postData = CATEGORY_DATA;

	private $putData;

	public function __construct()
	{
		$this->putData = $this->postData;
		$this->putData['description'] = 'other description';

		parent::__construct(
			'/modules/api/category/',
			'category_id',
			$this->postData,
			$this->putData
		);
	}

	// 	public function testCRUD_Ok();

}

class CategoryJsonTest extends Crud_Base
{
	private $postData = CATEGORY_DATA;

	private $putData;

	public function __construct()
	{
		$this->putData = $this->postData;
		$this->putData['description'] = 'other description';

		parent::__construct(
			'/modules/api/category/',
			'category_id',
			$this->postData,
			$this->putData
		);
		$this->method = Crud_Base::JSON;
	}

	// 	public function testCRUD_Ok();

}
