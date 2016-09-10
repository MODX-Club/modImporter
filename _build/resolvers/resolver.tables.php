<?php

$pkgNameLower = $options['namespace'];

$mimp_get_field_position = function ($field_position) {
  return $field_position == 'first' ? array('first' => true) : array('after' => $field_position);
};

$mimp_update_fields = function ($modx, $class_name) use ($mimp_get_field_position) {
    $fields_exist = array();
    $prev_field = false;
    $fields_at_meta = $modx->getFields($class_name);
    $table_name = $modx->getTableName($class_name);
    $manager = $modx->getManager();
    foreach ($modx->query("SHOW FIELDS IN {$table_name}") as $row) {
        $_field = $row['Field'];
        if (array_key_exists($_field, $fields_at_meta)) {
            $fields_exist[$_field] = (bool) $prev_field ? $prev_field['Field'] : 'first';

            $manager->alterField($class_name, $_field, $mimp_get_field_position($fields_exist[$_field]));
            $modx->log(xPDO::LOG_LEVEL_DEBUG, "Field {$class_name}.{$_field} was altered");
        } else {
            // here we could remove odd fields but xpdo doesn't load them cause they are not at map file. so we don't
        }
        $prev_field = $row;
    }
    unset($prev_field);
    $diff = array_diff_key($fields_at_meta, $fields_exist);
    if ($diff) {
        foreach ($diff as $key => $dif) {
            $manager->addField($class_name, $key);
            $modx->log(xPDO::LOG_LEVEL_DEBUG, "Field {$class_name}.{$_field} was added");
        }
    }
};

$mimp_update_indexes = function ($modx, $class_name) {
    $indexes_exist = array();
    $prev_field = false;
    $indexes_at_meta = $modx->getIndexMeta($class_name);
    $manager = $modx->getManager();
    foreach ($modx->query("SHOW INDEXES IN {$modx->getTableName($class_name)}") as $row) {
        if (array_key_exists($row['Key_name'], $indexes_at_meta)) {
            $indexes_exist[$row['Key_name']] = '';
        }
    }

    $diff = array_diff_key($indexes_at_meta, $indexes_exist);
    if ($diff) {
        foreach ($diff as $key => $dif) {
            $manager->addIndex($class_name, $key);
            $modx->log(xPDO::LOG_LEVEL_DEBUG, "Index {$class_name}.{$key} was added");
        }
    }
};

if ($object->xpdo) {
    $modx = &$object->xpdo;

    if (!($modx instanceof modX)) {
        return;
    }
    
    $modelPath = $modx->getOption("{$pkgNameLower}.core_path", null, $modx->getOption('core_path')."components/{$pkgNameLower}/").'model/';
    $modx->setLogLevel(modX::LOG_LEVEL_ERROR);
    $modx->addPackage($pkgNameLower, $modelPath);
    $manager = $modx->getManager();

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            // adding xpdo objects
            $objects = array('modImporterExport', 'modImporterImport', 'modImporterObject');
            foreach ($objects as $o) {
                $manager->createObjectContainer($o);
            }
    
            $modx->setLogLevel(modX::LOG_LEVEL_INFO);
            $modx->log(xPDO::LOG_LEVEL_INFO, 'Tables were added');
            
            # if($options[xPDOTransport::PACKAGE_ACTION] == xPDOTransport::ACTION_UPGRADE){
                $objects = array('modImporterExport', 'modImporterImport', 'modImporterObject');
                foreach ($objects as $o) {
                    $mimp_update_fields($modx, $o);
                    $mimp_update_indexes($modx, $o);
                }
    
                $modx->setLogLevel(modX::LOG_LEVEL_INFO);
                $modx->log(xPDO::LOG_LEVEL_INFO, 'Tables were upgraded');
            # }
            break;
    }

}

return true;
