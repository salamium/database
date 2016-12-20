<?php

namespace Salamium\Database;

class RunTest
{

	/** @var \Nette\DI\Container */
	private static $container;

	public static function setContainer($container)
	{
		return self::$container = $container;
	}

	public static function run(callable $factory)
	{
		foreach (self::getConnection() as $name => $context) {
			/* @var $context Context */
			if (!$context->getConnection()->getDsn()) {
				echo "\nSkipped:\nConnection for \"{$name}\" is not available.\n";
				continue;
			}
			$factory($context)->run();
		}
	}

	private static function getConnection()
	{
		$services = self::$container->findByType(Context::class);
		$context = [];
		foreach ($services as $class) {
			/* @var $connection Context */
			$context[explode('.', $class)[1]] = self::$container->getService($class);
		}
		if (!$context) {
			throw new InvalidArgumentException('No database connection exists.');
		}
		return $context;
	}

}
