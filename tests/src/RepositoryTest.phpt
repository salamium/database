<?php

namespace Salamium\Database;

use Salamium,
	Salamium\Test\Repository,
	Tester\Assert;

require __DIR__ . '/../bootstrap-container.php';

class RepositoryTest extends \Tester\TestCase
{

	/** @var Repository\Users */
	private $users;

	/** @var Repository\Countries */
	private $countries;

	/** @var Repository\UsersXCountries */
	private $usersXCountries;

	protected function setUp()
	{
		$this->users = Environment::getByType(Repository\Users::class);
		$this->countries = Environment::getByType(Repository\Countries::class);
		$this->usersXCountries = Environment::getByType(Salamium\Test\Repository\UsersXCountries::class);
		$this->users->getTransaction()->begin();
	}

	protected function tearDown()
	{
		$this->users->getTransaction()->rollBack();
	}

	public function testEntity()
	{
		$country = $this->countries->insert([
			'name' => 'CZ'
		]);

		/* @var $entity Salamium\Test\Entity\User */
		$entity = $this->users->insert([
			'name' => 'Milan',
			'surname' => 'h4kuna',
			'countries_id' => $country->id
		]);
		Assert::true($entity instanceof Salamium\Test\Entity\User);
		$user = $this->users->fetch($entity->id);
		Assert::true($user instanceof Salamium\Test\Entity\User);
		Assert::true($user->country instanceof Salamium\Test\Entity\Country);
		Assert::same('CZ', $user->country->name); // $user->countries is possible (default by nette)
	}

	public function testFetch()
	{
		$entity = $this->users->insert([
			'name' => 'Milan',
			'surname' => 'h4kuna'
		]);

		/* @var $entity Salamium\Test\Entity\User */
		Assert::same('Milan h4kuna', $entity->fullName);
		Assert::same('Milan', $entity->name);
		Assert::same('h4kuna', $entity->surname);
		Assert::true(is_int($entity->id));

		/* @var $entity2 Salamium\Test\Entity\User */
		$entity2 = $this->users->fetch($entity->id, 'name, ? AS surname', 'foo');
		Assert::same('Milan foo', $entity2->fullName);
	}

	public function testSave()
	{
		$entity = $this->users->save(['surname' => 'h4kuna'], ['name' => 'Foo']);
		$entity2 = $this->users->save(['surname' => 'h4kuna'], ['name' => 'Bar']);
		Assert::same($entity->id, $entity2->id);
		Assert::same('Foo', $entity->name);
		Assert::same('Bar', $entity2->name);
	}

	public function testDelete()
	{
		$entity = $this->users->save(['surname' => 'h4kuna'], ['name' => 'Foo']);
		$this->users->delete($entity->id);
		Assert::false($this->users->find($entity->id)->fetch());
	}

	public function testExists()
	{
		$entity = $this->users->insert([
			'name' => 'Milan',
			'surname' => 'h4kuna'
		]);

		Assert::false($this->users->exists(['id' => -1]));
		Assert::same($this->users->exists(['id' => $entity->id])->id, $entity->id);
	}

	public function testExistsStrict()
	{
		$entity = $this->users->insert([
			'name' => 'Milan',
			'surname' => 'h4kuna'
		]);

		Assert::false($this->users->existsStrict(['id' => -1]));
		Assert::true($this->users->existsStrict(['id' => $entity->id]));
	}

	public function testRelatedCountries()
	{
		/* @var $user Salamium\Test\Entity\User */
		$user = $this->users->insert([
			'name' => 'Milan',
			'surname' => 'h4kuna'
		]);

		$country1 = $this->countries->insert([
			'name' => 'foo',
		]);

		$country2 = $this->countries->insert([
			'name' => 'bar',
		]);

		$this->usersXCountries->insert([
			[
				'users_id' => $user->id,
				'countries_id' => $country1->id
			],
			[
				'users_id' => $user->id,
				'countries_id' => $country2->id
			]
		]);

		foreach ($user->countries as $item) {
			Assert::type(Salamium\Test\Entity\Country::class, $item);
		};

	}

}

(new RepositoryTest())->run();
