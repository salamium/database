<?php

namespace Salamium\Database;

use Nette\Database\Table,
	Nette\DI,
	Tester\Assert;

class RunTest
{

	public static function run(callable $factory)
	{
		foreach (self::getConnection() as $name => $context) {
			/* @var $context Context */
			if (!$context->getConnection()->getDsn()) {
				echo "\nSkipped:\nConnection for \"{$name}\" is not available.\n";
				continue;
			}
			$factory($context);
		}
	}

	/**
	 * @param $pattern
	 * @param $method
	 * @internal only for CheckSelection*
	 */
	public static function compareFile($pattern, $method)
	{
		$reflection = new \ReflectionClass(Table\Selection::class);
		Assert::same(1, preg_match('~' . preg_quote($pattern) . '~', file_get_contents($reflection->getFileName())), Table\Selection::class . '::' . $method . ' was changed.');
	}

	private static function getConnection()
	{
		$services = Environment::getContainer()->findByType(Context::class);
		$context = [];
		foreach ($services as $class) {
			/* @var $connection Context */
			$context[explode('.', $class)[1]] = Environment::getService($class);
		}
		if (!$context) {
			throw new InvalidArgumentException('No database connection exists.');
		}
		return $context;
	}

}

class Environment
{
	/** @var DI\Container */
	private static $container;

	public static function setContainer($container)
	{
		return self::$container = $container;
	}

	public static function getContainer()
	{
		return self::$container;
	}

	public static function getByType($class)
	{
		return self::$container->getByType($class);
	}

	public static function getService($name)
	{
		return self::$container->getService($name);
	}
}