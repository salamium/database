<?php

namespace Salamium\Test\Repository;

class Countries extends \Salamium\Database\Repository
{

	use \Salamium\Database\Extension\ListCacheTrait;


	protected function loadDialItems()
	{
		return $this->select()->order('name')->fetchPairs('id', 'name');
	}

}
