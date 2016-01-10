<?php

if(!isset($_REQUEST['ctx']) OR !in_array($_REQUEST['ctx'], array('web'))){
    $_REQUEST['ctx'] = 'web';
}
  
define('MODX_REQP', false);

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$_SERVER['HTTP_MODAUTH']= $modx->user->getUserToken($modx->context->get('key'));
 

/* handle request */
if(!$path = $modx->getOption('modimporter.core_path')){
    $path = $modx->getOption('core_path').'components/modimporter/';
}
$path .= 'processors/';

$location = 'modimporter/public/';

$params = array(
    'processors_path' => $path,
    'location' => $location,
);

if(isset($action)){
    $params['action'] = $action;
}

# print $path . $location;

$modx->request->handleRequest($params);

