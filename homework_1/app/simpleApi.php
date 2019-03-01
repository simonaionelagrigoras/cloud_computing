<?php
/**
 * Created by PhpStorm.
 * User: Simona
 * Date: 28/02/2019
 * Time: 16:39
 */
header("Content-Type: application/json; charset=UTF-8");
$start = microtime(true);
function init(){
    require_once('Users.php');
    require_once('Logger.php');

}

function runReq(){
    $logger = new Logger();
    $user   = new Users();

    $response = $user->getUsersList();
    $responseCode = 200;
    if(isset($response['error'])){
        $responseCode = 400;
    }
// set response code - 200 OK
    http_response_code($responseCode);
    global $start;
    $time_elapsed_secs = microtime(true) - $start;
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
    // show users data in json format
    echo json_encode($response);
}

init();
runReq();
