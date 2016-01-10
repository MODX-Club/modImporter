<?php

require_once dirname(dirname(__FILE__)). '/index.class.php';

class ModimporterControllersMgrImportIndexManagerController extends ControllersMgrManagerController{


    function loadCustomCssJs(){
        parent::loadCustomCssJs();
        # $assets_url = $this->getOption('assets_url');
    
        $action = $this->getAction();
        
        $source = (int)$this->modx->getOption("modimporter.media_source", null, $this->modx->getOption("default_media_source", null, 1));
        
        $this->addHtml('<script type="text/javascript">
    
            Ext.onReady(function(){
                MODx.add(new modImporter.panel.Import({
                    action: "'. $action .'"
                    ,source: '. $source .'
                }));    
            });
        </script>', true);
        
        
        return;
    }
    
    
    protected function getAction(){
        return 'import/console';
    }
}