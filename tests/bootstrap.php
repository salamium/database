<?php

use Nette\Utils;

include __DIR__ . "/../vendor/autoload.php";
include __DIR__ . '/RunTest.php';

$configurator = new Nette\Configurator();

$tmp = __DIR__ . '/temp/' . getmypid();
$logDir = $tmp . '/log';
Utils\FileSystem::createDir($logDir);

$configurator->enableDebugger($logDir);
$configurator->setTempDirectory($tmp);
$configurator->setDebugMode(TRUE);
Tracy\Debugger::enable(FALSE);

$configurator->createRobotLoader()
	->addDirectory(__DIR__ . '/Model')
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

Tester\Environment::setup();

return Salamium\Database\RunTest::setContainer($container);
