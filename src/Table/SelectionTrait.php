<?php

namespace Salamium\Database\Table;

trait SelectionTrait
{

	public function createSelectionInstance($table = null)
	{
		return new Selection($this->context, $this->conventions, $table ?: $this->name, $this->cache ? $this->cache->getStorage() : null);
	}


	protected function createGroupedSelectionInstance($table, $column)
	{
		return new GroupedSelection($this->context, $this->conventions, $table, $column, $this, $this->cache ? $this->cache->getStorage() : null);
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
