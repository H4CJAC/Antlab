<?php 
require './StuFile.php';
if((!empty($_POST["sid"]))){
    $allowedExts = array("zip", "rar", "ppt", "word","pdf","xls","xlsx");
    $temp = explode(".", $_FILES["file"]["name"]);
    $extension = array_pop($temp);
    $extension=array_search($extension, $allowedExts);
    if ($extension>-1)
    {
        if ($_FILES["file"]["error"] > 0)
        {
            echo "错误：: " . $_FILES["file"]["error"] . "<br>";
        }
        else
        {
            $sid=$_POST["sid"];
            $fn="stuUploads/" .$sid."_".implode("_", $temp)."_".date("U").".".$allowedExts[$extension];
            move_uploaded_file($_FILES["file"]["tmp_name"], $fn);
            $stuFile=new StuFile();
            //??????????
        }
    }
    else
    {
        echo "非法的文件格式";
    }

}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"> 
    <title>文件上传</title>
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">  
    <script src="./assets/js/jquery.min.js"></script>
    <script src="./assets/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <h1>文件上传</h1>
    <form class="" role="form" action="." method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="id">学号</label>
            <input type="text" class="form-control" name="sid" id="id" placeholder="请输入学号">
        </div>
        <div class="form-group">
            <label for="remark">备注</label>
            <textarea class="form-control" rows="3" name="remark" id="remark" placeholder="请输入备注"></textarea>
        </div>
        <div class="form-group">
            <label for="inputfile">文件输入（仅支持zip、rar、word、ppt、pdf、xls、xlsx）</label>
            <input type="file" id="inputfile" name="file">
        </div>
        <button type="submit" class="btn btn-default">提交</button>
        <div style="margin-top: 20px;" class="progress progress-striped active">
            <div class="progress-bar progress-bar-success" role="progressbar"
                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                 style="width: 20%;">
            </div>
        </div>
    </form>
</div>
    
</body>
</html>