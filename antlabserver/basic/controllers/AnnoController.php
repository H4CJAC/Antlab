<?php

namespace app\controllers;

use Yii;
use app\components\MyFilter;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\DTO;
use app\models\Announcement;
use app\models\Pic;
use app\models\Manager;

class AnnoController extends Controller
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
     * View 浏览量
     * @param $id
     * @return []
     */
    public function actionView($id){
        $cache=Yii::$app->cache;
        $k="anno_".$id;
        $v=$cache->get($k);
        if($v>5){
            $anno=Announcement::find()->where(['id'=>$id])->one();
            if($anno!=null){
                $anno->view+=$v;
                $anno->update();
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
     * List 获取列表
     * @param $pageNo
     * @param $pageSize
     * @return ['msg'=>[]]
     */
    public function actionList($pageNo=1,$pageSize=3){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $annos=Announcement::find()->orderBy(['top'=>SORT_DESC,'id'=>SORT_DESC])->limit($pageSize)->offset(($pageNo-1)*$pageSize)->all();
        $totalCount=Announcement::find()->count();
        foreach ($annos as $key => $value) {
            $pics=Pic::find()->where(['type'=>1,'oid'=>$value->id])->all();
            $annos[$key]=['pics'=>$pics,'data'=>$value];
        }
        $msg=new DTO();
        $msg->data=['annos'=>$annos,'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
        return ['msg'=>$msg];
    }

    /**
     * Detail 获取详情
     * @param $id
     * @return ['msg'=>[]]
     */
    public function actionDetail($id){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $anno=Announcement::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $msg->data=['anno'=>$anno];
        return ['msg'=>$msg];
    }

    /**
     * ManAdd 添加通知
     * @param $at_token
     * @param anno
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
            $anno=new Announcement();
            foreach ($body as $key => $value) {
                $anno[$key]=$value;
            }
            $anno->id=null;
            $man=Manager::find()->where(['token'=>$at_token])->one();
            $anno->pubby=$man->nickname;
            $anno->pubtime=date("Y-m-d H:i:s");
            $anno->view=0;
            if (!$anno->insert()) {
                $msg->code=-1;
                $msg->info="fail";
            }else{
                $msg->data=$anno;
            }
        }else{
            $msg->code=-2;
            $msg->info="标题、简介或内容不能为空！";
        }
        return ['msg'=>$msg];
    }

    /**
     * ManUpd 修改通知
     * @param $at_token
     * @param anno
     * @return ['msg'=>[]]
     */
    public function actionManUpd($at_token){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $req=Yii::$app->request;
        $body=$req->bodyParams;
        if(!empty($body['id'])){
            $oanno=Announcement::find()->where(['id'=>$body['id']])->one();
            if($oanno!=null){
                if ((!empty($body['title']))&&(!empty($body['content']))
                    &&(!empty($body['intro']))) {
                    $man=Manager::find()->where(['token'=>$at_token])->one();
                    $oanno->pubby=$man->nickname;
                    foreach ($body as $key => $value) {
                        if($key!="id"&&$key!="pubtime"&&$key!="pubby"&&$key!="view"){
                            $oanno[$key]=$value;
                        }
                    }
                    if (!$oanno->update()) {
                        $msg->code=-1;
                        $msg->info="无数据被改动。";
                    }else{
                        $msg->data=$oanno;
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
     * ManList 通知列表
     * @param $at_token
     * @param $pageNo
     * @param $pageSize
     * @return ['msg'=>[]]
     */
    public function actionManList($pageNo=1,$pageSize=3,$title="",$intro=""){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $annos=Announcement::find()->where(["and",['like','title',$title],['like','intro',$intro]])->orderBy(['top'=>SORT_DESC,'id'=>SORT_DESC])->limit($pageSize)->offset(($pageNo-1)*$pageSize)->all();
        $totalCount=Announcement::find()->where(["and",['like','title',$title],['like','intro',$intro]])->count();
        $msg->data=['annos'=>$annos,'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
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
        $anno=Announcement::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $msg->data=['anno'=>$anno];
        return ['msg'=>$msg];
    }

    /**
     * ManDel 删除
     * @param at_token
     * @param $id
     * @return ['msg'=>[]]
     */
    public function actionManDel($id){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $anno=Announcement::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        if($anno==null||(!$anno->delete())){
            $msg->info="删除失败！";
            $msg->code=-1;
        }
        return ['msg'=>$msg];
    }

    /**
     * ManTop 置顶
     * @param at_token
     * @param $id
     * @return ['msg'=>[]]
     */
    public function actionManTop($id){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $anno=Announcement::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $anno->top=time();
        if (!$anno->update()) {
            $msg->info="置顶失败。";
            $msg->code=-1;
        }
        return ['msg'=>$msg];
    }

    /**
     * ManTopC 取消置顶
     * @param at_token
     * @param $id
     * @return ['msg'=>[]]
     */
    public function actionManTopC($id){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $anno=Announcement::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $anno->top=0;
        if (!$anno->update()) {
            $msg->info="取消置顶失败。";
            $msg->code=-1;
        }
        return ['msg'=>$msg];
    }

}
