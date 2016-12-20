<?php

namespace Salamium\Database;

use Nette,
	Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

class CheckSelection extends \Tester\TestCase
{

	/**
	 * @dataProvider CheckSelection.ini
	 */
	public function testSelection($pattern, $method)
	{
		$reflection = new \ReflectionClass(Nette\Database\Table\Selection::class);
		Assert::same(1, preg_match('~' . preg_quote($pattern) . '~', file_get_contents($reflection->getFileName())), Nette\Database\Table\Selection::class . '::' . $method . ' was changed.');
	}

}

(new CheckSelection)->run();
