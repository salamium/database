<?php

use Nette\Utils;

$loader = include __DIR__ . '/../vendor/autoload.php';

/* @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('Salamium\Test\\', __DIR__ . '/Model/');

include __DIR__ . '/RunTest.php';

define('TEMP_DIR', __DIR__ . '/temp/' . getmypid());

$logDir = TEMP_DIR . '/log';
Utils\FileSystem::createDir($logDir);

Tester\Environment::setup();
Tracy\Debugger::enable(FALSE, $logDir);

return $logDir;