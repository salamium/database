<?php

namespace Salamium\Database;

use Salamium,
	Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class RepositoryTest extends \Tester\TestCase
{

	/** @var \Salamium\Test\Repository\Users */
	private $users;

	public function __construct(\Salamium\Test\Repository\Users $userRepository)
	{
		$this->users = $userRepository;
	}

	protected function setUp()
	{
		$this->users->getTransaction()->begin();
	}

	protected function tearDown()
	{
		$this->users->getTransaction()->rollback();
	}

	public function testFetch()
	{
		$entity = $this->users->insert([
			'name' => 'Milan',
			'surname' => 'h4kuna'
		]);

		/* @var $entity \Salamium\Test\Entity\User */
		Assert::same('Milan1 h4kuna', $entity->fullName);
		Assert::same('Milan1', $entity->name);
		Assert::same('h4kuna', $entity->surname);
		Assert::true(is_int($entity->id));

		/* @var $entity2 \Salamium\Test\Entity\User */
		$entity2 = $this->users->fetch($entity->id, 'name, ? AS surname', 'foo');
		Assert::same('Milan1 foo', $entity2->fullName);
	}

	public function testSave()
	{
		$entity = $this->users->save(['surname' => 'h4kuna'], ['name' => 'Foo']);
		$entity2 = $this->users->save(['surname' => 'h4kuna'], ['name' => 'Bar']);
		Assert::same($entity->id, $entity2->id);
		Assert::same('Foo1', $entity->name);
		Assert::same('Bar1', $entity2->name);
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

}

(new RepositoryTest($container->getByType(Salamium\Test\Repository\Users::class)))->run();
