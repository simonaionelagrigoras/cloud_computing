<?php
/**
 * Created by PhpStorm.
 * User: Simona
 * Date: 28/02/2019
 * Time: 21:12
 */


$response     = [];
$responseCode = 200;

$start   = microtime(true);
$review  = null;
function init(){
    header("Content-Type: application/json; charset=UTF-8");
    require_once('Review.php');

    global $review;
    $review  = new Review();

}

function authenticate($type){
    $headers = apache_request_headers();
    if(!isset($headers['Authorization'])){
        return false;
    }
    $authorized = false;
    $api_key = str_replace('Bearer ', '', $headers['Authorization']);
    $encodedKey = file_get_contents('../storage/auth_' . $type .'.txt');
    // this could be a MYSQL query that parses an API Key table, for example
    if($api_key == base64_decode($encodedKey)) {
        $authorized = true;
    } else if ($api_key == NULL) {

    } else {
        $authorized = false;
    }
    return $authorized;
}


/**
 * Get the given variable from $_REQUEST or from the url
 * @param string $variableName
 * @param mixed $default
 * @return mixed|null
 */
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
function getUrlParam($reviewId) {

    $url = $_SERVER['REQUEST_URI'];
    $url = str_replace('/cc/homework_2/app/reviews/' . $reviewId,'', $url);
    $url = explode('/', $url);
    $lastPart = array_pop($url);

    return $lastPart;
}


init();

try{
    switch ($_SERVER['REQUEST_METHOD']){
        case 'DELETE':
            if(!authenticate("customer")){
                $responseCode = 403;
                $response = ['error' => 'Consumer is not authorized to access %resources'];
            }else {
                $reviewId = getParam('reviews');

                if ($reviewId) {
                    $response = $review->delete($reviewId);
                } else {
                    $response = ['error' => '%review_id% is required'];
                }
                $responseCode = 200;
            }
            break;
        case 'GET':
            $response = $review->getReviewsList();
            if(!count($response)){
                $response = ["notice" =>  "There are no reviews "];
                $responseCode = 404;
            }
            if(isset($response['error'])){
                $responseCode = 503;
            }
            break;
        default:
            $response = ['error' => 'Unrecognized request type'];
    }

    if(isset($response['error'])){
        $responseCode = 400;
    }
}catch (Error $e){
    $responseCode = 500;
    $response = ['error' => 'Internal server Error. ' . $e->getMessage()];
}catch (ErrorException $e){
    $responseCode = 500;
    $response = ['error' => 'Internal server Error. ' . $e->getMessage()];
}catch (Exception $e){
    $responseCode = 500;
    $response = ['error' => 'Internal server Error. ' . $e->getMessage()];
}catch (ParseError $e){
    $responseCode = 500;
    $response = ['error' => 'Internal server Error. ' . $e->getMessage()];
}



$time_elapsed_secs = microtime(true) - $start;
http_response_code($responseCode);
header('X-PHP-Response-Code: ' . $responseCode, true, $responseCode);
if(!in_array($responseCode, [200,201])){
    $response['http_status'] = $responseCode;
    $response['time'] = $time_elapsed_secs;
}
echo json_encode($response);
