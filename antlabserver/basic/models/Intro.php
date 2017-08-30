<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
* 
*/
class Intro extends ActiveRecord
{

	public static function tableName(){
		return 'intro';
	}
}

?>