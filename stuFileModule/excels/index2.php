<?php 
require './StuFile.php';
$xlsN="./excels/stuFile.csv";
$msg=null;$smsg=null;
if(isset($_POST["sid"])){
    $sid=$_POST["sid"];
    if(!empty($sid)&&is_numeric($sid)){
        $allowedExts = array("zip", "rar", "ppt", "word","pdf","xls","xlsx");
        $temp = explode(".", $_FILES["file"]["name"]);
        $extension = array_pop($temp);
        $extension=array_search($extension, $allowedExts);
        if ($extension>-1)
        {
            if ($_FILES["file"]["error"] > 0)
            {
                $msg="错误：: " . $_FILES["file"]["error"] . "<br>";
            }
            else
            {
                $fn=$sid."_".implode("_", $temp)."_".date("U").".".$allowedExts[$extension];
                if(!file_exists("stuUploads"))mkdir("stuUploads");
                if(move_uploaded_file($_FILES["file"]["tmp_name"], "./stuUploads/".$fn)){
                    $stuFile=new StuFile();
                    $stuFile->id="'".$sid;
                    $stuFile->remark="'".$_POST["remark"];
                    $stuFile->fpath=$fn;
                    $mtx="stuFile";
                    $ofp=StuFile::insert($stuFile,$xlsN,function($xlsS,$rtw){
                            $cidx=0;$type=new StuFile();
                            foreach($type as $k=>$v){
                                if($k=="fpath")break;
                                $cidx++;
                            }
                            return $xlsS->getCell(chr(ord('A')+$cidx).$rtw)->getValue();
                        },$mtx);
                    if($ofp!=null&&file_exists('./stuUploads/'.$ofp)){
                        unlink('./stuUploads/'.$ofp);
                    }
                }
                $smsg="上传成功";
            }
        }
        else
        {
            $msg="非法文件格式";
        }
    }else{
        $msg="非法学号";
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
    <?php
    if($msg!=null)echo "<div class='alert alert-danger'>".$msg."</div>";
    if($smsg!=null)echo "<div class='alert alert-success'>".$smsg."</div>"
    ?>
    <form class="" role="form" action="." method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="id">学号*</label>
            <input type="text" class="form-control" name="sid" id="id" placeholder="请输入学号">
        </div>
        <div class="form-group">
            <label for="remark">备注</label>
            <textarea class="form-control" rows="3" name="remark" id="remark" placeholder="请输入备注"></textarea>
        </div>
        <div class="form-group">
            <label for="inputfile">文件输入*（仅支持zip、rar、word、ppt、pdf、xls、xlsx）</label>
            <input type="file" id="inputfile" name="file">
        </div>
        <button type="submit" class="btn btn-default">提交</button>
    </form>
    <table class="table table-striped">
        <caption>已上传文件</caption>
            <thead>
                <tr>
                    <th>学号</th>
                    <th>备注</th>
                    <th>文件名</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $farr=StuFile::listObj($xlsN,"StuFile");
                foreach($farr[1] as $key=>$val){
                    $farr[1][$key]->id=htmlspecialchars(substr($farr[1][$key]->id, 1),ENT_QUOTES);
                    $farr[1][$key]->remark=htmlspecialchars(substr($farr[1][$key]->remark, 1),ENT_QUOTES);
                }
                foreach ($farr[1] as $sf) {
                    echo "<tr><td>".$sf->id
                    ."</td><td>".$sf->remark
                    ."</td><td>".$sf->fpath
                    ."</td></tr>";
                }
                ?>
            </tbody>
    </table>
</div>
    
</body>
</html>