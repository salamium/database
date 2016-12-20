<?php

$config = array(
    'mysql' => array(
        'dns' => 'mysql:host=localhost;dbname=test',
        'user' => NULL,
        'password' => NULL
    ),
    'pgsql' => array(
        'dns' => 'pgsql:host=localhost;dbname=test',
        'user' => NULL,
        'password' => NULL
    ),
    'sqlite' => array(
        'dns' => 'sqlite::memory:',
        'user' => NULL,
        'password' => NULL
    )
);

$localConfig = __DIR__ . '/config.local.php';
$local = array();
if (is_file($localConfig)) {
    $local = require $localConfig;
}

return Nette\Utils\Arrays::mergeTree($local, $config);
