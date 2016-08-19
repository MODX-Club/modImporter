<?php

$externalKeyLength = $this->getOption('modimporter.external_key_length', null, 32);
$externalKeyType = $this->getOption('modimporter.external_key_type', null, 'varchar');
$externalKeyPhpType = $externalKeyType == 'int' ? 'integer' : 'string';

$xpdo_meta_map['modImporterObject'] = array(
  'package' => 'modImporter',
  'version' => '1.1',
  'table' => 'modimporter_objects',
  'extends' => 'xPDOSimpleObject',
  'fields' => array(
    'tmp_object_type' => null,
    'tmp_external_key' => '',
    'tmp_import_id' => '',
    'tmp_resource_id' => null,
    'tmp_parent' => null,
    'tmp_title' => '',
    'tmp_content' => '',
    'tmp_processed' => '0',
    'tmp_error' => 0,
    'tmp_error_msg' => null,
    'tmp_raw_data' => null,
  ),
  'fieldMeta' => array(
    'tmp_object_type' => array(
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
    ),
    'tmp_external_key' => array(
      'dbtype' => $externalKeyType,
      'precision' => $externalKeyLength,
      'phptype' => $externalKeyPhpType,
      'null' => true,
      'index' => 'index',
    ),
    'tmp_import_id' => array(
      'dbtype' => 'int',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => null,
      'index' => 'index',
    ),
    'tmp_resource_id' => array(
      'dbtype' => 'int',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => null,
      'index' => 'index',
    ),
    'tmp_parent' => array(
      'dbtype' => $externalKeyType,
      'precision' => $externalKeyLength,
      'phptype' => $externalKeyPhpType,
      'null' => false,
      'index' => 'index',
    ),
    'tmp_title' => array(
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'index' => 'index',
    ),
    'tmp_content' => array(
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
    ),
    'tmp_processed' => array(
      'dbtype' => 'enum',
      'precision' => '\'0\',\'1\'',
      'phptype' => 'string',
      'null' => false,
      'default' => '0',
      'index' => 'index',
    ),
    'tmp_error' => array(
      'dbtype' => 'smallint',
      'precision' => '5',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'tmp_error_msg' => array(
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
    ),
    'tmp_raw_data' => array(
      'dbtype' => 'text',
      'phptype' => 'array',
      'null' => false,
    ),
  ),
  'indexes' => array(
    'tmp_external_key' => array(
      'alias' => 'tmp_external_key',
      'primary' => false,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => array(
        'tmp_external_key' => array(
          'length' => '',
          'collation' => 'A',
          'null' => true,
        ),
        'tmp_object_type' => array(
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'tmp_import_id' => array(
      'alias' => 'tmp_resource_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => array(
        'tmp_resource_id' => array(
          'length' => '',
          'collation' => 'A',
          'null' => true,
        ),
      ),
    ),
    'tmp_resource_id' => array(
      'alias' => 'tmp_resource_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => array(
        'tmp_resource_id' => array(
          'length' => '',
          'collation' => 'A',
          'null' => true,
        ),
      ),
    ),
    'tmp_parent' => array(
      'alias' => 'tmp_parent',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => array(
        'tmp_parent' => array(
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'tmp_title' => array(
      'alias' => 'tmp_title',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => array(
        'tmp_title' => array(
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'tmp_processed' => array(
      'alias' => 'tmp_processed',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => array(
        'tmp_processed' => array(
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'tmp_error' => array(
      'alias' => 'tmp_error',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => array(
        'tmp_error' => array(
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
);
