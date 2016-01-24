<?php

require_once dirname(dirname(__FILE__)). '/index.class.php';

class ModimporterControllersMgrImportZipUnzipManagerController extends ModimporterControllersMgrImportIndexManagerController{
    
    protected function getAction(){
        
        return 'import/zip/unzip/console';
    }
    
}
