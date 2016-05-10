<?php

$xpdo_meta_map = array(
    'xPDOSimpleObject' => array(
        ),
);

$externalKeyLength = $this->getOption('modimporter.external_key_length', null, 32);
$externalKeyType = $this->getOption('modimporter.external_key_type', null, 'varchar');
$externalKeyPhpType = $externalKeyType == 'int' ? 'integer' : 'string';

$custom_fields = array(
    'modResource' => array(
        'fields' => array(
            'externalKey' => array(
                'defaultValue' => null,
                'metaData' => array(
                    'dbtype' => $externalKeyType,
                    'precision' => $externalKeyLength,
                    'attributes' => '',
                    'phptype' => $externalKeyPhpType,
                    'null' => true,
                    'index' => 'index',
                ),
            ),
            'importId' => array(
                'defaultValue' => null,
                'metaData' => array(
                    'dbtype' => 'int',
                    'precision' => 10,
                    'attributes' => 'unsigned',
                    'phptype' => 'integer',
                    'null' => true,
                    'default' => null,
                    'index' => 'index',
                ),
            ),
        ),

        'indexes' => array(
            'externalKey' => array(
                    'alias' => 'externalKey',
                    'primary' => false,
                    'unique' => false,
                    'type' => 'BTREE',
                    'columns' => array(
                            'externalKey' => array(
                                    'length' => '',
                                    'collation' => 'A',
                                    'null' => true,
                                ),
                        ),
                ),
            'importId' => array(
                    'alias' => 'importId',
                    'primary' => false,
                    'unique' => false,
                    'type' => 'BTREE',
                    'columns' => array(
                            'importId' => array(
                                    'length' => '',
                                    'collation' => 'A',
                                    'null' => true,
                                ),
                        ),
                ),
        ),
    ),
);

foreach ($custom_fields as $class => $class_data) {
    foreach ($class_data['fields'] as $field => $data) {
        $this->map[$class]['fields'][$field] = $data['defaultValue'];
        $this->map[$class]['fieldMeta'][$field] = $data['metaData'];
    }

    if (!empty($class_data['indexes'])) {
        foreach ($class_data['indexes'] as $index => $data) {
            $this->map[$class]['indexes'][$index] = $data;
        }
    }
}

// $this->map['modResourceTag']['indexes']['test'] = ;

// $manager = $modx->getManager();

// $index = $manager->addField('modResourceTag', 'test' );
// $index = $manager->addIndex('modResourceTag', 'test');
