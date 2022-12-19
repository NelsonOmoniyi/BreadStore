<?php
session_start();


include_once("lib/dbfunctions.php");
// Include all classes in the classes folder

foreach (glob("class/*.php") as $filename) {
	include_once($filename);
}
// ini_set('display_errors',1);
// error_reporting(E_ALL);
// error_reporting(E_ALL);
//$bd = new dbobject();
//$dds = $bd->DecryptData($_SESSION['crypto_key_sess'],$_REQUEST['op']);
//$token_class = explode(".", $dds);
//    if(!class_exists($token_class[0]))
//    {
//        echo json_encode(array('response_code'=>789,'response_message'=>'Token has been tampered with'));
//        exit();
//    }


$op = $_REQUEST['op'];
//user.register
//$op =  $dbobject->DecryptData("pacific",$op);
$operation  = array();
$operation = explode(".", $op);


// getting data for the class method
$params = array();
if(count($_FILES) > 0)
{
    $_REQUEST['_files'] = $_FILES;
}


$params = $_REQUEST;
$data = [$params];


//////////////////////////////
/// callling the method of  the class
$foo = new $operation[0];
echo call_user_func_array(array($foo, trim($operation[1])), $data);
//}else
//{
//	echo "invalid token";
//}