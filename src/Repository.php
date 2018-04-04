<?php declare(strict_types=1);

namespace Salamium\Database;

use Nette\Database\Table\ActiveRow;

/**
 * @author Milan Matejcek
 */
abstract class Repository
{

	/**
	 * @var string
	 * @readonly
	 */
	protected $table;

	/** @var Context */
	protected $context;

	public function __construct($table, Context $context)
	{
		$this->table = $table;
		$this->context = $context;
		$this->construct();
	}

	/**
	 * @param mixed $id
	 * @return int
	 */
	public function delete($id): int
	{
		return $this->wherePrimary($id)->delete();
	}

	public function exists(array $condition): ?ActiveRow
	{
		$entity = $this->bindConditions($condition)->limit(1)->fetch();
		if ($entity === false) {
			return null;
		}
		return $entity;
	}

	public function wherePrimary($id): Table\Selection
	{
		return $this->createSelection()->wherePrimary($id);
	}

	/**
	 * @param array $data
	 * @return Table\Entity
	 */
	public function insert($data)
	{
		return $this->createSelection()->insert($data);
	}

	public function save(array $condition, array $data): ?ActiveRow
	{
		$entity = $this->exists($condition);
		if ($entity !== null) {
			$entity->update($data);
			return $entity;
		}
		return $this->insert($data + $condition);
	}

	public function select(?string $columns = null, ...$args): Table\Selection
	{
		$sql = $this->createSelection();
		if ($columns !== null) {
			$sql->select($columns, ...$args);
		}
		return $sql;
	}

	public function update($id, $data): int
	{
		return $this->wherePrimary($id)->update($data);
	}

	public function getTransaction(): Transaction
	{
		return $this->context->getTransaction();
	}

	protected function createSelection(): Table\Selection
	{
		return $this->context->table($this->table);
	}

	/**
	 * Use in trait if you need anything set.
	 */
	protected function construct()
	{
	}

	/** @return string */
	final protected function getPrimary()
	{
		return $this->context->getConventions()->getPrimary($this->table);
	}

	protected function bindConditions(array $condition)
	{
		$sql = $this->createSelection();
		foreach ($condition as $column => $value) {
			$sql->where($column, $value);
		}
		return $sql;
	}

}
