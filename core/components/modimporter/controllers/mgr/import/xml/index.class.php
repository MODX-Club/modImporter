<?php

require_once dirname(dirname(__FILE__)) . '/index.class.php';

class ModimporterControllersMgrImportXmlIndexManagerController extends ModimporterControllersMgrImportIndexManagerController{
    
    protected function getAction(){
        
        return 'import/xml/console';
    }
}
