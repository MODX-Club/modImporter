<?php

$pkgNameLower = $options['namespace'];

if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
      $modx = &$object->xpdo;
      $modelPath = $modx->getOption("{$pkgNameLower}.core_path", null, $modx->getOption('core_path')."components/{$pkgNameLower}/").'model/';

      $modx->setLogLevel(modX::LOG_LEVEL_ERROR);
      $modx->addPackage($pkgNameLower, $modelPath);

      $manager = $modx->getManager();

      // adding xpdo objects
      # $manager->createObjectContainer('SamplePackageObject');
      # $manager->createObjectContainer('SamplePackageObject');

      $manager->createObjectContainer('modImporterExport');
      $manager->createObjectContainer('modImporterImport');
      $manager->createObjectContainer('modImporterObject');

      $modx->setLogLevel(modX::LOG_LEVEL_INFO);
      $modx->log(xPDO::LOG_LEVEL_INFO, 'Tables were added');

      break;
    case xPDOTransport::ACTION_UPGRADE:
      break;
  }
}

return true;
