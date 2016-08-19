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
  'action' => 'controllers/mgr/import/index',
  'params' => '',
  'handler' => '',
  'menuindex' => $menuindex++,
  'permissions' => 'modimporter',
  'namespace' => NAMESPACE_NAME,
), '', true, true);

$menus[] = $menu;

return $menus;
