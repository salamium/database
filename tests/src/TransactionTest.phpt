<?php

namespace Salamium\Database;

use Tester\Assert;

require __DIR__ . '/../bootstrap-container.php';

class TransactionTest extends \Tester\TestCase
{

	/** @var Context */
	private $context;

	public function __construct(Context $context)
	{
		$this->context = $context;
	}

	public function testBasic()
	{
		$transaction = $this->context;
		Assert::equal(1, $transaction->beginTransaction());
		Assert::equal(0, $transaction->commit());
		$this->checkNotInTransaction();

		Assert::equal(1, $transaction->beginTransaction());
		// fail
		Assert::equal(0, $transaction->rollBack());
		$this->checkNotInTransaction();
	}

	public function testSavepoint()
	{
		$transaction = $this->context->getTransaction();
		Assert::equal(1, $transaction->begin());
		Assert::equal(2, $transaction->begin());
		Assert::equal(1, $transaction->commit());
		Assert::equal(0, $transaction->commit());
		$this->checkNotInTransaction();

		Assert::equal(1, $transaction->begin());
		Assert::equal(2, $transaction->begin());
		Assert::equal(1, $transaction->rollBack());
		Assert::equal(0, $transaction->commit());
		$this->checkNotInTransaction();
	}

	public function testFail()
	{
		$transaction = $this->context->getTransaction();
		Assert::exception(function() use ($transaction) {
			$transaction->commit();
		}, NoTransactionException::class);

		Assert::exception(function() use ($transaction) {
			$transaction->rollBack();
		}, NoTransactionException::class);
	}

	private function checkNotInTransaction()
	{
		Assert::equal(0, $this->context->getTransaction()->inTransaction());
	}

}

RunTest::run(function($context) {
	(new TransactionTest($context))->run();
});
