<?php

namespace app\components;

use Yii;
use yii\base\ActionFilter;
use app\models\Manager;
use app\models\DTO;

class MyPicFilter extends ActionFilter
{
    public function beforeAction($action)
    {
        $req=Yii::$app->request;
        $token=$req->get('at_token');
        if($token==null||$token==''){
            return false;
        }
        $manager=Manager::find()->where(['token'=>$token])->one();
        

        return ($manager!=null)&&$this->veriPriv($action,$manager->pvlg)&&parent::beforeAction($action);
    }

    private function veriPriv($action,$pvlg){
        switch ($action->id) {
            case 'man-upload':
            case 'man-del':
            case 'man-m-del':
            case 'man-del-w-u':
                if($pvlg>0)return true;
                break;
            default:
                if($pvlg>-1)return true;
                break;
        }
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();
        $msg->code=-10;
        $msg->info="权限不足！";
        $response->data=['msg'=>$msg];
        return false;
    }

    public function afterAction($action, $result)
    {
        return parent::afterAction($action, $result);
    }
}

?>