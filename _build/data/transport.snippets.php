<?php

$snippets = array();

/* course snippets */

$list = array(PKG_NAME_LOWER);

foreach ($list as $v) {
    $snippet_name = $v;
    $content = getSnippetContent($sources['snippets'].$snippet_name.'.snippet.php');

    if (!empty($content)) {
        $snippet = $modx->newObject('modSnippet');
        $snippet->fromArray(array(
      'name' => $snippet_name,
      'description' => $snippet_name.'_desc',
      'snippet' => $content,
    ), '', true, true);
        $modx->log(modX::LOG_LEVEL_INFO, $snippet_name.' snippet was added.');
        flush();

        $path = $sources['properties']."{$snippet_name}.snippet.properties.php";
        if (is_file($path)) {
            $properties = include $path;
            $snippet->setProperties($properties);
            $modx->log(modX::LOG_LEVEL_INFO, 'Properties for '.$snippet_name.' snippet were added.');
            flush();
        }

        $snippets[] = $snippet;
    }
}

unset($properties, $snippet, $path, $snippet_name, $content, $list);

return $snippets;
