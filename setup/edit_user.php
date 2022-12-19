<?php
include_once("../libs/dbfunctions.php");
$dbobject = new dbobject();
$sql = "SELECT DISTINCT(State) as state,stateid FROM lga order by State";
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
if($user_role == 001)
{
    $sql_role = "SELECT * FROM role WHERE role_id IN ('003','005','006','007') ";
}elseif($user_role == 005)
{
    $sql_role = "SELECT * FROM role WHERE role_id = '003' ";
}else
{
    $sql_role = "SELECT * FROM role WHERE role_id <> '001' AND role_id <> '$user_role' AND role_id NOT IN ('003','005','006','007')";
}

    $roles = $dbobject->db_query($sql_role);

if(isset($_REQUEST['op']) && $_REQUEST['op'] == 'edit')
{
    $username  = $_REQUEST['username'];
    $user      = $dbobject->db_query("SELECT * FROM userdata WHERE username='$username'");
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
    .asterik
    {
        color:red;
    }
</style>
<div class="modal-header">
    <h4 class="modal-title" style="font-weight:bold"><?php echo ($operation=="edit")?"Edit ":""; ?>User Setup<div><small style="font-size:12px">All asterik fields are compulsory</small></div></h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>
<div class="modal-body m-3 ">
    <form id="form1" onsubmit="return false">
       <input type="hidden" name="op" value="Users.saveUser">
       <input type="hidden" name="operation" value="<?php echo $operation; ?>">
       <input type="hidden" name="account_name" id="account_name" value="<?php echo $user[0]['account_name']; ?>">
       <div class="row" style="<?php echo ($operation == "edit")?"display:none":""; ?>">
           <div class="col-sm-6">
               <div class="form-group">
                    <label class="form-label">Email<span class="asterik">*</span></label><small style="float:right;color:red">This will be used to login</small>
                    <input type="text" name="username" <?php echo ($operation == "edit")?"disabled":""; ?> class="form-control" value="<?php echo $username; ?>" placeholder="">
                    
                </div>
           </div>
           <div class="col-sm-6">
               <div class="form-group ">
                    <label class="form-label" style="display:block !important">Password<span class="asterik">*</span></label>
                    <div class="input-group">
                        <input type="password" <?php echo ($operation == "edit")?"disabled":""; ?>  autocomplete="off" name="password" value="<?php echo $church[0]['date_of_inception']; ?>" id="password" class="form-control" />
                        <div class="input-group-append" style="cursor:pointer; <?php echo ($operation == "edit")?"display:none":""; ?>">
                            <span class="input-group-text" id="show">Show</span>
                        </div>
                    </div>
                    
                </div>
           </div>
       </div>
            <?php
            if($operation == "edit")
            {
            ?>
                <input type="hidden" name="username" class="form-control" value="<?php echo $username; ?>" placeholder="">
            <?php
            }
           ?>
         <div class="row">
           <div class="col-sm-6">
               <div class="form-group">
                    <label class="form-label">First Name<span class="asterik">*</span></label>
                    <input type="text" name="firstname" value="<?php echo $user[0]['firstname'] ?>" class="form-control">
                </div>
           </div>
           <div class="col-sm-6">
               <div class="form-group">
                    <label class="form-label">Last Name<span class="asterik">*</span></label>
                    <input type="text" name="lastname" value="<?php echo $user[0]['lastname'] ?>" class="form-control">
                </div>
           </div>
        </div>
        <div class="row">
           <div class="col-sm-6">
               <div class="form-group">
                    <label class="form-label">Phone Number<span class="asterik">*</span></label>
                    <input type="number" name="mobile_phone" value="<?php echo $user[0]['mobile_phone'] ?>" class="form-control">
                </div>
           </div>
           <div class="col-sm-6">
               <div class="form-group">
                    <label class="form-label">Gender<span class="asterik">*</span></label>
                    <select class="form-control" name="sex" id="sex">
                        <option value="male" <?php echo ($user[0]['sex'] == "male")?"selected":""; ?>>Male</option>
                        <option value="female" <?php echo ($user[0]['sex'] == "female")?"selected":""; ?> >Female</option>
                    </select>
                </div>
           </div>
        </div>
        
        <div class="row">
            
            <div class="col-sm-6">
               <div class="form-group">
                    <label class="form-label">Role<span class="asterik">*</span></label>
                    <select onchange="show_bank_details(this.value)" class="form-control" name="role_id" id="role_id">
                       
                        <?php
                        if($operation == "new")
                        {
                            echo '<option value="">::SELECT USER ROLE::</option>';
                            foreach($roles as $row)
                            {
                                $selected = ($user[0]['role_id'] == $row['role_id'])?"selected":"";
                                echo "<option $selected value='".$row[role_id]."'>".$row['role_name']."</option>";
                            }
                        }else
                        {
                            echo "<option value='".$user[0]['role_id']."'>".$dbobject->getitemlabel('role','role_id',$user[0]['role_id'],'role_name')."</option>";
                        }
                        
                        ?>
                    </select>
                </div>
           </div>
        </div>
        <div id="parish_pastor_div" style="<?php echo ($user[0]['role_id']==003)?"display:block":"display:none"; ?>">
<!--
            <div class="form-group" >
                <label for="">Is this a parish pastor?</label>
                <label for="yes"><input onclick="check_bank_det(this.val)" value="1" name="parish_pastor" checked id="yes" type="radio">Yes</label>
                <label for="no"><input onclick="check_bank_det(this.val)" value="0"  name="parish_pastor" id="no" type="radio">No</label>
            </div>
-->
            <div class="row" id="bank_details" style="<?php ($operation=="edit")?($user[0]['parish_pastor']==1)?"display:block":"display:none":"display:block"; ?>">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="form-label">Bank Name</label>
                        <select name="bank_name" id="bank_name" class="form-control">
                            <option value="">::SELECT A BANK::</option>
                            <?php
                            foreach($banks as $row)
                            {
                                $selected = ($user[0]['bank_name'] == $row['bank_code'])?"selected":"";
                                echo "<option ".$selected." value='".$row['bank_code']."'>".$row['bank_name']."</option>";
                            }
                        ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="form-label">Account Number</label>
                        <input type="number" onkeyup="fetchAccName(this.value)"  name="account_no" value="<?php echo $user[0]['account_no'] ?>" class="form-control" placeholder="">
                        <small id="acc_name"><?php echo $user[0]['account_name']; ?></small>
                    </div>
                </div>
            </div>
        </div>
        
        
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
                    <label class="form-label" id="day1"><input type="checkbox" value="<?php echo isset($user[0]['user_locked'])?$user[0]['user_locked']:'0'; ?>" <?php echo (isset($user[0]['user_locked']))?($user[0]['user_locked'] == 0)?"":"checked":""; ?> name="user_locked" id="day1"> Lock User</label>
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
//            $("#lga-fd").empty();
            console.log(re);
            $("#lga-fd").html(re.state);
            $("#church_id").html(re.church);
            
        },'json');
    }
    function getUniqueChurch(el)
    {
        $("#church_id").html("<option>Loading Church</option>");
        var ste = $("#church_state").val();
        $.post("utilities.php",{op:'Church.churchByState',state:ste,lga:el},function(re){
//            $("#lga-fd").empty();
            console.log(re);
            $("#church_id").html(re);
            
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