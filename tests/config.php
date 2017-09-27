<?php
$config = [
	'mysql' => [
		'dns' => 'mysql:host=localhost;dbname=test',
		'user' => null,
		'password' => null
	],
	'pgsql' => [
		'dns' => 'pgsql:host=localhost;dbname=test',
		'user' => null,
		'password' => null
	],
	'sqlite' => [
		'dns' => 'sqlite::memory:',
		'user' => null,
		'password' => null
	]
];
$localConfig = __DIR__ . '/config.local.php';
$local = [];
if (is_file($localConfig)) {
	$local = require $localConfig;
}
return Nette\Utils\Arrays::mergeTree($local, $config);
