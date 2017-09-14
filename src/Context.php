<?php

namespace Salamium\Database;

use Nette\Caching,
	Nette\Database as ND;

/**
 * @author Milan Matějček
 */
class Context extends ND\Context
{

	/** @var IStorage */
	private $cacheStorage;

	/** @var Transaction */
	private $transaction;

	public function __construct(ND\Connection $connection, ND\IStructure $structure, Conventions\IConventions $conventions, Caching\IStorage $cacheStorage = NULL)
	{
		parent::__construct($connection, $structure, $conventions, $cacheStorage);
		$this->cacheStorage = $cacheStorage;
	}

	/** @return Selection */
	public function table($table)
	{
		return new Table\Selection($this, $this->getConventions(), $table, $this->cacheStorage);
	}

	public function rollBack()
	{
		return $this->getTransaction()->rollBack();
	}

	public function commit()
	{
		return $this->getTransaction()->commit();
	}

	public function beginTransaction()
	{
		return $this->getTransaction()->begin();
	}

	/** @return Transaction */
	public function getTransaction()
	{
		if ($this->transaction === NULL) {
			$this->transaction = new Transaction($this->getConnection());
		}

		return $this->transaction;
	}

}
