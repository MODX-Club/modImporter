<?php

require_once dirname(dirname(__FILE__)) . '/xml/console.class.php';


class modModimporterImportWisellConsoleProcessor extends modModimporterImportConsoleProcessor{

    
       
    public function initialize(){
            
        $this->setDefaultProperties(array(
            # Временно
            "filename"      => basename($this->getProperty("file")), 
            "category_tpl"      => 2,       // Шаблон категории
            "categories_parent" => 160,  // Корневой раздел каталога
            "product_tpl"       => 3,        // Шаблон товара
            "save_path"         => '/assets/images/yml/',
            "image_url"         => 'yml/'
        ));
        
        
        return parent::initialize();
    }
    
    
    protected function StepWriteTmpCategories(){
        
        if(!$reader = & $this->getReader()){
            return $this->failure('Не был получен ридер');
        }
        
        if(!$filename = $this->getProperty('filename')){
            return $this->failure("Не был указан файл");
        }
        
        
        $schema = array(
            "yml_catalog"   => array(
                "shop"  => array(
                    "categories"    => array(
                        "category"     => array(
                            "parse" => true,
                        ),
                    ),
                ),
            ),
        );
        
        
        $depth = 0;
        
        $parents = array();
        
        $categoryId = false;
        $objects = array();
        
        $result = $reader->read(array(
            "file" => $this->getImportPath().$filename,
        ), function(modImporterXmlReader $reader) use (& $schema, & $depth, & $categoryId, & $objects, & $parents){
            
            $xmlReader = & $reader->getReader();
            
            $node = $reader->getNodeName($xmlReader);            
    
            if(
                !$reader->isNodeText($xmlReader) 
                AND $reader->getSchemaNodeByKey($schema, $node) 
                AND $reader->isNode($node, $xmlReader) 
                AND isset($schema["parse"]) && $schema["parse"]
            ){       
                
                
                if($xml = $reader->getXMLNode($xmlReader)){
                    
                    $attributes = $xml->attributes();
                    
                    $id = (string)$attributes['id'];
                    
                    $title = (string)$xml;
                    $title = explode('/', $xml);
                    $pagetitle = trim($title[count($title)-1]);
                    
                    if($attributes['parentId'] > 0){
                        $parent_id = $attributes['parentId'];
                    }else{
                        $parent_id = 160;
                    }
                    
                    $data = array(
                        "tmp_parent" => $parent_id,
                        "tmp_title" => $pagetitle,
                        "tmp_import_id" => 1,
                        "tmp_raw_data"  => array(
                            "pagetitle" => $pagetitle
                        ),
                    );
                    
                    
                    $object = $this->createImportObject($id, $data, "category");
                    $object->save();
                }
                else{
                    $this->modx->log(1, "[".__CLASS__."] Ошибка разбора элемента");
                }
                
                
                $xmlReader->next();
                return true;
            }           
            
            return true;
        });
            
        
        if($result !== true AND $result !== false){
            return $this->failure($result);
        }
        
        return parent::StepWriteTmpCategories();
    }
    
    
    protected function StepWriteTmpGoods(){
        
        if(!$reader = & $this->getReader()){
            return $this->failure('Не был получен ридер');
        }
        
        if(!$filename = $this->getProperty('filename')){
            return $this->failure("Не был указан файл");
        }
        
        
        $schema = array(
            "yml_catalog"   => array(
                "shop"  => array(
                    "offers"    => array(
                        "offer"     => array(
                            "parse" => true,
                        ),
                    ),
                ),
            ),
        );
        
        $limit = $this->getProperty("limit", 5000);
        $count = 0;
        $this->setSessionValue("inserted", 0);
        if(!$inserted = (int)$this->getSessionValue("inserted")){
            $inserted = 0;
        }
        
        $next_step = false;
        
        $depth = 0;
        
        $parents = array();
        
        $categoryId = false;
        $objects = array();
        
        $result = $reader->read(array(
            "file" => $this->getImportPath().$filename,
        ), function(modImporterXmlReader $reader) use (& $schema, & $depth, & $categoryId, & $objects, & $parents, $limit, &$count, &$inserted, &$next_step){
            
            $xmlReader = & $reader->getReader();
            
            $node = $reader->getNodeName($xmlReader);            
            
            if(
                !$reader->isNodeText($xmlReader) 
                AND $reader->getSchemaNodeByKey($schema, $node) 
                AND $reader->isNode($node, $xmlReader) 
                AND isset($schema["parse"]) && $schema["parse"]
            ){       
                
                // Счетчик прочтенных элементов
                $count++;
                
                // Если меньше количества прочтенных, идем дальше
                if($count <= $inserted){
                    $xmlReader->next();
                    return true;
                }
                
                
                if($xml = $reader->getXMLNode($xmlReader)){
                    
                    $attributes = $xml->attributes();
                    
                    $id = (string)$attributes['id'];
                    
                    foreach ($xml->param as $param) {
                        switch((string) $param['name']) { // Получение атрибутов элемента по индексу
                            case 'Размер':
                                $size = (string)$param;
                                break;
                            case 'Цвет':
                                $color = (string)$param;
                            case 'Материал':
                                $material = (string)$param;
                        }
                    }
                    
                    $images = array();
                    
                    foreach($xml->picture as $picture){
                        $images[] = (string)$picture;
                    }
                    
                    $data = array(
                        "tmp_import_id" => 1,
                        "tmp_parent" => $xml->categoryId,
                        "tmp_title" => (string)$xml->name,
                        "tmp_raw_data"  => array(
                            "pagetitle" => (string)$xml->name,
                            "description" => (string)$xml->description,
                            "price" => (string)$xml->price,
                            "images" => json_encode($images),
                            "size" => $size,
                            "color" => $color,
                            "material" => $material,
                            "content" => (string)$xml->description,
                        ),
                    );
                    
                    $object = $this->createImportObject($id, $data, "product");
                        
                    $object->save();
                }
                else{
                    $this->modx->log(1, "[".__CLASS__."] Ошибка разбора элемента");
                }
                
                
                $inserted++;
                
                
                // Если счетчик достиг лимита, обрываем выполнение.
                if($inserted%$limit === 0){
                    $this->setSessionValue("inserted", $inserted);
                    $next_step = true;
                    return false;
                }
                
                $xmlReader->next();
                return true;
            }           
            
            return true;
        });
            
        
        if($next_step){
            return $this->progress("Прочитано {$inserted} товаров.", null, xPDO::LOG_LEVEL_DEBUG);
        }
        
        if($result !== true AND $result !== false){
            return $this->failure($result);
        }
        
        return parent::StepWriteTmpGoods();
    }
    
    protected function StepImportUpdateGoods(){
        
        $q = $this->prepareGetGoodQuery();
        
        $limit = $this->getProperty("limit", 20);
        
        if(!$processed = (int)$this->getSessionValue("goods_processed")){
            $processed = 0;
        }
        
        while($tmp_object = $this->modx->getObject("modImporterObject", $q)){
            
            $tmp_object->tmp_resource_id = $tmp_object->category_id;
            $tmp_object->tmp_processed = 1;
            $tmp_object->save();
            
            $data = $tmp_object->toArray();
            
            
            // Подготавливаем конечные данные категории
            $data = $this->prepareGoodUpdateData($data);

            $response = $this->modx->runProcessor('resource/update', $data);
            
            if($response->isError()){
                $tmp_object->tmp_error = 1;
                $tmp_object->tmp_error_msg = json_encode($response->getResponse());
                $tmp_object->save();
            }
            
            $object = $response->getObject();

            $resource = $this->modx->getObject('modResource', $object['id']);
            
            $migx = array();    
            $i = 0;
            
            if($data['tmp_raw_data']['size']){
                $sizes = explode(',',$data['tmp_raw_data']['size']);
                foreach($sizes as $size){                   
                    $i++;
                    $migx[] = array(
                        "MIGX_id"   => $i,
                        "size"      => trim($size),
                        "price"      => $data['tmp_raw_data']['price'],
                        "remains"      => 1,
                    );
                }
            }
            
            if($migx){
                $resource->setTVValue(11, json_encode($migx));
            }
            
            if($data['color']){
                $resource->setTVValue(12, $data['color']);
            }
            
            if($data['material']){
                $resource->setTVValue(13, $data['material']);
            }
            
            $gallery = array();    
            $j = 0;
            
            if($data['images']){
                foreach($data['images'] as $image){                   
                    $j++;
                    $gallery[] = array(
                        "MIGX_id"   => $j,
                        "image"      => $image,
                    );
                }
            }
            
            if($gallery){
                $resource->setTVValue(10, json_encode($gallery));
            }
            
            $processed++;
            
            if($limit AND $processed%$limit === 0){
                $this->setSessionValue("goods_processed", $processed);
                return $this->progress("Обновлено {$processed} товаров");
            }
        }
        
        
        return parent::StepImportUpdateGoods();
    }
    
    
    protected function prepareGetGoodQuery(){
        
        $q = $this->modx->newQuery("modImporterObject");
        
        $alias = $q->getAlias();
        
        $q->innerJoin("modResource", "product", "product.externalKey = {$alias}.tmp_external_key AND product.importId = {$alias}.tmp_import_id");
        
        $q->where(array(
            "tmp_object_type" => "product",
            "tmp_processed" => 0,
        ));
        
        $columns = $this->modx->getSelectColumns("modResource", "product", '', array('id', 'class_key'), true);
        $columns = explode(", ", $columns);
        
        $q->select($columns);
        
        $q->select(array(
            "product.id as product_id",
            "product.class_key as resource_class_key",
            "{$alias}.*",
        ));
        
        $q->limit(1);
        
        return $q;
    }
    
    
    protected function prepareGoodUpdateData(array $data){
        $content = $data['tmp_raw_data'];
        
        $images = json_decode($content['images'],1);
        $images_urls = array();
        
        foreach($images as $image){
            $remote_img = $image;
            $filename = basename($remote_img);
            $image_url = $this->getProperty('image_url').$filename;
            $save_path = $_SERVER['DOCUMENT_ROOT'].$this->getProperty('save_path').$filename;
            
            if(file_exists($save_path)){
                unlink($save_path);
            }
            
            if($img_content = file_get_contents($remote_img)){
                if(file_put_contents($save_path, $img_content)){
                    $images_urls[] = $image_url;
                }
            }
        }        
        
        $content['images'] = $images_urls;
        //copy($remote_img, $save_path);
        
        
        $data = array_merge($data, array(
            "id"            => $data['product_id'],       // Устанавливаем id документа
            "published"     => 1
        ));
        
        $data = array_merge($data, $content);
        
        return $data;
    }
    
    
    protected function StepImportCreateGoods(){
        
        $q = $this->modx->newQuery("modImporterObject");
        
        $alias = $q->getAlias();
        
        $q->innerJoin("modResource", "Category", "Category.externalKey = {$alias}.tmp_parent AND Category.importId = {$alias}.tmp_import_id");
        $q->leftJoin("modResource", "Resource", "Resource.externalKey = {$alias}.tmp_external_key");
        
        $q->where(array(
            "tmp_object_type" => "product",
            "tmp_processed" => 0,
            "Resource.id"   => null,
        ));
        
        $q->select(array(
            "{$alias}.*",     
            "Category.id as parent",     // ID категории
            "{$alias}.tmp_external_key as externalKey",     // Артикул в документ
        ));
        
        $q->limit(1);
        
        $limit = $this->getProperty("limit", 20);
        
        if(!$processed = (int)$this->getSessionValue("goods_created")){
            $processed = 0;
        }
        
        
        while($tmp_object = $this->modx->getObject("modImporterObject", $q)){
            
            # print_r($tmp_object->toArray());
            # 
            # break;
            
            // Отмечаем временную запись как отработанную
            $tmp_object->tmp_processed = 1;
            $tmp_object->save();
            
            $data = $tmp_object->toArray();
            
            $data = $this->prepareGoodCreateData($data);
            
            # print_r($data);
            # break;
            
            $response = $this->modx->runProcessor('resource/create', $data);
            
            if($response->isError()){
                $tmp_object->tmp_error = 1;
                $tmp_object->tmp_error_msg = json_encode($response->getResponse());
                $tmp_object->save();
                return $response->getResponse();
            }
            
            $object = $response->getObject();
            
            $tmp_object->tmp_resource_id = $object['id'];
            $tmp_object->save();
            
            # print_r($object);
            # print_r($tmp_object->toArray());
            
            if($response->isError()){
                $tmp_object->tmp_error = 1;
                $tmp_object->tmp_error_msg = json_encode($response->getResponse());
                $this->modx->error->reset();
            }
            else{
                
                $object = $response->getObject();
                $tmp_object->tmp_resource_id = $object['id'];
                
            }
            
            $tmp_object->save();
            
            $resource = $this->modx->getObject('modResource', $object['id']);
            $migx = array();    
            $i = 0;
            
            if($data['size']){
                $sizes = explode(',',$data['size']);
                foreach($sizes as $size){                   
                    $i++;
                    $migx[] = array(
                        "MIGX_id"   => $i,
                        "size"      => trim($size),
                        "price"      => $data['price'],
                        "remains"      => 1,
                    );
                }
            }
            
            if($migx){
                $resource->setTVValue(11, json_encode($migx));
            }
            
            if($data['color']){
                $resource->setTVValue(12, $data['color']);
            }
            
            if($data['material']){
                $resource->setTVValue(13, $data['material']);
            }
            
            $gallery = array();    
            $j = 0;
            
            if($data['images']){
                foreach($data['images'] as $image){                   
                    $j++;
                    $gallery[] = array(
                        "MIGX_id"   => $j,
                        "image"      => $image,
                    );
                }
            }
            
            if($gallery){
                $resource->setTVValue(10, json_encode($gallery));
            }
            
            $processed++;
            
            if($limit AND $processed%$limit === 0){
                $this->setSessionValue("goods_created", $processed);
                return $this->progress("Создано {$processed} товаров");
            }
            
            # break;
        }
        
        return parent::StepImportCreateGoods();
    }
    
    
    protected function prepareGoodCreateData(array $data){
        
        $template = $this->getProperty('product_tpl');
        $content = $data['tmp_raw_data'];
        
        $images = json_decode($content['images'],1);
        $images_urls = array();
        
        foreach($images as $image){
            
            $remote_img = $image;
            $filename = basename($remote_img);
            $image_url = $this->getProperty('image_url').$filename;
            $save_path = $_SERVER['DOCUMENT_ROOT'].$this->getProperty('save_path').$filename;
            
            if(file_exists($save_path)){
                unlink($save_path);
            }
            
            if($img_content = file_get_contents($remote_img)){
                if(file_put_contents($save_path, $img_content)){
                   $images_urls[] = $image_url;
                }
            }
        }
        
        $content['images'] = $images_urls;
        
        $data = array_merge($data, array(
            "class_key" => 'ShopmodxResourceProduct',
            "published" => 1,
            "isfolder"  => 0,
            "template"  => 3,
            "sm_currency"  => 79,
            "sm_price"  => 0,
            "importId"  => 1,
        ));
        
        $data = array_merge($data, $content);
        
        return $data;
    }
    
    
    protected function StepImportUpdateCategories(){
        
        /*
            Получаем только те временные данные, для которых есть имеющиеся категории
        */
        $q = $this->prepareGetCategoryQuery();
        
        
        while($tmp_object = $this->modx->getObject("modImporterObject", $q)){
            
            $tmp_object->tmp_resource_id = $tmp_object->category_id;
            $tmp_object->tmp_processed = 1;
            $tmp_object->save();
            
            $data = $tmp_object->toArray();
            
            
            // Подготавливаем конечные данные категории
            $data = $this->prepareCategoryUpdateData($data);
            $response = $this->modx->runProcessor('resource/update', $data);
            
            if($response->isError()){
                $tmp_object->tmp_error = 1;
                $tmp_object->tmp_error_msg = json_encode($response->getResponse());
                $tmp_object->save();
            }else{
                $response = $response->getResponse();
                $category_id = $response['object']['id'];
            }
            
            
            
        }
        
        return parent::StepImportUpdateCategories();
    }
    
    protected function prepareGetCategoryQuery(){
        
        $q = $this->modx->newQuery("modImporterObject");
        
        $alias = $q->getAlias();
        
        $q->innerJoin("modResource", "category", "category.externalKey = {$alias}.tmp_external_key AND category.importId = {$alias}.tmp_import_id");
        
        $q->where(array(
            "tmp_object_type" => "category",
            "tmp_processed" => 0,
        ));
        
        $columns = $this->modx->getSelectColumns("modResource", "category", '', array('id', 'class_key'), true);
        $columns = explode(", ", $columns); 
        
        $q->select($columns);
        
        $q->select(array(
            "category.id as category_id",
            "category.class_key as resource_class_key",
            "category.parent as category_parent",
            "{$alias}.*",
        ));
        
        $q->limit(1);
        
        return $q;
    }
    
    
    protected function prepareCategoryUpdateData(array $data){
        if($data['tmp_parent']){
            $parent = $this->modx->getObject('modResource', array('externalKey'=>$data['tmp_parent']))->id;
        }else{
            $parent = $data['category_parent'];
        }
        $data = array_merge($data, array(
            "id"         	=> $data['category_id'],       // Устанавливаем id документа
            "pagetitle"     => $data['tmp_raw_data']['pagetitle'],
            "published"     => 1,
            "parent"        => $parent,
        ));
        
        return $data;
    }
    
    
    protected function StepImportCreateCategories(){
        
        $q = $this->modx->newQuery("modImporterObject");
        
        $alias = $q->getAlias();
        
        $q->where(array(
            "tmp_object_type" => "category",
            "tmp_processed" => 0,
        ));
        
        $q->select(array(
            "{$alias}.*",     
            "{$alias}.tmp_external_key as externalKey",     // Артикул в документ
        ));
        
        $q->limit(1);
        
        $limit = $this->getProperty("limit", 100);
        
        if(!$processed = (int)$this->getSessionValue("category_processed")){
            $processed = 0;
        }
        
        
        while($tmp_object = $this->modx->getObject("modImporterObject", $q)){
            
            // Отмечаем временную запись как отработанную
            $tmp_object->tmp_processed = 1;
            $tmp_object->save();
            
            $data = $tmp_object->toArray();
            
            $data = $this->prepareCategoryCreateData($data);
            
            $response = $this->modx->runProcessor('resource/create', $data);
            
            if($response->isError()){
                $tmp_object->tmp_error = 1;
                $tmp_object->tmp_error_msg = json_encode($response->getResponse());
                $tmp_object->save();
                return $response->getResponse();
            }
            
            $object = $response->getObject();
            $tmp_object->tmp_resource_id = $object['id'];
            $tmp_object->save();
            $category_id = $object['id'];
            # print_r($object);
            
            $processed++;
            
            if($limit AND $processed%$limit === 0){
                $this->setSessionValue("category_processed", $processed);
                return $this->progress("Создано {$processed} категорий");
            }
        }
        
        return parent::StepImportCreateCategories();
    }
    
    
    protected function prepareCategoryCreateData(array $data){
        
        $parent = $this->getProperty('categories_parent');
        $template = $this->getProperty('category_tpl');
        
        $data = array_merge($data, array(
            "parent"    => $parent,
            "template"    => $template,
            "pagetitle"     => $data['tmp_raw_data']['pagetitle'],
            "isfolder"     => 1,
            "published"     => 1,
            "alias"          => $data['tmp_external_key'] .'-'.$data['tmp_raw_data']['pagetitle'],
            "importId"      => 1,
        ));
        
        return $data;
    }
    
    
        
}

return 'modModimporterImportWisellConsoleProcessor';
