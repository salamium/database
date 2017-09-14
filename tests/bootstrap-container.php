<?php

$logDir = include __DIR__ . '/bootstrap.php';

$configurator = new Nette\Configurator();

$configurator->enableDebugger($logDir);
$configurator->setTempDirectory(TEMP_DIR);
$configurator->setDebugMode(TRUE);

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

Tracy\Debugger::enable(FALSE, $logDir);

\Salamium\Database\Environment::setContainer($container);
