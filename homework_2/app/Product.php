<?php
/**
 * Created by PhpStorm.
 * User: Simona
 * Date: 28/02/2019
 * Time: 16:24
 */

class Product{

    protected $dbConnection;
    public function __construct(){
        require_once('Connection.php');
        $connection = new Connection();
        $this->dbConnection = $connection->getDbConnection();
    }
    public function getProductsList($vendorId = null){
        try{
            $this->dbConnection;

            $sql = "SELECT * FROM products";
            if(!is_null($vendorId)){
                $sql .= " WHERE vendor_id=" . $vendorId;
            }
            $query = $this->dbConnection->prepare($sql);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }catch (Exception $e){
            return ['error' => "Could not get the products: " . $e->getMessage()];
        }
    }

    public function getProduct($productId){
        try{
            $this->dbConnection;

            $sql = "SELECT * FROM products";
            if(!is_null($productId)){
                $sql .= " WHERE product_id=" . $productId;
            }
            $query = $this->dbConnection->prepare($sql);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }catch (Exception $e){
            return ['error' => "Could not get the products: " . $e->getMessage()];
        }
    }

    public function createProduct($sku, $name, $vendorId, $description = null){
        $sql = "SELECT * FROM vendors WHERE vendor_id=" . $vendorId;
        $query = $this->dbConnection->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        if(!count($result)){
            return ['error' => 'Vendor with id ' . $vendorId . ' doesn\'t exist'];
        }
        if(strlen($sku) <3){
            return ['error' => 'SKU must have at least 3 characters'];
        }

        if(strlen($name) <7){
            return ['error' => 'Product_name must have at least 7 characters'];
        }

        if(is_null($vendorId)){
            return ['error' => 'The product must be assigned to a vendor'];
        }

        try{
            $sql = "INSERT INTO products (`sku`, `product_name`,`vendor_id`,`description`) VALUES (?,?,?,?)";
            $stmt= $this->dbConnection->prepare($sql);
            $stmt->execute([$sku, $name, $vendorId, $description]);
            return ['success' => "Product created"];
        }catch (Exception $e){
            return ['error' => "Could not create product: " . $e->getMessage()];
        }

    }

    public function updateProduct($productId, $data)
    {
        $update = '';
        foreach ($data as $key => $value){
            switch($key){
                case 'sku':
                    if(strlen($value) <3){
                        return ['error' => 'SKU must have at least 3 characters'];
                    }else{
                        $update .= 'sku="' . $value . '",';
                    }
                    break;
                case 'product_name':
                    if(strlen($value) <7){
                        return ['error' => 'Product_name must have at least 7 characters'];
                    }else{
                        $update .= 'product_name="' . $value . '",';
                    }
                    break;
                case 'description':
                    if(!is_null($value)) {
                        $update .= 'description="' . $value . '",';
                    }
                    break;
                case 'vendor_id':
                    return ['error' => 'You cannot change the vendor of a product'];
                    break;
            }
        }

        try{
            $update = rtrim($update, ',');
            $sql = "UPDATE products SET " . $update . " WHERE product_id=" . $productId;
            //return $sql;
            $query= $this->dbConnection->prepare($sql);
            $query->execute();
            return ['success' => "Product updated"];
        }catch (Exception $e){
            return ['error' => "Could not update product: " . $e->getMessage()];
        }

    }
}