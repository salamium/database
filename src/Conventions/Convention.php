<?php

namespace Salamium\Database\Conventions;

use Nette\Database as ND;

class Convention implements IConventions
{

	/** @var ND\IConventions */
	private $conventions;

	/** @var array */
	private $entityMap = [];

	public function __construct(ND\IConventions $conventions, array $entityMap)
	{
		$this->conventions = $conventions;
		$this->entityMap = $entityMap;
	}

	public function getEntityClass($table)
	{
		if (!isset($this->entityMap[$table])) {
			return FALSE;
		}
		return $this->entityMap[$table];
	}

	public function getBelongsToReference($table, $key)
	{
		return $this->conventions->getBelongsToReference($table, $key);
	}

	public function getHasManyReference($table, $key)
	{
		return $this->conventions->getHasManyReference($table, $key);
	}

	public function getPrimary($table)
	{
		return $this->conventions->getPrimary($table);
	}

}
