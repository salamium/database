<?php

namespace Salamium\Database\DI;

use Salamium\Test\Repository,
	Salamium\Database,
	Nette\Database AS ND,
	Nette\DI,
	Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

$compiler = new DI\Compiler();
$compiler->addConfig([
	'parameters' => [
		'tempDir' => TEMP_DIR
	],
	'services' => [
		'database.foo.conventions' => [
			'factory' => Database\Conventions\Convention::class,
			'arguments' => [
				'@myConvention',
				[
					\Salamium\Test\Entity\User::class
				]
			]
		],
		'myConvention' => ND\Conventions\StaticConventions::class,
		'storage' => \Nette\Caching\Storages\DevNullStorage::class,
		'user.repository' => [
			'factory' => Repository\Users::class,
			'arguments' => ['users']
		],
		'country.repository' => [
			'factory' => Repository\Countries::class,
			'arguments' => ['countries']
		],
	]
]);

$extension = new DatabaseExtension();
$extension->setConfig([
	'entityMap' => [
		'default' => [
			\Salamium\Test\Entity\User::class
		],
	]
]);
$database = new \Nette\Bridges\DatabaseDI\DatabaseExtension();

$database->setConfig([
	'default' => [
		'debugger' => FALSE,
		'dsn' => 'sqlite::memory:'
	],
	'foo' => [
		'debugger' => FALSE,
		'dsn' => 'sqlite::memory:',
	],
	'bar' => [
		'debugger' => FALSE,
		'dsn' => 'sqlite::memory:',
		'conventions' => NULL
	]
]);
$compiler->addExtension('salamium.database', $extension);
$compiler->addExtension('database', $database);
//file_put_contents(__DIR__ . '/container.php', "<?php\n" . $compiler->compile());
eval($compiler->compile());
$container = new \Container();

/* @var $context Database\Context */
$context = $container->getService('database.default.context');
Assert::type(Database\Context::class, $context);

Assert::type(Database\Conventions\IConventions::class, $context->getConventions());

/* @var $context Database\Context */
$context = $container->getService('database.foo.context');
Assert::type(Database\Context::class, $context);

Assert::type(Database\Conventions\IConventions::class, $context->getConventions());

/* @var $context Database\Context */
$context = $container->getService('database.bar.context');
Assert::type(Database\Context::class, $context);

Assert::type(Database\Conventions\IConventions::class, $context->getConventions());