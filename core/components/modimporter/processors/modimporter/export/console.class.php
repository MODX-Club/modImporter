<?php

// ini_set('display_errors', 1);

/**
 * Read from the registry to console.
 *
 * @param string $register       The register to read from
 * @param string $topic          The topic in the register to read from
 * @param string $format         (optional) The format to output as. Defaults to json.
 * @param string $register_class (optional) If set, will load a custom registry
 *                               class.
 * @param int    $poll_limit     (optional) The number of polls to limit to.
 *                               Defaults to 1.
 * @param int    $poll_interval  (optional) The interval of polls to grab from.
 *                               Defaults to 1.
 * @param int    $time_limit     (optional) The time limit to sort by. Defaults to
 *                               10.
 * @param int    $message_limit  (optional) The max amount of messages to grab.
 *                               Defaults to 200.
 * @param bool   $remove_read    (optional) If false, will not remove the message
 *                               when read. Defaults to true.
 * @param bool   $show_filename  (optional) If true, will show the filename in
 *                               the message. Defaults to false.
 *
 *
 *
 * @level
 * debug
 * info
 * warn
 * error
 */
class modImporterExportConsoleProcessor extends modObjectProcessor
{
    protected $reader = null;
    protected $source = null;

    public function checkPermissions()
    {
        return
            $this->getProperty('modimporter_step') == 'modimporter_checkauth'
            or (
                $this->modx->user->isAuthenticated($this->modx->context->key)
                && parent::checkPermissions()
            )
            # OR php_sapi_name() == "cli"
            ;
    }

    public function initialize()
    {
        $this->modx->addPackage('modimporter', MODX_CORE_PATH.'components/modimporter/model/');

        /*
            Устанавливаем предпочтительные настройки для различных типов импорт-клиентов
        */
        switch ($this->getProperty('modimporter_client_type')) {

            // Консоль
            case 'bash':

                $this->setDefaultProperties(array(
                    'output_format' => 'print_r',
                    'modimporter_send_redirect_headers' => 1,
                ));
                break;
        }

        $this->setDefaultProperties(array(
            'use_zip' => $this->modx->getOption('modimporter.use_zip', null, true),
            'file_limit' => $this->modx->getOption('modimporter.use_zip.file_limit', null, 1024000),
            'output_format' => '',      // json or false
            'source' => (bool) $this->modx->getOption('modimporter.media_source', null, $this->modx->getOption('default_media_source', null, 1)),
            'modimporter_response_delay' => 0,
            'modimporter_send_redirect_headers' => 0,        // for local cURL mode
            "useMinishop"   => (bool)$this->modx->loadClass("msProduct"),
        ));

        $this->setProperties(array(
            'modimporter_in_cli_mode' => php_sapi_name() == 'cli',
        ));

        $this->modx->setLogLevel($this->getProperty('modimporter_log_level', $this->modx->getLogLevel()));          // 1-ERROR, 2-WARN, 3-INFO, 4-DEBUG
        $this->modx->setLogTarget($this->getProperty('modimporter_log_target', $this->modx->getLogTarget()));       // HTML|ECHO|FILE

        return true;
    }

    public function process()
    {
        return $this->processRequest();
    }

    protected function processRequest()
    {

        // Административные действия
        switch (trim($this->getProperty('modimporter_admin_action'))) {

            case 'get_actions_list':

                return $this->getActionsList();
                break;

            default:;
        }

        if (!$step = trim($this->getProperty('modimporter_step'))) {
            return $this->failure('Не указано действие');
        }

        switch ($step) {

            // Проверка авторизации
            case 'modimporter_checkauth':
                return $this->StepCheckouth();
                break;

            // Инициализация консоли
            case 'modimporter_console_init':
                return $this->StepInitConsole();
                break;

            // Подготовка данных для экспорта
            case 'modimporter_prepare_export_data':
                return $this->StepPrepareExportData();
                break;

            // Выполняем экспорт
            case 'modimporter_export':
                return $this->StepExport();
                break;

            // Выполняем импорт категорий
            case 'modimporter_export_categories':
                return $this->StepExportCategories();
                break;

            // Выполняем импорт товаров
            case 'modimporter_export_goods':

                return $this->StepExportGoods();
                break;

                // Выполняем импорт товаров
            case 'modimporter_save_file':
                return $this->StepSaveFile();
                break;

            case 'modimporter_save_record':
                return $this->StepSaveRecord();
                break;

            // Завершение экспорта
            case 'modimporter_deactivate':
                return $this->StepDeactivate();
                break;

            default:;
        }

        return $this->failure("Действие не известно '{$step}'", $this->properties);
    }

    protected function getActionsList()
    {
        $methods = get_class_methods(__CLASS__);

        $methods = array_filter($methods, function ($method) {
            return strpos($method, 'Step') === 0;
        });

        return $this->success('sad');
    }

    protected function StepInitConsole()
    {
        $this->flushSession();

        return $this->nextStep('modimporter_export', 'Консоль успешно инициализирована');
    }

    protected function StepPrepareExportData()
    {
        $this->PrepareExportData();

        return $this->nextStep('modimporter_save_file', 'Данные для экспорта успешно подготовлены');
    }

    protected function StepExport()
    {
        return $this->nextStep('modimporter_prepare_export_data', 'Начинаем выполнение экспорта', null, xPDO::LOG_LEVEL_WARN);
    }

    protected function StepSaveRecord()
    {
        $filename = 'export.csv';
        $this->SaveRecord($filename);

        return $this->nextStep('modimporter_deactivate', 'Запись экспорта успешно добавлена');
    }

    protected function StepSaveFile()
    {
        $filename = 'export.csv';
        $data = array();
        $fieldlist = array();
        $fields = $this->modx->getFields('modResource');
        foreach ($fields as $k => $v) {
            $fieldlist[] = $k;
        }
        $data[] = $fieldlist;
        $resources = $this->modx->getCollection('modResource');
        foreach ($resources as $res) {
            $data[] = $res->toArray();
        }

        return $this->saveFile($filename, $data);
    }

    // Авторизация
    protected function StepCheckouth()
    {
        $this->setDefaultProperties(array(
            'username' => !empty($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '',
            'password' => !empty($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '',
        ));

        if (
            !$this->modx->user->id
            or !$this->modx->user->isAuthenticated($this->modx->context->key)
        ) {
            $username = $this->getProperty('username');
            $password = $this->getProperty('password');

            if (!$username) {
                return $this->failure('Не указан логин');
            }

            if (!$password) {
                return $this->failure('Не указан пароль');
            }

            if (!$response = $this->modx->runProcessor('security/login', array(
                'username' => $username,
                'password' => $password,
            ))) {
                return $this->failure('Ошибка выполнения запроса');
            }

            // else
            if ($response->isError()) {
                if (!$msg = $response->getMessage()) {
                    $msg = 'Ошибка авторизации.';
                }

                return $this->failure($msg);
            }
        }

        // else
        return $this->prepareAuthResponse();
    }

    protected function prepareAuthResponse()
    {
        return $this->success('Пользователь успешно авторизован', array(
            'session_name' => session_name(),
            'session_id' => session_id(),
        ));
    }

    /*
        Деактивация.
        Следует учитывать, что некоторые источники могут за раз выполнять несколько загрузок.
        К примеру так делает 1С (может отдельно слать картинки, отдельно каталог, отдельно товарные предложения).
        Один раз за сеанс 1С шлет только метод init
    */
    protected function StepDeactivate()
    {
        return $this->success('Экспорт успешно завершен', null, xPDO::LOG_LEVEL_WARN);
    }

    protected function &getSource()
    {
        if (!$this->source) {
            $source_id = $this->getProperty('source');

            if (
                $source_id
                and $source = $this->modx->getObject('sources.modMediaSource', $source_id)
                and $source->initialize()
            ) {
                $this->source = $source;
            }
        }

        return $this->source;
    }

    protected function getImportPath()
    {
        if (!$source = &$this->getSource()) {
            return false;
        }

        return $source->getBasePath();
    }

    protected function getFilePath()
    {
        if (!$id = $this->getProperty('source') or !$source = $this->modx->getObject('sources.modMediaSource', $id)) {
            return '';
        };

        // Инициализируем
        if (!$source->initialize()) {
            return false;
        }
        $bases = $source->getBases($this->getProperty('file'));

        if (!file_exists($bases['pathAbsoluteWithPath']) or !is_readable($bases['pathAbsoluteWithPath'])) {
            return false;
        }

        return $bases['pathAbsoluteWithPath'];
    }

    # const LOG_LEVEL_FATAL = 0;
    # const LOG_LEVEL_ERROR = 1;
    # const LOG_LEVEL_WARN = 2;
    # const LOG_LEVEL_INFO = 3;
    # const LOG_LEVEL_DEBUG = 4;

    public function success($msg = '', $object = null, $level = xPDO::LOG_LEVEL_INFO, $continue = false, $step = '')
    {
        return $this->prepareResponse(true, $msg, $object, $level, $continue, $step);
    }

    public function failure($msg = '', $object = null, $level = xPDO::LOG_LEVEL_ERROR, $continue = false, $step = '')
    {

        // Фиксируем ошибку в MODX, чтобы можно было потом проверить
        // ее наличие методом $this->hasErrors();
        // Получить значения ошибок можно методом $ths->modx->error->getErrors()
        parent::failure($msg, $object);

        return $this->prepareResponse(false, $msg, $object, $level, $continue, $step);
    }

    protected function prepareResponse($success, $msg = '', $object = null, $level = xPDO::LOG_LEVEL_INFO, $continue = false, $step = '')
    {
        if ($response_delay = (int) $this->getProperty('modimporter_response_delay')) {
            sleep($response_delay);
        }

        $result = array(
            'success' => $success,
            'message' => $msg,
            'level' => $level,
            'continue' => $continue,
            'step' => $step,
            'data' => array(),          // Надо, чтобы MODX-Ajax не разваливался
            'object' => $object,
        );

        $this->prepareData($result);

        if ($continue && $this->getProperty('modimporter_send_redirect_headers')) {
            $url = parse_url($_SERVER['REQUEST_URI']);

            parse_str($url['query'], $arr);

            if ($step) {
                $arr['modimporter_step'] = $step;
            }

            $redirect_url = $url['path'].'?'.http_build_query($arr);

            header("Location: {$redirect_url}");
        }

        return $this->formatOutput($result);
    }

    protected function prepareData(array &$result)
    {
        return $result;
    }

    protected function formatOutput($result)
    {
        if ($this->getProperty('output_format') == 'json') {
            $result = json_encode($result);
        } elseif ($this->getProperty('output_format') == 'print_r') {
            $result = print_r($result, 1);
        }

        return $result;
    }

    protected function progress($msg = '', $object = null, $level = xPDO::LOG_LEVEL_INFO, $step = '')
    {
        return $this->success($msg, $object, $level, true, $step);
    }

    protected function nextStep($step, $msg = '', $object = null, $level = xPDO::LOG_LEVEL_INFO)
    {
        return $this->progress($msg, $object, $level, $step);
    }

    // Сохранение файла
    protected function saveFile($filename, $data)
    {
        $DATA = '';
        $file_post_encoding = $this->getProperty('file_post_encoding', 'windows-1251');

        if (!$filename) {
            return $this->failure('Не было указано имя файла');
        }

        if (!$data) {
            return $this->failure('Не были получены данные для экспорта');
        }

        $exportPath = $this->getImportPath();
        
        $ABS_FILE_NAME = $exportPath.$filename;

        if (!$fp = fopen($ABS_FILE_NAME, 'w')) {
            return $this->failure(false, 'Ошибка открытия файла');
        }
        fputs($fp, $bom = (chr(0xEF).chr(0xBB).chr(0xBF)));

        foreach ($data as $fields) {
            if (!fputcsv($fp, $fields, ';')) {
                return $this->failure(false, 'Ошибка записи файла');
            }
        }

        fclose($fp);

        return $this->afterFileSave($filename, $exportPath);
    }

    protected function afterFileSave($filename, $path)
    {
        return $this->nextStep('modimporter_save_record', 'Файл экспорта успешно сохранен');
    }

    protected function &getSession()
    {
        if (!isset($_SESSION['modImporter'])) {
            $_SESSION['modImporter'] = array(
                'STEP' => '',
            );
        }

        return $_SESSION['modImporter'];
    }

    protected function getSessionValue($key)
    {
        $NS = $this->getSession();

        return isset($NS[$key]) ? $NS[$key] : null;
    }

    protected function setSessionValue($key, $value)
    {
        $NS = &$this->getSession();
        $NS[$key] = $value;

        return;
    }

    protected function flushSession()
    {
        $NS = &$this->getSession();

        $NS = array();

        return;
    }

    # protected function getStep(){
    #
    #     return $this->getSessionValue("STEP");
    # }
    #
    # protected function setStep($step){
    #
    #     $this->setSessionValue("STEP", $step);
    #
    #     return;
    # }

    protected function &getReader(array $params = array())
    {
        if (!$this->reader) {
            $className = $this->getProperty('readerClassname', 'reader.modImporterXmlReader');

            if (
                $reader = &$this->modx->getService('modImporterParser', $className, '', $params)
                and $reader->initialize($this)
            ) {
                $this->reader = &$reader;
            }
        }

        return $this->reader;
    }

    protected function PrepareExportData()
    {
        $data = $this->modx->getCollection('modResource');
        foreach ($data as $r) {
            if (!$r->externalKey) {
                $r->set('externalKey', 'export-'.$r->id);
                $r->save();
            }
        }
    }

    protected function SaveRecord($filepath, $className = 'modImporterExport')
    {
        $record = $this->modx->newObject($className);
        $record->set('url', $filepath);
        $record->set('exportdon', date('Y-m-d H:i:s'));

        return ($record->save()) ? true : false;
    }

    # protected function processNode(SimpleXMLElement $node){
    #     print_r($node);
    #     return true;
    # }

    // Очищаем кеш
    # protected function clearCache(){
    #     $this->modx->runProcessor('system/clearcache');
    #     return;
    # }
}

return 'modImporterExportConsoleProcessor';
