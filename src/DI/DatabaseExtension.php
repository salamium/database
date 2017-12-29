<?php

namespace Salamium\Database\DI;

use Salamium\Database,
	Nette\Database AS ND,
	Nette\DI AS NDI;

class DatabaseExtension extends NDI\CompilerExtension
{

	private $defaults = [
		'entityMap' => [],
		'conventionClass' => Database\Conventions\Convention::class,
	];

	public function loadConfiguration()
	{
		$this->config += $this->defaults;
		$builder = $this->getContainerBuilder();
		// cacheAccessor
		$builder->addDefinition($this->prefix('cacheAccessor'))
			->setFactory(Database\Extension\Caching\CacheAccessor::class);
		$this->checkEntityMap();
		return $builder;
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$cache = $builder->getDefinition($this->prefix('cacheAccessor'));
		foreach ($builder->getDefinitions() as $name => $definition) {
			if ($definition->getFactory()->getEntity() === ND\Context::class) {
				$this->updateContext($builder, $name, $definition);
			}
			if ($definition->getFactory()->getEntity() && $this->isNeedCacheAccessor($definition)) {
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

	private function updateContext(NDI\ContainerBuilder $builder, $name, NDI\ServiceDefinition $definition)
	{
		$databaseName = self::getDatabaseName($name);
		if (!isset($this->config['entityMap'][$databaseName])) {
			$this->config['entityMap'][$databaseName] = [];
		}
		$arguments = $definition->getFactory()->arguments;
		if (!isset($arguments[2])) {
			$netteConvention = $builder->addDefinition($this->prefix('nette.convention.' . $databaseName))
				->setFactory(ND\Conventions\StaticConventions::class);
			$convention = $this->createConvention($builder, $netteConvention, $databaseName);
		} elseif (self::isIConventions($arguments[2]->getType(), Database\Conventions\IConventions::class)) {
			$convention = $arguments[2];
		} elseif (self::isIConventions($arguments[2]->getType(), ND\IConventions::class)) {
			$convention = $this->createConvention($builder, $arguments[2], $databaseName);
		} else {
			throw new Database\InvalidArgumentException('Unknown Conventions: ' . $arguments[2]->getType());
		}
		$arguments[2] = $convention;
		$definition->setFactory(Database\Context::class, $arguments)
			->setType(Database\Context::class);
	}

	private function isNeedCacheAccessor(NDI\ServiceDefinition $definition)
	{
		return self::isA($definition->getType(), Database\Extension\ListCacheTrait::class) || self::isA($definition->getType(), Database\Extension\CacheTrait::class);
	}

	private static function isA($className, $traitName)
	{
		try {
			$traitReflection = new \ReflectionClass($traitName);
		} catch (\ReflectionException $e) {
			throw new Database\InvalidArgumentException('Trait does not exists: ' . $traitName);
		}

		if (!$traitReflection->isTrait()) {
			return false;
		}

		return self::hasTrait(new \ReflectionClass($className), $traitName);
	}

	private static function hasTrait(\ReflectionClass $class, $traitName)
	{
		$traits = $class->getTraits();
		if (isset($traits[$traitName])) {
			return true;
		}

		foreach ($traits as $traitReflection) {
			if (self::hasTrait($traitReflection, $traitName)) {
				return true;
			}
		}

		if ($class->getParentClass()) {
			return self::hasTrait($class->getParentClass(), $traitName);
		}
		return false;
	}

	private function createConvention($builder, $convention, $databaseName)
	{
		return $builder->addDefinition($this->prefix('convention.' . $databaseName))
			->setClass($this->config['conventionClass'], [
				$convention,
				$this->config['entityMap'][$databaseName],
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
