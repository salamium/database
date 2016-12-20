<?php

namespace Salamium\Database;

use Salamium,
	Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

class MvsNRelation extends \Tester\TestCase
{

	/** @var \Salamium\Test\Repository\UsersXBooks */
	private $usersXBooks;

	public function __construct(\Salamium\Test\Repository\UsersXBooks $usersXBooks)
	{
		$this->usersXBooks = $usersXBooks;
	}

	protected function setUp()
	{
		$this->usersXBooks->getTransaction()->begin();
	}

	protected function tearDown()
	{
		$this->usersXBooks->getTransaction()->rollback();
	}

	public function testUpdateRelation()
	{
		$this->usersXBooks->updateRelation(['user_id' => 2], ['book_id' => [1, 2, 3]]);
		$this->usersXBooks->updateRelation(['user_id' => 1], ['book_id' => [1, 2, 3]]);
		$this->usersXBooks->updateRelation(['user_id' => 1], ['book_id' => [1, 2]]);
		$this->usersXBooks->updateRelation(['user_id' => 1], ['book_id' => [2, 3]]);
		Assert::same([2, 3], $this->usersXBooks->findBy(['user_id' => 1])->fetchPairs(NULL, 'book_id'));
		Assert::same([1, 2, 3], $this->usersXBooks->findBy(['user_id' => 2])->fetchPairs(NULL, 'book_id'));
	}

}

(new MvsNRelation($container->getByType(Salamium\Test\Repository\UsersXBooks::class)))->run();
