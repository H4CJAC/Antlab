<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
* 
*/
class Memtype extends ActiveRecord
{

	public static function tableName(){
		return 'memtypes';
	}
}

?>