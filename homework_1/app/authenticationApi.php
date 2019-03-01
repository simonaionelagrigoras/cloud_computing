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
function init(){
    header("Content-Type: application/json; charset=UTF-8");

    require_once('Users.php');
    require_once('Logger.php');
}

function authenticate(){
    $headers = apache_request_headers();
    if(!isset($headers['Authorization'])){
        return false;
    }
    $authorized = false;
    $api_key = str_replace('Bearer ', '', $headers['Authorization']);
    $encodedKey = file_get_contents('../storage/auth.txt');
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

    if(!isset($postData['user_name'])){
        $response = ['error' => '%user_name% is a required field'];
        return $response;
    }

    if(!isset($postData['user_email'])){
        $response = ['error' => '%user_email% is a required field'];
        return $response;
    }

    if(!isset($postData['age'])){
        $response =  ['error' => '%age% is a required field'];
        return $response;
    }

    return $postData;
}

function checkBodyDeleteRequest(){
    # Get JSON as a string
    $postDataJson = file_get_contents('php://input');

    # Get as an object
    $postData = json_decode($postDataJson, true);

    if(!isset($postData['user_id_to_delete'])){
        $response = ['error' => '%User id% is a required field for this operation'];
        return $response;
    }

    return $postData;
}

function runAddRequest(){
    global $responseCode;
    $postData = checkBodyRequest();

    if(isset($postData['error'])){

        $responseCode = 400;
        return $postData;
    }
    $user = new Users();
    $response = $user->createUser($postData['user_name'], $postData['user_email'], $postData['age']);
    $responseCode = 200;

    return $response;
}

function runDeleteRequest(){
    global $responseCode;
    $postData = checkBodyDeleteRequest();

    if(isset($postData['error'])){

        $responseCode = 400;
        return $postData;
    }
    $user = new Users();
    $response = $user->deleteUser($postData['user_id_to_delete']);
    $responseCode = 200;

    return $response;
}

init();
if(!authenticate()){
    $responseCode = 403;
    $response = ['error' => 'Consumer is not authorized to access %resources'];
}else{
    switch ($_SERVER['REQUEST_METHOD']){
        case 'POST':
            $response = runAddRequest();
            break;
        case 'DELETE':
            $response = runDeleteRequest();
            break;
        default:
            $response = ['error' => 'Unrecognized request type'];
    }

    if(isset($response['error'])){
        $responseCode = 400;
    }
}

$time_elapsed_secs = microtime(true) - $start;

$logger = new Logger();
$logger->log(
    [
        'method' => $_SERVER['REQUEST_METHOD'],
        'url' => "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
        'postBody' => json_decode(file_get_contents('php://input'), true),
        'response_code'=> $responseCode,
        'response'=> $response,
        'time'=> $time_elapsed_secs,
    ]
);
http_response_code($responseCode);

echo json_encode($response);
