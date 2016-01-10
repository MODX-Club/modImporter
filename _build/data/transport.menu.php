<?php

$menus = array();

# $action= $modx->newObject('modAction');
# $action->fromArray(array(
#   'id' => 1,
#   'namespace' => NAMESPACE_NAME,
#   'parent' => 0,
#   'controller' => 'controllers/mgr/indexpanel',
#   'haslayout' => true,
#   'lang_topics' => NAMESPACE_NAME.':default',
#   'assets' => '',
# ),'',true,true);

$menuindex = 0;

$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
  'text' => NAMESPACE_NAME,
  'parent' => 'components',
  'description' => NAMESPACE_NAME.'.desc',
  # 'icon' => 'images/icons/plugin.gif',
  'action'      => 'controllers/mgr/import/index',
  'params'      => '',
  'handler'     => '',
  'menuindex'   => $menuindex++,
  'permissions' => 'modimporter',
  'namespace'   => NAMESPACE_NAME,
),'',true,true);

$menus[] = $menu;


    $menu = $modx->newObject('modMenu',array(
      'parent' => NAMESPACE_NAME,
      'description' => "modimporter.from_xml.desc",
      'action'      => 'controllers/mgr/import/xml/index',
      'params'      => '',
      'handler'     => '',
      'menuindex'   => $menuindex++,
      'permissions' => 'modimporter',
      'namespace'   => NAMESPACE_NAME,
    ));
    $menu->set("text", "modimporter.from_xml");
    $menus[] = $menu;
    
    
        $menu = $modx->newObject('modMenu',array(
          'parent' => "modimporter.from_xml",
          'description' => "modimporter.from_xml.1c.desc",
          'action'      => 'controllers/mgr/import/xml/1c/index',
          'params'      => '',
          'handler'     => '',
          'menuindex'   => $menuindex++,
          'permissions' => 'modimporter',
          'namespace'   => NAMESPACE_NAME,
        ));
        $menu->set("text", "modimporter.from_xml.1c");
        $menus[] = $menu;


    $menu = $modx->newObject('modMenu',array(
      'parent' => NAMESPACE_NAME,
      'description' => "modimporter.from_csv.desc",
      'action'      => 'controllers/mgr/import/csv/index',
      'params'      => '',
      'handler'     => '',
      'menuindex'   => $menuindex++,
      'permissions' => 'modimporter',
      'namespace'   => NAMESPACE_NAME,
    ));
    $menu->set("text", "modimporter.from_csv");
    $menus[] = $menu;


    $menu = $modx->newObject('modMenu',array(
      'parent' => NAMESPACE_NAME,
      'description' => "modimporter.from_xlsx.desc",
      'action'      => 'controllers/mgr/import/xlsx/index',
      'params'      => '',
      'handler'     => '',
      'menuindex'   => $menuindex++,
      'permissions' => 'modimporter',
      'namespace'   => NAMESPACE_NAME,
    ));
    $menu->set("text", "modimporter.from_xlsx");
    $menus[] = $menu;


    $menu = $modx->newObject('modMenu',array(
      'parent' => NAMESPACE_NAME,
      'description' => "modimporter.unzip.desc",
      'action'      => 'controllers/mgr/import/zip/unzip',
      'params'      => '',
      'handler'     => '',
      'menuindex'   => $menuindex++,
      'permissions' => 'modimporter',
      'namespace'   => NAMESPACE_NAME,
    ));
    $menu->set("text", "modimporter.unzip");
    $menus[] = $menu;




return $menus;