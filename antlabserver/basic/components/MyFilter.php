<?php

namespace app\components;

use Yii;
use yii\base\ActionFilter;
use app\models\Manager;
use app\controllers\SiteController;
use app\models\DTO;

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

        return ($manager!=null)&&$this->veriPriv($action,$manager->pvlg)&&parent::beforeAction($action);
    }

    private function veriPriv($action,$pvlg){
        if($pvlg&1)return true;
        switch ($action->controller->id) {
            case 'activity':
                if($pvlg&2)return true;
                break;
            case 'anno':
                if($pvlg&4)return true;
                break;
            case 'research':
                if($pvlg&8)return true;
                break;
            case 'paper':
                if($pvlg&16)return true;
                break;
            case 'memtype':
                if($pvlg&32)return true;
                break;
            case 'member':
                if($pvlg&64)return true;
                break;
            case 'intro':
                if($pvlg&128)return true;
                break;
            default:
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