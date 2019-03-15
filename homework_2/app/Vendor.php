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

        $sql = "SELECT * FROM vendors WHERE cui=" . $cui;
        $query = $this->dbConnection->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        if(count($result)){
            return ['error' => 'A vendor with registration number ' . $cui . ' already exist', 'code_error' => 409];
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

    public function updateVendor($vendorId, $data)
    {
        $sql = "SELECT * FROM vendors WHERE vendor_id=" . $vendorId;
        $query = $this->dbConnection->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        if(!count($result)){
            return ['error' => 'Vendor with id ' . $vendorId . ' doesn\'t exist'];
        }

        if(isset($data['cui']) && !empty($data['cui'])){
            $sql = 'SELECT * FROM vendors WHERE cui="' . $data['cui'] . '"and vendor_id<>' . $vendorId;

            $query = $this->dbConnection->prepare($sql);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            if(count($result)){
                return ['error' => 'A vendor with registration number ' . $data['cui'] . ' already exist', 'code_error' => 409];
            }
        }

        $update = '';
        foreach ($data as $key => $value){
            switch($key){
                case 'company_name':
                    if(strlen($value) <3){
                        return ['error' => 'Name must have at least 3 characters'];
                    }else{
                        $update .= 'company_name="' . $value . '",';
                    }
                    break;
                case 'email':
                    if(strlen($value) <7){
                        return ['error' => 'Email must have at least 7 characters'];
                    }else{
                        $update .= 'email="' . $value . '",';
                    }
                    break;
                case 'cui':
                    if(!is_null($value)) {
                        $update .= 'cui="' . $value . '",';
                    }
                    break;
            }
        }

        try{
            $update = rtrim($update, ',');
            $sql = "UPDATE vendors SET " . $update . " WHERE vendor_id=" . $vendorId;

            $query= $this->dbConnection->prepare($sql);
            $query->execute();
            return ['success' => "Vendor updated"];
        }catch (Exception $e){
            return ['error' => "Could not create vendor: " . $e->getMessage()];
        }

    }
}