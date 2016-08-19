<?php

require_once dirname(dirname(dirname(__FILE__))).'/console.class.php';
require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/lib/PHPExcel/Classes/PHPExcel.php';

class modModimporterImportPairXlsxConsoleProcessor extends modModimporterImportConsoleProcessor
{
    public function initialize()
    {
        $this->setDefaultProperties(array(
            # Временно
            'filename' => basename($this->getProperty('file')),
        ));

        return parent::initialize();
    }

    protected function StepWriteTmpCategories()
    {

        if (!$filename = $this->getProperty('filename')) {
            return $this->failure('Не был указан файл');
        }
        
        $mediasource = $this->modx->getObject('modMediaSource', array('id' => $this->modx->getOption('modimporter.media_source')));
        $properties = $mediasource->get('properties');
        $basePath = $properties['basePath']['value'];
                
        $filepath = MODX_BASE_PATH.$basePath.$filename;
        
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objReader->setReadDataOnly(TRUE);
        $objPHPExcel = $objReader->load($filepath);
        
        $objWorksheet = $objPHPExcel->getSheet(0);
        
        $highestRow = $objWorksheet->getHighestRow(); 
        $highestColumn = $objWorksheet->getHighestColumn(); 
        $highestColumn++;
        
        $columnMap = array();
        for ($col = 'A'; $col != $highestColumn; ++$col) {
            array_push($columnMap, $objWorksheet->getCell($col . 1)->getValue());
        }
        
        for ($row = 2; $row <= $highestRow; ++$row) {
            $colData = array();
            for ($col = 'A'; $col != $highestColumn; ++$col) {
                $colData[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $categoryData = array();
            $categoryData = array_combine($columnMap, $colData);
            // return print_r($categoryData);
            $id = $categoryData['externalKey'];
            $data = array(
                'tmp_import_id' => 1,
                'tmp_parent' => $categoryData['parent'],
                'tmp_title' => $categoryData['pagetitle'],
                'tmp_raw_data' => $categoryData
            );
            $object = $this->createImportObject($id, $data, 'category');
            $object->save();
        }
        
        $objects = array();
        return parent::StepWriteTmpCategories();
    }
    
    // Создаем категории
    protected function StepImportCreateCategories(){
        
        $q = $this->modx->newQuery("modImporterObject");
        
        $q->where(array(
            "tmp_object_type" => "category",
            "tmp_processed" => 0,
        ));
        
        $q->limit(1);
        
        while($tmp_object = $this->modx->getObject("modImporterObject", $q)){
            $tmp_object->tmp_processed = 1;
            $tmp_object->save();
            
            $data = array(
                "pagetitle" => $tmp_object->tmp_title,
                "parent"    => $tmp_object->tmp_parent,
                "template"      => 2,
                "published"     => 1,
                "isfolder"      => 1,
            );
            
            $response = $this->modx->runProcessor('resource/create', $data);
            
            if($response->isError()){
                $tmp_object->tmp_error = 1;
                $tmp_object->tmp_error_msg = json_encode($response->getResponse());
                $tmp_object->save();
                return $response->getResponse();
            }
        }
        
        return parent::StepImportCreateCategories();
    }

    protected function StepWriteTmpGoods()
    {
        
        
        if (!$filename = $this->getProperty('filename')) {
            return $this->failure('Не был указан файл');
        }

        $limit = $this->getProperty('limit', 100);
        $count = 0;
        // $this->setSessionValue('inserted', 0);
        // if (!$inserted = (int) $this->getSessionValue('inserted')) {
        //     $inserted = 0;
        // }
        
        // $this->setSessionValue('rowNum', 2);
        // if (!$rowNum = (int) $this->getSessionValue('rowNum')) {
        //     $rowNum = 2;
        // }

        $next_step = false;

        $mediasource = $this->modx->getObject('modMediaSource', array('id' => $this->modx->getOption('modimporter.media_source')));
        $properties = $mediasource->get('properties');
        $basePath = $properties['basePath']['value'];
                
        $filepath = MODX_BASE_PATH.$basePath.$filename;
        
        $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        $objReader->setReadDataOnly(TRUE);
        $objPHPExcel = $objReader->load($filepath);
        
        $objWorksheet = $objPHPExcel->getSheet(1);
        
        $highestRow = $objWorksheet->getHighestRow(); 
        $highestColumn = $objWorksheet->getHighestColumn(); 
        $highestColumn++;
        
        $columnMap = array();
        for ($col = 'A'; $col != $highestColumn; ++$col) {
            array_push($columnMap, $objWorksheet->getCell($col . 1)->getValue());
        }

        $rowNum = 2;        
        
        // print $highestRow;
        // exit;
        
        for ($row = $rowNum; $row <= $highestRow; ++$row) {
            $colData = array();
            for ($col = 'A'; $col != $highestColumn; ++$col) {
                $colData[] = $objWorksheet->getCell($col . $row)->getValue();
            }
            $productData = array();
            $productData = array_combine($columnMap, $colData);
            
            
            // print_r($colData);
            // print_r($productData);
            
            // break;
            
            // continue;
            
            $sizes = array();
            
            $sizes[$productData['color']] = $productData['sizes'];
            if($row != $highestRow){
                
                // print $objWorksheet->getCell("A" . (1+$row))->getValue();
                // exit;
                while($objWorksheet->getCell("B" . (1+$row))->getValue() == '' && $row < $highestRow){
                    $row++;
                    
                    if($color = $objWorksheet->getCell("I" . $row)->getValue()){
                        
                        $sizes[$color] = $objWorksheet->getCell("J" . $row)->getValue();
                    }
                    
                    
                    // print "\n$row";
                    
                }
            }
     
            // return print_r($productData);
            $id = $productData['externalKey'];
            $productData['sizes'] = $sizes;
            $data = array(
                'tmp_import_id' => 1,
                'tmp_parent' => $productData['parent'],
                'tmp_title' => $productData['pagetitle'],
                'tmp_raw_data' => $productData
            );
            $object = $this->createImportObject($id, $data, 'product');
            $object->save(); 
            // Если счетчик достиг лимита, обрываем выполнение.
            // $inserted++;
            // if ($inserted % $limit === 0) {
            //     $this->setSessionValue('inserted', $inserted);
            //     $this->setSessionValue('rowNum', $row);
            //     $next_step = true;
    
            //     return false;
            // }
            
            // die("sdfsdf");
        }
        
        // $objects = array();

        


        // if ($next_step) {
        //     return $this->progress("Прочитано {$inserted} товаров.", null, xPDO::LOG_LEVEL_DEBUG);
        // }

        return parent::StepWriteTmpGoods();
    }

    protected function StepImportUpdateGoods()
    {
        $q = $this->prepareGetGoodQuery();

        $limit = $this->getProperty('limit', 20);

        if (!$processed = (int) $this->getSessionValue('goods_processed')) {
            $processed = 0;
        }

        while ($tmp_object = $this->modx->getObject('modImporterObject', $q)) {
            
            $tmp_object->tmp_resource_id = $tmp_object->product_id;
            $tmp_object->tmp_processed = 1;
            $tmp_object->save();
            
            if($resource = $this->modx->getObject("modResource", $tmp_object->product_id)){
                
    
                $data = $tmp_object->toArray();
    
                // Подготавливаем конечные данные категории
                $data = $this->prepareGoodUpdateData($data);
    
                foreach($data as $key => $val){
                    if(preg_match('/^tv([0-9]+)$/', $key, $match)){
                        if($tv_id = (int)$match[1]){
                            
                            # print_r($doc->id);
                            # print_r($val);
                            # print_r($tv_id);
                            $resource->setTVValue($tv_id, $val);
                        }
                    }
                }
                
                $response = $this->modx->runProcessor('resource/update', $data);
    
                if ($response->isError()) {
                    $tmp_object->tmp_error = 1;
                    $tmp_object->tmp_error_msg = json_encode($response->getResponse());
                    $tmp_object->save();
                }
                // else{
    
                //     $object = $response->getObject();
        
                //     $resource = $this->modx->getObject('modResource', $object['id']);
                    
                //     if($resource){
                //         foreach($data as $key => $val){
                //             if(preg_match('/^tv([0-9]+)$/', $key, $match)){
                //                 if($tv_id = (int)$match[1]){
                                    
                //                     # print_r($doc->id);
                //                     # print_r($val);
                //                     # print_r($tv_id);
                //                     $resource->setTVValue($tv_id, $val);
                //                 }
                //             }
                //         }
                //     }
                // }
            }
            else{
                $tmp_object->tmp_error = 1;
                $tmp_object->tmp_error_msg = "Не был получен документ";
                $tmp_object->save();
            }
            

            // $migx = array();
            // $i = 0;
            
            

            // if ($sizes = $data['sizes']) {
            //     foreach ($sizes as $k => $size) {
            //         ++$i;
            //         $migx[] = array(
            //             'MIGX_id' => $i,
            //             'color' => $k,
            //             'image' => '',
            //             'sizes' => $size
            //         );
                    
                    
            //     }
            // }
            // if ($migx && $resource) {
            //     $resource->setTVValue(13, json_encode($migx));
            // }
/*
            if ($data['color']) {
                $resource->setTVValue(12, $data['color']);
            }

            if ($data['material']) {
                $resource->setTVValue(13, $data['material']);
            }

            $gallery = array();
            $j = 0;

            if ($data['images']) {
                foreach ($data['images'] as $image) {
                    ++$j;
                    $gallery[] = array(
                        'MIGX_id' => $j,
                        'image' => $image,
                    );
                }
            }

            if ($gallery) {
                $resource->setTVValue(10, json_encode($gallery));
            }*/

            ++$processed;

            if ($limit and $processed % $limit === 0) {
                $this->setSessionValue('goods_processed', $processed);

                return $this->progress("Обновлено {$processed} товаров");
            }
        }

        return parent::StepImportUpdateGoods();
    }
    
    // Создаем товары
    protected function StepImportCreateGoods(){
        
        $q = $this->modx->newQuery("modImporterObject");
        
        $alias = $q->getAlias();
        
        # $q->innerJoin("modResource", "category", "category.parent = 1143 AND category.template IN (2,16) AND category.pagetitle = modImporterObject.tmp_parent");
        
        $q->where(array(
            "tmp_object_type" => "product",
            "tmp_processed" => 0,
        ));
        
        $q->select(array(
            "{$alias}.*",
            "tmp_title as pagetitle",
            // "tmp_title as longtitle",
            # "category.id as parent",
            "tmp_parent as parent",
        ));
        
        $q->limit(1);
        
        $limit = $this->getProperty("limit", 100);
        
        if(!$inserted = (int)$this->getSessionValue("goods_inserted")){
            $inserted = 0;
        }
        
        while($tmp_object = $this->modx->getObject("modImporterObject", $q)){
            
            $tmp_object->tmp_processed = 1;
            $tmp_object->save();
            
            $data = array_merge($tmp_object->toArray(), array(
                // "class_key" => 'ShopmodxResourceProduct',
                "published" => 1,
                "isfolder"  => 0,
                "template"  => 3,
                "currency"  => 79,
            ));
            
            
            $raw_data = $data['tmp_raw_data'];
            
            $data['alias'] = "{$data['pagetitle']}-{$raw_data['article']}";
            
            // $data = array_merge($data, $raw_data);
            
            
            // $data = $tmp_object->toArray();

            // Подготавливаем конечные данные категории
            $data = $this->prepareGoodUpdateData($data);
            
            // $migx = array();
            // $i = 0;
            
            // if (!empty($data['sizes']) AND $sizes = $data['sizes']) {
            //     foreach ($sizes as $k => $size) {
            //         ++$i;
            //         $migx[] = array(
            //             'MIGX_id' => $i,
            //             'color' => $k,
            //             'image' => '',
            //             'sizes' => $size
            //         );
            //     }
            // }
            // if ($migx) {
            //     $data["tv13"] = json_encode($migx);
            // }
            
            

            // $response = $this->modx->runProcessor('resource/create', $data);
            
            // if($response->isError()){
            //     $tmp_object->tmp_error = 1;
            //     $tmp_object->tmp_error_msg = json_encode($response->getResponse());
            //     $tmp_object->save();
            //     return $response->getResponse();
            // }
            
            // $object = $response->getObject();
            // $tmp_object->tmp_resource_id = $object['id'];
            // $tmp_object->save();

            $this->modx->error->reset();
            $response = $this->modx->runProcessor('resource/create', $data);
            
            if($response->isError()){
                $tmp_object->tmp_error = 1;
                $tmp_object->tmp_error_msg = json_encode($response->getResponse());
                $tmp_object->save();
                // return $response->getResponse();
            }
            else{
                $object = $response->getObject();
                $tmp_object->tmp_resource_id = $object['id'];
                $tmp_object->save();
            }
            
            $inserted++;
            
            if($limit AND $inserted%$limit === 0){
                $this->setSessionValue("goods_inserted", $inserted);
                return $this->progress("Создано {$inserted} новых товаров");
            }
            
        }
        
        return parent::StepImportCreateGoods();
    }

    protected function prepareGetGoodQuery()
    {
        $q = $this->modx->newQuery('modImporterObject');

        $alias = $q->getAlias();

        $q->innerJoin('modResource', 'product', "product.externalKey = {$alias}.tmp_external_key");

        $q->where(array(
            'tmp_object_type' => 'product',
            'tmp_processed' => 0,
        ));

        $columns = $this->modx->getSelectColumns('modResource', 'product', '', array('id', 'class_key'), true);
        $columns = explode(', ', $columns);

        $q->select($columns);

        $q->select(array(
            'product.id as product_id',
            'product.class_key as resource_class_key',
            "{$alias}.*",
        ));

        $q->limit(1);

        return $q;
    }

    protected function prepareGoodUpdateData(array $data)
    {
        $content = $data['tmp_raw_data'];

        /*$images = json_decode($content['images'], 1);
        $images_urls = array();

        foreach ($images as $image) {
            $remote_img = $image;
            $filename = basename($remote_img);
            $image_url = $this->getProperty('image_url').$filename;
            $save_path = $_SERVER['DOCUMENT_ROOT'].$this->getProperty('save_path').$filename;

            if (file_exists($save_path)) {
                unlink($save_path);
            }

            if ($img_content = file_get_contents($remote_img)) {
                if (file_put_contents($save_path, $img_content)) {
                    $images_urls[] = $image_url;
                }
            }
        }

        $content['images'] = $images_urls;*/
        //copy($remote_img, $save_path);

        $data = array_merge($data, array(
            'id' => $data['product_id'],       // Устанавливаем id документа
            'published' => 1,
        ));

        $data = array_merge($data, $content);


        $migx = array();
        $i = 0;
        
        if ($sizes = $data['sizes']) {
            foreach ($sizes as $k => $size) {
                ++$i;
                $migx[] = array(
                    'MIGX_id' => $i,
                    'color' => $k,
                    'image' => '',
                    'sizes' => $size
                );
                
                
            }
        }
        
        if($o = $this->modx->getObject('modResource',array('pagetitle'=>$data['Таблица размеров']))) {
            $size_table = $o->id;
        }
        
        $data = array_merge($data, array(
            "tv13"  => json_encode($migx),
            "tv16"  => trim($data['Состав']),
            "tv17"  => trim($data['Страна производства']),
            "tv18"  => trim($data['Сезон']),
            "tv19"  => trim($data['Коллекция']),
            "tv8"   => trim($data['Новинка']),
            "tv25"  => trim($size_table),
        ));
        
        // var_dump($data);
        
        // exit;

        return $data;
    }

    
    protected function StepImportUpdateCategories()
    {

        /*
            Получаем только те временные данные, для которых есть имеющиеся категории
        */
        $q = $this->prepareGetCategoryQuery();

        while ($tmp_object = $this->modx->getObject('modImporterObject', $q)) {
            $tmp_object->tmp_resource_id = $tmp_object->category_id;
            $tmp_object->tmp_processed = 1;
            $tmp_object->save();

            $data = $tmp_object->toArray();

            // Подготавливаем конечные данные категории
            $data = $this->prepareCategoryUpdateData($data);
            $response = $this->modx->runProcessor('resource/update', $data);

            if ($response->isError()) {
                $tmp_object->tmp_error = 1;
                $tmp_object->tmp_error_msg = json_encode($response->getResponse());
                $tmp_object->save();
            } else {
                $response = $response->getResponse();
                $category_id = $response['object']['id'];
            }
        }

        return parent::StepImportUpdateCategories();
    }

    protected function prepareGetCategoryQuery()
    {
        $q = $this->modx->newQuery('modImporterObject');

        $alias = $q->getAlias();

        $q->innerJoin('modResource', 'category', "category.externalKey = {$alias}.tmp_external_key");

        $q->where(array(
            'tmp_object_type' => 'category',
            'tmp_processed' => 0,
        ));

        $columns = $this->modx->getSelectColumns('modResource', 'category', '', array('id', 'class_key'), true);
        $columns = explode(', ', $columns);

        $q->select($columns);

        $q->select(array(
            'category.id as category_id',
            'category.class_key as resource_class_key',
            'category.parent as category_parent',
            "{$alias}.*",
        ));

        $q->limit(1);

        return $q;
    }

    protected function prepareCategoryUpdateData(array $data)
    {
        if ($data['tmp_parent']) {
            $parent = $this->modx->getObject('modResource', array('externalKey' => $data['tmp_parent']))->id;
        } else {
            $parent = $data['category_parent'];
        }
        $data = array_merge($data, array(
            'id' => $data['category_id'],       // Устанавливаем id документа
            'pagetitle' => $data['tmp_raw_data']['pagetitle'],
            'published' => 1,
            'parent' => $parent,
        ));

        return $data;
    }

    
}

return 'modModimporterImportPairXlsxConsoleProcessor';
