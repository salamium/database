<?php declare(strict_types=1);

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


	/** Return deep transaction. */
	public function begin(): int
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


	/** Return deep transaction. */
	public function commit(): int
	{
		if ($this->checkTransaction() > 1) {
			$this->connection->query('RELEASE ' . $this->getSavepoint());
		} else {
			$this->connection->getPdo()->commit();
		}
		return --$this->id;
	}


	/** Return deep transaction. */
	public function rollBack(): int
	{
		if ($this->checkTransaction() > 1) {
			$this->connection->query('ROLLBACK TO ' . $this->getSavepoint());
		} else {
			$this->connection->getPdo()->rollBack();
		}
		return --$this->id;
	}


	/**
	 * @param callable $callback
	 * @return mixed
	 */
	public function transaction(callable $callback)
	{
		try {
			$this->begin();
			$return = call_user_func($callback);
			$this->commit();
			return $return;
		} catch (\Exception $e) {
			$this->rollBack();
			throw $e;
		}
	}


	/** Return deep transaction. */
	public function inTransaction(): int
	{
		$inTransaction = $this->connection->getPdo()->inTransaction();
		if ($inTransaction && $this->id < 1 || !$inTransaction && $this->id > 0) {
			throw new NoTransactionException('Your transaction are out of internal state. Let\'s fix it.');
		}
		return $this->id;
	}


	/** Return deep transaction. */
	private function checkTransaction(): int
	{
		if (!$this->inTransaction()) {
			throw new NoTransactionException('Let\'s start transaction via begin().');
		}
		return $this->id;
	}


	private function getSavepoint(): string
	{
		return 'SAVEPOINT id_' . $this->id;
	}

}
