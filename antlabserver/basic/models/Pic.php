<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
* 
*/
class Pic extends ActiveRecord
{

	public static function tableName(){
		return 'pics';
	}
}

?>