<?php

$pkgNameLower = $options['namespace'];
if ($object->xpdo) {
    $modx = &$object->xpdo;
    $modelPath = $modx->getOption("{$pkgNameLower}.core_path", null, $modx->getOption('core_path')."components/{$pkgNameLower}/").'model/';

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
        if ($modx instanceof modX) {
            $modx->setLogLevel(modX::LOG_LEVEL_ERROR);
            $manager = $modx->getManager();

            $manager->addField('modResource', 'externalKey');
            $manager->addIndex('modResource', 'externalKey');

            $manager->addField('modResource', 'importId');
            $manager->addIndex('modResource', 'importId');

            $modx->setLogLevel(modX::LOG_LEVEL_INFO);
            $modx->log(xPDO::LOG_LEVEL_INFO, 'Fields were added');
        }
    case xPDOTransport::ACTION_UPGRADE:
        if ($modx instanceof modX) {
            $modx->setLogLevel(modX::LOG_LEVEL_ERROR);

            $modx->addExtensionPackage($pkgNameLower, "[[++core_path]]components/{$pkgNameLower}/model/", array(
          // 'serviceName' => $pkgName,
          // 'serviceClass' => $pkgName,
        ));

            $modx->addPackage($pkgNameLower, MODX_CORE_PATH."components/{$pkgNameLower}/model/", array(
          // 'serviceName' => $pkgName,
          // 'serviceClass' => $pkgName,
        ));
            $modx->setLogLevel(modX::LOG_LEVEL_INFO);
            $modx->log(xPDO::LOG_LEVEL_INFO, 'Package was registered');
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
