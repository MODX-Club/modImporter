<?php

$modx->log(modX::LOG_LEVEL_INFO, 'Menu adding initiated…'); flush();

$menus = include $sources['data'].'transport.menu.php';

if (!is_array($menus)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Could not add menu.');
} else {
    $attributes = array(
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::UNIQUE_KEY => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
      # 'Action' => array (
      #   xPDOTransport::PRESERVE_KEYS => false,
      #   xPDOTransport::UPDATE_OBJECT => true,
      #   xPDOTransport::UNIQUE_KEY => array ('namespace','controller'),
      # ),
      # 'Children' => array (
      #   xPDOTransport::UNIQUE_KEY => 'action',
      #   xPDOTransport::PRESERVE_KEYS => false,
      #   xPDOTransport::UPDATE_OBJECT => true,
      #   xPDOTransport::UNIQUE_KEY => array ('namespace','controller'),
      # ),
    ),
  );

    foreach ($menus as $menu) {
        $vehicle = $builder->createVehicle($menu, $attributes);
        $builder->putVehicle($vehicle);
        $modx->log(modX::LOG_LEVEL_INFO, $menu->text.' menu was added.');
    }
    unset($vehicle, $action);
}
