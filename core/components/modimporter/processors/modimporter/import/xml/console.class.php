<?php

require_once dirname(dirname(__FILE__)) . '/console.class.php';

class modModimporterImportXmlConsoleProcessor extends modModimporterImportConsoleProcessor{
    
    
    
    protected function StepWriteTmpCommercialInfo(){
        
        if(!$reader = & $this->getReader()){
            return $this->failure('Не был получен ридер');
        }
        
        
        
        if(!$filename = $this->getProperty('filename')){
            return $this->failure("Не был указан файл");
        }
        
        
        if(!$path = $this->getImportPath()){
            
            return $this->failure("Не была получена директория файлов");
        }
        
        
        $schema = array(
            "КоммерческаяИнформация"    => array(
                "Классификатор"     => array(
                    "Владелец"  => array(
                        "parse" => true,
                        "Ид"    => array(
                            "type" => "string",
                            "field" => "good_id",
                        ),
                    ),
                ),
            ),
        );
        
        
        $result = $reader->read(array(
            "file" => $path.$filename,
        ), function(modImporterXmlReader $reader) use (& $schema){
            
            $xmlReader = & $reader->getReader();
            
            $node = $reader->getNodeName($xmlReader);            
            
            if(!$reader->isNodeText($xmlReader) && $reader->getSchemaNodeByKey($schema, $node)){                                
                
                
                if(isset($schema["parse"]) && $schema["parse"]){
                    
                    $xml = $reader->getXMLNode($xmlReader);   
                    
                    if(!$this->writeCommercialInfo($xml)){
                        
                        return "Ошибка сохранения коммерческой информации";
                    }
                    
                    return false;
                }                               
                
            }           
            
            return true;
        });
        
        if($result !== true AND $result !== false){
            return $this->failure($result);
        }
        
        return parent::StepWriteTmpCommercialInfo();
    }
    
    
    
    protected function writeCommercialInfo(SimpleXMLElement $xml){
        
        # print_r($xml);
        
        $o = $this->createImportObject((string)$xml->Ид, array(
            "tmp_raw_data"  => array(
                "title"   => (string)$xml->Наименование,
                "inn"   => (string)$xml->ИНН,
                "okpo"   => (string)$xml->ОКПО,
            ),
        ), "commercial_info");
        
        # print_r($o->toArray());
        
        
        return $o->save();
    } 
    
    
    protected function StepWriteTmpCategories(){
        
        if(!$reader = & $this->getReader()){
            return $this->failure('Не был получен ридер');
        }
        
        if(!$filename = $this->getProperty('filename')){
            return $this->failure("Не был указан файл");
        }
        
        if(!$path = $this->getImportPath()){
            
            return $this->failure("Не была получена директория файлов");
        }
        
        # return $reader->initialize($this);
        
        $schema = array(
            "КоммерческаяИнформация"    => array(
                "Классификатор"     => array(
                    "Группы"  => array(
                        "Группа"    => array(
                            
                            "parse" => true,
                        )
                    ),
                    # "Группы"  => array(
                    #     "parse" => false,
                    # ),
                ),
            ),
        );
        
        
        $depth = 0;
        
        $parents = array();
        
        $categoryId = false;
        $objects = array();
        $groupProperties = array(
            "Наименование"  => "title",
        );
        
        $result = $reader->read(array(
            "file" => $path.$filename,
            # "schema"    => json_decode('{
            #     "КоммерческаяИнформация": {
            #         "parse": true
            #         ,"ПакетПредложений": {}
            #     }
            # }')
        ), function(modImporterXmlReader $reader) use (& $schema, & $depth, & $categoryId, & $objects, & $groupProperties , & $parents){
            
            $xmlReader = & $reader->getReader();
            
            $node = $reader->getNodeName($xmlReader);            
            
            # $schema = json_decode('{
            #     "КоммерческаяИнформация": {
            #         # "parse": true
            #         ,"ПакетПредложений": {}
            #         ,"Классификатор"    
            #     }
            # }');
            
            if(!$reader->isNodeText($xmlReader) && $reader->getSchemaNodeByKey($schema, $node)){                                
                # print "\n". $node;

                if($reader->isNode('Группа', $xmlReader))
                {              
                    $categoryId = true;
                    
                    $depth++;
                }
                
                
                if (
                    $reader->isNode('Ид', $xmlReader)
                    AND $categoryId
                ) 
                {
                    
                    $id = (string)$reader->getXMLNode($xmlReader);
                    
                    $parents[$depth] = $id;
                    
                    $categoryId = $id;
                    
                    $objects[$id] = array(
                        "id"   => $id,
                        "tmp_parent"   => !empty($parents[$depth]) ? $parents[$depth] : null,
                    );
                }
                
                
                if (
                    $reader->isNode($xmlReader->name, $xmlReader)
                    AND array_key_exists($xmlReader->name, $groupProperties)
                    AND $categoryId
                    AND isset($objects[$categoryId])
                ) 
                {
                    
                    $objects[$categoryId][$groupProperties[$xmlReader->name]] = (string)$reader->getXMLNode($xmlReader); 
                }
                
                
                
                if($reader->isNodeEnd('Группа', $xmlReader))
                {
                    $categoryId = false;
                    $depth--;
                }
                                            
                if ($reader->isNodeEnd('Классификатор', $xmlReader)) 
                {
                    
                    // Завершаем парсинг файла
                    return false;
                }
            }           
            
            return true;
        });
            
        # print_r($objects);
        # 
        # return;
        
        # print "\n Abs depth: " . $depth;
        # 
        # print_r($parents);
        
        if($objects){
            
            foreach($objects as $category){
                $category = array_merge($category, array(
                    "tmp_raw_data"  => $category,
                ));
                $o = $this->createImportObject($category["id"], $category, "category");
                $o->save();
            }
        }
            
        
        if($result !== true AND $result !== false){
            return $this->failure($result);
        }
        
        # $this->setSessionValue("STEP", "import_complite");
        # return $this->success("Данные успешно импортированы");
        # return $this->failure("Debug");
        
        return parent::StepWriteTmpCategories();
    }
    
    
    protected function StepWriteTmpGoods(){
        
        if(!$reader = & $this->getReader()){
            return $this->failure('Не был получен ридер');
        }
        
        
        if(!$filename = $this->getProperty('filename')){
            return $this->failure("Не был указан файл");
        }
        
        if(!$path = $this->getImportPath()){
            
            return $this->failure("Не была получена директория файлов");
        }
        
        $schema = array(
            "КоммерческаяИнформация"    => array(
                "Каталог"     => array(
                    "Товары"  => array(
                        "Товар"  => array(
                            "parse" => true,
                        ),
                    ),
                ),
            ),
        );
        
        $result = $reader->read(array(
            "file" => $path.$filename,
        ), function(modImporterXmlReader $reader) use (& $schema){
            
            $xmlReader = & $reader->getReader();
            
            $node = $reader->getNodeName($xmlReader);            
            
            if(!$reader->isNodeText($xmlReader) && $reader->getSchemaNodeByKey($schema, $node) && $reader->isNode($node, $xmlReader)){                           
                
                
                if(isset($schema["parse"]) && $schema["parse"]){
                    
                    $xml = $reader->getXMLNode($xmlReader);   
                    
                    # if(!$this->writeCommercialInfo($xml)){
                    #     
                    #     return "Ошибка сохранения коммерческой информации";
                    # }
                    
                    # print_r($xml);
                    $id = (string)$xml->Ид;
                    # print "\nID: " . $id;
                    $data = array(
                        "tmp_raw_data"  => (array)modImporterXmlReader::toArray($xml),
                    );
                    
                    $object = $this->createImportObject($id, $data, "product");
                    
                    $object->save();
                    
                    # print_r($data);
                    # 
                    # print_r($object->toArray());
                    
                    $xmlReader->next();
                    return true;
                }                               
                
            }           
            
            return true;
        });
        
        if($result !== true AND $result !== false){
            return $this->failure($result);
        }
        
        return parent::StepWriteTmpGoods();
    }
    
}

return 'modModimporterImportXmlConsoleProcessor';
