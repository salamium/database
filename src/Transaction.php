<?php

namespace Salamium\Database;

use Nette\Database as ND;

class Transaction
{

	/** @var ND\Connection */
	private $connection;

	/** @var int 0 - mean not in transaction */
	private $id = 0;


	public function __construct(ND\Connection $connection)
	{
		$this->connection = $connection;
	}


	/** @return int Id */
	public function begin()
	{
		if ($this->inTransaction()) {
			++$this->id;
			$this->connection->query($this->getSavepoint());
		} else {
			++$this->id;
			$this->connection->getPdo()->beginTransaction();
		}
		return $this->id;
	}


	/** @return int Id */
	public function commit()
	{
		if ($this->checkTransaction() > 1) {
			$this->connection->query('RELEASE ' . $this->getSavepoint());
		} else {
			$this->connection->getPdo()->commit();
		}
		return --$this->id;
	}


	/** @return int Id of point */
	public function rollBack()
	{
		if ($this->checkTransaction() > 1) {
			$this->connection->query('ROLLBACK TO ' . $this->getSavepoint());
		} else {
			$this->connection->getPdo()->rollBack();
		}
		return --$this->id;
	}


	/**
	 * @param \Closure $callback
	 * @return mixed
	 */
	public function transaction(\Closure $callback)
	{
		try {
			$this->begin();
			$return = $callback();
			$this->commit();
			return $return;
		} catch (\Exception $e) {
			$this->rollBack();
			throw $e;
		}
	}


	/** @return int Id */
	public function inTransaction()
	{
		$inTransaction = $this->connection->getPdo()->inTransaction();
		if ($inTransaction && $this->id < 1 || !$inTransaction && $this->id > 0) {
			throw new NoTransactionException('Your transaction are out of internal state. Let\'s fix it.');
		}
		return $this->id;
	}


	/** @return int */
	private function checkTransaction()
	{
		if (!$this->inTransaction()) {
			throw new NoTransactionException('Let\'s start transaction via begin().');
		}
		return $this->id;
	}


	private function getSavepoint()
	{
		return 'SAVEPOINT id_' . $this->id;
	}

}
