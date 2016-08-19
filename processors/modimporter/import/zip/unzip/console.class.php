<?php

require_once dirname(dirname(dirname(__FILE__))).'/console.class.php';

class modModimporterImportZipUnzipConsoleProcessor extends modModimporterImportConsoleProcessor
{
    protected function StepInitConsole()
    {
        parent::StepInitConsole();

        return $this->nextStep('modimporter_unzip_file', 'Консоль успешно инициализирована');
    }
}

return 'modModimporterImportZipUnzipConsoleProcessor';
