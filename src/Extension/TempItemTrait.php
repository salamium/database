<?php

namespace Salamium\Database\Extension;

use Salamium\Database\Table;

/**
 * For one request save data to local temp variable.
 * For example fetch user by id. You don't need every time send sql to database.
 */
trait TempItemTrait
{

	/** @var Table\Entity[] */
	private $items = [];

	/** @var string */
	private $cacheColumn;

	/**
	 * @param int $itemId
	 * @return Table\Entity|NULL
	 */
	public function fetchItem($itemId)
	{
		if (!$itemId) {
			return null;
		}
		if (!isset($this->items[$itemId])) {
			$this->items[$itemId] = $this->fetchBy([$this->cacheColumn => $itemId]);
		}
		return $this->items[$itemId];
	}

	public function update($id, $data)
	{
		unset($this->items[$id]);
		return parent::updateBy([$this->cacheColumn => $id], $data);
	}

	public function saveItem(Table\Entity $entity)
	{
		$this->items[(string) $entity->{$this->cacheColumn}] = $entity;
	}

	protected function setCacheColumn($cacheColumn)
	{
		$this->cacheColumn = $cacheColumn;
	}

	protected function construct()
	{
		parent::construct();
		$this->setCacheColumn($this->getPrimary());
	}

}
