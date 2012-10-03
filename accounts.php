<?php
/**********************************************
Author: Andres Amaya
Name: Accounts API
Free software under GNU GPL
***********************************************/

$path_to_root="../..";

include_once($path_to_root . "/modules/api/api.php");

include_once($path_to_root . "/gl/includes/db/gl_db_accounts.inc");
include_once($path_to_root . "/gl/includes/db/gl_db_account_types.inc");

$data = null;
$data = api_initAPI();

switch($data['action']){
	
	case 'list':
		all();
		break;
	case 'get':
		get($data['info']);
		break;
	case 'getTypes':
		getTypes();
		break;
	/*
	case 'add':
		add($data['info']);
		break;
	case 'edit':
		edit($data['info']);
		break;
	case 'delete':
		delete($data['info']);
		break;
	*/
	default:
		api_sendError('404', 'Invalid Action', 'Action Not Found');
		break;
}

function all(){

	$accounts = get_gl_accounts();
	$ret = array();
	while($acct = db_fetch($accounts)){
		
		$ret[] = array(
			'account_code' => $acct['account_code'],
			'account_name' => $acct['account_name'],
			'account_type' => $acct['account_type'],
			'account_code2' => $acct['account_code2']
		);
		
	}
	echo json_encode($ret);
	
}

function get($info){
	
	if(!isset($info['account_code'])){
		api_sendError('400', 'Invalid Account Code', 'Account Code Not Found');
	}
	
	$id = $info['account_code'];
	$acct = get_gl_account($id);
	echo json_encode($acct);
	
}

function getTypes(){
	
	$accttypes = get_account_types();
	$ret = array();
	while($type = db_fetch($accttypes)){
		
		$ret[] = array(
			'id' => $type['id'],
			'name' => $type['name'],
			'class_id' => $type['class_id'],
			'parent' => $$type['parent']
		);
		
	}
	echo json_encode($ret);
	
}

?>