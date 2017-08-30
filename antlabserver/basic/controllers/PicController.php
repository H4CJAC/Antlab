<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\DTO;
use app\models\Pic;
use app\components\MyFilter;
use app\models\UploadForm;
use yii\web\UploadedFile;

class PicController extends Controller
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
     * List 获取列表
     * @param $oid
     * @param $type
     * @return ['msg'=>[]]
     */
    public function actionList($pageNo=1,$pageSize=3,$type,$oid){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $pics=Pic::find()->where(['type'=>$type,'oid'=>$oid])->orderBy(['id'=>SORT_DESC])->limit($pageSize)->offset(($pageNo-1)*$pageSize)->all();
        $totalCount=Pic::find()->where(['type'=>$type,'oid'=>$oid])->count();
        $msg=new DTO();
        $msg->data=['pics'=>$pics,'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
        return ['msg'=>$msg];
    }

    /**
     * List 获取全部列表
     * @param $oid
     * @param $type
     * @return ['msg'=>[]]
     */
    public function actionListAll($type,$oid){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $pics=Pic::find()->where(['type'=>$type,'oid'=>$oid])->orderBy(['id'=>SORT_DESC])->all();
        $totalCount=Pic::find()->where(['type'=>$type,'oid'=>$oid])->count();
        $msg=new DTO();
        $msg->data=['pics'=>$pics];
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
        $pic=Pic::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $msg->data=['pic'=>$pic];
        return ['msg'=>$msg];
    }

    /**
     * ManUpload 上传图片
     * @param $at_token
     * @param UploadForm[imageFiles][]
     * @return ['msg'=>[]]
     */
    public function actionManUpload($oid=-1,$type=-1){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $model=new UploadForm();
        $msg=new DTO();

        $model->imageFiles=UploadedFile::getInstances($model,'imageFiles');
        $res=$model->upload();
        if(count($res)<=0){
            $msg->code=-1;
            $msg->info="fail";
            $msg->data=$model->imageFiles;
        }else{
            if($oid>0&&$type>0){
                $pics=[];
                foreach ($res as $key => $value) {
                    $pic=new Pic();
                    $pic->oid=$oid;
                    $pic->type=$type;
                    $pic->url=Yii::$app->params["host"].$value;
                    $pic->created=date("Y-m-d H:i:s");
                    $pic->insert();
                    $pics[$key]=$pic;
                }
                $msg->data=$pics;
            }else{
                foreach ($res as $key => $value) {
                    $res[$key]=Yii::$app->params["host"].$value;
                }
                $msg->data=$res;
            }
        }
        
        return ['msg'=>$msg];
    }


    /**
     * ManList 图片列表
     * @param $at_token
     * @param $pageNo
     * @param $pageSize
     * @return ['msg'=>[]]
     */
    public function actionManList($pageNo=1,$pageSize=3,$oid,$type){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $pics=Pic::find()->where(["oid"=>$oid,"type"=>$type])->orderBy(['id'=>SORT_DESC])->limit($pageSize)->offset(($pageNo-1)*$pageSize)->all();
        $totalCount=Pic::find()->where(["oid"=>$oid,"type"=>$type])->count();
        $msg->data=['pics'=>$pics,'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
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
        $pic=Pic::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $msg->data=['pic'=>$pic];
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
        $pic=Pic::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        if($pic!=null&&$pic->delete()){
            try {
                $tp=str_replace(Yii::$app->params["host"], "", $pic->url);
                unlink(dirname(__DIR__).$tp);
            } catch (Exception $e) {
            }
        }else{
            $msg->code=-1;
            $msg->info="failed!";
        }
        return ['msg'=>$msg];
    }

    /**
     * ManMDel 删除
     * @param at_token
     * @param $oid
     * @param $type
     * @return ['msg'=>[]]
     */
    public function actionManMDel($oid,$type){
        $msg=new DTO();
        if(is_numeric($oid)&&is_numeric($type)){
            $response=Yii::$app->response;
            $response->format=\yii\web\Response::FORMAT_JSON;
            $pics=Pic::find()->where(['oid'=>$oid,'type'=>$type])->all();
            Pic::deleteAll("oid=".$oid." and type=".$type);
            foreach ($pics as $key => $pic) {
                try {
                    $tp=str_replace(Yii::$app->params["host"], "", $pic->url);
                    unlink(dirname(__DIR__).$tp);
                } catch (Exception $e) {
                }
            }
        }else{
            $msg->code=-1;
            $msg->info="failed!";
        }
        return ['msg'=>$msg];
    }

    /**
     * ManDelWU 删除
     * @param at_token
     * @param $id
     * @return ['msg'=>[]]
     */
    public function actionManDelWU($url){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();
        try {
            $tp=str_replace(Yii::$app->params["host"], "", $url);
            unlink(dirname(__DIR__).$tp);
        } catch (Exception $e) {
        }
        return ['msg'=>$msg];
    }


}
