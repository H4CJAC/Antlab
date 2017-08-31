<?php

namespace app\controllers;

use Yii;
use app\components\MyFilter;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\DTO;
use app\models\Paper;
use app\models\Pic;
use app\models\Manager;
use yii\db\Query;

class PaperController extends Controller
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
                    'list*' => ['get'],
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
        $k="paper_".$id;
        $v=$cache->get($k);
        if($v>5){
            $paper=Paper::find()->where(['id'=>$id])->one();
            if($paper!=null){
                $paper->view+=$v;
                $paper->update();
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
     * ListTs 获取所有标题
     * @return ['msg'=>[]]
     */
    public function actionListTs(){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $query=Paper::find()->where(["isshow"=>1]);
        $papers=$query->orderBy(['id'=>SORT_DESC])->all();
        $totalCount=$query->count();
        foreach ($papers as $key => $value) {
            $papers[$key]->content="";
        }
        $msg=new DTO();
        $msg->data=['papers'=>$papers,'totalCount'=>$totalCount];
        return ['msg'=>$msg];
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
        $query=Paper::find()->where(["isshow"=>1]);
        $papers=$query->orderBy(['id'=>SORT_DESC])->limit($pageSize)->offset(($pageNo-1)*$pageSize)->all();
        $totalCount=$query->count();
        $msg=new DTO();
        $msg->data=['papers'=>$papers,'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
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
        $paper=Paper::find()->where(['and',['id'=>$id],["isshow"=>1]])->one();
        $msg=new DTO();
        $msg->data=['paper'=>$paper];
        return ['msg'=>$msg];
    }

    /**
     * ManAdd 添加论文
     * @param $at_token
     * @param paper
     * @return ['msg'=>[]]
     */
    public function actionManAdd($at_token){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $req=Yii::$app->request;
        $body=$req->bodyParams;
        if ((!empty($body['name']))&&(!empty($body['content']))) {
            $paper=new Paper();
            foreach ($body as $key => $value) {
                $paper[$key]=$value;
            }
            $paper->id=null;
            $man=Manager::find()->where(['token'=>$at_token])->one();
            $paper->pubby=$man->nickname;
            $paper->pubtime=date("Y-m-d H:i:s");
            $paper->view=0;
            if (!$paper->insert()) {
                $msg->code=-1;
                $msg->info="fail";
            }else{
                $msg->data=$paper;
            }
        }else{
            $msg->code=-2;
            $msg->info="类型或内容不能为空！";
        }
        return ['msg'=>$msg];
    }

    /**
     * ManUpd 修改论文
     * @param $at_token
     * @param paper
     * @return ['msg'=>[]]
     */
    public function actionManUpd($at_token){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $req=Yii::$app->request;
        $body=$req->bodyParams;
        if(!empty($body['id'])){
            $opaper=Paper::find()->where(['id'=>$body['id']])->one();
            if($opaper!=null){
                if ((!empty($body['name']))&&(!empty($body['content']))) {
                    $man=Manager::find()->where(['token'=>$at_token])->one();
                    $opaper->pubby=$man->nickname;
                    foreach ($body as $key => $value) {
                        if($key!="id"&&$key!="pubtime"&&$key!="pubby"&&$key!="view"){
                            $opaper[$key]=$value;
                        }
                    }
                    if (!$opaper->update()) {
                        $msg->code=-1;
                        $msg->info="无数据被改动。";
                    }else{
                        $msg->data=$opaper;
                    }
                }else{
                    $msg->code=-2;
                    $msg->info="类型或内容不能为空！";
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
     * ManList 论文列表
     * @param $at_token
     * @param $pageNo
     * @param $pageSize
     * @return ['msg'=>[]]
     */
    public function actionManList($pageNo=1,$pageSize=3,$name=""){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $papers=Paper::find()->where(['like','name',$name])->orderBy(['id'=>SORT_DESC])->limit($pageSize)->offset(($pageNo-1)*$pageSize)->all();
        $totalCount=Paper::find()->where(['like','name',$name])->count();
        $msg->data=['papers'=>$papers,'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
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
        $paper=Paper::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $msg->data=['paper'=>$paper];
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
        $paper=Paper::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        if($paper==null||(!$paper->delete())){
            $msg->info=-1;
            $msg->code="删除失败！";
        }
        return ['msg'=>$msg];
    }

}
