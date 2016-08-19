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
class modModimporterImportConsoleProcessor extends modObjectProcessor
{
    protected $reader = null;
    protected $source = null;

    // Классы временных таблиц
    protected $tmpClasses = array(
        'modImporterObject',
    );

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
            'source' => (int) $this->modx->getOption('modimporter.media_source', null, $this->modx->getOption('default_media_source', null, 1)),
            'modimporter_response_delay' => 0,
            'modimporter_send_redirect_headers' => 0,        // for local cURL mode
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

            // Загрузить файл
            case 'modimporter_upload_file':

                return $this->StepSaveFile();
                break;

            // Распаковать файл
            case 'modimporter_unzip_file':

                return $this->StepUnzipFile();
                break;

            // Выполняем импорт
            case 'modimporter_import':

                return $this->StepImport();
                break;

                // Удаление временных таблиц
                case 'modimporter_drop_tmp_tables':

                    return $this->StepDropTmpTables();
                    break;

                // Создание временных таблиц
                case 'modimporter_create_tmp_tables':

                    return $this->StepCreateTmpTables();
                    break;

                // Записываем все временные данные
                case 'modimporter_write_tmp_data':

                    return $this->StepWriteTmpData();
                    break;

                        // Выполняем импорт коммерческой информации
                        case 'modimporter_write_tmp_commercial_info':

                            return $this->StepWriteTmpCommercialInfo();
                            break;

                        // Выполняем импорт категорий
                        case 'modimporter_write_tmp_categories':

                            return $this->StepWriteTmpCategories();
                            break;

                        // Выполняем импорт товаров
                        case 'modimporter_write_tmp_goods':

                            return $this->StepWriteTmpGoods();
                            break;

                        // Выполняем импорт цен
                        case 'modimporter_write_tmp_prices':

                            return $this->StepWriteTmpPrices();
                            break;

                        // Выполняем импорт цен
                        case 'modimporter_write_tmp_remains':

                            return $this->StepWriteTmpRemains();
                            break;

                // Записываем все временные данные
                case 'modimporter_import_data':

                    return $this->StepImportData();
                    break;

                        // Выполняем импорт категорий
                        case 'modimporter_import_categories':

                            return $this->StepImportCategories();
                            break;

                            // Снимаем с публикации отсутствующие
                            case 'modimporter_import_unpublish_categories':

                                return $this->StepImportUnpublishCategories();
                                break;

                            // Обновляем категории
                            case 'modimporter_import_update_categories':

                                return $this->StepImportUpdateCategories();
                                break;

                            // Создаем новые
                            case 'modimporter_import_create_categories':

                                return $this->StepImportCreateCategories();
                                break;

                        // Выполняем импорт товаров
                        case 'modimporter_import_goods':

                            return $this->StepImportGoods();
                            break;

                            // Снимаем с публикации товары
                            case 'modimporter_import_unpublish_goods':

                                return $this->StepImportUnpublishGoods();
                                break;

                            // Обновляем товары
                            case 'modimporter_import_update_goods':

                                return $this->StepImportUpdateGoods();
                                break;

                            // Создаем новые товары
                            case 'modimporter_import_create_goods':

                                return $this->StepImportCreateGoods();
                                break;

                        // Выполняем импорт цен
                        case 'modimporter_import_prices':

                            return $this->StepImportPrices();
                            break;

                            // Сбрасываем цены
                            case 'modimporter_import_flush_prices':

                                return $this->StepImportFlushPrices();
                                break;

                            // Создаем
                            case 'modimporter_import_create_prices':

                                return $this->StepImportCreatePrices();
                                break;

                        // Выполняем импорт цен
                        case 'modimporter_import_remains':

                            return $this->StepImportRemains();
                            break;

                // Завершение импорта
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

        return $this->nextStep('modimporter_import', 'Консоль успешно инициализирована');
    }

    protected function StepDropTmpTables()
    {
        $this->dropTmpTables();

        return $this->nextStep('modimporter_create_tmp_tables', 'Временные таблицы успешно удалены');
    }

    protected function StepCreateTmpTables()
    {
        if (!$this->createTmpTables()) {
            return $this->failure('Ошибка создания временных таблиц');
        }

        return $this->nextStep('modimporter_write_tmp_data', 'Временные таблицы успешно созданы');
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

    protected function StepUnzipFile()
    {
        if (!$filename = $this->getProperty('filename')) {
            return $this->failure('Не было получено имя файла');
        }

        if (!$result = $this->unzip($filename)) {
            return $this->failure('Ошибка распаковки архива');
        }

        // else
        return $this->prepareSuccessUnzipFileResponse();
    }

    protected function prepareSuccessUnzipFileResponse()
    {
        return $this->success('Файл успешно распакован');
    }

    protected function StepImport()
    {
        return $this->nextStep('modimporter_drop_tmp_tables', 'Начинаем выполнение импорта');
    }

    // Запись временных данных

    protected function StepWriteTmpData()
    {
        return $this->nextStep('modimporter_write_tmp_commercial_info', 'Начинаем разбор исходных данных', null, xPDO::LOG_LEVEL_WARN);
    }

    protected function StepWriteTmpCommercialInfo()
    {
        return $this->nextStep('modimporter_write_tmp_categories', 'Коммерческая информация успешно записана');
    }

    protected function StepWriteTmpCategories()
    {
        return $this->nextStep('modimporter_write_tmp_goods', 'Категории успешно записаны');
    }

    protected function StepWriteTmpGoods()
    {
        return $this->nextStep('modimporter_write_tmp_prices', 'Товары успешно записаны');
    }

    protected function StepWriteTmpPrices()
    {
        return $this->nextStep('modimporter_write_tmp_remains', 'Цены успешно записаны');
    }

    protected function StepWriteTmpRemains()
    {
        return $this->nextStep('modimporter_import_data', 'Остатки успешно записаны');
    }

    // Импорт данных на сайт

    protected function StepImportData()
    {
        return $this->nextStep('modimporter_import_categories', 'Начинаем импорт данных на сайт', null, xPDO::LOG_LEVEL_WARN);
    }

    protected function StepImportCategories()
    {
        return $this->nextStep('modimporter_import_unpublish_categories', 'Стартуем импорт категорий');
    }

    protected function StepImportUnpublishCategories()
    {
        return $this->nextStep('modimporter_import_update_categories', 'Категории успешно сняты с публикации');
    }

    protected function StepImportUpdateCategories()
    {
        return $this->nextStep('modimporter_import_create_categories', 'Категории успешно обновлены');
    }

    protected function StepImportCreateCategories()
    {
        return $this->nextStep('modimporter_import_goods', 'Категории успешно созданы');
    }

    protected function StepImportGoods()
    {
        return $this->nextStep('modimporter_import_unpublish_goods', 'Стартуем импорт товаров');
    }

    protected function StepImportUnpublishGoods()
    {
        return $this->nextStep('modimporter_import_update_goods', 'Товары успешно сняты с публикации');
    }

    protected function StepImportUpdateGoods()
    {
        return $this->nextStep('modimporter_import_create_goods', 'Товары успешно обновлены');
    }

    protected function StepImportCreateGoods()
    {
        return $this->nextStep('modimporter_import_prices', 'Товары успешно созданы');
    }

    protected function StepImportPrices()
    {
        return $this->nextStep('modimporter_import_flush_prices', 'Стартуем импорт цен');
    }

    protected function StepImportFlushPrices()
    {
        return $this->nextStep('modimporter_import_create_prices', 'Цены успешно сброшены');
    }

    protected function StepImportCreatePrices()
    {
        return $this->nextStep('modimporter_import_remains', 'Цены успешно созданы');
    }

    protected function StepImportRemains()
    {
        return $this->nextStep('modimporter_deactivate', 'Остатки успешно импортированы');
    }

    protected function StepSaveFile()
    {
        return $this->saveFile($this->getProperty('filename'));
    }

    /*
        Деактивация.
        Следует учитывать, что некоторые источники могут за раз выполнять несколько загрузок.
        К примеру так делает 1С (может отдельно слать картинки, отдельно каталог, отдельно товарные предложения).
        Один раз за сеанс 1С шлет только метод init
    */

    protected function StepDeactivate(){
        $this->modx->cacheManager->refresh();
        return $this->success("Импорт успешно завершен", null, xPDO::LOG_LEVEL_WARN);
    }
    
    
    protected function & getSource(){
        
        if(!$this->source){
            
            $source_id = $this->getProperty("source");
            
            if(
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
    protected function saveFile($filename)
    {
        $DATA = '';
        $file_post_encoding = $this->getProperty('file_post_encoding', 'latin1');

        if (!$filename) {
            return $this->failure('Не было указано имя файла');
        }

        if (function_exists('file_get_contents')) {
            $DATA = file_get_contents('php://input');
        }

        if (!$DATA and isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $DATA = $GLOBALS['HTTP_RAW_POST_DATA'];
        }

        if (
            !$DATA
            or !$DATA_LEN = mb_strlen($DATA, $file_post_encoding)
        ) {
            return $this->failure('Не было получено содержимое файла');
        }

        // else
        $ImportPath = $this->getImportPath();
        $ABS_FILE_NAME = $ImportPath.$filename;

        if (!$fp = fopen($ABS_FILE_NAME, 'ab+')) {
            return $this->failure(false, 'Ошибка открытия файла');
        }

        // else
        if (!fwrite($fp, $DATA)) {
            return $this->failure(false, 'Ошибка записи файла');
        }

        // else
        return $this->afterFileSave($filename, $ImportPath);
    }

    protected function afterFileSave($filename, $path)
    {
        return $this->success('');
    }

    protected function unzip($filename)
    {
        $result = false;

        if ($this->modx->loadClass('compression.xPDOZip', XPDO_CORE_PATH, true, true)) {
            $path = $this->getImportPath();

            $from = $path.$filename;
            $to = $path;

            $archive = new xPDOZip($this->modx, $from);

            if ($archive) {
                $result = $archive->unpack($to);
                $archive->close();
            }
        }

        return $result;
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

    /*
        Удаление таблицы.
        Следует учитывать, что в методе removeObjectContainer не проверяется наличие таблицы
        и отсутствуие таблица может быть причиной возвращения ошибки
    */
    protected function dropTmpTables()
    {
        $removed = false;

        if ($this->tmpClasses) {
            foreach ($this->tmpClasses as $className) {
                $this->dropTmpTable($className);
            }

            $removed = true;
        }

        return $removed;
    }

    protected function dropTmpTable($className)
    {
        $manager = $this->modx->getManager();

        return $manager->removeObjectContainer($className);
    }

    /*
        Создание временных таблиц
    */
    protected function createTmpTables()
    {
        $created = false;

        if ($this->tmpClasses) {
            $manager = $this->modx->getManager();

            foreach ($this->tmpClasses as $className) {
                $manager->createObjectContainer($className);
            }

            $created = true;
        }

        return $created;
    }

    protected function createTmpTable($className)
    {
        $manager = $this->modx->getManager();

        return $manager->createObjectContainer($className);
    }

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

    protected function createImportObject($id, array $data = array(), $objectType = '', $className = 'modImporterObject')
    {
        $object = $this->modx->newObject($className, $data);

        $object->set('tmp_external_key', $id);
        $object->set('tmp_object_type', $objectType);

        return $object;
    }

    protected function getImportObject($id, $objectType = null, $className = 'modImporterObject')
    {
        $condition = array(
            'tmp_external_key' => $id,
        );

        if ($objectType !== null) {
            $condition['tmp_object_type'] = $objectType;
        }

        return $this->modx->getObject($className, $condition);
    }

    protected function setLastImportDate($id, $className = 'modImporterImport')
    {
        $import = $this->modx->getObject($className, $id);
        $import->set('lastimportdon', date('Y-m-d H:i:s'));

        return ($import->save()) ? true : false;
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

return 'modModimporterImportConsoleProcessor';
