<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
* 
*/
class Activity extends ActiveRecord
{

	public static function tableName(){
		return 'activities';
	}
}

?>