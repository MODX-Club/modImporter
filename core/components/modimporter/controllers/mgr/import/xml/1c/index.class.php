<?php

require_once dirname(dirname(__FILE__)) . '/index.class.php';

class ModimporterControllersMgrImportXml1cIndexManagerController extends ModimporterControllersMgrImportXmlIndexManagerController{
    
    protected function getAction(){
        
        return 'import/xml/1c/console';
    }
}
