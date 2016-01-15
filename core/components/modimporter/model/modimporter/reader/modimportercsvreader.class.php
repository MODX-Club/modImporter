<?php

require_once dirname(__FILE__) . '/modimporterreader.class.php';

class modImporterCsvReader extends modImporterReader{
    
    
    public function initialize(modProcessor & $processor){
        
        $this->setDefaultProperties(array(
            'csv_delimiter' => ",",
            'csv_enclosure' => '"',
            'csv_escape' => '\\',
        ));
        
        return parent::initialize($processor);
    }
    
    
    public function read(array $provider, $callback = null){
        
        if(empty($provider['file'])){
            return "Не был указан файл";
        }
        
        $file = $provider['file'];
        
        if(!$fo = fopen($file, 'r')){
            return $this->failure('Ошибка чтения файла');
        }
        
        $csv_delimiter = $this->getProperty('csv_delimiter');
        $csv_enclosure = $this->getProperty('csv_enclosure');
        $csv_escape = $this->getProperty('csv_escape');
        
        /**/
        while($data = fgetcsv ( $fo, 0, $csv_delimiter, $csv_enclosure, $csv_escape)){
            if(is_callable($callback)){
                $ok = $callback($this, $data);
                if($ok !== true){
                    return $ok;
                }
            }
        }
        
        return true;
    }
}