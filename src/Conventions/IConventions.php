<?php declare(strict_types=1);

namespace Salamium\Database\Conventions;

use Nette\Database as ND;

interface IConventions extends ND\IConventions
{

	function getEntityClass(string $table): string;

}
