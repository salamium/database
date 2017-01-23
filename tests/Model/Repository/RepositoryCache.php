<?php

namespace Salamium\Test\Repository;

abstract class RepositoryCache extends \Salamium\Database\Repository
{

	use \Salamium\Database\Extension\ListCacheTrait;

	public function __construct($table, \Salamium\Database\Context $context, \Salamium\Database\Extension\Caching\CacheAccessor $cacheAccessor)
	{
		parent::__construct($table, $context);
		$this->setCache($cacheAccessor);
	}

}
