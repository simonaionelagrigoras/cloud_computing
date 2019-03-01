<?php
/**
 * Created by PhpStorm.
 * User: Simona
 * Date: 28/02/2019
 * Time: 16:24
 */

class Logger{
    CONST LOG_FILE = '../var/log/request.log';
    public function log($data){
        $formattedLog = $this->formatLog($data);
        file_put_contents(self::LOG_FILE, $formattedLog, FILE_APPEND | LOCK_EX);
    }

    protected function formatLog($data)
    {
        //[2018-12-06 10:53:38]
        $date = date( "[Y-m-d H:i:s]",time());
        return $date . " " . json_encode($data) . "\n";
    }
}