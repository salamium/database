<?php

namespace Salamium\Database\DI;

use Salamium\Database,
	Nette\Database AS ND,
	Nette\DI AS NDI;

class DatabaseExtension extends NDI\CompilerExtension
{

	private $defaults = [
		'entityMap' => [],
		'conventionClass' => Database\Conventions\Convention::class
	];

	public function loadConfiguration()
	{
		$this->config += $this->defaults;
		$builder = $this->getContainerBuilder();

		// cacheAccessor
		$builder->addDefinition($this->prefix('cacheAccessor'))
			->setClass(Database\Extension\Caching\CacheAccessor::class);

		$this->checkEntityMap();

		return $builder;
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$cache = $builder->getDefinition($this->prefix('cacheAccessor'));

		foreach ($builder->getDefinitions() as $name => $definition) {
			if ($definition->getClass() === ND\Context::class) {
				$this->updateContext($builder, $name, $definition);
			}
			if ($definition->getClass() && $this->isNeedCacheAccessor($definition)) {
				$definition->addSetup('?->setCacheAccessor(?)', [$definition, $cache]);
			}
		}
	}

	private function checkEntityMap()
	{
		if (!is_array(current($this->config['entityMap']))) {
			$this->config['entityMap'] = ['default' => $this->config['entityMap']];
		}

		foreach ($this->config['entityMap'] as $name => $entities) {
			$error = '';
			foreach ($entities as $table => $entity) {
				if ($entity && !class_exists($entity)) {
					if ($error) {
						$error .= ', ';
					}
					$error .= "$table: $entity";
				}
			}

			if ($error) {
				throw new Database\InvalidArgumentException('In your entityMap is defined entity whose does not exists: ' . $error);
			}
		}
	}

	private function updateContext(NDI\ContainerBuilder $builder, $name, $definition)
	{
		$databaseName = self::getDatabaseName($name);
		if (!isset($this->config['entityMap'][$databaseName])) {
			$this->config['entityMap'][$databaseName] = [];
		}

		$arguments = $definition->getFactory()->arguments;
		if (!isset($arguments[2])) {
			$netteConvention = $builder->addDefinition($this->prefix('nette.convention.' . $databaseName))
				->setClass(ND\Conventions\StaticConventions::class);
			$convention = $this->createConvention($builder, $netteConvention, $databaseName);
		} elseif (self::isIConventions($arguments[2]->getClass(), Database\Conventions\IConventions::class)) {
			$convention = $arguments[2];
		} elseif (self::isIConventions($arguments[2]->getClass(), ND\IConventions::class)) {
			$convention = $this->createConvention($builder, $arguments[2], $databaseName);
		} else {
			throw new Database\InvalidArgumentException('Unknown Conventions: ' . $arguments[2]->getClass());
		}

		$arguments[2] = $convention;
		$definition->setClass(Database\Context::class, $arguments);
	}

	private function isNeedCacheAccessor($definition)
	{
		$class = new \ReflectionClass($definition->getClass());
		return $this->checkClass4Accessor($class);
	}

	private function checkClass4Accessor(\ReflectionClass $class)
	{
		static $cache = ['Salamium\Database\Extension\ListCacheTrait', 'Salamium\Database\Extension\CacheTrait'];

		foreach ($class->getTraits() as $trait) {
			if (in_array($trait->name, $cache)) {
				return TRUE;
			}
		}

		if ($class->getParentClass()) {
			return $this->checkClass4Accessor($class->getParentClass());
		}

		return FALSE;
	}

	private function createConvention($builder, $convention, $databaseName)
	{
		return $builder->addDefinition($this->prefix('convention.' . $databaseName))
			->setClass($this->config['conventionClass'], [
				$convention,
				$this->config['entityMap'][$databaseName]
			]);
	}

	private static function getDatabaseName($name)
	{
		return explode('.', $name)[1];
	}

	private static function isIConventions($class, $interface)
	{
		$interfaces = class_implements($class);
		return isset($interfaces[$interface]);
	}
}
