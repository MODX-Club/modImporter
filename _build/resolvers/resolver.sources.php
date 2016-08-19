<?php
// Add core source
$vehicle->resolve('file', array(
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
));
$modx->log(modX::LOG_LEVEL_INFO, 'CorePath was added'); flush();

// Add assets source
$vehicle->resolve('file', array(
    'source' => $sources['source_assets'],
    'target' => "return MODX_ASSETS_PATH . 'components/';",
));
$modx->log(modX::LOG_LEVEL_INFO, 'AssetsPath was added'); flush();

// Add manager source
$vehicle->resolve('file', array(
    'source' => $sources['source_manager'],
    'target' => "return MODX_MANAGER_PATH . 'components/';",
));
$modx->log(modX::LOG_LEVEL_INFO, 'ManagerPath was added'); flush();

// Add console source
$vehicle->resolve('file', array(
    'source' => $sources['source_console'],
    'target' => "return MODX_BASE_PATH . '/';",
));
$modx->log(modX::LOG_LEVEL_INFO, 'ConsolePath was added'); flush();
