<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
* 
*/
class Announcement extends ActiveRecord
{

	public static function tableName(){
		return 'announcements';
	}
}

?>