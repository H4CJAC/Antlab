<?php

namespace app\controllers;

use Yii;
use app\components\MyFilter;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\DTO;
use app\models\Member;
use app\models\Memtype;
use yii\db\Query;

class MemberController extends Controller
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
     * @param $pageNo
     * @param $pageSize
     * @return ['msg'=>[]]
     */
    public function actionList($pageNo=1,$pageSize=3){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $members=Member::find()->where([
                "in",
                "type",
                (new Query())->select("id")->from("memtypes")->where(["isshow"=>1])
            ])->orderBy(['name'=>SORT_ASC])->limit($pageSize)->offset(($pageNo-1)*$pageSize)->all();
        $totalCount=Member::find()->where([
                "in",
                "type",
                (new Query())->select("id")->from("memtypes")->where(["isshow"=>1])
            ])->count();
        foreach ($members as $member) {
            if(Memtype::find()->where(["id"=>$member->type,"showpic"=>1])->count()<=0){
                $member->coverpic="";
            }
        }
        $msg=new DTO();
        $msg->data=['members'=>$members,'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
        return ['msg'=>$msg];
    }

    /**
     * ListAll 获取全部列表
     * @param $type
     * @return ['msg'=>[]]
     */
    public function actionListAll($type){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();
        if (Memtype::find()->where(["id"=>$type,"isshow"=>1])->count()>0) {
            $members=Member::find()->where(['type'=>$type])->all();
            $totalCount=Member::find()->where(['type'=>$type])->count();
            if (Memtype::find()->where(["id"=>$type,"showpic"=>1])->count()<=0) {
                foreach ($members as $member) {
                    $member->coverpic="";
                }
            }
        }else{
            $members=[];
        }
        $msg->data=['members'=>$members];
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
        $member=Member::find()->where(['id'=>$id])->one();
        if ($member!=null) {
            if (Memtype::find()->where(["id"=>$member->type,"isshow"=>1])->count()<=0) {
                $member=[];
            }else if(Memtype::find()->where(["id"=>$member->type,"showpic"=>1])->count()<=0){
                $member->coverpic="";
            }
        }
        $msg=new DTO();
        $msg->data=['member'=>$member];
        return ['msg'=>$msg];
    }

    /**
     * ManAdd 添加成员
     * @param $at_token
     * @param member
     * @return ['msg'=>[]]
     */
    public function actionManAdd($at_token){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $req=Yii::$app->request;
        $body=$req->bodyParams;
        if ((!empty($body['name']))) {
            $member=new Member();
            foreach ($body as $key => $value) {
                $member[$key]=$value;
            }
            $member->id=null;
            if (!$member->insert()) {
                $msg->code=-1;
                $msg->info="fail";
            }else{
                $msg->data=$member;
            }
        }else{
            $msg->code=-2;
            $msg->info="名字不能为空！";
        }
        return ['msg'=>$msg];
    }

    /**
     * ManUpd 修改成员
     * @param $at_token
     * @param member
     * @return ['msg'=>[]]
     */
    public function actionManUpd($at_token){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $req=Yii::$app->request;
        $body=$req->bodyParams;
        if(!empty($body['id'])){
            $omember=Member::find()->where(['id'=>$body['id']])->one();
            if($omember!=null){
                if ((!empty($body['name']))) {
                    foreach ($body as $key => $value) {
                        if($key!="id"){
                            $omember[$key]=$value;
                        }
                    }
                    if (!$omember->update()) {
                        $msg->code=-1;
                        $msg->info="无数据被改动。";
                    }else{
                        $msg->data=$omember;
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
     * ManList 成员列表
     * @param $at_token
     * @param $pageNo
     * @param $pageSize
     * @return ['msg'=>[]]
     */
    public function actionManList($pageNo=1,$pageSize=3,$name=""){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $members=Member::find()->where(['like','name',$name])->orderBy(['id'=>SORT_DESC])->limit($pageSize)->offset(($pageNo-1)*$pageSize)->all();
        $totalCount=Member::find()->where(['like','name',$name])->count();
        $msg->data=['members'=>$members,'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
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
        $member=Member::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        $msg->data=['member'=>$member];
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
        $member=Member::find()->where(['id'=>$id])->one();
        $msg=new DTO();
        if($member==null||(!$member->delete())){
            $msg->info=-1;
            $msg->code="删除失败！";
        }
        return ['msg'=>$msg];
    }


}
