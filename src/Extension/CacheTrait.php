<?php

namespace Salamium\Database\Extension;

use Nette\Caching as NC;

trait CacheTrait
{

	/** @var Caching\CacheAccessor */
	private $cacheAccessor;

	public function setCacheAccessor(Caching\CacheAccessor $cacheAccessor)
	{
		$this->cacheAccessor = $cacheAccessor;
	}

	/** @var NC\Cache; */
	protected function getCache()
	{
		return $this->cacheAccessor->get($this->table);
	}

}
