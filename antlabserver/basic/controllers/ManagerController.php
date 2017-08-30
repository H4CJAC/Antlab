<?php

namespace app\controllers;

use Yii;
use app\components\MyFilter;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\DTO;
use app\models\Manager;

class ManagerController extends Controller
{

    public $layout=false;
    public $enableCsrfValidation=false;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => MyFilter::className(),
                'except'=>['login']
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Login
     * @param $password
     * @param $username
     * @return ['msg'=>[]]
     */
    public function actionLogin(){
        $req=Yii::$app->request;
        $password=$req->getBodyParam('password');
        $username=$req->getBodyParam('username');
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $manager=Manager::find()->where(['username'=>$username,'password'=>md5($password)])->one();
        $msg=new DTO();
        if($manager!=null&&$manager->id>0){
            $td=date("Y-m-d h:i:sa");
            $manager->token=md5(Yii::$app->params['atk'].$manager->id.$manager->nickname.$td);
            $manager->save();
            $msg->data=['at_token'=>$manager->token,'name'=>$manager->nickname];

        }else{
            $msg->code=-1;
            $msg->info="用户名密码错误！";
        }
        return ['msg'=>$msg];
    }

    /**
     * Logout
     * @param $id
     * @param $name
     * @param $at_token
     * @return ['msg'=>[]]
     */
    public function actionLogout($at_token){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $manager=Manager::find()->where(['token'=>$at_token])->one();
        $manager->token="";
        $manager->save();
        $msg=new DTO();
        return ['msg'=>$msg];
    }



}
