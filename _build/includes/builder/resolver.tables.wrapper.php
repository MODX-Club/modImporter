<?php

$modx->log(modX::LOG_LEVEL_INFO, 'Tables resolvers adding initiated…'); flush();
$vehicle->resolve('php', array(
 'source' => $sources['resolvers'].'resolver.tables.php',
));

$modx->log(modX::LOG_LEVEL_INFO, 'Tables were resolved.'); flush();
