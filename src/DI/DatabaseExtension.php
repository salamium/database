<?php

namespace Salamium\Database\DI;

use Salamium\Database,
	Nette\DI\CompilerExtension;

class DatabaseExtension extends CompilerExtension
{

	public $defaults = [
		'entityMap' => []
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		// cacheAccessor
		$builder->addDefinition($this->prefix('cacheAccessor'))
			->setClass(Database\Extension\Caching\CacheAccessor::class);

		// netteStaticConventions
		$builder->addDefinition($this->prefix('netteStaticConventions'))
			->setClass(\Nette\Database\Conventions\StaticConventions::class)
			->setAutowired(FALSE);

		// convention
		$convention = $builder->addDefinition($this->prefix('convention'))
			->setClass(Database\Conventions\Convention::class, [$this->prefix('@netteStaticConventions'), $config['entityMap']]);

		$this->updateContext($convention);
		$this->checkEntity($config['entityMap']);

		return $builder;
	}

	private function checkEntity($entityMap)
	{
		$error = '';
		foreach ($entityMap as $table => $entity) {
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

	private function updateContext($convention)
	{
		foreach ($this->getContainerBuilder()->getDefinitions() as $definition) {
			/* @var $definition \Nette\DI\ServiceDefinition */
			if ($definition->getClass() === \Nette\Database\Context::class) {
				$arguments = $definition->getFactory()->arguments;

				if (!$arguments[2]->getClass() instanceof Database\Conventions\IConventions) {
					$arguments[2] = $convention;
				}

				$definition->setClass(Database\Context::class, $arguments);
			}
		}
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$cache = $builder->getDefinition($this->prefix('cacheAccessor'));
		foreach ($builder->getDefinitions() as $definition) {
			if ($definition->getClass() && $this->isNeedCacheAccessor($definition)) {
				$definition->addSetup('?->setCacheAccessor(?)', [$definition, $cache]);
			}
		}
	}

	private function isNeedCacheAccessor($definition)
	{
		$cache = ['Salamium\Database\Extension\ListCacheTrait', 'Salamium\Database\Extension\CacheTrait'];
		$class = new \ReflectionClass($definition->getClass());
		foreach ($class->getTraits() as $trait) {
			if (in_array($trait->name, $cache)) {
				return TRUE;
			}
		}
		return FALSE;
	}

}
