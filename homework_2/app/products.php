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
$product = null;
$review  = null;
function init(){
    header("Content-Type: application/json; charset=UTF-8");

    require_once('Product.php');
    require_once('Review.php');

    global $product;
    global $review;
    $product = new Product();
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

function checkBodyRequest(){
    # Get JSON as a string
    $postDataJson = file_get_contents('php://input');

    # Get as an object
    $postData = json_decode($postDataJson, true);
    if(is_null($postData)){
        $response = ['error' => 'Decoding error: check json body'];
        return $response;
    }
    $response = [];
    if(!isset($postData['sku'])){
        $response = ['error' => '%sku% is a required field'];
        return $response;
    }

    if(!isset($postData['product_name'])){
        $response = ['error' => '%product_name% is a required field'];
        return $response;
    }

    if(!isset($postData['vendor_id'])){
        $response =  ['error' => '%vendor_id% is a required field'];
        return $response;
    }

    return $postData;
}

function checkBodyPatchRequest(){
    # Get JSON as a string
    $postDataJson = file_get_contents('php://input');

    # Get as an object
    $postData = json_decode($postDataJson, true);
//    if(!isset($postData['product_id'])){
//        $response = ['error' => 'Field %product_id% is required for this operation'];
//        return $response;
//    }
    if(!isset($postData['sku']) && !isset($postData['product_name']) && !isset($postData['description'])){
        $response = ['error' => 'You need to send at least one of the fields %sku%, %product_name% or %description% for this operation'];
        return $response;
    }

    return $postData;
}
function checkBodyReviewRequest(){
    # Get JSON as a string
    $postDataJson = file_get_contents('php://input');

    # Get as an object
    $postData = json_decode($postDataJson, true);
    if(!isset($postData['description'])){
        $response = ['error' => 'Field %description% is required for this operation'];
        return $response;
    }
    if(!isset($postData['rating'])){
        $response = ['error' => 'Field %rating% is required'];
        return $response;
    }

    return $postData;
}

function runAddRequest(){
    global $responseCode;
    global $product;
    $postData = checkBodyRequest();

    if(isset($postData['error'])){

        $responseCode = 400;
        return $postData;
    }
    $description = isset($postData['description']) ? $postData['description'] : null;
    $response = $product->createProduct($postData['sku'], $postData['product_name'], $postData['vendor_id'], $description);
    $responseCode = 200;

    return $response;
}

function runAddReview($productId){
    global $responseCode;
    global $review;

    $postData = checkBodyReviewRequest();
    if(isset($postData['error'])){
        $responseCode = 400;
        return $postData;
    }
    $customerName = isset($postData['customer_name']) ? $postData['customer_name'] : null;
    $response = $review->createReview($productId, $postData['description'], $postData['rating'], $customerName);
    $responseCode = 201;

    return $response;
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
function getUrlParam($productId) {

    $url = $_SERVER['REQUEST_URI'];
    $url = str_replace('/cc/homework_2/app/products/' . $productId,'', $url);
    $url = explode('/', $url);
    $lastPart = array_pop($url);

    return $lastPart;
}

function runPatchRequest($productId){
    global $responseCode;
    global $product;
    $postData = checkBodyPatchRequest();
    if(isset($postData['error'])){

        $responseCode = 400;
        return $postData;
    }

    $response = $product->updateProduct($productId, $postData);
    $responseCode = 200;

    return $response;
}

init();

try{
    switch ($_SERVER['REQUEST_METHOD']){
        case 'POST':

                $productId = getParam('products');
                $param = getUrlParam($productId);
                if (strlen($param) && $param != 'review') {
                    $responseCode = 404;
                    $response = ['error' => 'Request does not match any route'];
                }
                if ($productId) {
                    if(!authenticate("customer")){
                        $responseCode = 403;
                        $response = ['error' => 'Consumer is not authorized to access %resources'];
                    }else {
                        $response = runAddReview($productId);
                        if(isset($response['error'])){
                            $responseCode = 404;
                        }else{
                            $responseCode = 201;
                        }
                    }
                } else {
                    if(!authenticate("admin") && !authenticate("vendor")){
                        $responseCode = 403;
                        $response = ['error' => 'Consumer is not authorized to access %resources'];
                    }else {
                        $response = runAddRequest();
                        if(isset($response['code_error']) && isset($response['error'])){
                            $responseCode = $response['code_error'];
                            unset($response['code_error']);
                        }else{
                            $responseCode = 201;
                        }
                    }
                }
                //var_dump(pathinfo ("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"));

             break;
        case 'PATCH':
            $productId = getParam('products');
            var_dump($productId);
            if(empty($productId) || is_null($productId)){
                $response = ['error' => 'Product_id is required'];
                break;
            }
            if(!authenticate("admin") && !authenticate("vendor")){
                $responseCode = 403;
                $response = ['error' => 'Consumer is not authorized to access %resources'];
            }else {
                $response = runPatchRequest($productId);
            }
            break;
        case 'GET':
            $productId = getParam('products');

            if(!empty($productId)){

                $param  = getUrlParam($productId);

                if(strlen($param) && $param != 'reviews'){
                    $responseCode = 404;
                    $response = ['error' => 'Request does not match any route'];
                    break;
                }
                if(strlen($param)){
                    $response = $review->getReviewsList($productId);
                    if(!count($response)){
                        $response = ["notice" =>  "There are no reviews for product with id " . $productId];
                        $responseCode = 404;
                    }
                }else{
                    $response = $product->getProduct($productId);
                }
            }else{
                $response = $product->getProductsList();
            }

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
}catch (Error $e){
    $responseCode = 500;
    $response = ['error' => 'Internal server Error. ' . $e->getMessage()];
}catch (ErrorException $e){
    $responseCode = 500;
    $response = ['error' => 'Internal server Error. ' . $e->getMessage()];
}catch (Exception $e){
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
