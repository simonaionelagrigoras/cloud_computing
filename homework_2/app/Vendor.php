<?php
/**
 * Created by PhpStorm.
 * User: Simona
 * Date: 28/02/2019
 * Time: 16:24
 */

class Vendor{

    protected $dbConnection;
    public function __construct(){
        require_once('Connection.php');
        $connection = new Connection();
        $this->dbConnection = $connection->getDbConnection();
    }
    public function getVendorsList(){
        try{
            $this->dbConnection;

            $sql = "SELECT * FROM vendors ORDER BY vendor_id DESC";
            $query = $this->dbConnection->prepare($sql);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }catch (Exception $e){
            return ['error' => "Could not get vendors: " . $e->getMessage()];
        }
    }

    public function createVendor($companyName, $email, $cui){
        if(strlen($companyName) <3){
            return ['error' => 'Name must have at least 3 characters'];
        }

        if(strlen($email) <7){
            return ['error' => 'Email must have at least 7 characters'];
        }

        if(strlen($cui)<7){
            return ['error' => 'Invalid registration number: the registration number must have at least 7 characters'];
        }

        try{

            $sql = "INSERT INTO vendors (`company_name`, `email`, `cui`) VALUES (?,?,?)";
            $stmt= $this->dbConnection->prepare($sql);
            $stmt->execute([$companyName, $email, $cui]);
            return ['success' => "Vendor created"];
        }catch (Exception $e){
            return ['error' => "Could not create vendor: " . $e->getMessage()];
        }

    }

    public function updateVendor($companyName, $email, $cui){
        if(strlen($companyName) <3){
            return ['error' => 'Name must have at least 3 characters'];
        }

        if(strlen($email) <7){
            return ['error' => 'Email must have at least 7 characters'];
        }

        if(!is_numeric($cui)){
            return ['error' => 'CUI must be a number'];
        }

        try{

            $sql = "INSERT INTO vendors (`company_name`, `email`, `cui`) VALUES (?,?,?)";
            $stmt= $this->dbConnection->prepare($sql);
            $stmt->execute([$companyName, $email, $cui]);
            return ['success' => "Vendor created"];
        }catch (Exception $e){
            return ['error' => "Could not create vendor: " . $e->getMessage()];
        }

    }
}