
<?php
	//start session
	session_start();
 
	//redirect if logged in
	if(isset($_SESSION['user'])){
		header('location: logout.php');
	}
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]>      <html class="no-js"> <!--<![endif]-->
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Register Now</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="">
    </head>
    <body>
        <div class="text-center">
            <img src="../images/logo.png" alt="Bread Store" class="img-fluid rounded-circle" min-width="132" min-height="132" />
            <h4><strong><b>Exclusive Bread Store</b></strong></h4>
        </div>
        <form method="POST" action="../lib/validation.php">
            <fieldset>
                <div class="form-group">
                    <input class="form-control" placeholder="Username" type="text" name="username" autofocus required>
                </div>
                <div class="form-group">
                    <input class="form-control" placeholder="Password" type="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-lg btn-primary btn-block"><span class="glyphicon glyphicon-log-in"></span> Login</button>
            </fieldset>
        </form>

        <?php
            if(isset($_SESSION['message'])){
                ?>
                    <div class="alert alert-info text-center">
                        <?php echo $_SESSION['message']; ?>
                    </div>
                <?php

                unset($_SESSION['message']);
            }
        ?>
<!-- <script src="../js/jquery-3.2.1.min.js"></script>
<script src="../js/popper.min.js"></script>
<script src="../js/jquery.superslides.min.js"></script>
<script src="../js/form-validator.min.js"></script>
<script src="../js/contact-form-script.js"></script>
        <script>
            function saveRecord()
            {
                // window.alert("I am Here");
                $("#save_facility").text("Loading......");
                var dd = $("#form1").serialize();
                console.log(dd);
                $.post("./utilities.php",dd,function(re)
                {
                    console.log(re);
                    $("#save_facility").text("Save");
                    if(re.response_code == 0)
                        {
                            $("#server_mssg").text(re.response_message);
                            $("#server_mssg").css({'color':'green','font-weight':'bold'});
                            
                            setTimeout(()=>{
                                
                            },1000)
                        }
                    else
                        {
                            $("#server_mssg").text(re.response_message);
                            $("#server_mssg").css({'color':'red','font-weight':'bold'});
                        }
                        
                },'json');
            }
        
            $("#show").click(function()
            {
                // window.alert("I am Here");
                var password = $("#password").attr('type');
                if(password=="password")
                {
                    // window.alert("I am Here");
                    $("#password").attr('type','text');
                    $("#show").text("Hide");
                }else{
                    // window.alert("I am Here");
                    $("#password").attr('type','password');
                    $("#show").text("Show");
                }
            });
        
        </script> -->
    </body>
</html>  

