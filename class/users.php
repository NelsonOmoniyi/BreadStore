<?php
Class Users extends dbobject{
    
    public function login($data)
	{
        var_dump($data);
        exit;
        
		$username = $data['username'];
		$password = $data['password'];
        
        $validate = $this->validate($data,array('username'=>'required|email','password'=>'required'));
        if($validate['error'])
        {
            return json_encode(array('response_code'=>13,'response_message'=>$validate['messages'][0]));
        }
		$sql      = "SELECT username,firstname,lastname,sex,role_id,password,user_locked,user_disabled,pin_missed,day_1,day_2,day_3,day_4,day_5,day_6,day_7,passchg_logon,photo,church_id FROM userdata WHERE username = '$username' LIMIT 1";
		$result   = $this->db_query($sql,true);
		$count    = count($result); 
		if($count > 0)
		{
            if($result[0]['pin_missed'] < 5)
            {
                $encrypted_password = $result[0]['password'];
                $is_locked     = $result[0]['user_locked'];
                $is_disabled     = $result[0]['user_disabled'];
                // $verify_pass   = password_verify($password,$hash_password);

                $desencrypt = new DESEncryption();
                $key = $username;
                $cipher_password = $desencrypt->des($key, $password, 1, 0, null,null);
                $str_cipher_password = $desencrypt->stringToHex ($cipher_password);
                if($str_cipher_password == $encrypted_password)
                {
                    if($is_disabled != 1)
                    {
                        if($is_locked != 1)
                        {
                            $work_day = $this->workingDays($result[0]);
                            if($work_day['code'] != "44")
                            {
                                if($result[0]['church_id'] != "99")
                                {
                                    $church_details = $this->getItemLabelArr('church_table',array('church_id'),array($result[0]['church_id']),array('church_type','state','church_name'));
                                    $_SESSION['username_sess']   = $result[0]['username'];
                                    $_SESSION['firstname_sess']  = $result[0]['firstname'];
                                    $_SESSION['lastname_sess']   = $result[0]['lastname'];
                                    $_SESSION['sex_sess']        = $result[0]['sex'];
                                    $_SESSION['role_id_sess']    = $result[0]['role_id'];
                                    $_SESSION['church_id_sess']  = $result[0]['church_id'];
                                    $_SESSION['photo_file_sess']  = $result[0]['photo'];
                                    $_SESSION['photo_path_sess']  = "img/profile_photo/".$result[0]['photo'];
                                    $_SESSION['church_type_id_sess'] = $church_details['church_type'];
                                    $_SESSION['state_id_sess'] = $church_details['state'];;
                                    $_SESSION['church_name_sess']= $church_details['church_name'];;
                                    $_SESSION['role_id_name']    = $this->getitemlabel('role','role_id',$result[0]['role_id'],'role_name');
                                    $_SESSION['last_page_load'] = time();


                                    //update pin missed and last_login
                                    $this->resetpinmissed($username);
                                    return json_encode(array("response_code"=>0,"response_message"=>"Login Successful"));
                                }
                                else
                                {
                                    return json_encode(array("response_code"=>779,"response_message"=>"You can't login now... A profile transfer is currently ongoing. Try again at a later time or contact the Administrator"));
                                }

                            }
                            else
                            {
                                return json_encode(array("response_code"=>61,"response_message"=>$work_day['mssg']));
                            }
                        }
                        else
                        {
                            //inform the user that the account has been locked, and to contact admin, user has to provide useful info b4 he is unlocked
                            return json_encode(array("response_code"=>60,"response_message"=>"Your account has been locked, kindly contact the administrator."));
                        }
                    }
                    else
                    {
                        return json_encode(array("response_code"=>610,"response_message"=>"Your user privilege has been revoked. Kindly contact the administrator"));
                    }
                }
                else	
                {
                    $this->updatepinmissed($username);
                    
                    $remaining = (($result[0]['pin_missed']+1) <= 5)?(5-($result[0]['pin_missed']+1)):0;
                    return json_encode(array("response_code"=>90,"response_message"=>"Invalid username or password, ".$remaining." attempt remaining"));
                }
            }
            elseif($result[0]['pin_missed'] == 5)
            {
                $this->updateuserlock($username,'1');
                return json_encode(array("response_code"=>64,"response_message"=>"Your account has been locked, kindly contact the administrator."));
            }
            else
            {
                 return json_encode(array("response_code"=>62,"response_message"=>"Your account has been locked, kindly contact the administrator."));
            }
		}
        else
		{
			return json_encode(array("response_code"=>20,"response_message"=>"Invalid username or password"));
		}
    }
    public function userlist($data)
    {
		$table_name    = "userdata";
		$primary_key   = "username";
		$columner = array(
			array( 'db' => 'username', 'dt' => 0 ),
			array( 'db' => 'username', 'dt' => 1, 'formatter' => function($d,$row){
                $sql    = "SELECT id from log_table WHERE table_id = '$d' ";
                $result = $this->db_query($sql);
                $count = count($result);
                $count_display = ($count > 0)?"":"display:none";

                return "<b>".$d."</b><div> <span style='cursor:pointer;$count_display' class='badge badge-primary' onclick=\"getpage('log_list.php?table_id=$d','page')\"><i class='fa fa-user'></i> This record was updated $count time(s)</span></div>";
                // $sql = "SELECT username FROM log_table WHERE table_id = '$d' LIMIT 1";
                // file_put_contents("trail.txt",$sql);
                // $result = $this->db_query($sql);
                // if (count($result[0]) > 0) {
                //     return $d." | <button onclick=\"getpage('log_list.php?log_table=".$table_name."','page');\" class='btn btn-sm btn-success'>This Record Have Been Changed</button>";
                // }else{
                //     return $d;
                // }
                
            }),
			array( 'db' => 'firstname',  'dt' => 2 ),
			array( 'db' => 'lastname',   'dt' => 3 ),
			array( 'db' => 'mobile_phone',   'dt' => 4 ),
			array( 'db' => 'role_id',   'dt' => 5, 'formatter'=>function($d,$row){
                return  $this->getitemlabel('role','role_id',$d,'role_name');
            }  ),
			array( 'db' => 'email',   'dt' => 6 ),
			array( 'db' => 'pin_missed',   'dt' => 7 ),
			array( 'db' => 'user_disabled',   'dt' => 8, 'formatter'=>function($d,$row){
                return  ($d==1)?'Disabled':'Enabled';
            } ),
            array( 'db' => 'username',   'dt' => 9, 'formatter'=>function($d,$row){
                $locking = ($row['user_disabled']==1)?"Enable User":"Disable User";
                $locking_class = ($row['user_disabled']==1)?"btn-success":"btn-danger";
                if($_SESSION['role_id_sess'] == 001)
                {
                    return  $sack."<button onclick=\"trigUser('".$d."','".$row['user_disabled']."')\" class='btn btn-sm ".$locking_class."'>".$locking."</button><a class='btn btn-sm btn-warning'   onclick=\"getModal('setup/admin.php?op=edit&username=".$d."','modal_div')\"  href=\"javascript:void(0)\" data-toggle=\"modal\" data-target=\"#defaultModalPrimary\" >EDIT THIS USER</a>";
                }
                else if($_SESSION['role_id_sess'] == 003)
                {
                    return  "<button onclick=\"trigUser('".$d."','".$row['user_disabled']."')\" class='btn btn-sm ".$locking_class."'>".$locking."</button>&nbsp;|&nbsp;<a class='btn btn-sm btn-warning'   onclick=\"getModal('setup/confirm_data.php?op=edit&username=".$d."','modal_div')\"  href=\"javascript:void(0)\" data-toggle=\"modal\" data-target=\"#defaultModalPrimary\" >EDIT THIS USER</a>";
                }
                
            } ),
			array( 'db' => 'created',   'dt' => 10 )
			);
		$filter = " AND role_id <> '001' AND role_id <> '$_SESSION[role_id_sess]'";
        $church_users_filter = ($_SESSION[role_id_sess] == '001' || $_SESSION[role_id_sess] == '005')?"":"AND church_id = '$_SESSION[church_id_sess]'";
        $filter = $filter.$church_users_filter;
        // var_dump($data);
        $datatableEngine = new engine();
	
		echo $datatableEngine->generic_table($data,$table_name,$columner,$filter,$primary_key);

    }
    public function generatePwdLink($data)
    {
        
        $username               = $data['username'];
        $sql                    = "SELECT username,email FROM userdata WHERE username = '$username'";
        $rr                     = $this->db_query($sql);
        if(count($rr) > 0)
        {
            if (filter_var($rr[0]['email'], FILTER_VALIDATE_EMAIL))
            {
                $data                   = $username."::".date('Y-m-d h:i:s');
                $desencrypt             = new DESEncryption();
                $key                    = "accessis4life_tlc";
                $cipher_password        = $desencrypt->des($key, $data, 1, 0, null,null);
                $sqltr_cipher_password  = $desencrypt->stringToHex ($cipher_password);
                $link                   = $sqltr_cipher_password;
                $val                    = $this->getitemlabelarr("userdata",array('username'),array($username),array('firstname','lastname','email'));
                $firstname              = $val['firstname'];
                $lastname               = $val['lastname'];
                $email                  = $val['email'];
                $sql                    = "UPDATE userdata SET reset_pwd_link = '$link' WHERE username = '$username' LIMIT 1";
                $this->db_query($sql);
                mail($email,"Password Reset - The Lord's Chosen","Dear ".$lastname.", \n To reset your password kindly follow this link below \n http://accessng.com/tlc/pwd_reset.php?ga=".$link);
                return json_encode(array('response_code'=>0,'response_message'=>'Follow the reset link sent to your email'));
            }else
            {
                return json_encode(array('response_code'=>340,'response_message'=>'Your email address was not setup properly'));
            }
            
        }else
        {
            return json_encode(array('response_code'=>940,'response_message'=>'Username does not exist'));
        }
        
    }
    
    public function verifyLink($link)
    {
        $desencrypt      = new DESEncryption();
        $key             = "accessis4life_tlc";
        $json_value      = $this->DecryptData($key,$link);
        $arr             = explode("::",$json_value);
        $date            = $arr[1];
        $username        = $arr[0];
        $sql = "SELECT reset_pwd_link,firstname,lastname FROM userdata WHERE username = '$username' AND reset_pwd_link = '$link' LIMIT 1";
        $result = $this->db_query($sql);
        if(count($result) > 0)
        {
            $date1  = strtotime($date);  
            $date2  = strtotime(date('Y-m-d h:i:s'));  
            // Formulate the Difference between two dates 
            $diff   = abs($date2 - $date1);
            // To get the year divide the resultant date into 
            // total seconds in a year (365*60*60*24) 
            $years  = floor($diff / (365*60*60*24));   
            $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));  
            $days   = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
            $hours  = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24) / (60*60));
            if($hours > 72)
            {
                return json_encode(array('response_code'=>88,'response_message'=>'This link has expired'));
            }else
            {
                $sql = "UPDATE userdata SET reset_pwd_link = '' WHERE username = '$username' LIMIT 1";
                $this->db_query($sql);
                return json_encode(array('response_code'=>0,'response_message'=>'OK','data'=>array('username'=>$username,'firstname'=>$result[0]['firstname'],'lastname'=>$result[0]['lastname'])));
            }
        }else
        {
            return json_encode(array('response_code'=>848,'response_message'=>'This link has already been used or tampared with'));
        }
    }
    public function register($data)
	{
		// check if record does not exists before then insert
        $data['day_1'] = (isset($data['day_1']))?1:0;
        $data['day_2'] = (isset($data['day_2']))?1:0;
        $data['day_3'] = (isset($data['day_3']))?1:0;
        $data['day_4'] = (isset($data['day_4']))?1:0;
        $data['day_5'] = (isset($data['day_5']))?1:0;
        $data['day_6'] = (isset($data['day_6']))?1:0;
        $data['day_7'] = (isset($data['day_7']))?1:0;
        $data['passchg_logon'] = (isset($data['passchg_logon']))?1:0;
        $data['user_disabled'] = (isset($data['user_disabled']))?1:0;
        $data['user_locked']   = (isset($data['user_locked']))?1:0;
        $data['posted_user']     = $_SESSION['username_sess'];
        
            if($data['operation'] != 'edit')
            {
                $validation = $this->validate($data,
                        array(
                            'firstname'=>'required|min:2',
                            'lastname'=>'required',
                            'mobile_phone'=>'required|int',
                            'sex'=>'required',
                            'role_id'=>'required',
                            'username'=>'required|unique:userdata.username',
                            'password'=>'required|min:6'
                        ),
                        array('firstname'=>'First Name','lastname'=>'Last name','role_id'=>'Role ID','mobile_phone'=>'Phone Number','sex'=>'Gender')
                       );
                if(!$validation['error'])
                {
                    $data['email']       = $data['username'];
                    $data['created']     = date('Y-m-d h:i:s');
                    
                    $desencrypt          = new DESEncryption();
                    $key                 = $data['username'];
                    $cipher_password     = $desencrypt->des($key, $data['password'], 1, 0, null,null);
                    $str_cipher_password = $desencrypt->stringToHex ($cipher_password);
                    $data['password']    = $str_cipher_password;

                    $sql = "SELECT username FROM userdata WHERE role_id = '$data[role_id]' AND church_id = '$data[church_id]' LIMIT 1";
                    $role_cnt = $this->db_query($sql,false);
                    if($role_cnt < 1)
                    {
                        $count = $this->doInsert('userdata',$data,array('op','confirm_password','operation'));
                        if($count == 1)
                        {
              rename('user_passport/'.$temp_pass,'user_passport/'.$data['email'].".".end($array));
                            return json_encode(array("response_code"=>0,"response_message"=>'Record saved successfully'));
                        }
                        else
                        {
                            return json_encode(array("response_code"=>78,"response_message"=>'Failed to save record'));
                        }
                    }else
                    {
                        $role_name = $this->getitemlabel('role','role_id',$data['role_id'],'role_name');
                        $church_name = $this->getitemlabel('church_table','church_id',$data['church_id'],'church_name');
                        return json_encode(array("response_code"=>20,"response_message"=>$role_name." already exist for ".$church_name));
                    }
                    
                }else
                {
                    return json_encode(array("response_code"=>20,"response_message"=>$validation['messages'][0]));
                }
            }
            else
            {
            //                EDIT EXISTING USER 
                $data['modified_date'] = date('Y-m-d h:i:s');
                $validation = $this->validate($data,
                        array(
                            'firstname'=>'required|min:2',
                            'lastname'=>'required',
                            'mobile_phone'=>'required|int',
                            'sex'=>'required',
                            'role_id'=>'required',
                            'username'=>'required|email',
                        ),
                        array('firstname'=>'First Name','lastname'=>'Last name','role_id'=>'Role ID','mobile_phone'=>'Phone Number','sex'=>'Gender')
                       );
                if(!$validation['error'])
                {
                    $current_user_data = $this->getCurrentData('userdata','username',$data['username']);
                    $count = $this->doUpdate('userdata',$data,array('op','operation','password'),array('username'=>$data['username']));
                    
                    $this->logData($current_user_data,$data,['table_name'=>'userdata', 'table_id'=>$data['username'],'table_alias'=>'User Information'],['op','operation','password']);
                    if($count == 1)
                    {
                 
                        return json_encode(array("response_code"=>0,"response_message"=>'Record saved successfully'));
                    } 
                    else
                    {
                        return json_encode(array("response_code"=>78,"response_message"=>'Failed to save record'));
                    }
                }
                else
                {
                    return json_encode(array("response_code"=>20,"response_message"=>$validation['messages'][0]));
                }
            }
        
	}
    public function userEdit($data)
    {
        $data['day_1'] = (isset($data['day_1']))?1:0;
        $data['day_2'] = (isset($data['day_2']))?1:0;
        $data['day_3'] = (isset($data['day_3']))?1:0;
        $data['day_4'] = (isset($data['day_4']))?1:0;
        $data['day_5'] = (isset($data['day_5']))?1:0;
        $data['day_6'] = (isset($data['day_6']))?1:0;
        $data['day_7'] = (isset($data['day_7']))?1:0;
        $data['passchg_logon'] = (isset($data['passchg_logon']))?1:0;
        $data['user_disabled'] = (isset($data['user_disabled']))?1:0;
        $data['user_locked']   = (isset($data['user_locked']))?1:0;
        $data['posted_user']     = $_SESSION['username_sess'];
        $cnt = $this->doUpdate('userdata',$data,array('op','operation'),array('username'=>$data['username']));
        if($cnt == 1)
        {
             return json_encode(array("response_code"=>0,"response_message"=>'Record saved successfully'));
        }else
        {
             return json_encode(array("response_code"=>78,"response_message"=>'Failed to save record'));
        }
    }
    public function updatePastorBank($data)
    {
        $validation = $this->validate($data,
                        array(
                            'bank_name'=>'required',
                            'account_no'=>'required',
                            'account_name'=>'required',
                        ),
                        array('account_name'=>'Account Name','account_no'=>'Account Number','bank_name'=>'Bank Name')
                       );
        if(!$validation['error'])
        {
            $count = $this->doUpdate("userdata",$data,array('op','operation'),array("username"=>$_SESSION['username_sess']));
            if($count > 0)
            {
                return json_encode(array("response_code"=>0,"response_message"=>'Updated personal information.'));
            }else
            {
                return json_encode(array("response_code"=>78,"response_message"=>'Failed to save record'));
            }
        }
        else
        {
            return json_encode(array("response_code"=>20,"response_message"=>$validation['messages'][0]));
        }
    }
    public function profileEdit($data)
    {
        $validate = $this->validate($data,array('username'=>'required|email','firstname'=>'required','lastname'=>'required','mobile_phone'=>'required','sex'=>'required'),array('mobile_phone'=>'Phone Number','firstname'=>'First Name','lastname'=>'Last Name','sex'=>'Gender'));
        if(!$validate['error'])
        {
            $cnt = $this->doUpdate('userdata',$data,array('op','operation'),array('username'=>$data['username']));
            if($cnt == 1)
            {
                 return json_encode(array("response_code"=>0,"response_message"=>'Record saved successfully'));
            }
            else
            {
                 return json_encode(array("response_code"=>78,"response_message"=>'No update was made'));
            }
        }
        else
        {
            return json_encode(array('response_code'=>13,'response_message'=>$validate['messages'][0]));
        }
    }
    public function saveUser($data)
    {
        $role_id = $data['role_id'];
        $data['parish_pastor'] = 1;
        $validation = "";
        
        if($role_id == 003)
        {

            $validation = $this->validate($data,
                array(
                    'church_id'    =>'required',
                    'bank_name'    =>'required',
                    'account_name' =>'required',
                    'account_no'   =>'required'
                ),
                array('account_no'=>'Account Number','account_name'=>'Account Name','bank_name'=>'Bank Name','church_id'=>'church')
                );
            if(!$validation['error'])
            {
                if($data['operation'] == "new")
                {
                    $sql = "SELECT username,firstname,lastname FROM userdata WHERE role_id = '003' AND parish_pastor='1' AND church_id = '$data[church_id]' LIMIT 1 ";
                    $resu = $this->db_query($sql);
                    if(count($resu) > 0)
                    {
                        $church_name = $this->getitemlabel('church_table','church_id',$data[church_id],'church_name');
                        $pastor_name = $resu[0]['firstname']." ".$resu[0]['lastname'];
                        $validation['error'] = true;
                        $validation['messages'][0] = $church_name." already has a parish pastor[".$pastor_name."] there can only be one parish pastor. ";
                    }
                } 
            }
                
            
        }else
        {
            $validation['error'] = false;
        }
        
        if(!$validation['error'])
        {
              return $this->register($data);
        }
        else
        {
            return json_encode(array("response_code"=>20,"response_message"=>$validation['messages'][0]));
        }
        
    }
    public function workingDays($dbrow)
    {
        $days_of_week = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
        $db_day       = array('day_1','day_2','day_3','day_4','day_5','day_6','day_7');
        $ddate        = date('w');
        $mssg         = array('code'=>0,'mssg'=>'');
        foreach($days_of_week as $k => $v)
        {
            if($dbrow[$db_day[$k]] == 0 && $ddate == $k)
            {
                $mssg = array( "mssg"=>"You are not allowed to login on $days_of_week[$k]","code"=>"44");
               
            }
        }
        if($dbrow['passchg_logon'] == '1')
        {
            $mssg = array( "mssg"=>"You are required to change your password, follow this link to  <a href='change_psw_logon.php?username={$dbrow[username]}'> change password </a>","code"=>"44");
        }
        return $mssg;
    }
    public function emailPasswordReset($data)
    {
         $email = $data['email'];
        
        $pass_dateexpire = @date("Y-m-d H:i:s",strtotime($today."+ 24 hours"));
		$upd = $this->db_query("UPDATE userdata SET pwd_expiry='".$pass_dateexpire."' WHERE username = '$email'");
        
       
        $recordBiodata = $this->getItemLabelArr('userdata',array('email'),array($email),'*');

        $fname = $recordBiodata['first_name'];
        $lname = $recordBiodata['last_name'];

        
        return json_encode(array("response_code"=>0,"response_message"=>'Check your mail'));
    }
    
    public function sackUser($data)
    {
        $username = $data['username'];
        $status   = ($data['status'] == 1)?"0":"1";
        $sql      = "UPDATE userdata SET status = '$status' WHERE username = '$username' LIMIT 1";
        $cc = $this->db_query($sql,false);
        if($cc)
        {
            return json_encode(array('response_code'=>0,'response_message'=>'Action on user profile is now effective'));
        }else
        {
            return json_encode(array('response_code'=>432,'response_message'=>'Action failed'));
        }
        
    }
    public function notifyChurchUsers($church_id,array $roles, $msg, $notification_type = "email")
    {
        $usersContact = array();
        if($notification_type == "email")
        {
            foreach($roles as $role_value)
            {
                $sql    = "SELECT email FROM userdata WHERE church_id = '$church_id' AND role_id = '$role_value' ";
                $result = $this->db_query($sql);
//                $usersContact[] = $result[0]['email'];
//                $msg    = "Good Day Sir/Madam,\n The Accountant has just posted a collection, and needs your approval.\n Kindly login to the portal to approve collection";
                mail($result[0]['email'],"The Lord's Chosen Charismatic Revival Church::Approval Notification ",$msg);
            }
        }
        elseif($notification_type == "sms")
        {
            
        }
        
    }
    public function changeUserStatus($data)
    {
        $username = $data['username'];
        $status = ($data['current_status'] == 1)?0:1;
        $sql = "UPDATE userdata SET user_disabled = '$status' WHERE username = '$username'";
        $this->db_query($sql);
        return json_encode(array("response_code"=>0,"response_message"=>"updated successfully"));
    }
    
    public function doForgotPasswordChange($data)
    {
        $validation = $this->validate($data,
                        array(
                            'username'=>'required',
                            'password'=>'required|min:6',
                            'confirm_password'=>'required|matches:password'
                        ),
                        array('current_password'=>'Current Password')
                       );
           
            if(!$validation['error'])
            {
                $username      = $data['username'];
                $user_password = $data['password'];
                $key            = $username;
                $desencrypt             = new DESEncryption();
                $cipher_password = $desencrypt->des($key, $user_password, 1, 0, null,null);
                $str_cipher_password = $desencrypt->stringToHex ($cipher_password);
                $query_data = "UPDATE userdata set password='$str_cipher_password', passchg_logon = '0', user_locked = '0' where username= '$username'";
//                    echo $query_data;
                $result_data = $this->db_query($query_data,false);
                if($result_data > 0)
                {
                    return json_encode(array('response_code'=>0,'response_message'=>'Your password was changed successfully'));
                }
                else
                {
                    return json_encode(array('response_code'=>45,'response_message'=>'Your password was changed successfully'));
                }
            }
        else
        {
            return json_encode(array("response_code"=>20,"response_message"=>$validation['messages'][0]));
        }
    }
    public function doPasswordChange($data)
    {
            $validation = $this->validate($data,
                        array(
                            'username'=>'required',
                            'current_password'=>'required',
                            'password'=>'required|min:6',
                            'confirm_password'=>'required|matches:password'
                        ),
                        array('confirm_password'=>'Confirm password','current_password'=>'Current Password')
                       );
           if($data[current_password] == $data[password])
           {
               $validation['error'] = true;
               $validation['messages'][0] = "Kindly choose a password that is different from your current one.";
           }
            if(!$validation['error'])
            {
                $username      = $data['username'];
                $user_password = $data['password'];
                $user_curr_password = $data['current_password'];
                
                $desencrypt = new DESEncryption();
                $key = $username;
                $cipher_password = $desencrypt->des($key, $user_curr_password, 1, 0, null,null);
                $str_cipher_password = $desencrypt->stringToHex ($cipher_password);
//                $str_cipher_password = $this->EncryptData($username,$user_password);
                  $sql = "SELECT username FROM userdata WHERE username = '$username' AND password = '$str_cipher_password'";
                $rr  = $this->db_query($sql,false);
                if($rr == 1)
                {
                    
                    $cipher_password = $desencrypt->des($key, $user_password, 1, 0, null,null);
                    $str_cipher_password = $desencrypt->stringToHex ($cipher_password);
                    $query_data = "UPDATE userdata set password='$str_cipher_password', passchg_logon = '0' where username= '$username'";
//                    echo $query_data;
                    $result_data = $this->db_query($query_data,false);
                    if($result_data > 0)
                    {
                        if($data['page'] == 'first_login')
                        {
                            return json_encode(array('response_code'=>0,'response_message'=>'Your password was changed successfully... <a href="index.html">Proceed to login</a>'));
                        }
                        else
                        {
                            return json_encode(array('response_code'=>0,'response_message'=>'Your password was changed successfully... logging you out'));
                        }
                        
                    }
                    else
                    {
                        return json_encode(array('response_code'=>45,'response_message'=>'Your password could not be changed'));
                    }
                }else
                {
                    return json_encode(array('response_code'=>455,'response_message'=>'current password is invalid'));
                }

                
            }
        else
        {
            return json_encode(array("response_code"=>20,"response_message"=>$validation['messages'][0]));
        }
	}
    public function passwordHash($secret)
	{
		$hashvalue = password_hash($secret,PASSWORD_DEFAULT);
		return $hashvalue;
//		echo "<br/>".password_verify($secret,'$2y$10$s4N.5vNNy5iniEQ2Pycn.uE.OJJ69p.1eT9W6JOce7j9TAgzjrxJS');
//		var_dump( password_get_info('$2y$10$s4N.5vNNy5iniEQ2Pycn.uE.OJJ69p.1eT9W6JOce7j9TAgzjrxJS') );
	}
	

}