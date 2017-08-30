<?php

namespace app\components;

use Yii;
use yii\base\ActionFilter;
use app\models\Manager;

class MyFilter extends ActionFilter
{
    public function beforeAction($action)
    {
    	$req=Yii::$app->request;
    	$token=$req->get('at_token');
        if($token==null||$token==''){
            return false;
        }
        $manager=Manager::find()->where(['token'=>$token])->one();
        return ($manager!=null)&&parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        return parent::afterAction($action, $result);
    }
}

?>