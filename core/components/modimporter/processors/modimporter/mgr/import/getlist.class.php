<?php

class modImporterImportGetListProcessor extends modObjectGetListProcessor {

    public $classKey = 'modImporterImport';
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'desc';

    public function prepareRow(xPDOObject $object) {
        $array = $object->toArray();
        $array['actions'] = array();
        // Edit
        $array['actions'][] = array(
            'cls' => '',
            'icon' => 'icon icon-arrow-circle-right',
            'action' => 'startImport',
            'title' => 'Начать импорт',
            'button' => true,
            'menu' => true,
        );
        return $array;
    }

}

return 'modImporterImportGetListProcessor';
