<?php
/**
 * Created by PhpStorm.
 * User: Simona
 * Date: 01/03/2019
 * Time: 00:41
 */

$file = file('http://localhost/cc/homework_1/var/log/request.log');
$file = array_reverse($file);
$lines = [];
foreach($file as $f){

    preg_match('/\[[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\]?/', $f, $date);
    if(isset($date[0])){
        $date = $date[0];
        $dataLog = str_replace($date, "", $f);
        $lines[$date] = json_decode($dataLog, true);
    }
}
