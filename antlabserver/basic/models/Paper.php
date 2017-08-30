<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
* 
*/
class Paper extends ActiveRecord
{

    public static function tableName(){
        return 'papers';
    }
}

?>