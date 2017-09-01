<?php

namespace Salamium\Database\Extension;

use Nette\Caching as NC;

/**
 * If you use Nette, class DatabaseExtension automatically call setter.
 *
 * Do you need cache in repository divided by tables?
 */
trait CacheTrait
{

	/** @var Caching\CacheAccessor */
	private $cacheAccessor;

	public function setCacheAccessor(Caching\CacheAccessor $cacheAccessor)
	{
		$this->cacheAccessor = $cacheAccessor;
	}

	/** @return NC\Cache */
	protected function getCache()
	{
		return $this->cacheAccessor->get($this->table);
	}

}
