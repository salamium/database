<?php

namespace Salamium\Database\Extension\Caching;

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
	public function get($table)
	{
		$key = 'salamium.database.' . $table;
		if (!isset($this->cache[$key])) {
			$this->cache[$key] = new Caching\Cache($this->storage, $key);
		}
		return $this->cache[$key];
	}

}
