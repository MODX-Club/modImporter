<?php

$settings = array();

$setting_name = PKG_NAME_LOWER.'.media_source';
$setting = $modx->newObject('modSystemSetting');
$setting->fromArray(array(
    'key' => $setting_name,
    'value' => '1',
    'xtype' => 'modx-combo-source',
    'namespace' => NAMESPACE_NAME,
    'area' => 'default',
), '', true, true);

$settings[] = $setting;


$setting_name = PKG_NAME_LOWER.'.external_key_type';
$setting = $modx->newObject('modSystemSetting');
$setting->fromArray(array(
    'key' => $setting_name,
    'value' => 'varchar',
    'xtype' => 'textfield',
    'namespace' => NAMESPACE_NAME,
    'area' => 'default',
), '', true, true);

$settings[] = $setting;


$setting_name = PKG_NAME_LOWER.'.external_key_length';
$setting = $modx->newObject('modSystemSetting');
$setting->fromArray(array(
    'key' => $setting_name,
    'value' => 36,
    'xtype' => 'textfield',
    'namespace' => NAMESPACE_NAME,
    'area' => 'default',
), '', true, true);

$settings[] = $setting;


$setting_name = PKG_NAME_LOWER.'.category_template_id';
$setting = $modx->newObject('modSystemSetting');
$setting->fromArray(array(
    'key' => $setting_name,
    'value' => '',
    'xtype' => 'modx-combo-template',
    'namespace' => NAMESPACE_NAME,
    'area' => 'default',
), '', true, true);

$settings[] = $setting;


$setting_name = PKG_NAME_LOWER.'.product_template_id';
$setting = $modx->newObject('modSystemSetting');
$setting->fromArray(array(
    'key' => $setting_name,
    'value' => '',
    'xtype' => 'modx-combo-template',
    'namespace' => NAMESPACE_NAME,
    'area' => 'default',
), '', true, true);

$settings[] = $setting;


unset($setting, $setting_name);

return $settings;
