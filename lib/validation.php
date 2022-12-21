<?php
//start session
session_start();
 
include_once('../class/users.php');
 
$user = new User();
 
// var_dump($_POST);
// exit;
if(isset($_POST['login'])){
	$username = $user->escape_string($_POST['username']);
	$password = $user->escape_string($_POST['password']);
 
	$auth = $user->check_login($username, $password);
//  var_dump($auth);
// exit;
	if(!$auth){
		$_SESSION['message'] = 'Invalid Username or Password';
    	// header('location:login.php');
		header("Location: ../setup/login.php");
	}
	else{
		$_SESSION['user'] = $auth;
		header("Location: ../home.php");
	}
}
else{
	$_SESSION['message'] = 'You need to login first';
	header("Location: ../setup/login.php");
}
?>