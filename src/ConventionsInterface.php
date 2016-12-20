<?php

namespace Salamium\Database;

use Nette\Database as ND;

interface ConventionsInterface extends ND\IConventions
{

	/**
	 * @param string $table
	 */
	function getEntityClass($table);

	/**
	 * @param string $table
	 */
	function checkEntity($table);
}
