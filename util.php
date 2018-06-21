<?php
/**********************************************
Author: Andres Amaya
Name: REST API Utils
Free software under GNU GPL
***********************************************/

function api_login()
{
    $app = \Slim\Slim::getInstance('SASYS');
    $app->hook('slim.before', function () use ($app) {
        $req = $app->request();
        $company = $req->headers('X-COMPANY');
        $user = $req->headers('X-USER');
        $password = $req->headers('X-PASSWORD');

        // TESTING
        /*$company = 0;
        $user = 'admin';
        $password = '123';*/

        $succeed = $_SESSION["wa_current_user"]->login(
            $company, $user, $password
        );
        if (!$succeed) {
            $app->halt(403, 'Bad Login For Company: ' . $company . ' With User: ' . $user);
        }
    }, 1);
}

function api_response($code, $body)
{
    $app = \Slim\Slim::getInstance('SASYS');
    $app->response()->status($code);
    if (is_array($body)) {
        $body= json_encode($body);
    }
    $app->response()->body($body);
}

function api_success_response($body)
{
    $app = \Slim\Slim::getInstance('SASYS');
    api_response(200, $body);
    //$app->response()->['Content-Type'] = $content_type;
}

function api_create_response($body)
{
    $app = \Slim\Slim::getInstance('SASYS');
    api_response(201, $body);
    //$app->response()->['Content-Type'] = $content_type;
}

function api_error($code, $msg)
{
    $app = \Slim\Slim::getInstance('SASYS');
    $app->halt($code, json_encode(array('code' => $code, 'success' => 0, 'msg' => $msg)));
}

function api_ensureAssociativeArray($a)
{
    if (!$a) {
        $a = array();
    }
    foreach ($a as $key => $value) {
        if (is_int($key)) {
            unset($a[$key]);
        }
    }
    return $a;
}

function api_validate_required($property, $model)
{
    if (!isset($model[$property])) {
        return false;
    }
    return true;
}

function api_validate_message($test, $property)
{
    $messages = array(
        "api_validate_required" => "Missing a required proprty '$property'"
    );
    if (isset($messages[$test])) {
        return $messages[$test];
    }
    return '';
}

function api_validate($property, $model, $code = 412, $test = 'api_validate_required', $msg = null)
{
    if ($test($property, $model)) {
        return;
    }
    if (!$msg) {
        $msg = \api_validate_message($test, $property);
        if (!$msg) {
            $msg = "Unknown error in '$property'";
        }
    }
    \api_error($code, $msg);
}

/**
 * @param string $property
 * @param array $model
 * @param string|array $default
 */
function api_check($property, &$model, $default = '')
{
    if (!isset($model[$property])) {
        if (is_array($default)) {
            if (array_key_exists($property, $default)) {
                $default = $default[$property];
            }
        }
        $model[$property] = $default;
    }
}
