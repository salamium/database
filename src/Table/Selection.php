<?php declare(strict_types=1);

namespace Salamium\Database\Table;

use Nette\Database\Table as NDT;

/**
 * @author Milan Matějček
 */
class Selection extends NDT\Selection
{

	use SelectionTrait;
}
