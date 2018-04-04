<?php

namespace Salamium\Database\Extension;

use Nette\Caching as NC;
use Nette\Database\Row;

/**
 * If you need cache all table like dial data, for example list of countries.
 */
trait ListCacheTrait
{

	use CacheTrait;

	/** @var string */
	private $idCache;


	public function getItems()
	{
		$data = $this->getCache()->load('items');
		if ($data !== null) {
			return $data;
		}
		return $this->getCache()->save('items', $this->loadDialItems(), $this->addGlobalTag([]));
	}


	public function insert($data): Row
	{
		$result = parent::insert($data);
		$this->clearCache($data, []);
		return $result;
	}


	protected function addGlobalTag(array $conditions): array
	{
		if (!isset($conditions[NC\Cache::TAGS])) {
			$conditions[NC\Cache::TAGS] = [];
		}
		$conditions[NC\Cache::TAGS][] = $this->getGlobalTag();
		$this->prepareConditions($conditions);
		return $conditions;
	}


	protected function clearCache($data, array $condition): void
	{
		$this->getCache()->clean($this->addGlobalTag([]));
	}


	private function getGlobalTag(): string
	{
		if ($this->idCache === null) {
			$classPath = explode('\\', static::class);
			$this->idCache = end($classPath);
		}
		return $this->idCache;
	}


	protected function prepareConditions(& $conditions): void
	{
	}


	/**
	 * List of items for cache.
	 */
	abstract protected function loadDialItems(): array;

}
