<?php
/**
 * Created by PhpStorm.
 * User: Simona
 * Date: 28/02/2019
 * Time: 21:12
 */


$response = [];
$responseCode = 200;
$start = microtime(true);
$vendor = null;
function init(){
    header("Content-Type: application/json; charset=UTF-8");

    require_once('Vendor.php');

    global $vendor;
    $vendor = new Vendor();

}

function authenticate(){
    $headers = apache_request_headers();
    if(!isset($headers['Authorization'])){
        return false;
    }
    $authorized = false;
    $api_key = str_replace('Bearer ', '', $headers['Authorization']);
    $encodedKey = file_get_contents('../storage/auth_admin.txt');
    // this could be a MYSQL query that parses an API Key table, for example
    if($api_key == base64_decode($encodedKey)) {
        $authorized = true;
    } else if ($api_key == NULL) {

    } else {
        $authorized = false;
    }
    return $authorized;

}

function checkBodyRequest(){
    # Get JSON as a string
    $postDataJson = file_get_contents('php://input');

    # Get as an object
    $postData = json_decode($postDataJson, true);

    $response = [];

    if(!isset($postData['company_name'])){
        $response = ['error' => '%company_name% is a required field'];
        return $response;
    }

    if(!isset($postData['email'])){
        $response = ['error' => '%email% is a required field'];
        return $response;
    }

    if(!isset($postData['cui'])){
        $response =  ['error' => '%cui% is a required field'];
        return $response;
    }

    return $postData;
}

function checkBodyPatchRequest(){
    # Get JSON as a string
    $postDataJson = file_get_contents('php://input');

    # Get as an object
    $postData = json_decode($postDataJson, true);

    if(is_null($postData['company_name']) && is_null($postData['email']) && is_null($postData['cui'])){
        $response = ['error' => 'You need to send at least one of the fields %company_name%, %email% or %cui% for this operation'];
        return $response;
    }

    return $postData;
}

function runAddRequest(){
    global $responseCode;
    global $vendor;
    $postData = checkBodyRequest();

    if(isset($postData['error'])){

        $responseCode = 400;
        return $postData;
    }

    $response = $vendor->createVendor($postData['company_name'], $postData['email'], $postData['cui']);
    $responseCode = 200;

    return $response;
}

function runPatchRequest($vendorId){
    global $responseCode;
    global $vendor;
    $postData = checkBodyPatchRequest();

    if(isset($postData['error'])){

        $responseCode = 400;
        return $postData;
    }

    $response = $vendor->updateVendor($vendorId, $postData);
    if(isset($response['error'])){
        $responseCode = 400;
        return $response;
    }
    $responseCode = 200;

    return $response;
}
function getParam($variableName, $default = null) {

    // Was the variable actually part of the request
    if(array_key_exists($variableName, $_REQUEST))
        return $_REQUEST[$variableName];

    // Was the variable part of the url
    $urlParts = explode('/', preg_replace('/\?.+/', '', $_SERVER['REQUEST_URI']));
    $position = array_search($variableName, $urlParts);
    if($position !== false && array_key_exists($position+1, $urlParts))
        return $urlParts[$position+1];

    return $default;
}

init();
if(!authenticate()){
    $responseCode = 403;
    $response = ['error' => 'Consumer is not authorized to access %resources'];
}else{
    switch ($_SERVER['REQUEST_METHOD']){
        case 'POST':
            $response = runAddRequest();
            if(isset($response['code_error']) && isset($response['error'])){
                $responseCode = $response['code_error'];
                unset($response['code_error']);
            }else{
                $responseCode = 201;
            }

            break;
        case 'PATCH':

            $vendorId = getParam('vendors');
            if(empty($vendorId)){
                $response = ['error' => 'Vendor id is required'];
            }
            $response = runPatchRequest($vendorId);
            if(isset($response['code_error']) && isset($response['error'])){
                $responseCode = $response['code_error'];
                unset($response['code_error']);
            }else{
                $responseCode = 200;
            }

            break;
        case 'GET':
            $response = $vendor->getVendorsList();
            if(isset($response['error'])){
                $responseCode = 503;
            }
            break;
        default:
            $response = ['error' => 'Unrecognized request type'];
    }

    if(isset($response['error']) && ($responseCode == 201 || $responseCode==200 || is_null($responseCode))){
        $responseCode = 400;
    }
}

$time_elapsed_secs = microtime(true) - $start;
http_response_code($responseCode);
header('X-PHP-Response-Code: ' . $responseCode, true, $responseCode);
if(!in_array($responseCode, [200,201])){
    $response['http_status'] = $responseCode;
    $response['time'] = $time_elapsed_secs;
}

echo json_encode($response);
