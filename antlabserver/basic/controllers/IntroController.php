<?php

namespace app\controllers;

use Yii;
use app\components\MyFilter;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\DTO;
use app\models\Intro;

class IntroController extends Controller
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
                'only'=>['man*']
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'list' => ['get'],
                    'man-list'=>['get'],
                    'detail'=>['get'],
                    'man-detail'=>['get']
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
        ];
    }

    /**
     * Detail 获取详情
     * @return ['msg'=>[]]
     */
    public function actionDetail(){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $intro=Intro::find()->where(['id'=>1])->one();
        $msg=new DTO();
        $msg->data=['intro'=>$intro];
        return ['msg'=>$msg];
    }

    /**
     * ManUpd 修改简介
     * @param $at_token
     * @param intro
     * @return ['msg'=>[]]
     */
    public function actionManUpd($at_token){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $req=Yii::$app->request;
        $body=$req->bodyParams;
        if(!empty($body['id'])){
            $ointro=Intro::find()->where(['id'=>$body['id']])->one();
            if($ointro!=null){
                if ((!empty($body['title']))&&(!empty($body['content']))
                    &&(!empty($body['intro']))) {
                    foreach ($body as $key => $value) {
                        if($key!="id"){
                            $ointro[$key]=$value;
                        }
                    }
                    if (!$ointro->update()) {
                        $msg->code=-1;
                        $msg->info="无数据被改动。";
                    }else{
                        $msg->data=$ointro;
                    }
                }else{
                    $msg->code=-2;
                    $msg->info="标题、简介或内容不能为空！";
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
     * ManDetail 获取详情
     * @param at_token
     * @param $id
     * @return ['msg'=>[]]
     */
    public function actionManDetail($id){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $intro=Intro::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $msg->data=['intro'=>$intro];
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
        $intro=Intro::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        if($intro==null||(!$intro->delete())){
            $msg->info=-1;
            $msg->code="删除失败！";
        }
        return ['msg'=>$msg];
    }


}
