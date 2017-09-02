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
                'except'=>['login','logout']
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
        $msg=new DTO();
        if($manager!=null){
            $manager->token="";
            $manager->save();
        }
        return ['msg'=>$msg];
    }

/**
     * ManAdd 添加管理员
     * @param $at_token
     * @param manager
     * @return ['msg'=>[]]
     */
    public function actionManAdd($at_token){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $req=Yii::$app->request;
        $body=$req->bodyParams;
        if ((!empty($body['username']))&&(!empty($body['password']))) {
            $manager=new Manager();
            foreach ($body as $key => $value) {
                $manager[$key]=$value;
            }
            $manager->password=md5($manager->password);
            $manager->id=null;
            $manager->token="";
            try {
                if (!$manager->insert()) {
                    $msg->code=-1;
                    $msg->info="fail";
                }else{
                    $msg->data=$manager;
                }
            } catch (yii\db\IntegrityException $e) {
                $msg->code=-5;
                $msg->info="用户名已存在，请尝试别的用户名。";
            }
        }else{
            $msg->code=-2;
            $msg->info="用户名、密码不能为空！";
        }
        return ['msg'=>$msg];
    }

    /**
     * ManUpd 修改管理员
     * @param $at_token
     * @param manager
     * @return ['msg'=>[]]
     */
    public function actionManUpd($at_token){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $req=Yii::$app->request;
        $body=$req->bodyParams;
        if(!empty($body['id'])){
            $omanager=Manager::find()->where(['id'=>$body['id']])->one();
            if($omanager!=null){
                if ((!empty($body['username']))) {
                    foreach ($body as $key => $value) {
                        if($key!="id"&&$key!="token"&&$key!="password"){
                            $omanager[$key]=$value;
                        }
                    }
                    if((!empty($body["password"]))&&$omanager->password!=$body["password"])$omanager->password=md5($body["password"]);
                    try {
                        if (!$omanager->update()) {
                            $msg->code=-1;
                            $msg->info="无数据被改动。";
                        }else{
                            $msg->data=$omanager;
                        }
                    } catch (yii\db\IntegrityException $e) {
                        $msg->code=-5;
                        $msg->info="用户名已存在，请尝试别的用户名。";
                    }
                }else{
                    $msg->code=-2;
                    $msg->info="用户名不能为空！";
                }
            }else{
                $msg->code=-3;
                $msg->info="无此id信息！";
            }
        }else{
            $msg->code=-4;
            $msg->info="id不能为空！";
        }
        return ['msg'=>$msg];
    }

    /**
     * ManList 管理员列表
     * @param $at_token
     * @param $pageNo
     * @param $pageSize
     * @return ['msg'=>[]]
     */
    public function actionManList($pageNo=1,$pageSize=3,$username="",$nickname=""){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $query=Manager::find()->where(['and',['like','username',$username],['like','nickname',$nickname]]);
        $managers=$query->orderBy(['id'=>SORT_DESC])->limit($pageSize)->offset(($pageNo-1)*$pageSize)->all();
        $totalCount=$query->count();
        $msg->data=['managers'=>$managers,'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
        return ['msg'=>$msg];
    }

    /**
     * ManDetail 获取详情
     * @param at_token
     * @param $id
     * @return ['msg'=>[]]
     */
    public function actionManDetail($id){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $manager=Manager::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $msg->data=['manager'=>$manager];
        return ['msg'=>$msg];
    }

    /**
     * ManDetail 删除
     * @param at_token
     * @param $id
     * @return ['msg'=>[]]
     */
    public function actionManDel($id){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $manager=Manager::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        if($manager==null||(!$manager->delete())){
            $msg->info=-1;
            $msg->code="删除失败！";
        }
        return ['msg'=>$msg];
    }



}
