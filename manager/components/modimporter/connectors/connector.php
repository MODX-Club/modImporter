<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

/* handle request */
if (!$path = $modx->getOption('modimporter.core_path')) {
    $path = $modx->getObject('modNamespace', 'modimporter')->getCorePath();
}

$path .= 'processors/';
$location = 'modimporter/';

$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => $location,
));
