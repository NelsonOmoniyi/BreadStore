<?php
@session_start();

///////////////////	
//error_reporting(E_ERROR);
ini_set('display_errors', 1);
//error_reporting(E_ALL);
 error_reporting(1);

require_once("dbcnx.inc.php");
// echo "sdsfghjk";

require('/validation.php');

//use Aws\S3\S3Client;  
//use Aws\Exception\AwsException;
//////////////////////
class dbobject extends dbcnx
{
  
    function __construct()
    {
        $this->key = hash('sha256', $this->key, true);
    }
	function begin(){
		@mysql_query("BEGIN");
		}
	function commit()
        {
		@mysql_query("COMMIT");
		}
	function rollback(){
		@mysql_query("ROLLBACK");
		}

	 public function db_query($sql,$object = true)
	 {
		 // if you are performig a UPDATE query; you will need to set $object == false
         file_put_contents('lo.txt',$sql);
		  $cnx = new dbcnx();
          $this->myconn = $cnx->__construct();
		 $result = mysqli_query($this->myconn,$sql);
		 $count  = ($object)?mysqli_num_rows($result):mysqli_affected_rows($this->myconn);
		 if($object)
		 {
			 if($count > 0)
			 {
				 $data = array();
				 while($row = mysqli_fetch_array($result,MYSQLI_ASSOC))
				 {
					 $data[] = $row;
				 }
				 return $data;
			 }else
			 {
				 return null;
			 }
		 }else
		 {
			 return $count;
		 }
	 }
    
    function doInsert($table,$arr,$exp_arr)
        {
            $patch1  = "(";
            $patch2  = "(";
            $cnx          = new dbcnx();
            $this->myconn = $cnx->connect();
            foreach($arr as $key=>$value)
            {
                if(!in_array($key,$exp_arr))
                {
                    $patch1.= $key.",";
                    $patch2.= "'".mysqli_real_escape_string($this->myconn,$value)."',";
                }
            }
            $patch1 =  substr($patch1,0,-1).")";
            $patch2 =  substr($patch2,0,-1).")";
            $sql = "insert into ".$table." ".$patch1." VALUES ".$patch2;
            file_put_contents('m_query.txt',$sql);
            $num_row = $this->db_query($sql,false);
            return $num_row;
        }
        function doUpdate($table,$arr,$exp_arr,$clause)
        {
            $patch1     = "";
            $key_id     = "";
            $cnx          = new dbcnx();
            $this->myconn = $cnx->connect();
            foreach($arr as $key=>$value)
            {
                if(!in_array($key,$exp_arr))
                {
                    $patch1.= $key."='".mysqli_real_escape_string($this->myconn,$value)."',";
                }
            }
            foreach($clause as $key=>$value)
            {
                $key_id.= " ".$key."='".$value."' AND";
            }
            $key_id  =  substr($key_id,0,-3);
            $patch1  =  substr($patch1,0,-1);
            $sql    = "UPDATE ".$table." SET ".$patch1." WHERE ".$key_id;
            file_put_contents("user_edit.txt",$sql);
            $num_row = $this->db_query($sql,false);
            return $num_row;
        }
	public function insertMysql($table, $data = array()) 
	{
 		$fields = implode(', ', array_keys($data));
 		$values = implode('", "', array_map('mysql_escape_string', $data));
 		$query = sprintf('INSERT INTO %s (%s) VALUES ("%s")', $table, $fields, $values);
 		return $this->queryMysql($query);
 	}

	public function queryMysql($sql) 
	{
 		if ($this->debug === false)
		{
 			try {
 				$result = mysql_query($sql);
 				if ($result === false)
				{
 					throw new Exception('MySQL Query Error: ' . mysql_error());
					//$result = '-1';
 				}
 				return $result;
 			}
 			catch (Exception $e) {
 				return $e->getMessage();
 				//exit();
 			}
 		}
 		else {
 			printf('<textarea>%s</textarea>', $sql);
 		}
 	}

function getcheckdetails($user,$password) {
	//echo 'country code : '.$countrycode;
	$str_cipher_password = hash("sha512",$password);
	
	$label = "";
	$table_filter = " where username='".$user."' and password='".$str_cipher_password."'";
	
	$query = "select * from userdata ".$table_filter;
	//echo $query;
	$result = mysql_query($query);
	$numrows = mysql_affected_rows();
	//echo ' num rows :'.$numrows;
	$dbobject = new dbobject();
	$no_of_pin_misses = $dbobject->getitemlabel('parameter','parameter_name','no_of_pin_misses','parameter_value');
	$pin_missed = $dbobject->getitemlabel('userdata','username',$user,'pin_missed');
	$override_wh = $dbobject->getitemlabel('userdata','username',$user,'override_wh');
	$extend_wh = $dbobject->getitemlabel('userdata','username',$user,'extend_wh');
	
	if($numrows > 0){
		
			    $label = "1";
                $_SESSION['username_sess']  = $user;
                $_SESSION['role_id_sess']   = $row['role_id'];
                $_SESSION['firstname_sess'] = $row['firstname'];
                $_SESSION['lastname_sess']  = $row['lastname'];
                $_SESSION['super_agent_id'] = $row['super_agent_id'];
                $_SESSION['reg_status']     = $row['reg_status'];
                $_SESSION['approval']       = $row['user_approved'];
                $_SESSION['password']       = $password;
                $_SESSION['last_page_load'] = time();

	}else{
			$label = "12";
	}
	   return $label."::||::".(4 - $pin_missed);
}




///// NEW ADDITIONS

function logaccess ($username,$time,$message) {
	$filename = date("Y-M-d");
	$my_file = "logs/".$filename.'.log';
	$success =  $time.' by '.$username.' --- using  '.$_SERVER['REMOTE_ADDR'].' -- '.$message."\r\n";
	$handle = fopen($my_file, 'a+') or die('Cannot open file:  '.$my_file); //implicitly creates file
	fwrite($handle,$success);
	fclose($handle);
		
}

function getuserip_status($user){
	$date = date("Y-m-d");
	$qr1 = " SELECT AUDIT_IP,AUDIT_USER FROM audit_trail_account WHERE AUDIT_USER = '".$user."' AND  
	SUBSTR(AUDIT_T_IN, 1, 10)= '$date'  AND AUDIT_T_OUT IS NULL  ";
	//echo $qr1;
			$mq1 = mysql_query($qr1);
			$mn1 = mysql_num_rows($mq1);
			$label = 0;
			if($mn1>0){
				$label = $mn1;
			
			}
			return $label;
	
	
}


function doAuditTrai_logout($operation,$user){
			//$count_entry = 0;
			//$user= $_SESSION[username_sess];
			$date = date("Y-m-d");
			$client_ip = $_SERVER['REMOTE_ADDR'];
				   $query = "UPDATE  audit_trail_account SET AUDIT_T_OUT=now() WHERE AUDIT_USER='$user'
				   AND SUBSTR(AUDIT_T_IN, 1, 10) = '$date' ";
				   //echo $query;
				   //$unset = unset($_SESSION['IN']);
				   $result = mysql_query($query);
			       $count_entry = $query;

			return $count_entry;
}



function doAuditTrail($operation){
			//$count_entry = 0;
			$user= $_SESSION[username_sess];
			$client_ip = $_SERVER['REMOTE_ADDR'];

			if($operation=="IN"){
			@$now = date("Y-m-d H:i:s");
			$_SESSION['IN'] = $now;
			$query = " INSERT INTO  audit_trail_account (AUDIT_USER,AUDIT_T_IN,AUDIT_IP)
			  VALUES('$user','$now','$client_ip')";
			//echo $query;
			$result = mysql_query($query);
			//$count_entry = mysql_num_rows($result);
			}
			else
			   if($operation=="0UT"){
				   //echo "innow";
				   $now = $_SESSION['IN'];
				   $query = "UPDATE  audit_trail_account SET AUDIT_T_OUT=now() WHERE AUDIT_USER='$user'
				   AND AUDIT_T_IN='$now'";
				   //echo $query;
				   //$unset = unset($_SESSION['IN']);
				   $result = mysql_query($query);
			       $count_entry = $query;

			}
			return $count_entry;
		}

function getlastlogin($user){
	$date = date("Y-m-d");
	//check to see if the user has logged in to this system or another system
			$qr = " SELECT AUDIT_IP, AUDIT_T_IN FROM audit_trail_account WHERE AUDIT_USER='".$user."' AND 
			        SUBSTR(AUDIT_T_IN, 1, 10)= '$date'  ORDER BY AUDIT_T_IN DESC LIMIT 1  ";
					//echo $qr;
			$mq = mysql_query($qr);
			$mn = mysql_num_rows($mq);
			if($mn>0){
				$rr = mysql_fetch_array($mq);
				$the_ip = $rr["AUDIT_IP"];
				$last_time_in = $rr["AUDIT_T_IN"];			
			}
			return $last_time_in;	
}



	function reset_ip($user)
	{
		
		//check to see if the user has logged in to this system or another system
		$date = date("Y-m-d");
				$qr = " SELECT AUDIT_IP FROM audit_trail_account WHERE AUDIT_USER='".$user."' AND 
						SUBSTR(AUDIT_T_IN, 1, 10)= '$date'  AND AUDIT_T_OUT IS NULL ";
						//echo $qr;
				$mq = mysql_query($qr);
				$mn = mysql_num_rows($mq);
				if($mn>0){
					$rr = mysql_fetch_array($mq);
					$the_ip = $rr["AUDIT_IP"];
					$sys_ip  = $_SERVER['REMOTE_ADDR'];
					//$label = "16";
					$operation="0UT";
					$dbobject = new dbobject();
					$audit = $dbobject->doAuditTrai_logout($operation,$user);
						
				}
	}
		
	function getitemlabel2($tablename,$table_col,$table_val,$table_col2,$table_val2,$ret_val) {
	//echo 'country code : '.$countrycode;
	$label = "";
	$table_filter = " where ".$table_col."='".$table_val."' and ".$table_col2."='".$table_val2."'";

	$query = "select ".$ret_val." from ".$tablename.$table_filter;
	//echo $query;
	$result = mysql_query($query);
	$numrows = mysql_num_rows($result);
	if($numrows > 0){
		$row = mysql_fetch_array($result);
		$label = $row[$ret_val];
	}
	return $label;
	}
	function getitemlabel4($tablename,$table_col,$table_val,$table_col2,$table_val2,$table_col3,$table_val3,$table_col4,$table_val4,$ret_val) {
	//echo 'country code : '.$countrycode;
	$label = "";
	$table_filter = " where ".$table_col."='".$table_val."' and ".$table_col2."='".$table_val2."' and ".$table_col3."='".$table_val3."' and ".$table_col4."='".$table_val4."'";

	$query = "select ".$ret_val." from ".$tablename.$table_filter;
	//echo $query;
	$result = mysql_query($query);
	$numrows = mysql_num_rows($result);
	if($numrows > 0){
		$row = mysql_fetch_array($result);
		$label = $row[$ret_val];
	}
	return $label;
	}
	
	
	function reset_login_status($user){
	$date = date("Y-m-d");
	$now = $_SESSION['IN'];
	$qr1 = " SELECT AUDIT_IP,AUDIT_USER FROM audit_trail_account WHERE AUDIT_USER = '".$user."' AND  
	AUDIT_T_IN= '$now'  AND AUDIT_T_OUT IS NULL AND AUDIT_IP = '".$_SERVER['REMOTE_ADDR']."' ";
	//echo $qr1;
			$mq1 = mysql_query($qr1);
			$mn1 = mysql_num_rows($mq1);
			$label = 0;
			if($mn1>0){
				$label = $mn1;
			
			}
			return $label;	
}

	//// select a field from a table
	function getitemlabel($tablename,$table_col,$table_val,$ret_val) {
	$label = "";
	$table_filter = " where ".$table_col."='".$table_val."'";

	$query = "select ".$ret_val." from ".$tablename.$table_filter;
	//echo $query;
	$result = $this->db_query($query);
	$numrows = count($result);
	if($numrows > 0){
//		$row = mysql_fetch_array($result);
        foreach($result as $row)
        {
            $label = $row[$ret_val];
        }
		
	}
	return $label;
	}
	
	function getitemlabelmenu($tablename,$table_col,$table_val,$ret_val) {
	$label = "";
	$table_filter = " where ".$table_col."='".$table_val."'";

	$query = "select ".$ret_val." from ".$tablename.$table_filter;
	//echo $query;
	$result = mysql_query($query);
	$numrows = mysql_affected_rows();
	if($numrows > 0){
		while($row = mysql_fetch_array($result)){
		$label .= "'".$row[$ret_val]."',";
		}
		$label = rtrim($label,",");
	}
	return $label;
	}
	
	///////////////
	function loadParameters(){
		$label = "";
		$query = "select * from parameter";
		$result = mysql_query($query);
		$numrows = mysql_num_rows($result);
		for($i=0; $i<$numrows; $i++){
			$row = mysql_fetch_array($result);
			$label = $label .'"'.$row["parameter_name"].'"=>"'.$row["parameter_value"]."\", ";
			$_SESSION[$row["parameter_name"]] = $row["parameter_value"];
		}
		return $label;
	}
	//////////
	function getrecordset($tablename,$table_col,$table_val)
	{
		$label = "";
		$table_filter = " where ".$table_col."='".$table_val."'";
	
		$query = "select * from ".$tablename.$table_filter;
		//echo $query;
		$result = mysql_query($query);
		//$numrows = mysql_num_rows($result);
		/*
		if($numrows > 0){
			$row = mysql_fetch_array($result);
			$label = $row[$ret_val];
		}
		*/
		return $result;
	}
	/////////////////
	function getrecordsetdata($query) {
	$query = $query;
	//echo $query;
	$result = mysql_query($query);
	return $result;
	}
	

	//////////////////
	function getparentmenu($opt) {
	$filter = "";
	$options = "<option value='#'>::: None ::: </option>";
		 /*
		 if($opt!= ""){
		 $filter = "where menu_id='".$opt."' and parent_id='#' "; //" username='$username' and password='$password' ";
		 }else{
		 */
			$filter = "where parent_id='#' or parent_id2='#'  order by menu_order";
		 //}
	$query = "select distinct menu_id, menu_name from menu  ".$filter;
	//echo $query;
	$result = mysql_query($query);
	$numrows = mysql_num_rows($result);
	if($numrows > 0){
		for($i=0; $i<$numrows; $i++){
		$row = mysql_fetch_array($result);
		//echo $row['country_code'];
		 if($opt==$row['menu_id']) $filter='selected';
		//echo ($opt=='$row["country_code"]'?'selected':'None');
		$options = $options."<option value='$row[menu_id]' $filter >$row[menu_name]</option>";
		$filter='';
		}
	}
	return $options;
	}
	function getsubmenu($opt) {
		$filter = "";
		$options = "";
			 if($opt!= ""){
			 $filter = "where parent_id='$opt' order by menu_order"; //" username='$username' and password='$password' ";
			 }
		$query = "select distinct menu_id, menu_name from menu  ".$filter;
		//echo $query;
		$result = mysql_query($query);
		$numrows = mysql_num_rows($result);
		if($numrows > 0){
			for($i=0; $i<$numrows; $i++){
			$row = mysql_fetch_array($result);
			$options = $options."<option value='$row[menu_id]' $filter >$row[menu_name]</option>";
			$filter='';
			}
		}
		return $options;
		}
	////////////////////////////////////
		function reorder_submenu($parent_menu,$sub_menu){
		$num_count = 0;
		$sub_menu_arr = explode(',',$sub_menu);
		for($i=0; $i<sizeof($sub_menu_arr); $i++){
			$query = "update menu set menu_order=$i where menu_id= '$sub_menu_arr[$i]'";
			//echo $query;
			$result = mysql_query($query);
			$num_count+=mysql_affected_rows();
		}
			return $num_count;
		}
		///////////////////////////////////
	function validatepassword($user,$password){
	//echo 'country code : '.$countrycode;
	
	$str_cipher_password = hash("sha512",$password);
	
	$label = "";
	$table_filter = " where username='".$user."' and password='".$str_cipher_password."'";

	$query = "select * from userdata".$table_filter;
	//echo $query;
	$result = mysql_query($query);
	$numrows = mysql_num_rows($result);
	if($numrows > 0) $label = "1";
	else $label = "-1";	
	
	return $label;
	}
	
	// Change to user profile password
	function doPasswordChange($username,$user_password)
    {
        //		auditTrail("update","Change Password","userdata","changepassword.php","username",$username);
			$desencrypt = new DESEncryption();
			$key = $username;
			$cipher_password = $desencrypt->des($key, $user_password, 1, 0, null,null);
			$str_cipher_password = $desencrypt->stringToHex ($cipher_password);
		    $query_data = "update userdata set password='$str_cipher_password' where username= '$username'";
			//echo $query_data;
			$result_data = mysql_query($query_data);
			$count_entry = mysql_affected_rows();
			
			return $count_entry;
	}
	function pick_role($opt) {
	$filter = "";
	$options = "<option value=''>::: Select a Role ::: </option>";
	/*
	if($opt!= ""){
	 $filter = "where role_id='".$opt."'"; //" username='$username' and password='$password' ";
	 }
	 */
	$dbobject = new dbobject();
	$user_role_session = $_SESSION['role_id_sess'];
	//$filter_role_id = $dbobject->getitemlabel('parameter','parameter_name','admin_code','parameter_value');
	//$filteradmin = ($user_role_session == $filter_role_id)?"":" and role_id not in ('".$filter_role_id."')";
	$query = "select distinct role_id, role_name from role where 1=1  ";//.$filteradmin;
	//echo $query;
	$result = mysql_query($query);
	$numrows = mysql_num_rows($result);
	if($numrows > 0){
		for($i=0; $i<$numrows; $i++){
		$row = mysql_fetch_array($result);
		//echo $row['country_code'];
		 if($opt==$row['role_id']) $filter='selected';
		//echo ($opt=='$row["country_code"]'?'selected':'None');
		$options = $options."<option value='$row[role_id]' $filter >$row[role_name]</option>";
		$filter='';
		}
	}
	return $options;
	}
	////////////////////////
	function doRole($role_id,$role_name,$enable_role){
			$count_entry = 0;
			$query = "select * from role  where role_id='$role_id'";
			//echo $query;
				$result = mysql_query($query);
				$numrows = mysql_num_rows($result);
			if($numrows >=1)
			{
				$query_data ="update role set role_name='$role_name', role_enabled='$enable_role' where role_id='$role_id' ";
				$result_data = mysql_query($query_data);
				$count_entry =mysql_affected_rows();
			}
			else
			{
			$sql = "select * from role  where role_name='$role_name'";
			if($res=mysql_query($sql))
			{
			if(mysql_num_rows($res)>=1)
			{
				$count_entry=-9;
				}			
			else
			{	
				$query_data = "insert into role (role_id,role_name,role_enabled,created) values( '$role_id','$role_name','$enable_role',now())";
				//echo $query_data;
				$result_data = mysql_query($query_data);
				$count_entry = mysql_affected_rows();
			}
			}
		}
			return $count_entry;
		}
	function doUser($operation,$username,$userpassword,$firstname,$lastname,$email,$phone, $chgpword_logon, $user_locked, $user_disable,$day_1,$day_2,$day_3,$day_4,$day_5,$day_6,$day_7,$override_wh,$extend_wh,$role_id,$role_name,$insurance_coy,$issuer_code,$reg_no,$c_addr,$city,$state,$dob,$sex,$marital_status,$account_no,$account_name,$bank_name,$contact_address,$office_address,$s_agent_id,$office_state,$office_lga,$company_name,$company_address,$rc_number)
	{
				
				$role_name = $this->getitemlabel("role","role_id",$role_id,"role_name");
				$posted_user=$_SESSION['username_sess'];
				$desencrypt = new DESEncryption();
				$count_entry = 0;
				$key = $username;
				$cipher_password = $desencrypt->des($key, $userpassword, 1, 0, null,null);
				$str_cipher_password = $desencrypt->stringToHex ($cipher_password);
				
				$query = "select * from userdata where username='$username'";
				//echo $query;
				$result = mysql_query($query);
				$numrows = mysql_num_rows($result);
				//$operation = $_SESSION['save_user_operation'];
				//echo $operation.":::".$numrows.":::";
				if($numrows >=1 && $operation=='new')
				{
					$count_entry = -9;
					
				}
				if($numrows >=1 && $operation!='new')
				{
						///////////////////////////
						$addquery = $user_locked=='0'?",pin_missed=0":"";
						$query_data ="update userdata set password='$str_cipher_password', role_id='$role_id', role_name = '$role_name', firstname='$firstname', lastname='$lastname', email='$email', mobile_phone='$phone', passchg_logon='$chgpword_logon', user_disabled='$user_disable', user_locked='$user_locked', day_1='$day_1', day_2='$day_2', day_3='$day_3', day_4='$day_4', day_5='$day_5', day_6='$day_6', day_7='$day_7', modified=now(), override_wh='$override_wh', extend_wh='$extend_wh',posted_user='$posted_user', bank_name = '$bank_name', super_agent_id = '$s_agent_id',account_no = '$account_no',account_name = '$account_name',contact_address = '$contact_address',office_address = '$office_address',reg_status = '1',office_state='$office_state',office_lga='$office_lga' $addquery where username='$username'";
						//echo $query_data;
            //                    		file_put_contents("june.txt",$query_data);
						$result_data = mysql_query($query_data) or die(mysql_error());
						//echo mysql_error();
						$count_entry = mysql_affected_rows();
                        $_SESSION['reg_status'] = 1;
				}
				//echo "church_id" . $church_id;
				if($numrows ==0 && $operation=='new')
				{
						$pass_expiry_days = $_SESSION['password_expiry_days'];
						$today = @date("Y-m-d");
						$pass_dateexpire = @date("Y-m-d",strtotime($today."+".$pass_expiry_days."days"));
						//echo $branch_id . $branch_name;
						
						 $query_data = "insert into userdata (username,password,role_id,role_name, firstname, lastname, email, mobile_phone, passchg_logon, user_disabled, user_locked,day_1,day_2,day_3,day_4,day_5,day_6,day_7,created, modified,override_wh,extend_wh,pass_dateexpire,posted_user,bank_name,account_no,account_name,super_agent_id,contact_address,office_address,office_state,office_lga,reg_status) values( '$username','$str_cipher_password','$role_id', '$role_name', '$firstname','$lastname','$email','$phone','$chgpword_logon','$user_disable','$user_locked','$day_1', '$day_2', '$day_3', '$day_4', '$day_5', '$day_6', '$day_7' , now(), now(), '$override_wh', '$extend_wh', '$pass_dateexpire', '$posted_user','$bank_name','$account_no','$account_name','$s_agent_id','$contact_address','$office_address','$office_state','$office_lga','1')";
						// $query_data;
						$result_data = mysql_query($query_data); //
						// die(mysql_error());
						if($role_id == 002)
						{
							$this->coperate_data($operation,$role_id,"insert",$company_name,$company_address,$rc_number,$_SESSION['username_sess'],$username);
						}
						
						$count_entry = mysql_affected_rows();
				} 
				//End inner else
				// echo $query_data;
				return $count_entry;
	}		

		function paddZeros($id, $length){
		$data = "";
		$zeros = "";
		 $rem_len = $length - strlen($id);

		if($rem_len > 0){
			for($i=0; $i<$rem_len; $i++){
				$zeros.="0";
			}
			$data = $zeros.$id;
		}else{
			$data = $id;
		}
		return $data;
	}
	
	///////////////////////////////
	function getnextid($tablename){
	//require_once("../../Copy of acomoran/lib/connect.php");
	$id = 0;
	$query = "update gendata set table_id=table_id+1 where table_name= '$tablename'";
	//echo $query;
	$resultid = $this->db_query($query,false);
	// $numrows = mysql_affected_rows();
	//echo 'result '.$resultid;
	if($resultid==0){
		$query_ins = "insert into gendata values ('$tablename', 1)";
		//echo $query_ins;
		$result_ins = $this->db_query($query_ins,false);
		// $numrows = mysql_affected_rows();
	}
	// Get the new id
	$query_sel = "select table_id from gendata where table_name= '$tablename'";
	//echo $query;
	$result_sel = $this->db_query($query_sel);
	// $numrows_sel = mysql_num_rows($result_sel);
		if(count($result_sel)==1){
			// $row = mysql_fetch_array($result_sel);
			$id = $result_sel[0]['table_id'];
			
			//result count when it reaches 
			if($id > 999998){
				$query = "update gendata set table_id=0 where table_name= '$tablename'";
				//echo $query;
				$resultid = $this->db_query($query,false);
			}
		}

	return $id;
	}
	
	
	//////////////////////////////////////////
	function doMenu($menu_id,$menu_name,$menu_url,$parent_menu,$menu_level,$parent_menu2){
			$count_entry = 0;
			$query = "select * from menu  where menu_id='$menu_id'";
			//echo $query;
			$result = mysql_query($query);
			$numrows = mysql_num_rows($result);
			if($numrows >=1){
				 $query_data ="update menu set menu_name='$menu_name', menu_url='$menu_url', parent_id='$parent_menu',  parent_id2='$parent_menu2', menu_level='$menu_level' where menu_id='$menu_id' ";
			//echo $query_data;
			$result_data = mysql_query($query_data);
			$count_entry = mysql_affected_rows();
			}
			else
			{
			$sql="select * from menu  where menu_name='$menu_name'";
			if($res=mysql_query($sql))
			{
				if(mysql_num_rows($res)>=1)
				{
				$count_entry=-9;	
				}
				else if(mysql_num_rows==0)
				{
			 $query_data = "insert into menu (menu_id,menu_name,menu_url,parent_id,parent_id2,menu_level,created) values( '$menu_id','$menu_name','$menu_url','$parent_menu','$parent_menu2','$menu_level',now())";
			//echo $query_data;
			$result_data = mysql_query($query_data);
			$count_entry = mysql_affected_rows();
				}
				else
				{
				$count_entry=-9;	
				}
			}
			}
			return $count_entry;
		}
		/////////////////////////////////////////////////////////
	function getmenu($opt) {
	$filter = "";
	$options = "<option value='#'>::: Select Menu Option ::: </option>";
		 if($opt!= ""){
		 $filter = " and menu_id='".$opt."' "; //" username='$username' and password='$password' ";
		 }
		 $filter .=" order by menu_name ";
		 $dbobject = new dbobject();
	 $user_role_session = $_SESSION['role_id_sess'];
	 //$filter_role_id = $dbobject->getitemlabel('parameter','parameter_name','admin_code','parameter_value');
	 //$filter_menu_id = $dbobject->getitemlabelmenu('parameter','parameter_name','admin_menu_code','parameter_value');
	 //$filteradmin = ($user_role_session == $filter_role_id)?"":" and menu_id not in (".$filter_menu_id.")";
	$query = "select distinct menu_id, menu_name from menu where 1=1 ".$filter;
	//echo $query;
	$result = mysql_query($query);
	$numrows = @mysql_num_rows($result);
	if($numrows > 0){
		for($i=0; $i<$numrows; $i++){
		$row = mysql_fetch_array($result);
		//echo $row['country_code'];
		 if($opt==$row['menu_id']) $filter='selected';
		//echo ($opt=='$row["country_code"]'?'selected':'None');
		$options = $options."<option value='$row[menu_id]' $filter >$row[menu_name]</option>";
		$filter='';
		}
	}
	return $options;
	}
	/////////////////////////////////
	function getexistrole($opt) {
	$filter = "";
	$user_role_session = $_SESSION['role_id_sess'];
	//$options = "<option value='#'>::: Select Menu Option ::: </option>";
		 if($opt!= ""){
		 $filter = "where menu_id='".$opt."' "; //" username='$username' and password='$password' ";
		 }
	$query = "select role_id, role_name from role where role_id in (select role_id from menugroup   ".$filter.") and role_id not in(select parameter_value from parameter where parameter_name='$user_role_session' )";
	//echo $query;
	$result = mysql_query($query);
	$numrows = mysql_num_rows($result);
	if($numrows > 0){
		for($i=0; $i<$numrows; $i++){
		$row = mysql_fetch_array($result);
		//echo $row['country_code'];
		 //if($opt==$row['role_id']) $filter='selected';
		//echo ($opt=='$row["country_code"]'?'selected':'None');
		$options = $options."<option value='$row[role_id]' $filter >$row[role_name]</option>";
		$filter='';
		}
	}
	return $options;
	}
	///////////////////////////////////////////

	
	function doMenuGroup($menu_id,$exist_role){
			$comp_id = "#";
			$count_entry = 0;
			$exist_role_arr = explode(",",$exist_role);
			$role_id = "";
			for($i=0; $i<count($exist_role_arr); $i++){
			$role_id = $role_id."'".$exist_role_arr[$i]."', ";
			}
			$role_id = substr($role_id,0,(strlen($role_id)-2));
			$query_data ="delete from menugroup where role_id not in ($role_id, 001) and menu_id='$menu_id' ";
			//echo $query_data.'<br>';
			$result_data = mysql_query($query_data);
			$count_entry += mysql_affected_rows();

			for($i=0; $i<count($exist_role_arr); $i++){
                echo $query_data_i = "insert into menugroup values ('$exist_role_arr[$i]','$menu_id')";
                //echo $query_data_i.'<br>';
                $result_data_i = mysql_query($query_data_i);
                $count_entry += mysql_affected_rows();
			}

			//echo "Count Entry :: "+$count_entry;
			return $count_entry;
		}
		////////////////////////////////////////////////

		function gettableselect($tablename, $field1, $field2, $opt) {
	$filter = "";
	$options = "<option value=''>::: please select option ::: </option>";
	$query = "select distinct $field1, $field2 from $tablename  ".$filter;
	//echo $query;
	$result = mysql_query($query);
	$numrows = mysql_num_rows($result);
	if($numrows > 0){
		for($i=0; $i<$numrows; $i++){
		$row = mysql_fetch_array($result);
		//echo $row['country_code'];
		 if($opt==$row[$field1]) $filter='selected';
		//echo ($opt=='$row["country_code"]'?'selected':'None');
		$options = $options."<option value='$row[$field1]' $filter >$row[$field2]</option>";
		$filter='';
		}
	}
	return $options;
	}

	///////////////////////////////////
	function gettableselectorder($tablename, $field1, $field2, $opt,$order) {
	$filter = "";
	$order_by = "";
	$options = "<option value=''>::: please select option ::: </option>";
	if($order!='') $order_by = " order by ".$order;
	$query = "select distinct $field1, $field2 from $tablename  ".$filter.$order_by ;
	//echo $query;
	$result = mysql_query($query);
	$numrows = mysql_num_rows($result);
	if($numrows > 0){
		for($i=0; $i<$numrows; $i++){
		$row = mysql_fetch_array($result);
		//echo $row['country_code'];
		 if($opt==$row[$field1]) $filter='selected';
		//echo ($opt=='$row["country_code"]'?'selected':'None');
		$options = $options."<option value='$row[$field1]' $filter >$row[$field2]</option>";
		$filter='';
		}
	}
	return $options;
 }
	/////////////////////////////////////
	function getdataselect($sql) {
	$filter = "";
	$options = "<option value=''>::: please select option ::: </option>";
	//$query = "select distinct $field1, $field2 from $tablename  ".$filter;
	//echo $sql;
	$result = mysql_query($sql);
	$numrows = mysql_num_rows($result);
	if($numrows > 0){
		for($i=0; $i<$numrows; $i++){
		$row = mysql_fetch_array($result);
		$options = $options."<option value='$row[0]' $filter >$row[1]</option>";
		$filter='';
		}
	}
	return $options;
	}

	
	function getTblField($tablename,$field1,$field2,$field3) {
		$query = "select distinct $field1 from $tablename  where $field2='$field3'";
		//echo $query;
		$result = mysql_query($query);
		$numrows = mysql_num_rows($result);
		if($numrows > 0){
			$row = mysql_fetch_array($result);
			$options = $row[$field1];
		}
		return $options;
	}
	
	function getTblItemList($tablename,$field1) {
	$options = "<option value=''>::: please select option ::: </option>";
		$query = "select distinct $field1 from $tablename";
		//echo $query;
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result)){
			$options .= "<option value='$row[$field1]'>$row[$field1]</option>";
		}
		return $options;
	}
	
	function getFormInput($tablename,$field2,$field3,$field4,$field5){
		$query = "select * from $tablename  where $field2='$field3' and $field4='$field5'";
		//echo $query;
		$result = mysql_query($query);
		//$numrows = mysql_num_rows($result);
		/*while($row = mysql_fetch_array($result)){
			$options .= "<input type='checkbox' name='<?php echo $row[$field1]; ?>' id='<?php echo $row[$field1]; ?>'> ".$row[$field]."  &nbsp;&nbsp;&nbsp;&nbsp;".$row[$field1]."<br /><hr></hr>";
		}*/
		return $result;
	}
	

	function getparameter($opt,$parameter_id,$parameter_table,$parameter_col,$val1) {
	$filter = "";
	$options = "<option value=''>::: Select ::: </option>";
		 /*
		 if($opt!= ""){
		 $filter = "where menu_id='".$opt."' and parent_id='#' "; //" username='$username' and password='$password' ";
		 }else{
		 */$filter1 = "";
			if($parameter_id!=''){$filter1= "and  ".$parameter_col." = '$parameter_id' ";}
			$filter = " where 1=1 ";
		 //}
	$query = "select * from ".$parameter_table.$filter.$filter1;
	//echo $query;
	$result = mysql_query($query);
	$numrows = mysql_num_rows($result);
	$filter='';
	if($numrows > 0){
		for($i=0; $i<$numrows; $i++){
		$row = mysql_fetch_array($result);
		//echo $row['country_code'];
		 if($opt==$row[$val1]) $filter='selected';
		//echo ($opt=='$row["country_code"]'?'selected':'None');
		$options = $options."<option value='$row[$val1]' $filter >$row[$val1]</option>";
		$filter='';
		}
	}
	return $options;
	}


	function doDbTblUpdate($tbl,$setFieldArr,$setFieldValArr,$whrFieldArr,$whrFieldValArr)
	{
		if(count($setFieldArr)==count($setFieldValArr) && count($whrFieldArr)==count($whrFieldValArr))
		{
			////////// set clause starts here////////////////////////////////
			for($i=0; $i<count($setFieldArr);$i++)
			{
				$setClause .= $setFieldArr[$i]."='".$setFieldValArr[$i]."', ";
			}
			$setClause = rtrim($setClause,", ");
			//echo $setClause;
			/////////////////////////////////////////////////////////////////
			///////////////where clause starts here/////////////////////////
			for($j=0; $j<count($whrFieldArr);$j++)
			{
				$whrClause .= $whrFieldArr[$j]."='".$whrFieldValArr[$j]."' AND ";
			}
			$whrClause = rtrim($whrClause," AND ");
			// echo $whrClause;
			///////////////////////////////////////////////////////////////
			////////////the complete query/////////////////////////////////
			$query = "UPDATE ".$tbl." SET ".$setClause." WHERE ".$whrClause;
			 //echo $query;
			$result = $this->db_query($query,false);
			if($result>=0)
			{
				$resp = 1;//successful
				return $resp;
			}else
			{
				$resp = 2;//update not successful. Possibly transaction details not available 
				return $resp;
			}
		}else
		{
			$resp = 3; //array count does not match
			return $resp;
		}
		
	}
	
	function getItemLabelArr($tablename,$table_col_arr,$table_val_arr,$ret_val_arr)
	{
		$label = "";
		/////////////////////////////////////////////////////////////////
		////////// select clause starts here////////////////////////////////
		if($ret_val_arr=="*")
		{
			$qquery = "SHOW COLUMNS FROM $tablename ";
			//echo $qquery;
			$result = $this->db_query($qquery);
            //			echo mysql_error();
            //			while($roww = mysql_fetch_array($result))
            foreach($result as $roww)
      		{
				$selectClause .=$roww[0].", ";
				$ret_val[] = $roww[0];
			}
			$retCount =$ret_val;
			$selectClause = rtrim($selectClause,", ");
		}else
		{
			for($i=0; $i<count($ret_val_arr);$i++)
			{
				$selectClause .=$ret_val_arr[$i].", ";
			}
			$selectClause = rtrim($selectClause,", ");
			$retCount = $ret_val_arr;
			//echo $setClause;
		}
		/////////////////////////////////////////////////////////////////
		///////////////where clause starts here/////////////////////////
		for($j=0; $j<count($table_col_arr);$j++)
		{
			$whrClause .= " AND ".$table_col_arr[$j]."='".$table_val_arr[$j]."' ";
		}
		$whrClause = rtrim($whrClause,", ");
		/////////////////////////////////////////////////////////////
		/////////////////////////////////////////////////////////////
		$table_filter = " where 1=1 ".$whrClause;
	
		$query = "select ".$selectClause." from ".$tablename.$table_filter;
		//echo $query;
		$result = $this->db_query($query);
		$numrows = count($result);
		if($numrows > 0)
		{
			$retValue  = $result;
		}
		return $retValue;
	}
    
    public function getCurrentData($table_name,$table_field,$table_id)
    {
        $sql = "SELECT * FROM $table_name WHERE  $table_field = '$table_id' LIMIT 1";
        $result = $this->db_query($sql);
        return $result[0];
    }
	
    public function logData($current_data,$insert_data,Array $option, Array $exempt = [])
    {
        $result       = $this->doInsert("log_table",array("username"=>$_SESSION['username_sess'],"table_name"=>$option['table_name'],"table_id"=>$option['table_id'],"table_alias"=>$option['table_alias'],"created"=>date("Y-m-d h:i:s")),[]);
        $insert_id    = $this->getInsert_id();
        if($result == "1")
        {
            $difference = array_diff($insert_data,$current_data);
            foreach($difference as $key=>$value)
            {
                if(!in_array($key,$exempt))
                {
                    $this->doInsert("log_details",array("log_id"=>$insert_id,"field_name"=>$key,"previous_data"=>$current_data[$key],"current_data"=>$value,"field_alias"=>""),[]);
                }
            }
        }
    }
	public function getInsert_id()
	 {
		 return mysqli_insert_id($this->myconn);
	 }
    
	
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//End Class
?>
