<?php
class dbcnx{
 
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'breadstore';
 
    protected $connection;
 
    public function __construct(){
 
        if (!isset($this->connection)) {
 
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
 
            if (!$this->connection) {
                echo 'Cannot connect to database server';
                exit;
            }            
        }    
 
        return $this->connection;
    }

    // public function db_query($sql,$object = true)
    // {
        
    //      $myconn = $this->__construct();
    //     $result = mysqli_query($myconn,$sql);
    //     $count  = ($object)?mysqli_num_rows($result):mysqli_affected_rows($myconn);
    //     if($object)
    //     {
    //         if($count > 0)
    //         {
    //             $data = array();
    //             while($row = mysqli_fetch_array($result,MYSQLI_ASSOC))
    //             {
    //                 $data[] = $row;
    //             }
    //             return $data;
    //         }else
    //         {
    //             return null;
    //         }
    //     }else
    //     {
    //         return $count;
    //     }
    // }
}
?>