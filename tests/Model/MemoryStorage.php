<?php

namespace Salamium\Test;

use Nette\Caching;

/**
 * Memory cache storage.
 */
class MemoryStorage implements Caching\IStorage
{

	/** @var array */
	private $data = [];

	/** @var array */
	private $tags = [];

	/**
	 * Read from cache.
	 * @param  string key
	 * @return mixed|NULL
	 */
	public function read($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}

	/**
	 * Prevents item reading and writing. Lock is released by write() or remove().
	 * @param  string key
	 * @return void
	 */
	public function lock($key)
	{
	}

	/**
	 * Writes item into the cache.
	 * @param  string key
	 * @param  mixed  data
	 * @param  array  dependencies
	 * @return void
	 */
	public function write($key, $data, array $dependencies)
	{
		$this->data[$key] = $data;
		if (isset($dependencies[Caching\Cache::TAGS])) {
			foreach ((array) $dependencies[Caching\Cache::TAGS] as $tag) {
				$this->tags[$tag][] = $key;
			}
		}
	}

	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return void
	 */
	public function remove($key)
	{
		unset($this->data[$key]);
	}

	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param  array  conditions
	 * @return void
	 */
	public function clean(array $conditions)
	{
		if (!empty($conditions[Caching\Cache::ALL])) {
			$this->data = [];
			return;
		}
		if (!empty($conditions[Caching\Cache::TAGS])) {
			foreach ($conditions[Caching\Cache::TAGS] as $tag) {
				if (!isset($this->tags[$tag])) {
					continue;
				}
				foreach ($this->tags[$tag] as $key) {
					$this->remove($key);
				}
			}
		}
	}

}
