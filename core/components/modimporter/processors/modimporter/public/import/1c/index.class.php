<?php

/*
    Публичный класс для импортера
*/

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/import/xml/1c/console.class.php';

class modModimporterPublicImport1cIndexProcessor extends modModimporterImportXml1cConsoleProcessor{
    
    
    public function initialize(){
        
        $this->setDefaultProperties(array(
            "debug"         => 1,
            "inputCharset" => $this->modx->getOption('modx_charset',null,'UTF-8'),
            "outputCharset" => $this->modx->getOption('modimporter.1c_output_charset',null,'windows-1251'),
        ));
        
        return parent::initialize();
    }
    
    
    protected function prepareAuthResponse(){
        $output = '';
        $output .= session_name() ."\n";
        $output .= session_id() ."\n";
        return $this->success($output);
    }
    
    
    
    public function success($msg = '', $object = null, $level = xPDO::LOG_LEVEL_INFO, $continue = false) {
        $msg = "success\n{$msg}";
        
        return parent::success($msg, $object, $level, $continue);
    }
    
    
    public function failure($msg = '', $object = null, $level = xPDO::LOG_LEVEL_ERROR, $continue = false) {
        $msg = "failure\n{$msg}";
        
        return parent::failure($msg, $object, $level, $continue);
    }
    
    
    protected function prepareResponse($success, $msg = '', $object = null, $level = xPDO::LOG_LEVEL_ERROR, $continue = false){
        
        if($this->getProperty('debug')){
            $this->modx->log(1, "modImporter connector");
    
            $this->modx->log(1, 'MODE: ' . $this->getProperty("modimporter_step"));
            $this->modx->log(1, 'STEP: ' . $this->getSessionValue("STEP"));
    
            $this->modx->log(1, '$_SERVER');
            $this->modx->log(1, print_r($_SERVER, 1));
            
            $this->modx->log(1, '$_REQUEST');
            $this->modx->log(1, print_r($_REQUEST, 1));
            
            $this->modx->log(1, 'Response');
            $this->modx->log(1, $msg);
        }
        
        $inputCharset = $this->getProperty('inputCharset');
        $outputCharset = $this->getProperty('outputCharset');
        
        if(
            $inputCharset AND $outputCharset
            AND mb_strtolower($inputCharset, $inputCharset) != mb_strtolower($outputCharset, $inputCharset)
        ){
            $msg = mb_convert_encoding($msg, $outputCharset, $inputCharset);
            $header= 'Content-Type: text/html';
            $header .= '; charset=' . $outputCharset;
            $this->modx->response->header[] = $header;
        }
        
        if($response_delay = (int)$this->getProperty('modimporter_response_delay')){
            sleep($response_delay);
        }
        
        return $msg;
    }
    
    
    protected function progress($msg = '',$object = null, $level = xPDO::LOG_LEVEL_INFO){
        $msg = "progress\n{$msg}";
        return $this->prepareResponse(true, $msg, $object, $level, true);
    }
    
}

return 'modModimporterPublicImport1cIndexProcessor';
