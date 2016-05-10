<?php

$namespace = $modx->newObject('modNamespace');
$namespace->set('name', NAMESPACE_NAME);

if ($pkgDebug) {
    $namespace->set('path', getBuildPath($sources['source_core']));
    $namespace->set('assets_path', getBuildPath($sources['source_assets']));
} else {
    $namespace->set('path', '{core_path}components/'.PKG_NAME_LOWER.'/');
    $namespace->set('assets_path', '{assets_path}components/'.PKG_NAME_LOWER.'/');
}

$vehicle = $builder->createVehicle($namespace, array(
    xPDOTransport::UNIQUE_KEY => 'name',
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
));

$builder->putVehicle($vehicle);

$modx->log(modX::LOG_LEVEL_INFO, NAMESPACE_NAME.' namespace was added.'); flush();

unset($vehicle, $namespace);
