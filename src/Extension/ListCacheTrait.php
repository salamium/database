<?php

namespace Salamium\Database\Extension;

use Nette\Caching as NC;

trait ListCacheTrait
{

	use CacheTrait;

	/** @var string */
	private $idCache;

	public function getItems()
	{
		$data = $this->getCache()->load('items');
		if ($data !== NULL) {
			return $data;
		}
		return $this->getCache()->save('items', $this->loadDialItems(), $this->addGlobalTag([]));
	}

	public function deleteBy(array $condition)
	{
		$result = parent::deleteBy($condition);
		$this->clearCache([], $condition);
		return $result;
	}

	public function insert($data)
	{
		$result = parent::insert($data);
		$this->clearCache($data, []);
		return $result;
	}

	public function updateBy(array $condition, $data)
	{
		$result = parent::updateBy($condition, $data);
		$this->clearCache($data, $condition);
		return $result;
	}

	protected function addGlobalTag(array $conditions)
	{
		if (!isset($conditions[NC\Cache::TAGS])) {
			$conditions[NC\Cache::TAGS] = [];
		}

		$conditions[NC\Cache::TAGS][] = $this->getGlobalTag();
		$this->prepareConditions($conditions);
		return $conditions;
	}

	protected function clearCache($data, $condition)
	{
		$this->getCache()->clean($this->addGlobalTag([]));
	}

	private function getGlobalTag()
	{
		if ($this->idCache === NULL) {
			$classPath = explode('\\', static::class);
			$this->idCache = end($classPath);
		}
		return $this->idCache;
	}

	protected function prepareConditions(& $conditions)
	{
	}

	/**
	 * List of items for cache.
	 * @return array
	 */
	abstract protected function loadDialItems();
}
