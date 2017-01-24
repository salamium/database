<?php

namespace Salamium\Database\Conventions;

use Nette\Database as ND;

interface IConventions extends ND\IConventions
{

	/**
	 * @param string $table
	 */
	function getEntityClass($table);
}
