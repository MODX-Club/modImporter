<?php

class ControllersMgrManagerController extends modExtraManagerController{
    
    # function __construct(modX &$modx, $config = array()) {
    #     parent::__construct($modx, $config);
    #     
    # }
    
    
    public function initialize() {
        
        # print '<pre>';
        $namespace = $this->config['namespace'];
        $default_type = $this->modx->getOption("modimporter.default_action", null,'import/console');
        $source = (int)$this->modx->getOption("modimporter.media_source", null, $this->modx->getOption("default_media_source", null, 1));

        $this->config['namespace_assets_path'] = $this->modx->call('modNamespace','translatePath',array(&$this->modx, $this->config['namespace_assets_path']));
        $this->config['source'] = $source;
        $this->config['default_type'] = $default_type;

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
        $this->addCss($assets_url.'css/bootstrap.buttons.css');
        $this->addCss($assets_url.'css/main.css');
        $this->addJavascript($assets_url.'js/modimporter.js'); 
        $this->addJavascript($assets_url.'js/misc/mdi.combo.js');
        $this->addJavascript($assets_url.'js/misc/mdi.utils.js');
        $this->addJavascript($assets_url.'js/widgets/console.js'); 
        $this->addJavascript($assets_url.'js/widgets/import.grid.js'); 
        $this->addJavascript($assets_url.'js/widgets/export.grid.js'); 
        $this->addJavascript($assets_url.'js/home.panel.js');
        
        $this->addHtml('<script type="text/javascript">
            modImporter.config = '. $this->modx->toJSON($this->config).';
            Ext.onReady(function(){
                MODx.add(new modImporter.panel.Import());    
            });
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
