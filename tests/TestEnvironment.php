<?php
use GuzzleHttp\Client;
require_once (__DIR__ . '/TestConfig.php');

class TestEnvironment
{

	/**
	 *
	 * @return \GuzzleHttp\Client
	 */
	public static function client()
	{
		return new Client(array(
			'base_url' => 'http://localhost:8000'
		), array(
			'request.options' => array(
				'exceptions' => false
			)
		));
	}

	public static function createCustomer($client, $ref, $name)
	{
		$response = $client->post('/modules/api/customers/', array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'custname' => $name,
				'cust_ref' => $ref,
				'address' => 'address',
				'tax_id' => 'tax_id',
				'curr_code' => 'USD',
				'credit_status' => '1',
				'payment_terms' => '1',
				'discount' => '0',
				'pymt_discount' => '0',
				'credit_limit' => '1000',
				'sales_type' => '1',
				'notes' => 'notes'
			)
		));
		$result = $response->getStatusCode();
		if ($result != 201) {
			throw new \Exception('Create customer failed', $result);
		}
	}

	public static function createItem($client, $id, $description)
	{
		$response = $client->post('/modules/api/inventory/', array(
			'headers' => TestEnvironment::headers(),
			'body' => array(
				'stock_id' => $id,
				'description' => $description,
				'long_description' => $description,
				'category_id' => '1',
				'tax_type_id' => '1',
				'units' => 'ea',
				'mb_flag' => '0',
				'sales_account' => '1',
				'inventory_account' => '1',
				'cogs_account' => '1',
				'adjustment_account' => '1',
				'assembly_account' => '1'
			)
		));

		$result = $response->getStatusCode();
		if ($result != 201) {
			throw new \Exception('Create item failed', $result);
		}
	}

	public static function createId()
	{
		return date('YmdHis');
	}

	/**
	 * Return the X-COMPANY, X-USER, and X-PASSWORD headers
	 *
	 * @return multitype:string
	 */
	public static function headers()
	{
		return array(
			'X-COMPANY' => '0',
			'X-USER' => 'test',
			'X-PASSWORD' => 'test'
		);
	}

	private static function setupErrorReporting()
	{
		$GLOBALS['messages'] = array();
	}

	private static function resetErrorReporting()
	{
		// Undo that which is setup in config.php (of all places)
		error_reporting(- 1);
	}

	private static function setupAPI()
	{
		global $Ajax, $Validate, $Editors, $Pagehelp, $Refs;

		$_SERVER['REMOTE_ADDR'] = 'phpunit';

		self::includeFile('includes/ajax.inc');
		// Ajax communication object
		$Ajax = new Ajax();

		// js/php validation rules container
		$Validate = array();
		// bindings for editors
		$Editors = array();
		// page help. Currently help for function keys.
		$Pagehelp = array();

		self::mockRefs();
	}

	private static function setupSQL()
	{
		global $db, $show_sql, $sql_trail, $select_trail, $go_debug, $sql_queries, $Ajax, $db_connections, $db_last_inserted_id;
		self::includeFile('config_db.php');
		self::includeFile('includes/db/connect_db.inc');
		self::includeFile('includes/db/sql_functions.inc');
		self::includeFile('includes/errors.inc');
		set_global_connection();
	}

	private static function setupSQLDependencies()
	{
		self::includeFile('includes/hooks.inc');
		self::includeFile('includes/types.inc');
		self::includeFile('includes/systypes.inc');
		self::includeFile('includes/prefs/sysprefs.inc');
		self::includeFile('includes/db/comments_db.inc');
		self::includeFile('includes/db/audit_trail_db.inc');
		$_SESSION['SysPrefs'] = null;
		$GLOBALS['SysPrefs'] = &$_SESSION['SysPrefs'];
		self::mockRefs();
	}

	private static function setupSesstion()
	{
		$_SESSION["wa_current_user"] = new TestUser();
	}

	private static function mockRefs()
	{
		$GLOBALS['Refs'] = new MockRefs();
	}

	public static function isGoodToGo()
	{
		global $db_connections;
		self::setupErrorReporting();
		self::setupAPI();
		// self::resetErrorReporting();
		self::setupSesstion();
		self::setupSQL();
		self::setupSQLDependencies();
		$msg = '';
		$dbname = $db_connections[0]['dbname'];
		$expected = 'fa_test';
		if ($dbname != $expected) {
			$msg .= "Error: Wrong database '$dbname' expected '$expected'";
		}
		return ($msg == '') ? 'OK' : $msg;
	}

	public static function includeFile($filePath)
	{
		$path_to_root = SRC_PATH;
		require_once (SRC_PATH . '/' . $filePath);
	}

	public static function currentAccount()
	{
		return 1;
	}

	public static function cashAccount()
	{
		return 2;
	}

	public static function cleanTable($table)
	{
		$sql = 'DELETE FROM ' . '0_' . $table;
		db_query($sql, "Could not clean table '$table'");
	}

	public static function cleanBanking()
	{
		self::cleanTable('bank_trans');
		self::cleanTable('gl_trans');
		self::cleanTable('comments');
	}
}