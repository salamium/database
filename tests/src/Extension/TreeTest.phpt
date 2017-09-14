<?php

use Salamium\Test\Repository,
	Salamium\Database,
	Tester\Assert;

$container = require __DIR__ . '/../../bootstrap-container.php';

Database\RunTest::run(function ($context) {
	/* @var $menu Repository\Menu */
	$menu = new Repository\Menu('menu', $context);
	$menu->freshTable();


	$foo = $menu->addAfter(NULL, ['title' => 'foo']);
	$boo = $menu->addAfter($foo->id, ['title' => 'boo']);

	$menu->addSon($foo->id, ['title' => 'foo-1']);
	$foo2 = $menu->addSonFirst($foo->id, ['title' => 'foo-2']);
	$menu->addBefore($foo2->id, ['title' => 'foo-3']);
	$menu->addSon($foo->id, ['title' => 'foo-4']);
	$bar = $menu->addAfter($foo->id, ['title' => 'bar']);

	// check structure
	$nodes = $menu->findNodes();
	Assert::same([1, 5, 4, 3, 6, 7, 2], $nodes->fetchPairs(NULL, 'id'));
	Assert::same([NULL, 1, 1, 1, 1, NULL, NULL], $nodes->fetchPairs(NULL, 'parent_id'));
	Assert::same([1, 2, 4, 6, 8, 11, 13], $nodes->fetchPairs(NULL, 'left'));
	Assert::same([10, 3, 5, 7, 9, 12, 14], $nodes->fetchPairs(NULL, 'right'));
	Assert::same([0, 1, 1, 1, 1, 0, 0], $nodes->fetchPairs(NULL, 'deep'));

	// move son
	$menu->moveNodesSon(6, 3);
	$nodes = $menu->findNodes();
	Assert::same([1, 5, 4, 3, 6, 7, 2], $nodes->fetchPairs(NULL, 'id'));
	Assert::same([NULL, 1, 1, 1, 3, NULL, NULL], $nodes->fetchPairs(NULL, 'parent_id'));
	Assert::same([1, 2, 4, 6, 7, 11, 13], $nodes->fetchPairs(NULL, 'left'));
	Assert::same([10, 3, 5, 9, 8, 12, 14], $nodes->fetchPairs(NULL, 'right'));
	Assert::same([0, 1, 1, 1, 2, 0, 0], $nodes->fetchPairs(NULL, 'deep'));

	// move after
	$menu->moveNodesAfter(3, 1);
	$nodes = $menu->findNodes();
	Assert::same([1, 5, 4, 3, 6, 7, 2], $nodes->fetchPairs(NULL, 'id'));
	Assert::same([NULL, 1, 1, NULL, 3, NULL, NULL], $nodes->fetchPairs(NULL, 'parent_id'));
	Assert::same([1, 2, 4, 7, 8, 11, 13], $nodes->fetchPairs(NULL, 'left'));
	Assert::same([6, 3, 5, 10, 9, 12, 14], $nodes->fetchPairs(NULL, 'right'));
	Assert::same([0, 1, 1, 0, 1, 0, 0], $nodes->fetchPairs(NULL, 'deep'));

	// move after, no changes
	$menu->moveNodesAfter(3, 1);
	$nodes = $menu->findNodes();
	Assert::same([1, 5, 4, 3, 6, 7, 2], $nodes->fetchPairs(NULL, 'id'));
	Assert::same([NULL, 1, 1, NULL, 3, NULL, NULL], $nodes->fetchPairs(NULL, 'parent_id'));
	Assert::same([1, 2, 4, 7, 8, 11, 13], $nodes->fetchPairs(NULL, 'left'));
	Assert::same([6, 3, 5, 10, 9, 12, 14], $nodes->fetchPairs(NULL, 'right'));
	Assert::same([0, 1, 1, 0, 1, 0, 0], $nodes->fetchPairs(NULL, 'deep'));

	// move before
	$menu->moveNodesBefore(3, 1);
	$nodes = $menu->findNodes();
	Assert::same([3, 6, 1, 5, 4, 7, 2], $nodes->fetchPairs(NULL, 'id'));
	Assert::same([NULL, 3, NULL, 1, 1, NULL, NULL], $nodes->fetchPairs(NULL, 'parent_id'));
	Assert::same([1, 2, 5, 6, 8, 11, 13], $nodes->fetchPairs(NULL, 'left'));
	Assert::same([4, 3, 10, 7, 9, 12, 14], $nodes->fetchPairs(NULL, 'right'));
	Assert::same([0, 1, 0, 1, 1, 0, 0], $nodes->fetchPairs(NULL, 'deep'));

	// move son first
	$menu->moveNodesSonFirst(2, 3);
	$nodes = $menu->findNodes();
	Assert::same([3, 2, 6, 1, 5, 4, 7], $nodes->fetchPairs(NULL, 'id'));
	Assert::same([NULL, 3, 3, NULL, 1, 1, NULL], $nodes->fetchPairs(NULL, 'parent_id'));
	Assert::same([1, 2, 4, 7, 8, 10, 13], $nodes->fetchPairs(NULL, 'left'));
	Assert::same([6, 3, 5, 12, 9, 11, 14], $nodes->fetchPairs(NULL, 'right'));
	Assert::same([0, 1, 1, 0, 1, 1, 0], $nodes->fetchPairs(NULL, 'deep'));

	// after
	$menu->moveNodesAfter(3, 7);
	$nodes = $menu->findNodes();
	Assert::same([1, 5, 4, 7, 3, 2, 6], $nodes->fetchPairs(NULL, 'id'));
	Assert::same([NULL, 1, 1, NULL, NULL, 3, 3], $nodes->fetchPairs(NULL, 'parent_id'));
	Assert::same([1, 2, 4, 7, 9, 10, 12], $nodes->fetchPairs(NULL, 'left'));
	Assert::same([6, 3, 5, 8, 14, 11, 13], $nodes->fetchPairs(NULL, 'right'));
	Assert::same([0, 1, 1, 0, 0, 1, 1], $nodes->fetchPairs(NULL, 'deep'));

	// before
	$menu->moveNodesBefore(1, 3);
	$nodes = $menu->findNodes();
	Assert::same([7, 1, 5, 4, 3, 2, 6], $nodes->fetchPairs(NULL, 'id'));
	Assert::same([NULL, NULL, 1, 1, NULL, 3, 3], $nodes->fetchPairs(NULL, 'parent_id'));
	Assert::same([1, 3, 4, 6, 9, 10, 12], $nodes->fetchPairs(NULL, 'left'));
	Assert::same([2, 8, 5, 7, 14, 11, 13], $nodes->fetchPairs(NULL, 'right'));
	Assert::same([0, 0, 1, 1, 0, 1, 1], $nodes->fetchPairs(NULL, 'deep'));

	// before
	$menu->moveNodesSon(5, 4);
	$nodes = $menu->findNodes();
	Assert::same([7, 1, 4, 5, 3, 2, 6], $nodes->fetchPairs(NULL, 'id'));
	Assert::same([NULL, NULL, 1, 4, NULL, 3, 3], $nodes->fetchPairs(NULL, 'parent_id'));
	Assert::same([1, 3, 4, 5, 9, 10, 12], $nodes->fetchPairs(NULL, 'left'));
	Assert::same([2, 8, 7, 6, 14, 11, 13], $nodes->fetchPairs(NULL, 'right'));
	Assert::same([0, 0, 1, 2, 0, 1, 1], $nodes->fetchPairs(NULL, 'deep'));

	// before
	$menu->moveNodesSon(5, 1);
	$nodes = $menu->findNodes();
	Assert::same([7, 1, 4, 5, 3, 2, 6], $nodes->fetchPairs(NULL, 'id'));
	Assert::same([NULL, NULL, 1, 1, NULL, 3, 3], $nodes->fetchPairs(NULL, 'parent_id'));
	Assert::same([1, 3, 4, 6, 9, 10, 12], $nodes->fetchPairs(NULL, 'left'));
	Assert::same([2, 8, 5, 7, 14, 11, 13], $nodes->fetchPairs(NULL, 'right'));
	Assert::same([0, 0, 1, 1, 0, 1, 1], $nodes->fetchPairs(NULL, 'deep'));

	$menu->moveNodesSon(5, 4);

	// exceptions
	Assert::exception(function () use ($menu) {
		$menu->addSon([1, 2], ['title' => 'fail']);
	}, Database\InvalidArgumentException::class);

	Assert::exception(function () use ($menu) {
		$menu->addSon(-1, ['title' => 'fail']);
	}, Database\TreeParentDoesNotExistsException::class);

	Assert::exception(function () use ($menu) {
		$menu->moveNodesSon(1, 5);
	}, Database\TreeLogicException::class);

	// crumbs
	Assert::same([1, 4, 5], $menu->getBreadcrumbs(5)->fetchPairs(NULL, 'id'));
	Assert::same([], $menu->getBreadcrumbs(-1));

	// delete subtree (clear)
	Assert::same(1, $menu->deleteSubtree(4, FALSE));
	$nodes = $menu->findNodes();
	Assert::same([7, 1, 4, 3, 2, 6], $nodes->fetchPairs(NULL, 'id'));
	Assert::same([NULL, NULL, 1, NULL, 3, 3], $nodes->fetchPairs(NULL, 'parent_id'));
	Assert::same([1, 3, 4, 7, 8, 10], $nodes->fetchPairs(NULL, 'left'));
	Assert::same([2, 6, 5, 12, 9, 11], $nodes->fetchPairs(NULL, 'right'));
	Assert::same([0, 0, 1, 0, 1, 1], $nodes->fetchPairs(NULL, 'deep'));

	// delete subtree
	$menu->addSon(4, ['title' => 'add removed']);
	Assert::same(2, $menu->deleteSubtree(4));
	$nodes = $menu->findNodes();
	Assert::same([7, 1, 3, 2, 6], $nodes->fetchPairs(NULL, 'id'));
	Assert::same([NULL, NULL, NULL, 3, 3], $nodes->fetchPairs(NULL, 'parent_id'));
	Assert::same([1, 3, 5, 6, 8], $nodes->fetchPairs(NULL, 'left'));
	Assert::same([2, 4, 10, 7, 9], $nodes->fetchPairs(NULL, 'right'));
	Assert::same([0, 0, 0, 1, 1], $nodes->fetchPairs(NULL, 'deep'));

	// delete node
	Assert::same(1, $menu->deleteNode(3));
	$nodes = $menu->findNodes();
	Assert::same([7, 1, 2, 6], $nodes->fetchPairs(NULL, 'id'));
	Assert::same([NULL, NULL, NULL, NULL], $nodes->fetchPairs(NULL, 'parent_id'));
	Assert::same([1, 3, 5, 7], $nodes->fetchPairs(NULL, 'left'));
	Assert::same([2, 4, 6, 8], $nodes->fetchPairs(NULL, 'right'));
	Assert::same([0, 0, 0, 0], $nodes->fetchPairs(NULL, 'deep'));

});