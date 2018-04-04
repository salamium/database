<?php

namespace Salamium\Database\Extension;

use Nette\Database AS ND,
	Salamium\Database,
	Salamium\Database\Table;

trait TreeTrait
{

	private static
		$AFTER = 1,
		$BEFORE = 2,
		$SON = 3,
		$SON_FIRST = 4;

	/** @var Tree\TreeColumnMapper */
	protected $columnMapper;

	/** @var bool */
	private $isSqlite;

	/** @var string */
	private $comlumns;


	public function setColumnMapper(Tree\TreeColumnMapper $columnMapper)
	{
		$this->columnMapper = $columnMapper;
	}


	/**
	 * Add node as son to end position
	 * @param int $id
	 * @param array|\ArrayAccess $data
	 * @return ND\IRow
	 */
	public function addSon($id, $data)
	{
		return $this->addNodeTransaction($id, $data, self::$SON);
	}


	/**
	 * Add node as son to first position
	 * @param int $id
	 * @param array|\ArrayAccess $data
	 * @return ND\IRow
	 */
	public function addSonFirst($id, $data)
	{
		return $this->addNodeTransaction($id, $data, self::$SON_FIRST);
	}


	/**
	 * Add node after this node
	 * @param int $id
	 * @param array|\ArrayAccess $data
	 * @return ND\IRow
	 */
	public function addAfter($id, $data)
	{
		return $this->addNodeTransaction($id, $data, self::$AFTER);
	}


	/**
	 * Add node before this
	 * @param int $id
	 * @param array|\ArrayAccess $data
	 * @return ND\IRow
	 */
	public function addBefore($id, $data)
	{
		return $this->addNodeTransaction($id, $data, self::$BEFORE);
	}


	/**
	 * Append to end of tree
	 * @param array|\ArrayAccess $data
	 * @return ND\IRow
	 */
	public function append($data)
	{
		$l = $this->columnMapper->left;
		$r = $this->columnMapper->right;
		return $this->getTransaction()->transaction(function () use ($l, $r, $data) {
			$row = $this->select("COALESCE( MAX($r), 0) + 1 AS  $l, COALESCE( MAX($r), 0) + 2 AS $r")->fetch();
			$data[$l] = $row[$l];
			$data[$r] = $row[$r];
			$data[$this->columnMapper->deep] = 0;
			$data[$this->columnMapper->parentId] = null;
			return $this->insert($data);
		});
	}


	/**
	 * @param int $fromId
	 * @param int $toId
	 * @return int
	 */
	public function moveNodesAfter($fromId, $toId)
	{
		return $this->moveNodesTransaction($fromId, $toId, self::$AFTER);
	}


	/**
	 * @param int $fromId
	 * @param int $toId
	 * @return int
	 */
	public function moveNodesBefore($fromId, $toId)
	{
		return $this->moveNodesTransaction($fromId, $toId, self::$BEFORE);
	}


	/**
	 * @param int $fromId
	 * @param int $toId
	 * @return int
	 */
	public function moveNodesSon($fromId, $toId)
	{
		return $this->moveNodesTransaction($fromId, $toId, self::$SON);
	}


	/**
	 * @param int $fromId
	 * @param int $toId
	 * @return int
	 */
	public function moveNodesSonFirst($fromId, $toId)
	{
		return $this->moveNodesTransaction($fromId, $toId, self::$SON_FIRST);
	}


	/**
	 * Remove all nodes under this id
	 * @param int $id Node ID
	 * @return int
	 */
	public function deleteSubtree($id, $withMe = true)
	{
		$r = $this->columnMapper->right;
		$l = $this->columnMapper->left;
		return $this->getTransaction()->transaction(function () use ($id, $l, $r, $withMe) {
			$sql = $this->find($id);
			try {
				$row = $this->forUpdate($sql, (int) $id);
			} catch (Database\TreeParentDoesNotExistsException $e) {
				return 0;
			}
			$diff = $row[$r] - $row[$l];
			if ($withMe) {
				$equal = '=';
				$rEqual = '';
				$diff += 1;
			} else {
				$equal = '';
				$rEqual = '=';
				$diff -= 1;
			}
			$res = $this->select()
				->where("$l >$equal ?", $row[$l])
				->where("$r <$equal ?", $row[$r])
				->delete();
			$this->createSelection()
				->where("$l > ?", $row[$r])
				->update([$l => self::l($this->delimite($l) . " - $diff")]);
			$this->createSelection()
				->where("$r >$rEqual ?", $row[$r])
				->update([$r => self::l($this->delimite($r) . " - $diff")]);
			return $res;
		});
	}


	/**
	 * Delete node with current id
	 * @param int $id Node ID
	 * @return int
	 */
	public function deleteNode($id)
	{
		$r = $this->columnMapper->right;
		$l = $this->columnMapper->left;
		$d = $this->columnMapper->deep;
		$p = $this->columnMapper->parentId;
		return $this->getTransaction()->transaction(function () use ($id, $r, $l, $d, $p) {
			try {
				$parent = $this->forUpdate((int) $id);
			} catch (Database\TreeParentDoesNotExistsException $e) {
				return 0;
			}
			$this->select()
				->where("$l > ?", $parent[$l])
				->where("$r < ?", $parent[$r])
				->update([
					$l => self::l($this->delimite($l) . " - 1"),
					$r => self::l($this->delimite($r) . " - 1"),
					$d => self::l($this->delimite($d) . " - 1"),
					$p => self::l("CASE WHEN $p = {$parent[$this->getPrimary()]} THEN ? ELSE $p END", $parent[$p]),
				]);
			$this->select()->where($r . ' > ?', $parent[$r])->update([
				$r => self::l($this->delimite($r) . ' - 2'),
				$l => self::l("CASE WHEN " . $this->delimite($l) . " > {$parent[$r]} THEN " . $this->delimite($l) . " - 2 ELSE " . $this->delimite($r) . " END"),
			]);
			return $this->createSelection()
				->where($this->getPrimary(), $parent[$this->getPrimary()])
				->delete();
		});
	}


	/** @return Table\Selection */
	public function findNodes()
	{
		return $this->select()->order($this->columnMapper->left);
	}


	/**
	 * Gets breadcrumbs
	 * @param int $id
	 * @return array|Table\Selection
	 */
	public function getBreadcrumbs($id)
	{
		$r = $this->columnMapper->right;
		$l = $this->columnMapper->left;
		$row = $this->fetch($id, "$r, $l");
		if (!$row) {
			return [];
		}
		return $this->findNodesAbove($row[$l], $row[$r]);
	}


	/**
	 * Gets all nodes above in tree
	 * @param int $left
	 * @param int $right
	 * @return Table\Selection
	 */
	public function findNodesAbove($left, $right)
	{
		return $this->findNodes()
			->where($this->columnMapper->left . ' <= ?', (int) $left)
			->where($this->columnMapper->right . ' >= ?', (int) $right);
	}


	/**
	 * Gets all nodes below in tree
	 * @param int $left
	 * @param int $right
	 * @return Table\Selection
	 */
	public function findNodesBelow($left, $right)
	{
		return $this->findNodes()
			->where($this->columnMapper->left . ' >= ?', (int) $left)
			->where($this->columnMapper->right . ' <= ?', (int) $right);
	}


	protected function delimite($column)
	{
		return $this->context->getConnection()->getSupplementalDriver()->delimite($column);
	}


	final protected function getColumnsForSelect()
	{
		if ($this->comlumns !== null) {
			return $this->comlumns;
		}
		$driver = $this->context->getConnection()->getSupplementalDriver();
		$this->comlumns = $driver->delimite($this->getPrimary());
		foreach ($this->columnMapper as $value) {
			$this->comlumns .= ', ';
			$this->comlumns .= $driver->delimite($value);
		}
		return $this->comlumns;
	}


	/**
	 * Gets all node ids
	 * @param int $left
	 * @param int $right
	 * @param int|NULL $toId
	 * @return array
	 * @throws Database\TreeLogicException
	 */
	private function fetchNodeIds($left, $right, $toId = null)
	{
		$out = [];
		$col = $this->getPrimary();
		foreach ($this->findNodesBelow($left, $right) as $row) {
			if ($toId && $row[$col] == $toId) {
				throw new Database\TreeLogicException('You can not move parent to son.');
			}
			$out[] = $row[$col];
		}
		return $out;
	}


	/**
	 * @param int|NULL $id
	 * @param array|\ArrayAccess $data
	 * @param int $where
	 * @return ND\IRow
	 */
	private function addNodeTransaction($id, $data, $where)
	{
		if (!$id) {
			return $this->append($data);
		}
		if (is_array($id)) {
			throw new Database\InvalidArgumentException('Id could not be array.');
		}
		return $this->getTransaction()->transaction(function () use ($id, $data, $where) {
			return $this->addNode($id, $data, $where);
		});
	}


	private function addNode($id, $data, $where)
	{
		$d = $this->columnMapper->deep;
		$l = $this->columnMapper->left;
		$p = $this->columnMapper->parentId;
		$r = $this->columnMapper->right;
		$parent = $this->forUpdate((int) $id); // lock critical section
		foreach ([$d, $l, $p, $r] as $v) {
			$data[$v] = $parent[$v];
		}
		switch ($where) {
			case self::$AFTER:
				$data[$l] = $parent[$r] + 1;
				$data[$r] += 2;
				$rUpdate = $data[$l];
				break;
			case self::$BEFORE:
				$rUpdate = $data[$r] = $parent[$l] + 1;
				break;
			case self::$SON:
				$rUpdate = $data[$l] = $parent[$r];
				++$data[$r];
				$data[$p] = $id;
				++$data[$d];
				break;
			case self::$SON_FIRST:
				$rUpdate = $parent[$l];
				++$data[$l];
				$data[$r] = $parent[$l] + 2;
				$data[$p] = $id;
				++$data[$d];
				break;
		}
		$this->createSelection()->where($l . ' >= ?', $data[$l])->update([$l => self::l($this->delimite($l) . ' + 2')]);
		$this->createSelection()->where($r . ' >= ?', $rUpdate)->update([$r => self::l($this->delimite($r) . ' + 2')]);
		return $this->insert($data);
	}


	/**
	 * Move node from id to id by method move
	 * @param int $fromId
	 * @param int $toId
	 * @param int $move
	 * @return int
	 * @throws Database\InvalidArgumentException
	 */
	private function moveNodesTransaction($fromId, $toId, $move)
	{
		return $this->getTransaction()->transaction(function () use ($fromId, $toId, $move) {
			return $this->moveNodes($fromId, $toId, $move);
		});
	}


	/**
	 * Move node from id to id by method move
	 * @param int $fromId
	 * @param int $toId
	 * @param int $move
	 * @return int
	 */
	private function moveNodes($fromId, $toId, $move)
	{
		$r = $this->columnMapper->right;
		$l = $this->columnMapper->left;
		$d = $this->columnMapper->deep;
		$p = $this->columnMapper->parentId;
		$y = $this->getPrimary();
		$rows = $this->forUpdate((int) $fromId, (int) $toId); // lock
		$from = $rows[$fromId];
		$to = $rows[$toId];
		$nodes = $from[$r] - $from[$l];
		$index = $nodes + 1;
		$left = 0;
		switch ($move) {
			case self::$AFTER:
				if ($to[$l] < $from[$l] && $from[$r] < $to[$r]) {
					$left = $to[$r] - $nodes;
					$index *= -1;
				} elseif ($from[$l] > $to[$l]) {
					$left = $to[$r] + 1;
				} else {
					$left = $to[$r] - $nodes;
				}
				break;
			case self::$BEFORE:
				$left = $to[$l];
				if ($from[$l] < $to[$l]) {
					$left -= ($nodes + 1);
				}
				break;
			case self::$SON:
				$left = $to[$r];
				if ($to[$l] < $from[$l] && $from[$r] < $to[$r]) {
					$left -= ($nodes + 1);
					$index *= -1;
				} elseif ($from[$l] < $to[$l]) {
					$left -= ($nodes + 1);
				}
				break;
			case self::$SON_FIRST:
				$left = $to[$l] + 1;
				break;
		}
		if ($move === self::$SON || $move === self::$SON_FIRST) {
			$deep = ($to[$d] + 1) - $from[$d];
			$to[$p] = $to[$y];
		} else {
			$deep = $to[$d] - $from[$d];
		}
		$diff = $left - $from[$l];
		if (!$diff) {
			return 0;
		}
		$ids = $this->fetchNodeIds($from[$l], $from[$r], $toId);
		$this->createSelection()->where($y, $ids)
			->update([
				$l => self::l($this->delimite($l) . ' + ' . $diff),
				$r => self::l($this->delimite($r) . ' + ' . $diff),
				$d => self::l($this->delimite($d) . ' + ' . $deep),
			]);
		if ($to[$p] != $from[$p]) {
			// Set new parent_id
			$this->createSelection()->where([$y => $fromId])->update([$p => $to[$p]]);
		}
		$min = min($left, $from[$l]);
		$max = max($left + $nodes, $from[$r]);
		if ($from[$l] < $to[$l]) {
			$index *= -1;
		}
		// Update moved nodes
		$res = $this->createSelection()->where($r . " >= ? AND $l <= ?", $min, $max)
			->where($y . ' NOT IN (?)', $ids)
			->update([
				$l => self::l("CASE WHEN " . $this->delimite($l) . " >= $min THEN " . $this->delimite($l) . " + $index ELSE " . $this->delimite($l) . " END"),
				$r => self::l("CASE WHEN " . $this->delimite($r) . " <= $max THEN " . $this->delimite($r) . " + $index ELSE " . $this->delimite($r) . " END"),
			]);
		return $res;
	}


	/**
	 * Select row for update/delete and lock it
	 * @param mixed $ids
	 * @return ND\IRow|ND\IRow[]
	 * @throws Database\TreeParentDoesNotExistsException
	 */
	private function forUpdate(...$ids)
	{
		$stm = 'SELECT ' . $this->getColumnsForSelect() . ' FROM ' . $this->table . ' WHERE ' . $this->getPrimary() . ' IN (?) ';
		if (!$this->isSqlite()) {
			$stm .= 'FOR UPDATE';
		}
		$sql = $this->context->query($stm, $ids);
		/* @var $row ND\IRow */
		$rows = $sql->fetchAssoc($this->getPrimary() . '|');
		switch (count($rows)) {
			case 0:
				throw new Database\TreeParentDoesNotExistsException;
			case 1:
				return reset($rows);
		}
		return $rows;
	}


	private function isSqlite()
	{
		if ($this->isSqlite === null) {
			$this->isSqlite = preg_match('/sqlite/', $this->context->getConnection()->getDsn());
		}
		return $this->isSqlite;
	}


	private static function l($value, ...$parameters)
	{
		return new ND\SqlLiteral($value, $parameters);
	}

}
