
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
        <form id="form1" onsubmit="return false">
        <input type="hidden" name="op" value="Users.saveUser">
        <div class="row" >
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="form-label">Email<span class="asterik">*</span></label><br>
                    <input type="text" name="username" placeholder="">
                </div>
            </div>
            <br>
            <div class="col-sm-6">
                <div class="form-group ">
                    <label class="form-label" style="display:block !important">Password<span class="asterik">*</span></label>
                    <div class="input-group">
                        <input type="password" autocomplete="off" name="password" id="password" />
                        <div class="input-group-append" style="cursor:pointer;">
                            <button id="show" >Show</button>
                        </div>
                    </div>
                    <br>
                </div>
            </div>
        </div>
            <div class="row">
                <div class="col-sm-12">
                    <div id="server_mssg"></div>
                </div>
            </div>
            <button id="save_facility" onclick="saveRecord()" class="btn btn-primary">Submit</button>
        </form>

<script src="../js/jquery-3.2.1.min.js"></script>
<script src="../js/popper.min.js"></script>
<script src="../js/jquery.superslides.min.js"></script>
<script src="../js/form-validator.min.js"></script>
<script src="../js/contact-form-script.js"></script>
        <script>
            function saveRecord()
            {
                window.alert("I am Here");
                $("#save_facility").text("Loading......");
                var dd = $("#form1").serialize();
                $.post("../utilities.php",dd,function(re)
                {
                    console.log(re);
                    $("#save_facility").text("Save");
                    if(re.response_code == 0)
                        {
                            $("#server_mssg").text(re.response_message);
                            $("#server_mssg").css({'color':'green','font-weight':'bold'});
                            getpage('user_list.php','page');
                            setTimeout(()=>{
                                $('#defaultModalPrimary').modal('hide');
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
        
        </script>
    </body>
</html>  

