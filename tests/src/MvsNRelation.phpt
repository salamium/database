<?php

namespace Salamium\Database;

use Salamium,
	Tester\Assert;

require __DIR__ . '/../bootstrap-container.php';

class MvsNRelation extends \Tester\TestCase
{

	/** @var Salamium\Test\Repository\UsersXCountries */
	private $usersXCountries;


	protected function setUp()
	{
		$this->usersXCountries = Environment::getByType(Salamium\Test\Repository\UsersXCountries::class);
		$this->usersXCountries->getTransaction()->begin();
	}


	protected function tearDown()
	{
		$this->usersXCountries->getTransaction()->rollBack();
	}


	public function testUpdateRelation()
	{
		$this->usersXCountries->updateRelation(['users_id' => 2], ['countries_id' => [1, 2, 3]]);
		$this->usersXCountries->updateRelation(['users_id' => 1], ['countries_id' => [1, 2, 3]]);
		$this->usersXCountries->updateRelation(['users_id' => 1], ['countries_id' => [1, 2]]);
		$this->usersXCountries->updateRelation(['users_id' => 1], ['countries_id' => [2, 3]]);
		Assert::same([2, 3], $this->usersXCountries->findBy(['users_id' => 1])->fetchPairs(null, 'countries_id'));
		Assert::same([1, 2, 3], $this->usersXCountries->findBy(['users_id' => 2])->fetchPairs(null, 'countries_id'));
	}

}

(new MvsNRelation)->run();
