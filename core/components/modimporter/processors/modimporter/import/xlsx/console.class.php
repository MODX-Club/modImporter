<?php

require_once dirname(dirname(__FILE__)) . '/console.class.php';

class modModimporterImportXlsxConsoleProcessor extends modModimporterImportConsoleProcessor{
    
    
    protected function processRequest(){ 
        
        switch($this->getProperty('modimporter_step')){
            
            case 'modimporter_write_tmp_xlsx_shared_strings':
                
                return $this->StepWriteTmpXlsxSharedStrings();
                break;
            
            
            default:;
        }
        
        return parent::processRequest();
    } 
    
    
    // Запись временных данных
    protected function StepWriteTmpData(){
        
        return $this->nextStep("modimporter_unzip_file", "Распаковываем файл", null, xPDO::LOG_LEVEL_WARN);
    }
    
    
    protected function prepareSuccessUnzipFileResponse(){
        
        // else
        return $this->nextStep('modimporter_write_tmp_xlsx_shared_strings', 'Файл успешно распакован');
    }
    
    
    protected function StepWriteTmpXlsxSharedStrings(){
        
        if(!$reader = & $this->getReader()){
            return $this->failure('Не был получен ридер');
        }
        
        $filename = 'xl/sharedStrings.xml';
        
        # if(!$filename = $this->getProperty('filename')){
        #     return $this->failure("Не был указан файл");
        # }
        
        if(!$path = $this->getImportPath()){
            
            return $this->failure("Не была получена директория файлов");
        }
        
        
        $schema = array(
            "sst"    => array(
                "si"     => array(
                    "parse" => true,
                ),
            ),
        );
        
        $index = 0;
                
        $result = $reader->read(array(
            "file" => $path.$filename,
        ), function(modImporterXmlReader $reader) use (& $schema, & $index){
            
            $xmlReader = & $reader->getReader();
            
            $node = $reader->getNodeName($xmlReader);            
            
            if(!$reader->isNodeText($xmlReader) && $reader->getSchemaNodeByKey($schema, $node) && $reader->isNode($node, $xmlReader)){                                
                
                
                if(isset($schema["parse"]) && $schema["parse"] && $node == "si"){
                    
                    $xml = $reader->getXMLNode($xmlReader);   
                    
                    $object = $this->createImportObject($index, array(
                        "tmp_title" => (string)$xml->t,
                    ), "shared_string");
                    
                    if(!$object->save()){
                        $error = "Ошибка сохранения объекта записи";
                        $this->modx->log(xPDO::LOG_LEVEL_ERROR, $error, null, __FUNCTION__, __FILE__, __LINE__);
                        return $error;
                    }
                    
                    # print_r($object->toArray());
                    
                    $index++;
                    
                    # $xmlReader->next();
                    
                    return true;
                }                               
                
            }           
            
            return true;
        });
        
        if($result !== true AND $result !== false){
            return $this->failure($result);
        }
        
        return $this->nextStep("modimporter_write_tmp_commercial_info", "Строковые значения успешно записаны", null, xPDO::LOG_LEVEL_WARN);
    }
    
}

return 'modModimporterImportXlsxConsoleProcessor';