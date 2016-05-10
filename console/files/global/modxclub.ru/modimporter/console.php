<?php

echo '<pre>';
ini_set('display_errors', 1);
$modx->switchContext('web');
$modx->setLogLevel(3);
$modx->setLogTarget('HTML');

$namespace = 'modimporter';        // Неймспейс комопонента

// Добавить веб-сессию текущего пользователя
// Если вы не авторизованы во фронте, можете получить
// в результате выполнения сообщение Доступ запрещён!
// $modx->user->addSessionContext('web');

// Удалить веб-сессию текущего пользователя
// $modx->user->removeSessionContext('web');

// Сбросить сессию компонента
// $_SESSION["SM_1C_IMPORT"] = array();

// Вывести данные сессии компонента
// print_r($_SESSION["SM_1C_IMPORT"]);

// print_r($_SESSION["SM_1C_IMPORT"]);
// unset($_SESSION["modImporter"]);

$params = array(
    // "step"  => "SDFsdf",
    'debug' => false,
    // "mode" => "checkauth",
    // "modimporter_step" => "modimporter_checkauth",
    // "modimporter_step" => "modimporter_console_init",
    // "modimporter_step" => "modimporter_drop_tmp_tables",
    // "modimporter_step" => "modimporter_create_tmp_tables",
    // "modimporter_step" => "modimporter_write_tmp_xlsx_shared_strings",
    // "modimporter_step" => "modimporter_write_tmp_categories",
    // "modimporter_step" => "modimporter_import_data",
    // "modimporter_step" => "modimporter_import_update_categories",
    // "modimporter_step" => "modimporter_import_update_categories",
    // "modimporter_step" => "modimporter_import_create_categories",
    // "modimporter_step" => "modimporter_import_update_goods",
    // "modimporter_step" => "modimporter_import_create_goods",
    // "modimporter_step" => "modimporter_import_flush_prices",
    // "modimporter_step" => "modimporter_import_create_prices",

    // "filename"  => "import.xml",
    // "username"  => "admin",
    // "password"  => "wefwef",
    'outputCharset' => 'utf-8',
);

// $_SERVER['PHP_AUTH_USER'] = 'admin';
// $_SERVER['PHP_AUTH_PW'] = 'admin';

if (!$response = $modx->runProcessor('modimporter/import/console',
$params, array(
'processors_path' => $modx->getObject('modNamespace', $namespace)->getCorePath().'processors/',
))) {
    echo 'Не удалось выполнить процессор';

    return;
}

$memory = round(memory_get_usage(true) / 1024 / 1024, 4).' Mb';
echo "<div>Memory: {$memory}</div>";
$totalTime = (microtime(true) - $modx->startTime);
$queryTime = $modx->queryTime;
$queryTime = sprintf('%2.4f s', $queryTime);
$queries = isset($modx->executedQueries) ? $modx->executedQueries : 0;
$totalTime = sprintf('%2.4f s', $totalTime);
$phpTime = $totalTime - $queryTime;
$phpTime = sprintf('%2.4f s', $phpTime);
echo "<div>TotalTime: {$totalTime}</div>";

print_r($response->getResponse());

// $objects = $response->getObject();
// foreach($objects as $object){
// }
