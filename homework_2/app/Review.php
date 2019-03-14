<?php
/**
 * Created by PhpStorm.
 * User: Simona
 * Date: 28/02/2019
 * Time: 16:24
 */

class Review{

    protected $dbConnection;

    public function __construct(){
        require_once('Connection.php');
        $connection = new Connection();
        $this->dbConnection = $connection->getDbConnection();
    }
    public function getReviewsList($productId = null){
        try{
            $this->dbConnection;
            $sql = "SELECT * FROM reviews ";
            if(!is_null($productId)){
                $sql .= " WHERE product_id=" . $productId;
            }
            $query = $this->dbConnection->prepare($sql);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }catch (Exception $e){
            return ['error' => "Could not get reviews: " . $e->getMessage()];
        }
    }

    public function createReview($productId, $description, $rating, $customerName = null){

        $sql = "SELECT * FROM products WHERE product_id=" . $productId;
        $query = $this->dbConnection->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        if(!count($result)){
            return ['error' => 'Product with id ' . $productId . ' doesn\'t exist'];
        }
        if(strlen($description) <3){
            return ['error' => 'Description must have at least 3 characters'];
        }

        if(!in_array($rating,[1,2,3,4,5])){
            return ['error' => 'the rating must be a value between 1 and 5'];
        }

        if(is_null($customerName) || !strlen($customerName)){
            $customerName = 'anon';
        }
        //var_dump($customerName);exit;
        try{
            $sql = "INSERT INTO reviews (`product_id`, `customer_name`, `rating`,`description`) VALUES (?,?,?,?)";
            $stmt= $this->dbConnection->prepare($sql);
            $stmt->execute([$productId, $customerName, $rating, $description]);
            return ['success' => "Review created"];
        }catch (Exception $e){
            return ['error' => "Could not create review: " . $e->getMessage()];
        }
    }

    public function delete($reviewId){
        if(!strlen($reviewId)){
            return ['error' => 'Review id is required'];
        }

        $sql = "SELECT * FROM reviews WHERE id=" . $reviewId;
        $query = $this->dbConnection->prepare($sql);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        if(!count($result)){
            return ['error' => 'Review with id ' . $reviewId . ' doesn\'t exist'];
        }

        try{
            //$pdo = $this->connect();
            $pdo = $this->dbConnection;

            $sql = "DELETE FROM `reviews` WHERE id=$reviewId";
            $query = $pdo->prepare($sql);
            $query->execute();
            return ['success' => "Review Deleted"];
        }catch (Exception $e){
            return ['error' => "Could not delete user" . $e->getMessage()];
        }

    }

    public function updateReview($companyName, $email, $cui){
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
            $sql = "INSERT INTO reviews (`company_name`, `email`, `cui`) VALUES (?,?,?)";
            $stmt= $this->dbConnection->prepare($sql);
            $stmt->execute([$companyName, $email, $cui]);
            return ['success' => "Review created"];
        }catch (Exception $e){
            return ['error' => "Could not create review: " . $e->getMessage()];
        }

    }
}