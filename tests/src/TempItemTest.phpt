<?php

namespace Salamium\Database;

use Salamium\Test\Repository,
	Tester\Assert;

require __DIR__ . '/../bootstrap-container.php';

class TempItemTest extends \Tester\TestCase
{

	/** @var Repository\Users */
	private $users;

	public function __construct()
	{
		$this->users = Environment::getByType(Repository\Users::class);
	}

	public function testBasic()
	{
		$user = $this->users->insert([
			'name' => 'Doe',
			'surname' => 'Jou',
		]);
		Assert::null($this->users->fetchItem(null));
		$user1 = $this->users->fetchItem($user->id);
		$user2 = $this->users->fetchItem($user->id);
		Assert::same($user1, $user2);
		$this->users->update($user->id, ['name' => 'Joe']);
		$user1 = $this->users->fetchItem($user->id);
		Assert::notSame($user1, $user2);
		$this->users->saveItem($user2);
		$user1 = $this->users->fetchItem($user->id);
		Assert::same($user1, $user2);
	}

}

(new TempItemTest)->run();
