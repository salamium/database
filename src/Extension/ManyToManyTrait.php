<?php

namespace Salamium\Database\Extension;

/**
 * If you have optional relation m:n.
 * For example users hobbies.
 * User has many choices and user can change it.
 */
trait ManyToManyTrait
{

	/**
	 * Table M:N update
	 * @param array $columnL - column => one value, scalar ['user_id' => 1]
	 * @param array $columnR - column => list of array ['book_id' => [1, 2, 3]]
	 */
	public function updateRelation(array $columnL, array $columnR)
	{
		$cR = key($columnR);
		$newValues = (array) current($columnR);
		$exists = $this->select()->where(key($columnL), current($columnL))->fetchPairs($cR, $cR);
		$delete = array_diff($exists, $newValues);

		$insert = [];
		foreach ($newValues as $valueR) {
			if (!array_key_exists($valueR, $exists)) {
				$insert[] = $columnL + [$cR => $valueR];
			}
		}

		if ($delete && $insert) {
			$this->getTransaction()->begin();
			$this->deleteBy($columnL + [$cR => $delete]);
			$this->insert($insert);
			$this->getTransaction()->commit();
		} elseif ($insert) {
			$this->insert($insert);
		} elseif ($delete) {
			$this->deleteBy($columnL + [$cR => $delete]);
		}
	}

}
