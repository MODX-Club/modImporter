<?php

$pkgName = 'modImporter';
$pkgNameLower = strtolower($pkgName);

if ($object->xpdo) {
    $modx = &$object->xpdo;
    $modelPath = $modx->getOption("{$pkgNameLower}.core_path", null, $modx->getOption('core_path')."components/{$pkgNameLower}/").'model/';

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
      if ($modx instanceof modX) {
          $modx->addExtensionPackage($pkgNameLower, "[[++core_path]]components/{$pkgNameLower}/model/", array(
          // 'serviceName' => $pkgName,
          // 'serviceClass' => $pkgName,
        ));

          $modx->addPackage($pkgNameLower, MODX_CORE_PATH."components/{$pkgNameLower}/model/", array(
          // 'serviceName' => $pkgName,
          // 'serviceClass' => $pkgName,
        ));

          $manager = $modx->getManager();

          $manager->addField('modResource', 'externalKey');
          $manager->addIndex('modResource', 'externalKey');

          $manager->addField('modResource', 'importId');
          $manager->addIndex('modResource', 'importId');

          $modx->log(xPDO::LOG_LEVEL_INFO, 'Adding ext package');
      }
      break;

    case xPDOTransport::ACTION_UNINSTALL:
      if ($modx instanceof modX) {
          $modx->removeExtensionPackage($pkgNameLower);
      }
      break;
  }
}

return true;
