<?php
/**********************************************
Author: Andres Amaya
Name: Inventory REST API
Free software under GNU GPL
***********************************************/

global $security_areas, $security_groups, $security_headings, $path_to_root, $db, $db_connections;

$path_to_root = "../..";

$page_security = 'SA_API';
ini_set('xdebug.auto_trace', 1);
include_once ($path_to_root . "/modules/api/session-custom.inc");

add_access_extensions();
$app = & $_SESSION["App"];
if (isset ($_GET['application']))
	$app->selected_application = $_GET['application'];

function api_initAPI(){
	
	api_processRequest();
	$mapData = api_mapData();
	if(!api_validateAccess($mapData['company'], $mapData['username'], $mapData['password'])){
		api_sendError(403, api_getStatusCodeMsg(403), 'Please Verify Company, Username and Password');
	}
	
	return $mapData;
	
}

function api_processRequest(){
	
	$request_method = strtolower($_SERVER['REQUEST_METHOD']);
	$headers = apache_request_headers();
	$request = explode("/", substr($_SERVER['PATH_INFO'], 1));
	
	// Validate Request Method
	if($request_method != 'get'){
		api_sendError(400, 'Not Acceptable', 'Only GET is enabled');
	}
	
}

function api_mapData(){
	
	if($_GET == null){
		api_sendError(400, 'Not Acceptable', 'GET Params are null');
	}
	
	$tmp = $_GET;
	
	if(!isset($tmp['company']) || !isset($tmp['username']) || !isset($tmp['password'])){
		api_sendError(400, 'Not Acceptable', 'Company, Username or Password cannot be null');
	}
	
	if(!isset($tmp['action'])){
		api_sendError(400, 'Not Acceptable', 'No Action Received');
	}
	
	if(isset($tmp['info'])){
		$inf = $tmp['info'];
		$tmp['info'] = json_decode(base64_decode($inf), true);
	} else {
		$tmp['info'] = null;
	}
	//print_r($tmp);
	return $tmp;
	
}

function api_validateAccess($company, $user, $password){
	
	//Retrieve company database information
	$db = set_global_connection($company);
	
	$succeed = $_SESSION["wa_current_user"]->login($company,
				$user, $password);
	
	return $_SESSION["wa_current_user"]->logged_in();
	
}

function api_sendError($code, $msg, $detailedMsg){
	
	$body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
					<html>
						<head>
							<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
							<title>' . $code . ' ' . ' API Error' . '</title>
						</head>
						<body>
							<h1>' . $msg . '</h1>
							<p>' . $detailedMsg . '</p>
							<hr />
							<address>' . 'API' . '</address>
						</body>
					</html>';
	
		echo $body;
		exit;
	
}

function api_sendSuccess($message){
	echo json_encode(array('success' => 1, 'message' => $message));
	exit;
}

function api_getStatusCodeMsg($status)
	{
		
		$codes = Array(
		    100 => 'Continue',
		    101 => 'Switching Protocols',
		    200 => 'OK',
		    201 => 'Created',
		    202 => 'Accepted',
		    203 => 'Non-Authoritative Information',
		    204 => 'No Content',
		    205 => 'Reset Content',
		    206 => 'Partial Content',
		    300 => 'Multiple Choices',
		    301 => 'Moved Permanently',
		    302 => 'Found',
		    303 => 'See Other',
		    304 => 'Not Modified',
		    305 => 'Use Proxy',
		    306 => '(Unused)',
		    307 => 'Temporary Redirect',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    407 => 'Proxy Authentication Required',
		    408 => 'Request Timeout',
		    409 => 'Conflict',
		    410 => 'Gone',
		    411 => 'Length Required',
		    412 => 'Precondition Failed',
		    413 => 'Request Entity Too Large',
		    414 => 'Request-URI Too Long',
		    415 => 'Unsupported Media Type',
		    416 => 'Requested Range Not Satisfiable',
		    417 => 'Expectation Failed',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    502 => 'Bad Gateway',
		    503 => 'Service Unavailable',
		    504 => 'Gateway Timeout',
		    505 => 'HTTP Version Not Supported'
		);

		return (isset($codes[$status])) ? $codes[$status] : '';
	}

?>