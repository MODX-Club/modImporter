<?php

$modx->log(modX::LOG_LEVEL_INFO, 'Register resolvers adding initiated…'); flush();
$vehicle->resolve('php', array(
 'source' => $sources['resolvers'].'resolver.register.php',
));

$modx->log(modX::LOG_LEVEL_INFO, 'Package was registered.'); flush();
