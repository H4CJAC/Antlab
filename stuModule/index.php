<?php 
require './Stu.php';
$xlsN="./excels/stu.csv";
$msg=null;$smsg=null;
if(isset($_POST["id"])){
    $id=$_POST["id"];$name=$_POST["name"];
    if(!empty($id)&&is_numeric($id)){
        if(!empty($name)){
            $mtx="stu";
            $stu=new Stu();
            $stu->id="'".$id;
            $stu->name=$name;
            $stu->email=$_POST["email"];
            Stu::insert($stu,$xlsN,null,$mtx);
            $smsg="登记成功";
        }else $msg="姓名不能为空";
    }else $msg="非法学号";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"> 
    <title>信息登记</title>
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">  
    <script src="./assets/js/jquery.min.js"></script>
    <script src="./assets/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <h1>信息登记</h1>
    <?php
    if($msg!=null)echo "<div class='alert alert-danger'>".$msg."</div>";
    if($smsg!=null)echo "<div class='alert alert-success'>".$smsg."</div>"
    ?>
    <form class="" role="form" action="." method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="id">学号*</label>
            <input type="text" class="form-control" name="id" id="id" placeholder="请输入学号">
        </div>
        <div class="form-group">
            <label for="name">姓名*</label>
            <input type="text" class="form-control" name="name" id="name" placeholder="请输入姓名">
        </div>
        <div class="form-group">
            <label for="email">邮箱</label>
            <input type="text" class="form-control" name="email" id="email" placeholder="请输入邮箱">
        </div>
        <button type="submit" class="btn btn-default">提交</button>
    </form>
    <table class="table table-striped">
        <caption>已登记学生</caption>
            <thead>
                <tr>
                    <th>学号</th>
                    <th>姓名</th>
                    <th>邮箱</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sarr=Stu::listObj($xlsN,"Stu");
                foreach($sarr[1] as $key=>$val){
                    $sarr[1][$key]->id=substr($sarr[1][$key]->id, 1);
                }
                foreach ($sarr[1] as $stu) {
                    echo "<tr><td>".$stu->id
                    ."</td><td>".$stu->name
                    ."</td><td>".$stu->email
                    ."</td></tr>";
                }
                ?>
            </tbody>
    </table>
</div>
    
</body>
</html>