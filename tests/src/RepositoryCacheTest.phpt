<?php

namespace Salamium\Database;

use Salamium,
	Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

class RepositoryCacheTest extends \Tester\TestCase
{

	/** @var \Salamium\Test\Repository\Countries */
	private $countries;

	public function __construct(\Salamium\Test\Repository\Countries $countryRepository)
	{
		$this->countries = $countryRepository;
	}

	protected function setUp()
	{
		$this->countries->getTransaction()->begin();
	}

	protected function tearDown()
	{
		$this->countries->getTransaction()->rollback();
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

(new RepositoryCacheTest($container->getByType(Salamium\Test\Repository\Countries::class)))->run();
