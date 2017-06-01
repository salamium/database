<?php

namespace Salamium\Test\Repository;

class Countries extends RepositoryCache
{

	protected function loadDialItems()
	{
		return $this->select()->order('name')->fetchPairs('id', 'name');
	}

}
