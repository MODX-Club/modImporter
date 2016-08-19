<?php

require_once dirname(dirname(dirname(__FILE__))).'/console.class.php';
require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/lib/PHPExcel/Classes/PHPExcel.php';

class modImporterExportPairXlsxConsoleProcessor extends modImporterExportConsoleProcessor
{
    
    protected $filename;
    
    
    public function initialize(){
        
        if(empty($_SESSION['modImporter']['export']['filename'])){
            $_SESSION['modImporter']['export']['filename'] = 'export-' .date('Ymd_His'). '.xlsx';
        }
        
        $this->filename = $_SESSION['modImporter']['export']['filename'];
        
        return parent::initialize();
    }
    
    protected function PrepareExportData()
    {
        $data = $this->modx->getCollection('modResource', array('template:IN'=>array(2,3)));
        foreach ($data as $r) {
            if (!$r->externalKey) {
                $r->set('externalKey', 'export-'.$r->id);
                $r->save();
            }
        }
    }
    
    protected function StepSaveFile()
    {
        
        $filename = $this->filename;
        // $filename = 'export.xlsx';
        // $filename = 'export.xlsx';
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->removeSheetByIndex(0);
        $categoriesSheet = new PHPExcel_Worksheet($objPHPExcel, 'Categories');
        $objPHPExcel->addSheet($categoriesSheet, 0);
        $productsSheet = new PHPExcel_Worksheet($objPHPExcel, 'Products');
        $objPHPExcel->addSheet($productsSheet, 1);
        
        $objPHPExcel->getSheet(0)->setCellValue('A1', 'id');
        $objPHPExcel->getSheet(0)->setCellValue('B1', 'pagetitle');
        $objPHPExcel->getSheet(0)->setCellValue('C1', 'parent');
        $objPHPExcel->getSheet(0)->setCellValue('D1', 'externalKey');

        $categories = $this->modx->getCollection('modResource', array('template'=> 2));
        
        $idx = 2;
        
        foreach($categories as $cat){
            $objPHPExcel->getSheet(0)->setCellValue('A'.$idx, $cat->id);
            $objPHPExcel->getSheet(0)->setCellValue('B'.$idx, $cat->pagetitle);
            $objPHPExcel->getSheet(0)->setCellValue('C'.$idx, $cat->parent);
            $objPHPExcel->getSheet(0)->setCellValue('D'.$idx, $cat->externalKey);
            $idx++;
        }
        
        // Забиваем лист продуктов
        
        $objPHPExcel->getSheet(1)->setCellValue('A1', 'id');
        $objPHPExcel->getSheet(1)->setCellValue('B1', 'pagetitle');
        $objPHPExcel->getSheet(1)->setCellValue('C1', 'parent');
        $objPHPExcel->getSheet(1)->setCellValue('D1', 'externalKey');
        $objPHPExcel->getSheet(1)->setCellValue('E1', 'description');
        $objPHPExcel->getSheet(1)->setCellValue('F1', 'introtext');
        $objPHPExcel->getSheet(1)->setCellValue('G1', 'content');
        $objPHPExcel->getSheet(1)->setCellValue('H1', 'price');
        $objPHPExcel->getSheet(1)->setCellValue('I1', 'color');
        $objPHPExcel->getSheet(1)->setCellValue('J1', 'sizes');
        $objPHPExcel->getSheet(1)->setCellValue('K1', 'article');
        $objPHPExcel->getSheet(1)->setCellValue('L1', 'Состав');
        $objPHPExcel->getSheet(1)->setCellValue('M1', 'Страна производства');
        $objPHPExcel->getSheet(1)->setCellValue('N1', 'Сезон');
        $objPHPExcel->getSheet(1)->setCellValue('O1', 'Коллекция');
        $objPHPExcel->getSheet(1)->setCellValue('P1', 'Новинка');
        $objPHPExcel->getSheet(1)->setCellValue('Q1', 'Таблица размеров');
        
        // $products = $this->modx->getCollection('modResource', array(
        //     'template'  => 3, 
        //     "deleted"   => 0,
        //     "id:in"    => [381, 199, 407,]
        // ));
        
        $idx = 2;
        
        foreach($this->modx->getIterator('modResource', array(
            'template'  => 3, 
            "deleted"   => 0,
            // "id:in"    => [381, 199, 407,]
        )) as $product){
            // print "\n".$idx;
            $objPHPExcel->getSheet(1)->setCellValue('A'.$idx, $product->id);
            $objPHPExcel->getSheet(1)->setCellValue('B'.$idx, $product->pagetitle);
            $objPHPExcel->getSheet(1)->setCellValue('C'.$idx, $product->parent);
            $objPHPExcel->getSheet(1)->setCellValue('D'.$idx, $product->externalKey);
            $objPHPExcel->getSheet(1)->setCellValue('E'.$idx, $product->description);
            $objPHPExcel->getSheet(1)->setCellValue('F'.$idx, $product->introtext);
            $objPHPExcel->getSheet(1)->setCellValue('G'.$idx, $product->content);
            $objPHPExcel->getSheet(1)->setCellValue('H'.$idx, $product->price);
            $objPHPExcel->getSheet(1)->setCellValue('K'.$idx, $product->article);
            $objPHPExcel->getSheet(1)->setCellValue('L'.$idx, $product->getTVvalue(16));       // Состав
            $objPHPExcel->getSheet(1)->setCellValue('M'.$idx, $product->getTVvalue(17));       // Страна
            $objPHPExcel->getSheet(1)->setCellValue('N'.$idx, $product->getTVvalue(18));       // Сезон
            $objPHPExcel->getSheet(1)->setCellValue('O'.$idx, $product->getTVvalue(19));       // Коллекция
            $objPHPExcel->getSheet(1)->setCellValue('P'.$idx, $product->getTVvalue(8));       // Новинка
            $objPHPExcel->getSheet(1)->setCellValue('Q'.$idx, $product->getTVvalue(25) ? $this->modx->getObject('modResource',$product->getTVvalue(25))->pagetitle : '');       // Таблица размеров
            
            $options = $product->getTVvalue('options');
            if($options){
                $options = json_decode($options,1);
                
                $i = 0;
                $count = count($options);
                foreach($options as $option){
                    
                    $i++;
                    
                    $objPHPExcel->getSheet(1)->setCellValue('I'.$idx, $option['color']);
                    $objPHPExcel->getSheet(1)->setCellValue('J'.$idx, $option['sizes']);
                    
                    if($count > $i) {
                        $idx++; 
                    }
                }
            }
            // print "\n".$idx;
            
            // die("OK");
            
            $idx++;
        }
        
        // $data = array();
        // $fieldlist = array();
        // $fields = $this->modx->getFields('modResource');
        // foreach ($fields as $k => $v) {
        //     $fieldlist[] = $k;
        // }
        // $data[] = $fieldlist;
        // $resources = $this->modx->getCollection('modResource');
        // foreach ($resources as $res) {
        //     $data[] = $res->toArray();
        // }
        return $this->saveFile($filename, $objPHPExcel);
        
    }
    
    protected function StepSaveRecord()
    {
        // $filename = 'export.xlsx';
        // $filename = 'export-' .date('Ymd_His'). '.xlsx';
        $this->SaveRecord($this->filename);

        return $this->nextStep('modimporter_deactivate', 'Запись экспорта успешно добавлена');
    }
    
    protected function saveFile($filename, $objPHPExcel)
    {
        
        $exportPath = MODX_BASE_PATH.'assets/export/';
        $file = $exportPath.$filename;
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $objWriter->save($file);
        return $this->afterFileSave($filename, $exportPath);
    }
    
    
    
}

return 'modImporterExportPairXlsxConsoleProcessor';
