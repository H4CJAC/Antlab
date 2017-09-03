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

    static public function listObj($xlsN,$typeName){
        if(!file_exists($xlsN))return [0,[]];
        $xlsR=new \PHPExcel_Reader_CSV();
        $xls=$xlsR->setDelimiter(',')
            ->load($xlsN);
        $xlsS=$xls->getActiveSheet(0);
        $hr=$xlsS->getHighestRow();
        $colidx=ord('A');
        $type=new $typeName();
        foreach($type as $key) $colidx++;
        $resarr=$xlsS->rangeToArray("A2:".chr($colidx).$hr,null,true,true,false);
        $res=array();
        foreach($resarr as $key=>$val){
            $res[$key]=(array)new $typeName();
            $idx=0;
            foreach($res[$key] as $k=>$v)$res[$key][$k]=$val[$idx++];
            $res[$key]=(Object)$res[$key];
        }
        return [$hr-1,$res];
    }

    static private function initxls($xlsN,$type,$mtx){
        if(!file_exists("runtime"))mkdir("runtime");
        if(!file_exists("excels"))mkdir("excels");
        $xlsmtx="./runtime/".$mtx."_init_mtx";
        $fp=fopen($xlsmtx, "w+");
        if(flock($fp,LOCK_EX)){
            if(file_exists($xlsN)){
                fclose($fp);
                return;
            }
            $xls=new \PHPExcel();
            $colidx=ord('A');
            $xlsS=$xls->getActiveSheet(0);
            foreach($type as $key=>$val)$xlsS->setCellValue(chr($colidx++)."1",$key);
            $xlsW=new \PHPExcel_Writer_CSV($xls);
            $xlsW->setDelimiter(',');
            $xlsW->save($xlsN);
            fclose($fp);
        }
    }

    static public function insert($obj,$xlsN,$cbarr,$mtx){
        $res=null;
        while(!file_exists($xlsN)){
            self::initxls($xlsN,$obj,$mtx);
            usleep(5000);
        }
        $sarr=array();
        $n=0;
        foreach($obj as $key=>$val)$sarr[$n++]=$val;
        $xlsR=new \PHPExcel_Reader_CSV();
        $xlsmtx="./runtime/".$mtx."_add_mtx";
        $fp=fopen($xlsmtx, "w+");
        while(!flock($fp,LOCK_EX))usleep(5000);
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
        fclose($fp);
        return $res;
    }
    
}



?>