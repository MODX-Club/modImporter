<?php

$events = include_once $sources['data'].'transport.events.php';

$attributes = array(
    xPDOTransport::UNIQUE_KEY => 'name',
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => false,
);

if (!is_array($events)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Adding events failed.');
}
foreach ($events as $event) {
    $vehicle = $builder->createVehicle($event, $attributes);
    $builder->putVehicle($vehicle);
}
$modx->log(modX::LOG_LEVEL_INFO, count($events).' system events were added.'); flush();
unset($events, $event, $attributes);
