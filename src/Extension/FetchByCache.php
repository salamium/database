<?php

namespace Salamium\Database\Extension;

trait FetchByCache
{

	private $fetchEntity = [];

	/**
	 * @param array $condition
	 * @param string $column
	 * @return Table\Entity
	 */
	public function fetchBy(array $condition, $column = NULL, ...$args)
	{
		$key = serialize($condition);
		if (!isset($this->fetchEntity[$key])) {
			$this->fetchEntity[$key] = parent::fetchBy($condition, $column, ...$args);
		}
		return $this->fetchEntity[$key];
	}

}
