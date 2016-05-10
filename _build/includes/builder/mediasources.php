<?php

# $mediaSources = include $sources['data'].'transport.mediasources.php';
# if (!is_array($mediaSources)){
#   $modx->log(modX::LOG_LEVEL_ERROR,'Adding MediaSources failed.'); }
# else{
#   $vehicleParams = array(
#     xPDOTransport::PRESERVE_KEYS => false,
#     xPDOTransport::UPDATE_OBJECT => false,
#     xPDOTransport::UNIQUE_KEY => 'name',
#     # xPDOTransport::RELATED_OBJECTS => false,
#     # xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
#     #   # 'Snippets' => array(
#     #   #   xPDOTransport::PRESERVE_KEYS => false,
#     #   #   xPDOTransport::UPDATE_OBJECT => true,
#     #   #   xPDOTransport::UNIQUE_KEY => 'name',
#     #   # ),
#     #   # 'Chunks' => array(
#     #   #   xPDOTransport::PRESERVE_KEYS => false,
#     #   #   xPDOTransport::UPDATE_OBJECT => true,
#     #   #   xPDOTransport::UNIQUE_KEY => 'name',
#     #   # ),
#     # ),
#   );
#
#   # foreach($mediaSources as & $mediaSource){
#   #   $vehicle = $builder->createVehicle($mediaSource, $vehicleParams);
#   #   $builder->putVehicle($vehicle);
#   # }
#   # $modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($mediaSources).' MediaSources.'); flush();
# }
# unset($mediaSources,$vehicle,$vehicleParams);

$vehicleParams = array(
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => false,
    xPDOTransport::UNIQUE_KEY => 'target',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
          'Target' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => false,
            xPDOTransport::UNIQUE_KEY => 'name',
          ),
    ),
);

# foreach($mediaSources as & $mediaSource){
#     $vehicle = $builder->createVehicle($mediaSource, $vehicleParams);
#     $builder->putVehicle($vehicle);
# }

$mediaSourceAccess = $modx->newObject('sources.modAccessMediaSource', array(
    'principal_class' => 'modUserGroup',
    'principal' => 1,
    'authority' => 0,
    'policy' => 8,
));

$params = array(
  'basePath' => array(
    'name' => 'basePath',
    'desc' => 'prop_file.basePath_desc',
    'type' => 'textfield',
    'options' => array(),
    'value' => 'core/components/'.PKG_PATH.'/import/',
    'lexicon' => 'core:source',
  ),
  'baseUrl' => array(
    'name' => 'baseUrl',
    'desc' => 'prop_file.baseUrl_desc',
    'type' => 'textfield',
    'options' => array(),
    'value' => 'core/components/'.PKG_PATH.'/import/',
    'lexicon' => 'core:source',
  ),
  'skipFiles' => array(
    'name' => 'skipFiles',
    'desc' => 'prop_file.skipFiles_desc',
    'type' => 'textfield',
    'options' => array(),
    'value' => '.svn,.git,_notes,nbproject,.idea,.DS_Store,.gitignore',
    'lexicon' => 'core:source',
  ),
);

$mediaSource = $modx->newObject('sources.modMediaSource', array(
 'name' => 'modImporter import files',
 'class_key' => 'sources.modFileMediaSource',
 'description' => 'Файлы импорта', //PKG_NAME_LOWER.' Core Source',
 'properties' => $params,
));

$mediaSourceAccess->addOne($mediaSource, 'Target');

$vehicle = $builder->createVehicle($mediaSourceAccess, $vehicleParams);
$builder->putVehicle($vehicle);

$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($mediaSourceAccess).' MediaSourceAccess.'); flush();
