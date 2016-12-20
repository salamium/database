<?php

namespace Salamium\Database\Caching;

use Nette\Caching;

class CacheAccessor
{

	/** @var Caching\IStorage */
	private $storage;

	/** @var Caching\Cache */
	private $cache;

	public function __construct(Caching\IStorage $storage)
	{
		$this->storage = $storage;
	}

	/** @return Caching\Cache */
	public function get()
	{
		if ($this->cache === NULL) {
			$this->cache = new Caching\Cache($this->storage, 'salamium.database');
		}
		return $this->cache;
	}

}
