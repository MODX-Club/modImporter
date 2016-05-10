<?php

if (!isset($_REQUEST['ctx']) or !in_array($_REQUEST['ctx'], array('web'))) {
    $_REQUEST['ctx'] = 'web';
}

// In cli-mode
if (php_sapi_name() == 'cli' and !empty($argv[1])) {
    $args = array();

    $params = parse_str($argv[1], $args);

    $_GET = array_merge($_GET, $args);
    $_REQUEST = array_merge($_REQUEST, $args);
}

define('MODX_REQP', false);

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$_SERVER['HTTP_MODAUTH'] = $modx->user->getUserToken($modx->context->get('key'));

/* handle request */
if (!$path = $modx->getOption('modimporter.core_path')) {
    $path = $modx->getObject('modNamespace', 'modimporter')->getCorePath();
}
$path .= 'processors/modimporter/';

if (!isset($location)) {
    $location = '';
}

$params = array(
    'processors_path' => $path,
    'location' => $location,
);

if (isset($action)) {
    $params['action'] = $action;
}

$modx->request->handleRequest($params);
