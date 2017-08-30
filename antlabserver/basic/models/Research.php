<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
* 
*/
class Research extends ActiveRecord
{

	public static function tableName(){
		return 'researches';
	}
}

?>