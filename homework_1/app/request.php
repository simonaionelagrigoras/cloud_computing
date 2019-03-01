<?php
/**
 * Created by PhpStorm.
 * User: Simona
 * Date: 28/02/2019
 * Time: 22:44
 */


function getUsers(){
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'http://localhost/cc/homework_1/app/simpleApi.php'
    ]);
    $result = curl_exec($curl);
    curl_close($curl);

    return json_encode(json_decode($result, true));
}

function addUser(){
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'http://localhost/cc/homework_1/app/authenticationApi.php'
    ]);
    $result = curl_exec($curl);
    curl_close($curl);

    return json_encode(json_decode($result, true));
}

if (isset($_GET['function'])) {
    echo $_GET['function']();
}

if (isset($_POST['function'])) {
    echo $_POST['function']();
}


function curl_request_async($url, $params = null, $type='POST')
{
    if($params){
        foreach ($params as $key => &$val) {
            if (is_array($val)) $val = implode(',', $val);
            $post_params[] = $key.'='.urlencode($val);
        }
        $post_string = implode('&', $post_params);
    }

    $parts=parse_url($url);
    $fp = [];
    for($i=1;$i<=20;$i++){
        $fp[$i] = fsockopen($parts['host'],
            isset($parts['port'])?$parts['port']:80,
            $errno, $errstr, 30);
    }


    // Data goes in the path for a GET request
    //if('GET' == $type) $parts['path'] .= '?'.$post_string;

    $out = "$type ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
   // $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    // Data goes in the request body for a POST request
    if ('POST' == $type && isset($post_string)) $out.= $post_string;

    for($i=1;$i<=20;$i++){
        fwrite($fp[$i], $out);
        fclose($fp[$i]);
    }

}
curl_request_async("http://localhost/cc/homework_1/app/simpleApi.php");