<?php 
require './Stu.php';
$xlsN="./excels/stu.csv";
$msg=null;$smsg=null;

//for old input info display
$isIdOK = 0;
$isNameOK = 0;
$isEmailOK = 0;
$isNoteOK = 0;

if(isset($_POST["id"])){
    $id=$_POST["id"];
	$name=$_POST["name"];
    $email=$_POST["email"];
	
	//for old input info display
	if(!empty($id))
		$isIdOK = 1;
	
	if(!empty($name))
		$isNameOK = 1;

	if(!empty($email))
		$isEmailOK = 1;

	$remark=$_POST["remark"];
	if(!empty($name))
		$isNoteOK = 1;
	
    if(!empty($id)&&is_numeric($id)){
        if((!empty($name))&&(!empty($email)))
		{	
			if(checkEmail($email))
			{			
				$mtx="stu";
				$stu=new Stu();
				$stu->id="'".$id;
				$stu->name="'".$name;
				$stu->remark="'".$_POST["remark"];
				$stu->email="'".$email;
				$res=Stu::insert($stu,$xlsN,null,$mtx);
				if($res)
				{
					$smsg="Your email has been registered. Please check the following table. <br>你的信息已登记，请在下方列表中确认！";
					$isIdOK = 0;
					$isNameOK = 0;
					$isEmailOK = 0;
					$isNoteOK = 0;
				}
				else 
					$msg="Your student ID has been registered. Please check the following table. Please contact Dr. Lin CUI if you need any changes. <br/>该学号已经登记，如需修改信息请联系老师！";
			}
			else
				$msg="Please input your correct Email address! <br>请正确输入你的邮箱！";
		}
		else 
			$msg="Please input your Name and Email! <br>请输入你的姓名和邮箱！";
    }
	else 
		$msg="Please input your correct Stduent ID! <br>请正确输入你的学号！";
}

//Check Email address format
function checkEmail($email)
{
    $pregEmail = "/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i";
    return preg_match($pregEmail,$email);  
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"> 
    <title>Info Registration | 信息登记</title>
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">  
    <script src="./assets/js/jquery.min.js"></script>
    <script src="./assets/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <h1 align="center">Students Email Registration / 学生邮箱信息登记</h1>
	
	<div class='alert alert-info'>Your email address will be used to broadcast notifications of this course. <br>
	Please use Chrome, Firefox, Safari or IE10(and above).<br>
	此处收集的电子邮箱将用于发送本课程的相关通知和信息，请务必认真填写！<br>
	请使用Chrome、Firefox、Safari、IE10及以上版本。</div>
    <?php
    if($msg!=null)echo "<div class='alert alert-danger'>".$msg."</div>";
    if($smsg!=null)echo "<div class='alert alert-success'>".$smsg."</div>"
    ?>
    <form class="" role="form" action="." method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="id">Student ID / 学号*</label>
            <input type="text" class="form-control" name="id" id="id" placeholder="Your Student ID / 请输入学号"  required
			<?php
			if($isIdOK==1)
				echo " value='".$_POST["id"]."'";
			?>
			>
        </div>
        <div class="form-group">
            <label for="name">Student Name / 姓名*</label>
            <input type="text" class="form-control" name="name" id="name" placeholder="Your Name / 请输入姓名"  required
			<?php
			if($isNameOK==1)
				echo " value='".$_POST["name"]."'";
			?>
			>
        </div>
        <div class="form-group">
            <label for="email">Email / 邮箱*</label>
            <input type="text" class="form-control" name="email" id="email" placeholder="Your Email / 请输入邮箱"  required
			<?php
			if($isEmailOK==1)
				echo " value='".$_POST["email"]."'";
			?>
			>
        </div>
        <div class="form-group">
            <label for="remark">Notes / 备注</label>
            <textarea class="form-control" rows="3" name="remark" id="remark" placeholder="Anything else you want me know (This information will be kept confidential). / 其他相关信息（该项信息不会在下方列表中展示）。如果你是学委或班长，请注明你的职务和手机号。"
			><?php 
			if($isNoteOK==1)
				echo $_POST["remark"];
			?></textarea>
        </div>
        <button type="submit" class="btn btn-default">Submit / 提交</button>
    </form>
	<br>
	<br>
	<br>
    <table class="table table-striped">
        <caption style="color:black;font-weight:bold;font-size:150%;">List of Registed Students / 已登记学生列表</caption>
            <thead>
                <tr>
                    <th>Student ID / 学号</th>
                    <th>Name / 姓名</th>
                    <th>Email / 邮箱</th>
                    <!--th>备注</th-->
                </tr>
            </thead>
            <tbody>
                <?php
                $sarr=Stu::listObj($xlsN,"Stu");
                foreach($sarr[1] as $key=>$val){
                    $sarr[1][$key]->id=htmlspecialchars(substr($sarr[1][$key]->id, 1),ENT_QUOTES);
                    $sarr[1][$key]->name=htmlspecialchars(substr($sarr[1][$key]->name, 1),ENT_QUOTES);
                    $sarr[1][$key]->email=htmlspecialchars(substr($sarr[1][$key]->email, 1),ENT_QUOTES);
                    //$sarr[1][$key]->remark=htmlspecialchars(substr($sarr[1][$key]->remark, 1),ENT_QUOTES);
                }
                foreach ($sarr[1] as $stu) {
                    echo "<tr><td>".$stu->id
                    ."</td><td>".$stu->name
                    ."</td><td>".$stu->email
                    //."</td><td>".$stu->remark
                    ."</td></tr>";
                }
                ?>
            </tbody>
    </table>
</div>
    
</body>
</html>
