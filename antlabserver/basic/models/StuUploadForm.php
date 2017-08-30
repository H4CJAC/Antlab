<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class StuUploadForm extends Model{

    public $upFiles;
    public $sid;

    public function rules(){
        return [
            [['upFiles'],'file','skipOnEmpty'=>true,'extensions'=>['word','pdf','ppt','zip','rar','xls','xlsx'],'maxFiles'=>2]
        ];
    }

    public function upload(){
        $res=[];
        if ($this->validate()) {
            $file=$this->upFiles[0];
            $res[0]='/uploads/'.$this->sid."_".$file->baseName."_".date("U").'.'.$file->extension;
            $file->saveAs(dirname(__DIR__).$res[0]);
            return $res;
        }else{
            return $res;
        }
    }

}

?>