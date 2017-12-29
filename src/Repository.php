<?php

namespace Salamium\Database;

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
	public function delete($id)
	{
		return $this->find($id)->delete();
	}

	/**
	 * @param array $condition
	 * @return int
	 */
	public function deleteBy(array $condition)
	{
		return $this->findBy($condition)->delete();
	}

	/**
	 * @param array $condition
	 * @return Table\Entity|FALSE
	 */
	public function exists(array $condition)
	{
		return $this->findBy($condition)->limit(1)->fetch();
	}

	/**
	 * @param array $condition
	 * @return bool
	 */
	public function existsStrict(array $condition)
	{
		return (bool) $this->findBy($condition)->select('1 AS exists')->limit(1)->fetch();
	}

	/**
	 * @param mixed $id
	 * @param string $column
	 * @param mixed $params
	 * @return Table\Entity
	 */
	public function fetch($id, $column = null, ...$params)
	{
		$sql = $this->find($id);
		if ($column) {
			$sql->select($column, ...$params);
		}
		return $sql->fetch();
	}

	/**
	 * @param array $condition
	 * @param string $column
	 * @param mixed $params
	 * @return Table\Entity
	 */
	public function fetchBy(array $condition, $column = null, ...$params)
	{
		$sql = $this->findBy($condition);
		if ($column) {
			$sql->select($column, ...$params);
		}
		return $sql->fetch();
	}

	/**
	 * @param mixed $id
	 * @return Table\Selection
	 */
	public function find($id)
	{
		return $this->createSelection()->wherePrimary($id);
	}

	/**
	 * @param array $condition
	 * @return Table\Selection
	 */
	public function findBy(array $condition)
	{
		$sql = $this->createSelection();
		foreach ($condition as $column => $value) {
			$sql->where($column, $value);
		}
		return $sql;
	}

	/**
	 * @param array $data
	 * @return Table\Entity
	 */
	public function insert($data)
	{
		return $this->createSelection()->insert($data);
	}

	/**
	 * Only for unique row.
	 * @param array $condition
	 * @param array $data
	 * @return Table\Entity
	 */
	public function save(array $condition, array $data)
	{
		if ($entity = $this->exists($condition)) {
			$entity->update($data);
			return $entity;
		}
		return $this->insert($data + $condition);
	}

	/**
	 * @param string|NULL $columns
	 * @param mixed $args
	 * @return Table\Selection
	 */
	public function select($columns = null, ...$args)
	{
		$sql = $this->createSelection();
		if ($columns !== null) {
			$sql->select($columns, ...$args);
		}
		return $sql;
	}

	/**
	 * @param mixed $id
	 * @param array $data
	 * @return int
	 */
	public function update($id, $data)
	{
		return $this->find($id)->update($data);
	}

	/**
	 * @param array $condition
	 * @param array $data
	 * @return int
	 */
	public function updateBy(array $condition, $data)
	{
		return $this->findBy($condition)->update($data);
	}

	/** @return Transaction */
	public function getTransaction()
	{
		return $this->context->getTransaction();
	}

	/** @return Table\Selection */
	protected function createSelection()
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

}
