<?php

namespace Salamium\Database;

use Salamium,
	Tester\Assert;

require __DIR__ . '/../bootstrap.php';

class TempItemTest extends \Tester\TestCase
{

	/** @var \Salamium\Test\Repository\Users */
	private $users;

	public function __construct(\Salamium\Test\Repository\Users $users)
	{
		$this->users = $users;
	}

	public function testBasic()
	{
		$user = $this->users->insert([
			'name' => 'Doe',
			'surname' => 'Jou'
		]);
		$user1 = $this->users->fetchItem($user->id);
		$user2 = $this->users->fetchItem($user->id);
		Assert::same($user1, $user2);
	}

}

$users = $container->getByType(Salamium\Test\Repository\Users::class);
(new TempItemTest($users))->run();
