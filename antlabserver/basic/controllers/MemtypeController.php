<?php

namespace app\controllers;

use Yii;
use app\components\MyFilter;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\DTO;
use app\models\Memtype;

class MemtypeController extends Controller
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
     * ListAll 获取全部列表
     * @return ['msg'=>[]]
     */
    public function actionListAll(){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $memtypes=Memtype::find()->where(["isshow"=>1])->orderBy(["name"=>SORT_ASC])->all();
        $totalCount=Memtype::find()->where(["isshow"=>1])->count();
        $msg=new DTO();
        $msg->data=['memtypes'=>$memtypes];
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
        $memtype=Memtype::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $msg->data=['memtype'=>$memtype];
        return ['msg'=>$msg];
    }

    /**
     * ManAdd 添加成员类型
     * @param $at_token
     * @param memtype
     * @return ['msg'=>[]]
     */
    public function actionManAdd($at_token){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $req=Yii::$app->request;
        $body=$req->bodyParams;
        if ((!empty($body['name']))) {
            $memtype=new Memtype();
            foreach ($body as $key => $value) {
                $memtype[$key]=$value;
            }
            $memtype->id=null;
            if (!$memtype->insert()) {
                $msg->code=-1;
                $msg->info="fail";
            }else{
                $msg->data=$memtype;
            }
        }else{
            $msg->code=-2;
            $msg->info="名字不能为空！";
        }
        return ['msg'=>$msg];
    }

    /**
     * ManUpd 修改成员类型
     * @param $at_token
     * @param memtype
     * @return ['msg'=>[]]
     */
    public function actionManUpd($at_token){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $req=Yii::$app->request;
        $body=$req->bodyParams;
        if(!empty($body['id'])){
            $omemtype=Memtype::find()->where(['id'=>$body['id']])->one();
            if($omemtype!=null){
                if ((!empty($body['name']))) {
                    foreach ($body as $key => $value) {
                        if($key!="id"){
                            $omemtype[$key]=$value;
                        }
                    }
                    if (!$omemtype->update()) {
                        $msg->code=-1;
                        $msg->info="无数据被改动。";
                    }else{
                        $msg->data=$omemtype;
                    }
                }else{
                    $msg->code=-2;
                    $msg->info="名字不能为空！";
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
     * ManList 成员类型列表
     * @param $at_token
     * @param $pageNo
     * @param $pageSize
     * @param $name
     * @return ['msg'=>[]]
     */
    public function actionManList($pageNo=1,$pageSize=3,$name=""){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $memtypes=Memtype::find()->where(['like','name',$name])->orderBy(['id'=>SORT_DESC])->limit($pageSize)->offset(($pageNo-1)*$pageSize)->all();
        $totalCount=Memtype::find()->where(['like','name',$name])->count();
        $msg->data=['memtypes'=>$memtypes,'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
        return ['msg'=>$msg];
    }

    /**
     * ManListAll 所有成员类型列表
     * @param $at_token
     * @return ['msg'=>[]]
     */
    public function actionManListAll(){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $memtypes=Memtype::find()->orderBy(['name'=>SORT_DESC])->all();
        $totalCount=Memtype::find()->count();
        $msg->data=['memtypes'=>$memtypes,'totalCount'=>$totalCount];
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
        $memtype=Memtype::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $msg->data=['memtype'=>$memtype];
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
        $memtype=Memtype::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        if($memtype==null||(!$memtype->delete())){
            $msg->info=-1;
            $msg->code="删除失败！";
        }
        return ['msg'=>$msg];
    }


}
