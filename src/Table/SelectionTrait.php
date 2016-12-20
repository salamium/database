<?php

namespace Salamium\Database\Table;

trait SelectionTrait
{

	public function createSelectionInstance($table = NULL)
	{
		return new Selection($this->context, $this->conventions, $table ?: $this->name, $this->cache ? $this->cache->getStorage() : NULL);
	}

	protected function createGroupedSelectionInstance($table, $column)
	{
		return new GroupedSelection($this->context, $this->conventions, $table, $column, $this, $this->cache ? $this->cache->getStorage() : NULL);
	}

	protected function createRow(array $row)
	{
		$class = $this->conventions->getEntityClass($this->getName());
		if (!$class) {
			return parent::createRow($row);
		}
		return new $class($row, $this);
	}

}
