<?php
ini_set( 'date.timezone', 'Africa/Lagos' );

class dbcnx
{
	public $host  = "localhost";
     public $user  = "root";
     public $pass  = "";
     public $db    = "breadstore";
    

     public $myconn;
	public function connect()
	{
		$this->myconn = new mysqli($this->host,$this->user, $this->pass ,$this->db );
	  /* check connection */
		if (mysqli_connect_errno()) {
		  printf("Connect failed: %s\n", mysqli_connect_error());
		  exit();
		}
		return $this->myconn;
	}
}

	 
?>