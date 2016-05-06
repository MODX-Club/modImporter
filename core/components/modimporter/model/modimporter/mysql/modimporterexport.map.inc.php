<?php

$xpdo_meta_map['modImporterExport']= array (
    'package' => 'modImporter',
    'version' => '1.1',
    'table' => 'modimporter_exports',
    'extends' => 'xPDOSimpleObject',
    'fields' =>
        array (
            'url' => NULL,
            'exportdon' => NULL,
            'params' => '',
        ),
    'fieldMeta' =>
        array (
            'url' =>
                array (
                    'dbtype' => 'varchar',
                    'precision' => '500',
                    'phptype' => 'string',
                    'null' => true,
                ),
            'exportdon' =>
                array (
                    'dbtype' => 'datetime',
                    'phptype' => 'datetime',
                    'null' => true,
                ),
            'params' =>
                array (
                    'dbtype' => 'text',
                    'phptype' => 'string',
                    'null' => false,
                ),

        ),
    'indexes' =>
        array (

        ),
);
