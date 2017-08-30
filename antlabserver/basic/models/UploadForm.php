<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model{

	public $imageFiles;

	public function rules(){
		return [
			[['imageFiles'],'file','skipOnEmpty'=>false,'extensions'=>['png','jpg'],'maxFiles'=>4]
		];
	}

	public function upload(){
		$res=[];
		if ($this->validate()) {
			$i=0;
			foreach ($this->imageFiles as $file) {
				$res[$i]='/uploads/'.MD5($i.date("U").$file->baseName).'.'.$file->extension;
				$file->saveAs(dirname(__DIR__).$res[$i++]);
			}
			return $res;
		}else{
			return $res;
		}
	}

}

?>