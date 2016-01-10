<?php

class ControllersMgrManagerController extends modExtraManagerController{
    
    # function __construct(modX &$modx, $config = array()) {
    #     parent::__construct($modx, $config);
    #     
    # }
    
    
    public function initialize() {
        
        # print '<pre>';
        $namespace = $this->config['namespace'];
        
        $this->config['namespace_assets_path'] = $this->modx->call('modNamespace','translatePath',array(&$this->modx, $this->config['namespace_assets_path']));
        
        $this->config['manager_url'] = 
            $this->config['assets'] = 
            $this->config['assets_url'] = 
                $this->modx->getOption("{$namespace}.manager_url", null, $this->modx->getOption('manager_url')."components/{$namespace}/");
                
        $this->config['connector_url'] = $this->config['manager_url'].'connectors/';
        
        # print_r($this->config);
        
        return parent::initialize();
    }
    
    # public static function getInstance(modX &$modx, $className, array $config = array()) {
    #     $className = __CLASS__;
    #     return new $className($modx, $config);
    # }
    
    public function getOption($key, $options = null, $default = null, $skipEmpty = false){
        $options = array_merge($this->config, (array)$options);
        return $this->modx->getOption($key, $options, $default, $skipEmpty);
    }

    public function getLanguageTopics() {
        return array("{$this->config['namespace']}:default");
    }

    function loadCustomCssJs(){
        parent::loadCustomCssJs();
        
        $assets_url = $this->getOption('assets_url');
        
        # $attrs = $this->modx->user->getAttributes(array(),'', true);
        # $policies = array();
        # if(!empty($attrs['modAccessContext']['mgr'])){
        #     foreach($attrs['modAccessContext']['mgr'] as $attr){
        #         foreach($attr['policy'] as $policy => $value){
        #             if(empty($policies[$policy])){
        #                 $policies[$policy] = $value;
        #             }
        #         }
        #     }
        # }
        # 
        # $this->modx->regClientStartupScript('<script type="text/javascript">
        #     Shop.policies = '. $this->modx->toJSON($policies).';
        # </script>', true);
         
        $this->addJavascript($assets_url.'js/widgets/import.js'); 
        
        
        $this->addHtml('<script type="text/javascript">
            modImporter.config = '. $this->modx->toJSON($this->config).';
        </script>');
        
        return;
    }
    
    # public function getTemplatesPaths($coreOnly = false) {
    #     $paths = parent::getTemplatesPaths($coreOnly);
    #     $paths[] = $this->config['namespace_path']."templates/default/";
    #     return $paths;
    # }
    # 
    # public function getTemplateFile() {
    #     return 'index.tpl';
    # }
}
?>
