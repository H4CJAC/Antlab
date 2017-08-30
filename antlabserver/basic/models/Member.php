<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
* 
*/
class Member extends ActiveRecord
{

	public static function tableName(){
		return 'members';
	}
}

?>