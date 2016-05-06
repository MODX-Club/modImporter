<?php

$xpdo_meta_map['modImporterImport']= array (
    'package' => 'modImporter',
    'version' => '1.1',
    'table' => 'modimporter_imports',
    'extends' => 'xPDOSimpleObject',
    'fields' =>
        array (
            'name' => NULL,
            'description' => '',
            'format' => NULL,
            'type' => NULL,
            'source' => NULL,
            'lastimportdon' => NULL,
        ),
    'fieldMeta' =>
        array (
            'name' =>
                array (
                    'dbtype' => 'varchar',
                    'precision' => '255',
                    'phptype' => 'string',
                    'null' => true,
                ),
            'description' =>
                array (
                    'dbtype' => 'text',
                    'phptype' => 'string',
                    'null' => false,
                ),
            'format' =>
                array (
                    'dbtype' => 'varchar',
                    'precision' => '50',
                    'phptype' => 'string',
                    'null' => true,
                ),
            'type' =>
                array (
                    'dbtype' => 'varchar',
                    'precision' => '255',
                    'phptype' => 'string',
                    'null' => true,
                ),
            'source' =>
                array (
                    'dbtype' => 'varchar',
                    'precision' => '500',
                    'phptype' => 'string',
                    'null' => true,
                ),
            'lastimportdon' =>
                array (
                    'dbtype' => 'datetime',
                    'phptype' => 'datetime',
                    'null' => true,
                ),

        ),
    'indexes' =>
        array (
            'name' =>
                array (
                    'alias' => 'name',
                    'primary' => false,
                    'unique' => false,
                    'type' => 'BTREE',
                    'columns' =>
                        array (
                            'name' =>
                                array (
                                    'length' => '',
                                    'collation' => 'A',
                                    'null' => true,
                                ),
                        ),
                ),
        ),
);
