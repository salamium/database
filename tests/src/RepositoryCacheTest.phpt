<?php

namespace Salamium\Database;

use Salamium\Test\Repository,
	Tester\Assert;

require __DIR__ . '/../bootstrap-container.php';

class RepositoryCacheTest extends \Tester\TestCase
{

	/** @var Repository\Countries */
	private $countries;

	protected function setUp()
	{
		$this->countries = Environment::getByType(Repository\Countries::class);
		$this->countries->getTransaction()->begin();
	}

	protected function tearDown()
	{
		$this->countries->getTransaction()->rollBack();
	}

	public function testBasic()
	{
		$items = $this->countries->getItems();
		Assert::same([], $items);
		$this->countries->insert(['name' => 'Foo']);
		$items = $this->countries->getItems();
		Assert::same(['Foo'], array_values($items));
		$this->countries->insert(['name' => 'Bar']);
		$items = $this->countries->getItems();
		Assert::same(['Bar', 'Foo'], array_values($items));
		$entity = $this->countries->insert(['name' => 'Zab']);
		$items = $this->countries->getItems();
		Assert::same(['Bar', 'Foo', 'Zab'], array_values($items));
		$this->countries->update($entity->id, ['name' => 'Cak']);
		$items = $this->countries->getItems();
		Assert::same(['Bar', 'Cak', 'Foo'], array_values($items));
		Assert::same($items, $this->countries->getItems()); // read from cache
		$this->countries->delete($entity->id);
		$items = $this->countries->getItems();
		Assert::same(['Bar', 'Foo'], array_values($items));
	}

}

(new RepositoryCacheTest)->run();
