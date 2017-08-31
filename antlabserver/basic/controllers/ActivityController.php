<?php

namespace app\controllers;

use Yii;
use app\components\MyFilter;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\DTO;
use app\models\Activity;
use app\models\Pic;
use app\models\Manager;

class ActivityController extends Controller
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
                    'man-detail'=>['get'],
                    'view'=>['get']
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
     * List 获取列表
     * @param $pageNo
     * @param $pageSize
     * @return ['msg'=>[]]
     */
    public function actionList($pageNo=1,$pageSize=3){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $activitys=Activity::find()->orderBy(['id'=>SORT_DESC])->limit($pageSize)->offset(($pageNo-1)*$pageSize)->all();
        $totalCount=Activity::find()->count();
        foreach ($activitys as $key => $value) {
            $pics=Pic::find()->where(['type'=>2,'oid'=>$value->id])->all();
            $activitys[$key]=['pics'=>$pics,'data'=>$value];
        }
        $msg=new DTO();
        $msg->data=['activitys'=>$activitys,'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
        return ['msg'=>$msg];
    }

    /**
     * View 浏览量
     * @param $id
     * @return []
     */
    public function actionView($id){
        $cache=Yii::$app->cache;
        $k="act_".$id;
        $v=$cache->get($k);
        if($v>5){
            $activity=Activity::find()->where(['id'=>$id])->one();
            if($activity!=null){
                $activity->view+=$v;
                $activity->update();
            }
            $cache->set($k,1);
        }else {
            if(!$v)$v=1;
            else $v++;
            $cache->set($k,$v);
        }
        return "";
    }

    /**
     * Detail 获取详情
     * @param $id
     * @return ['msg'=>[]]
     */
    public function actionDetail($id){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $activity=Activity::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $msg->data=['activity'=>$activity];
        return ['msg'=>$msg];
    }

    /**
     * ManAdd 添加活动
     * @param $at_token
     * @param activity
     * @return ['msg'=>[]]
     */
    public function actionManAdd($at_token){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $req=Yii::$app->request;
        $body=$req->bodyParams;
        if ((!empty($body['title']))&&(!empty($body['content']))
            &&(!empty($body['intro']))) {
            $activity=new Activity();
            foreach ($body as $key => $value) {
                $activity[$key]=$value;
            }
            $activity->id=null;
            $man=Manager::find()->where(['token'=>$at_token])->one();
            $activity->pubby=$man->nickname;
            $activity->pubtime=date("Y-m-d H:i:s");
            $activity->view=0;
            if (!$activity->insert()) {
                $msg->code=-1;
                $msg->info="fail";
            }else{
                $msg->data=$activity;
            }
        }else{
            $msg->code=-2;
            $msg->info="标题、简介或内容不能为空！";
        }
        return ['msg'=>$msg];
    }

    /**
     * ManUpd 修改活动
     * @param $at_token
     * @param activity
     * @return ['msg'=>[]]
     */
    public function actionManUpd($at_token){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $req=Yii::$app->request;
        $body=$req->bodyParams;
        if(!empty($body['id'])){
            $oact=Activity::find()->where(['id'=>$body['id']])->one();
            if($oact!=null){
                if ((!empty($body['title']))&&(!empty($body['content']))
                    &&(!empty($body['intro']))) {
                    $man=Manager::find()->where(['token'=>$at_token])->one();
                    $oact->pubby=$man->nickname;
                    foreach ($body as $key => $value) {
                        if($key!="id"&&$key!="pubtime"&&$key!="pubby"&&$key!="view"){
                            $oact[$key]=$value;
                        }
                    }
                    if (!$oact->update()) {
                        $msg->code=-1;
                        $msg->info="无数据被改动。";
                    }else{
                        $msg->data=$oact;
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
     * ManList 活动列表
     * @param $at_token
     * @param $pageNo
     * @param $pageSize
     * @return ['msg'=>[]]
     */
    public function actionManList($pageNo=1,$pageSize=3,$title="",$intro=""){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();
        $arr=[];
        if (!empty($title)) {
            $arr['title']=$title;
        }
        if (!empty($intro)) {
            $arr['intro']=$intro;
        }
        $activitys=Activity::find()->where(["and",['like','title',$title],['like','intro',$intro]])->orderBy(['id'=>SORT_DESC])->limit($pageSize)->offset(($pageNo-1)*$pageSize)->all();
        $totalCount=Activity::find()->where(["and",['like','title',$title],['like','intro',$intro]])->count();
        $msg->data=['activitys'=>$activitys,'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
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
        $activity=Activity::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $msg->data=['activity'=>$activity];
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
        $activity=Activity::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        if($activity==null||(!$activity->delete())){
            $msg->info=-1;
            $msg->code="删除失败！";
        }
        return ['msg'=>$msg];
    }


}
