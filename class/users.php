<?php
require_once('./lib/dbcnx.inc.php');
 
class User extends dbcnx{
 
    public function __construct(){
 
        parent::__construct();
    }
 
    public function check_login($username, $password){
 
        $sql = "SELECT * FROM userdata WHERE username = '$username' AND password = '$password'";

        $query = $this->connection->query($sql);

        if($query->num_rows > 0){
            $row = $query->fetch_array();
            // var_dump($row);
            // exit;
            return $row['username'];
        }
        else{
            return false;
        }
    }
 
    public function details($sql){
 
        $query = $this->connection->query($sql);
        // var_dump($sql);
        // exit;
        $row = $query->fetch_array();
 
        return $row;       
    }
 
    public function escape_string($value){
 
        return $this->connection->real_escape_string($value);
    }
}