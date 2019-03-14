<?php
/**
 * Created by PhpStorm.
 * User: Simona
 * Date: 28/02/2019
 * Time: 16:24
 */

class Connection{

    protected $dbName;
    protected $dbUser;
    protected $dbPass;
    public function __construct()
    {
        $this->dbName = 'marketplace';
        $this->dbUser = 'app_user';
        $this->dbPass = 'app_us3r_pass';
    }

    public function getDbConnection(){

        try {
            $pdo = new PDO("mysql:dbname={$this->dbName};host=localhost", $this->dbUser, $this->dbPass);
        }catch (PDOException $e) {
            throw new Exception("Error!: " . $e->getMessage());
            //die();
        }
        if (!$pdo) {
            throw new Exception("Error in mysql connection");
            //print "Eroare: Nu a fost posibilă conectarea la MySQL." . PHP_EOL;
            //print "Valoarea errno: " . mysqli_connect_errno() . PHP_EOL;
            //print "Valoarea error: " . mysqli_connect_error() . PHP_EOL;
        }
        // print "Succes: Connection to db successful" . PHP_EOL;
        return $pdo;
    }
}