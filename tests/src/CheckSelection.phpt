<?php

namespace Salamium\Database;

require __DIR__ . '/../bootstrap-container.php';

class CheckSelection extends \Tester\TestCase
{

	/**
	 * @dataProvider CheckSelection.ini
	 */
	public function testSelection($pattern, $method)
	{
		RunTest::compareFile($pattern, $method);
	}

}

(new CheckSelection)->run();
