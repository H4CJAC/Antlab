<?php
namespace app\controllers;

require dirname(__DIR__).'/excelExt/PHPExcel.php';
use Yii;
// use app\components\MyFilter;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\DTO;
use app\models\Student;
use app\models\StuFile;
use app\models\StuUploadForm;
use yii\web\UploadedFile;

class StudentController extends Controller
{

    public $layout=false;
    public $enableCsrfValidation=false;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            // 'access' => [
            //     'class' => MyFilter::className(),
            //     'only'=>['man*']
            // ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'file-list'=>['get'],
                    'list' => ['get'],
                    'add'=>['post']
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
     * 
     */
    public function actionTest(){
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();
        $cache=Yii::$app->cache;
        $res=$cache->add("new124",3,10);
        $msg->data=['res'=>$res];
        return ['msg'=>$msg];
    }

    /**
     * FileList 获取列表
     * @param $pageNo
     * @param $pageSize
     * @return ['msg'=>[]]
     */
    public function actionFileList($pageNo=1,$pageSize=3){
        $xlsN=dirname(__DIR__)."/excels/stuFile.csv";
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $s=($pageNo-1)*$pageSize+1;
        $e=$s+$pageSize-1;
        $stuFileArr=$this->listObj($xlsN,$s,$e,"app\models\StuFile");
        $totalCount=$stuFileArr[0];
        foreach($stuFileArr[1] as $key=>$val){
            $stuFileArr[1][$key]->id=substr($stuFileArr[1][$key]->id, 1);
            $stuFileArr[1][$key]->remark=substr($stuFileArr[1][$key]->remark, 1);
        }
        $msg=new DTO();
        $msg->data=['stuFiles'=>$stuFileArr[1],'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
        return ['msg'=>$msg];
    }

    /**
     * List 获取列表
     * @param $pageNo
     * @param $pageSize
     * @return ['msg'=>[]]
     */
    public function actionList($pageNo=1,$pageSize=3){
        $xlsN=dirname(__DIR__)."/excels/stu.csv";
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $s=($pageNo-1)*$pageSize+1;
        $e=$s+$pageSize-1;
        $stuArr=$this->listObj($xlsN,$s,$e,"app\models\Student");
        $totalCount=$stuArr[0];
        foreach($stuArr[1] as $key=>$val){
            $stuArr[1][$key]->id=substr($stuArr[1][$key]->id, 1);
        }
        $msg=new DTO();
        $msg->data=['stus'=>$stuArr[1],'pageNo'=>$pageNo,'pageSize'=>$pageSize,'totalCount'=>$totalCount];
        return ['msg'=>$msg];
    }

    /**
     * Add 添加
     * @param student
     * @return ['msg'=>[]]
     */
    public function actionAdd(){
        $xlsN=dirname(__DIR__)."/excels/stu.csv";
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $msg=new DTO();

        $req=Yii::$app->request;
        $body=$req->bodyParams;
        if ((!empty($body['id']))&&(!empty($body['name']))) {
            $stu=new Student();
            foreach ($body as $key => $value) {
                $stu[$key]=$value;
            }
            $stu->id="'".$stu->id;
            $this->insert($stu,$xlsN,null);
            $msg->data=$stu;
        }else{
            $msg->code=-2;
            $msg->info="学号或姓名不能为空！";
        }
        return ['msg'=>$msg];
    }

    /**
     * Upload 上传文件
     * @param UploadForm[upFiles][]
     * @return ['msg'=>[]]
     */
    public function actionUpload($sid,$remark=""){
        $xlsN=dirname(__DIR__)."/excels/stuFile.csv";
        $response=Yii::$app->response;
        $response->format=\yii\web\Response::FORMAT_JSON;
        $model=new StuUploadForm();
        $msg=new DTO();
        if(empty($sid)){
            $msg->code=-1;
            $msg->info="学号不能为空！";
            return ['msg'=>$msg];
        }

        $model->upFiles=UploadedFile::getInstances($model,'upFiles');
        $model->sid=$sid;
        $res=$model->upload();
        if(count($res)<=0){
            $msg->code=-1;
            $msg->info="文件格式不正确！";
            $msg->data=$model->upFiles;
        }else{
            $sfs=[];
            foreach ($res as $key => $value) {
                $sf=new StuFile();
                $sf->id="'".$sid;
                $sf->remark="'".$remark;
                // $sf->fpath=Yii::$app->params["host"].$value;
                $sf->fpath=$value;
                $ofp=$this->insert($sf,$xlsN,function($xlsS,$rtw){
                    $cidx=0;$type=new StuFile();
                    foreach($type as $k=>$v){
                        if($k=="fpath")break;
                        $cidx++;
                    }
                    return $xlsS->getCell(chr(ord('A')+$cidx).$rtw)->getValue();
                });
                if($ofp!=null){
                    try {
                        // $tp=str_replace(Yii::$app->params["host"], "", $ofp);
                        // unlink(dirname(__DIR__).$tp);
                        unlink(dirname(__DIR__).'/stuUploads/'.$ofp);
                    } catch (Exception $e) {
                    }
                }
                $sfs[$key]=$sf;
            }
            $msg->data=$sfs;
        }
        
        return ['msg'=>$msg];
    }

    private function listObj($xlsN,$s,$e,$typeName){
        if(!file_exists($xlsN))return [0,[]];
        $xlsR=new \PHPExcel_Reader_CSV();
        $xls=$xlsR->setDelimiter(',')
            ->load($xlsN);
        $xlsS=$xls->getActiveSheet(0);
        $hr=$xlsS->getHighestRow();
        $s++;$e++;
        if($s>$hr)return [$hr,[]];
        if($e>$hr)$e=$hr;
        $colidx=ord('A');
        $type=new $typeName();
        foreach($type as $key) $colidx++;
        $resarr=$xlsS->rangeToArray("A".$s.":".chr($colidx).$e,null,true,true,false);
        $res=array();
        foreach($resarr as $key=>$val){
            $res[$key]=new $typeName();
            $idx=0;
            foreach($res[$key] as $k=>$v)$res[$key][$k]=$val[$idx++];
        }
        return [$hr-1,$res];
    }

    private function initxls($xlsN,$type){
        $cache=Yii::$app->cache;
        $xlsmtx=$xlsN."_init_mtx";
        if($cache->add($xlsmtx,1,5)){
            $xls=new \PHPExcel();
            $colidx=ord('A');
            $xlsS=$xls->getActiveSheet(0);
            foreach($type as $key=>$val)$xlsS->setCellValue(chr($colidx++)."1",$key);
            $xlsW=new \PHPExcel_Writer_CSV($xls);
            $xlsW->setDelimiter(',');
            $xlsW->save($xlsN);
            $cache->delete($xlsmtx);
        }
    }

    private function insert($obj,$xlsN,$cbarr){
        $res=null;
        while(!file_exists($xlsN)){
            $this->initxls($xlsN,$obj);
            usleep(5000);
        }
        $sarr=array();
        $n=0;
        foreach($obj as $key=>$val)$sarr[$n++]=$val;
        $xlsR=new \PHPExcel_Reader_CSV();
        $cache=Yii::$app->cache;
        $xlsmtx=$xlsN."_add_mtx";
        while(!$cache->add($xlsmtx,1,5))usleep(5000);
        $xls=$xlsR->setDelimiter(',')
            ->load($xlsN);
        $xlsS=$xls->getActiveSheet(0);
        $hr=$xlsS->getHighestRow();
        $xlsS->setCellValue("A".($hr+2),'=MATCH("'.$obj->id.'",A2:A'.$hr.',0)');
        $rtw=$xlsS->getCell("A".($hr+2))->getCalculatedValue();
        $xlsS->removeRow($hr+2);
        if(!is_numeric($rtw))$rtw=$hr+1;
        else{
            $rtw++;
            if($cbarr!=null)$res=call_user_func($cbarr,$xlsS,$rtw);
        }
        $xlsS->fromArray($sarr,null,"A".$rtw);
        $xlsW=new \PHPExcel_Writer_CSV($xls);
        $xlsW->setDelimiter(',');
        $xlsW->save($xlsN);
        $cache->delete($xlsmtx);
        return $res;
    }


}
