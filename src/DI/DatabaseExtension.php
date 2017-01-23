<?php

namespace Salamium\Database\DI;

use Salamium\Database,
	Nette\DI\CompilerExtension;

class DatabaseExtension extends CompilerExtension
{

	public $defaults = [
		'enityNamespace' => '',
		'entityMap' => []
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		// cacheAccessor
		$builder->addDefinition($this->prefix('cacheAccessor'))
			->setClass(Database\Caching\CacheAccessor::class);

		// netteStaticConventions
		$builder->addDefinition($this->prefix('netteStaticConventions'))
			->setClass(\Nette\Database\Conventions\StaticConventions::class)
			->setAutowired(FALSE);

		// convention
		$convention = $builder->addDefinition($this->prefix('convention'))
			->setClass(Database\Conventions\Convention::class, [$this->prefix('@netteStaticConventions'), $config['enityNamespace'], $config['entityMap']]);

		$this->updateContext($convention);

		return $builder;
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

}
