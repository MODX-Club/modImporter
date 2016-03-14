<?php

require_once dirname(dirname(__FILE__)) . '/console.class.php';

class modModimporterImportXml1cConsoleProcessor extends modModimporterImportXmlConsoleProcessor{
    // Классы временных таблиц
    # protected $tmpClasses = array(
    # );        
    
    
    public function checkPermissions(){
        return $this->getProperty('mode') == 'checkauth' OR parent::checkPermissions();
    }
    
    
    public function initialize(){
        
        # $this->setProperties(array(
        #     "DIR_NAME" => $this->modx->getOption('modimporter.import_dir', null, MODX_CORE_PATH . 'components/modimporter/import/') ,
        # ));
        
            
        # $this->setDefaultProperties(array(
        #     # Временно
        #     "filename"      => basename($this->getProperty("file")), 
        # ));
        
        if($mode = $this->getProperty('mode')){
            
            switch($mode){
                
                case 'init':
                    
                    $mode = 'modimporter_console_init';
                    break;
                
                case 'checkauth':
                    
                    $mode = 'modimporter_checkauth';
                    break;
                
                case 'file':
                    
                    $mode = 'modimporter_upload_file';
                    break;
                
                case 'import':
                    
                    if(!$mode = $this->getSessionValue("STEP")){
                        $mode = 'modimporter_import';
                        # $mode = 'modimporter_drop_tmp_tables';
                    }
                    break;
                    
                default:;
            }
            
            $this->setDefaultProperties(array(
                'modimporter_step' => $mode,
            ));
        }
        
        return parent::initialize();
    }
    
    
    protected function processRequest(){
                
        
        
        switch($this->getProperty('modimporter_step')){
            
            
            // Проверка авторизации
            # case 'checkauth':
            #     
            #     $this->setDefaultProperties(array(
            #         "username"  => !empty($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '',
            #         "password"  => !empty($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '',
            #     ));
            #     
            #     return $this->checkouth();
            #     break;
            
            
            # Инициализация
            # case 'init':
            #     
            #     # $this->setDefaultProperties(array(
            #     #     "use_zip"  => $this->modx->getOption("shopmodx1c.use_zip", null, true),
            #     #     "file_limit"  => $this->modx->getOption("shopmodx1c.file_limit", null, 1024000),
            #     # ));
            #     
            #     return $this->init();
            #     break;
            
            
            # Распаковка файла
            case 'modimporter_unzip_file':
                
                $this->setProperties(array(
                    'filename' => $this->getSessionValue('zip'),
                ));
                
                # return $this->StepUnzipFile();
                break;
            # 
            # 
            # // Сохранение файла
            # case 'file':
            # 
            #     return $this->saveFile($this->getProperty('filename'));
            #     break;
            
            
            // Импорт данных
            # case 'import':
            #     
            #     /*
            #         Если предыдущие загруженный файл - zip-архив,
            #         то распаковываем его
            #     */
            #     if($filename = $this->getSessionValue('zip')){
            #         return $this->unzip($filename);
            #     }
                
                // Иначе выполняем импорт
                # return $this->import();
                
                // P.S. не спрашивайте меня почему в 1С такая фигня
                // и это не разбито на отдельные подшаги,
                // я сом в шоке
                # break;
            
            # default: $result = $this->failure("Действие не известно '{$mode}'");
            default: ;
        }
                
        return parent::processRequest();
    }
    
    
    protected function nextStep($step, $msg = '', $object = null, $level = xPDO::LOG_LEVEL_INFO){
        # $this->setStep($step);
        $this->setSessionValue("STEP", $step);
        return parent::nextStep($step, $msg, $object, $level);
    }
    
    
    # protected function init(){
    protected function StepInitConsole(){
        /*
            1С в этом методе не реагирует на ошибки никак, потому
            вернуть можно только $this->prepareResponse();
            
            1С ждет ответа только типа 
            zip=yes
            file_limit=204800
        */
        
        
        
        # $this->flushSession();
        
        $result = parent::StepInitConsole();
        
        $output = "";
        
        $use_zip = $this->getProperty("use_zip") && class_exists("ZipArchive");
        $file_limit = $this->getProperty("file_limit");
        
        /*
            Так как 1С выполняет импорт в несколько этапов, 
            удаляем таблицы только при инициаизации импорта (инициализация выполняется только
            один раз)
        */
        $this->setSessionValue('NeedDropTable', true); 
        
        # $this->setSessionValue('zip', '');
        # $this->setSessionValue("STEP", '');
        
        # $this->setSessionValue("STEP", 'import_initialize');
        
        $output .= "zip=".($use_zip ? "yes" : "no")."\n";
        
        $output .= "file_limit={$file_limit}\n";
        
        if(!$this->getProperty('modimporter_in_console_mode')){
            return $this->prepareResponse(1, $output);
        }
        
        return $result;
    }
    
    
    # protected function init(){
    #     /*
    #         1С в этом методе не реагирует на ошибки никак, потому
    #         вернуть можно только $this->prepareResponse();
    #         
    #         1С ждет ответа только типа 
    #         zip=yes
    #         file_limit=204800
    #     */
    #     
    #     
    #     
    #     # $this->flushSession();
    #     
    #     parent::StepInitConsole();
    #     
    #     $output = "";
    #     
    #     $use_zip = $this->getProperty("use_zip") && class_exists("ZipArchive");
    #     $file_limit = $this->getProperty("file_limit");
    #     
    #     /*
    #         Так как 1С выполняет импорт в несколько этапов, 
    #         удаляем таблицы только при инициаизации импорта (инициализация выполняется только
    #         один раз)
    #     */
    #     $this->setSessionValue('NeedDropTable', true); 
    #     
    #     # $this->setSessionValue('zip', '');
    #     # $this->setSessionValue("STEP", '');
    #     
    #     # $this->setSessionValue("STEP", 'import_initialize');
    #     
    #     $output .= "zip=".($use_zip ? "yes" : "no")."\n";
    #     
    #     $output .= "file_limit={$file_limit}\n";
    #     
    #     return $this->prepareResponse(1, $output);
    # }
     
    
    
    # protected function import(){
    #     
    #     if(!$this->getProperty("filename")){
    #         return $this->failure("Не указано имя файла");
    #     }
    #     
    #     switch($this->getSessionValue("STEP")){
    #         
    #         case "import_initialize":
    #         
    #             return $this->stepImportInitialize();
    #             break;
    #         
    #         
    #         case "import_drop_tmp_tables":
    #         
    #             return $this->StepDropTmpTables();
    #             break;
    #         
    #         
    #         case "import_create_tmp_tables":
    #         
    #             return $this->StepCreateTmpTables();
    #             break;
    #         
    #         
    #         case "import_parseFile":
    #         
    #             return $this->stepImportParseFile();
    #             break;
    #         
    #         
    #         case "import_write_commercial_info":
    #         
    #             return $this->stepWriteCommercialInfo();
    #             break;
    #         
    #         
    #         case "import_write_categories_info":
    #         
    #             return $this->stepWriteCategoriesInfo();
    #             break;
    #         
    #         
    #         case "import_complite":
    #         
    #             return $this->stepImportComplite();
    #             break;
    #         
    #         
    #         default: ;
    #     }
    #     
    #     return $this->failure('Неизвестный шаг');
    # }
    
     
    
    
    protected function StepUnzipFile(){
        
        # if(!$filename = $this->getProperty('zip_filename')){
        #     return $this->failure("Не было получено имя файла");
        # }
        # 
        # if(!$result = $this->unzip($filename)){
        #     
        #     return $this->failure("Ошибка распаковки архива");
        # }
        
        $result = parent::StepUnzipFile();
        
        if($this->hasErrors()){
            return $result;
        }
        
        // else
        $this->setSessionValue('zip', '');
        return $this->nextStep('modimporter_import', 'Файл успешно распакован');
    }
    
    
    
    protected function afterFileSave($filename, $path){
        
        /*
            Если это zip, сохраняем его для дальнейшей распаковки
        */
        $pi = pathinfo($filename);
        
        if(mb_strtolower($pi['extension']) == 'zip'){
            $this->setSessionValue('zip', $filename);
            $this->setSessionValue('STEP', "modimporter_unzip_file");
            
            # 1С требует возврата только сообщения success.
            # Все остальное расценивается как ошибка
            # return $this->nextStep("modimporter_unzip_file");
        } 
        
        return parent::afterFileSave($filename, $path);
    }
    
    
    
    # protected function stepImportInitialize(){
    #     $this->setSessionValue("STEP", "import_drop_tmp_tables");
    #     return $this->progress("Инициализация каталога успешно выполнена");
    # }
    
    
    protected function dropTmpTables(){
        
        # $this->setSessionValue("STEP", "import_create_tmp_tables");
        # return parent::StepDropTmpTables();
        
        $result = true;
        
        if($this->getSessionValue("NeedDropTable")){
            $result = parent::dropTmpTables();
            $this->setSessionValue("NeedDropTable", false);
        }
        
        return $result;
    }
    
    
    # protected function createTmpTables(){
    #     
    #     $this->setSessionValue("STEP", "import_parseFile");
    #     return true;
    # }
    
    # protected function stepImportParseFile(){
    #     
    #     $this->setSessionValue("STEP", 'import_write_commercial_info');
    #     return $this->progress("Файл успешно прочитан");
    # }
    
    
    
    /*
        Необходимо для импорта из 1С, так как 1Ска за раз отправляет несколько файлов
        и если не будет сброшен шаг, то на последующих
        файлах сразу будет завершен импорт
    */
    protected function StepDeactivate(){
        
        // Сбрасываем шаг
        $this->setSessionValue('STEP', null);
        
        return parent::StepDeactivate();
    }
    
    # protected function stepImportComplite(){
    #     
    #     return $this->success("Импорт успешно завершен");
    # }
    
}

return 'modModimporterImportXml1cConsoleProcessor';
