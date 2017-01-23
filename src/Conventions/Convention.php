<?php

namespace Salamium\Database\Conventions;

use Salamium\Database,
	Nette\Database as ND;

class Convention implements IConventions
{

	/** @var string */
	private $entityNS;

	/** @var array */
	private $entityClass;

	/** @var ND\IConventions */
	private $conventions;

	/** @var array */
	private $entityMap = [];

	public function __construct(ND\IConventions $conventions, $entityNS, array $entityMap)
	{
		$this->conventions = $conventions;
		$this->entityNS = trim($entityNS, '\\') . '\\';
		$this->entityMap = $entityMap;
	}

	public function getEntityClass($table)
	{
		if (!isset($this->entityMap[$table])) {
			return FALSE;
		} elseif (isset($this->entityClass[$table])) {
			return $this->entityClass[$table];
		}
		$class = $this->entityNS . $this->entityMap[$table];
		if (!class_exists($class)) {
			throw new Database\InvalidArgumentException("Create class {$class} or remove definotion from entityMap:.");
		}
		return $this->entityClass[$table] = $class;
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

	public function checkEntity($table)
	{
		if (!array_key_exists($table, $this->entityMap)) {
			throw new Database\InvalidStateException('You forgot add entity for table "' . $table . '" to entityMap.');
		}
	}

}
