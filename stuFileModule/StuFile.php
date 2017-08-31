<?php

require './excelExt/PHPExcel.php';
/**
* 
*/
class StuFile
{

    public $id;
    public $remark;
    public $fpath;

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



?>