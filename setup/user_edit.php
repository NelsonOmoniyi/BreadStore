<?php
include_once("../libs/dbfunctions.php");
$dbobject = new dbobject();
$sql = "SELECT DISTINCT(State) as state FROM lga order by State";
$states = $dbobject->db_query($sql);

$sql2 = "SELECT bank_code,bank_name FROM banks WHERE bank_type = 'commercial' order by bank_name";
$banks = $dbobject->db_query($sql2);

$sql_pastor = "SELECT username,firstname,lastname FROM userdata WHERE role_id = '003'";
$pastors = $dbobject->db_query($sql_pastor);

$sql_ch_type = "SELECT id,name FROM church_type";
$church_type = $dbobject->db_query($sql_ch_type);

$sql_church = "SELECT * FROM church_table";
    $churches = $dbobject->db_query($sql_church);
$user_role = $_SESSION['role_id_sess'];
$sql_role = "SELECT * FROM role WHERE role_id <> '001' AND role_id <> '$user_role'";
    $roles = $dbobject->db_query($sql_role);

if(isset($_REQUEST['op']) && $_REQUEST['op'] == 'edit')
{
    $username  = $_REQUEST['username'];
    $user      = $dbobject->db_query("SELECT * FROM userdata WHERE username='$username' LIMIT 1");
    $operation = 'edit';
}
else
{
    $operation = 'new';
}
?>
 <link rel="stylesheet" href="codebase/dhtmlxcalendar.css" />
<script src="codebase/dhtmlxcalendar.js"></script>
<script>
    doOnLoad();
    var myCalendar;
function doOnLoad()
{
   myCalendar = new dhtmlXCalendarObject(["start_date"]);
   myCalendar.hideTime();
}
</script>
<style>
    #login_days>label{
        margin-right: 10px;
    }
</style>
<div class="modal-header">
    <h4 class="modal-title" style="font-weight:bold">User Edit</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>
<div class="modal-body m-3 ">
    <form id="form1" onsubmit="return false">
       <input type="hidden" name="op" value="Users.userEdit">
       <input type="hidden" name="operation" value="<?php echo $operation; ?>">
       <input type="hidden" name="username" value="<?php echo $username; ?>">
         <h3 style="text-align:center"><?php echo $user[0]['firstname']." ".$user[0]['lastname'] ?></h3>
        
        
        <div class="row">
            <div class="col-sm-12">
                <label for=""><b>Login Days</b></label>
            </div>
        </div>
        <div class="row">
           <div class="col-sm-12">
               <div class="form-group" id="login_days">
                    <label class="form-label" id="day1"><input type="checkbox" value="<?php echo isset($user[0]['day_1'])?$user[0]['day_1']:'1'; ?>" <?php echo (isset($user[0]['day_1']))?($user[0]['day_1'] == 0)?"":"checked":"checked"; ?> name="day_1"> Sunday</label>
                    <label class="form-label" id="day2"><input type="checkbox" value="<?php echo isset($user[0]['day_2'])?$user[0]['day_2']:'1'; ?>" <?php echo (isset($user[0]['day_2']))?($user[0]['day_2'] == 0)?"":"checked":"checked"; ?>  name="day_2"> Monday</label>
                    <label class="form-label" id="day3"><input type="checkbox" value="<?php echo isset($user[0]['day_3'])?$user[0]['day_3']:'1'; ?>" <?php echo (isset($user[0]['day_3']))?($user[0]['day_3'] == 0)?"":"checked":"checked"; ?> name="day_3"> Tuesday</label>
                    <label class="form-label" id="day4"><input type="checkbox" value="<?php echo isset($user[0]['day_4'])?$user[0]['day_4']:'1'; ?>" <?php echo (isset($user[0]['day_4']))?($user[0]['day_4'] == 0)?"":"checked":"checked"; ?> name="day_4"> Wednesday</label>
                    <label class="form-label" id="day5"><input type="checkbox" value="<?php echo isset($user[0]['day_5'])?$user[0]['day_5']:'1'; ?>" <?php echo (isset($user[0]['day_5']))?($user[0]['day_5'] == 0)?"":"checked":"checked"; ?> name="day_5"> Thursday</label>
                    <label class="form-label" id="day6"><input type="checkbox" value="<?php echo isset($user[0]['day_6'])?$user[0]['day_6']:'1'; ?>" <?php echo (isset($user[0]['day_6']))?($user[0]['day_6'] == 0)?"":"checked":"checked"; ?> name="day_6"> Friday</label>
                    <label class="form-label" id="day7"><input type="checkbox" value="<?php echo isset($user[0]['day_7'])?$user[0]['day_7']:'1'; ?>" <?php echo (isset($user[0]['day_7']))?($user[0]['day_7'] == 0)?"":"checked":"checked"; ?> name="day_7"> Saturday</label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <label for=""><b>Security Settings</b></label>
            </div>
        </div>
        <div class="row">
           <div class="col-sm-12">
               <div class="form-group" >
                    <label class="form-label" id="day1"><input type="checkbox" value="<?php echo isset($user[0]['user_locked'])?$user[0]['user_locked']:'1'; ?>" <?php echo (isset($user[0]['user_locked']))?($user[0]['user_locked'] == 0)?"":"checked":"checked"; ?> name="user_locked" id="day1"> Lock User</label>
                    <label class="form-label" id="day1"><input type="checkbox" value="<?php echo isset($user[0]['passchg_logon'])?$user[0]['passchg_logon']:'1'; ?>" name="passchg_logon" <?php echo (isset($user[0]['passchg_logon']))?($user[0]['passchg_logon'] == 0)?"":"checked":"checked"; ?> id="passchg_logon"> Change password on first login</label>
                    
                   
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
</div>
<script>
    function saveRecord()
    {
        $("#save_facility").text("Loading......");
        var dd = $("#form1").serialize();
        $.post("utilities.php",dd,function(re)
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
    if($("#sh_display").is(':checked'))
        {
            
        }
    function show_bank_details(val)
    {
        if(val == 003)
            {
                $("#parish_pastor_div").show();
            }
        else{
            $("#parish_pastor_div").hide();
        }
    }
    function fetchLga(el)
    {
        $("#lga-fd").html("<option>Loading Lga</option>");
        $.post("utilities.php",{op:'Church.getLga',state:el},function(re){
            $("#lga-fd").empty();
            $("#lga-fd").html(re);
            
        });
    }
    
    $("#show").click(function()
    {
        var password = $("#password").attr('type');
        if(password=="password")
            {
                $("#password").attr('type','text');
                $("#show").text("Hide");
            }else{
                $("#password").attr('type','password');
                $("#show").text("Show");
            }
    });
    function check_bank_det(el)
    {
        if($("#yes").is(':checked')){
            $("#bank_details").slideDown()
        }else if($("#no").is(':checked'))
         {
            $("#bank_details").slideUp()
         }
    }
    function fetchAccName(acc_no)
    {
        if(acc_no.length == 10)
            {
                var account  = acc_no;
                var bnk_code = $("#bank_name").val();
                $("#acc_name").text("Verifying account number....");
                $("#account_name").val("");
                $.post("utilities.php",{op:"Church.getAccountName",account_no:account,bank_code:bnk_code},function(res){
                    
                    $("#acc_name").text(res);
                    $("#account_name").val(res);
                });
            }else{
                $("#acc_name").text("Account Number must be 10 digits");
            }
        
    }
</script>