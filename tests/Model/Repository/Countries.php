<?php

namespace Salamium\Test\Repository;

class Countries extends RepositoryCache
{

	protected function loadDialItems()
	{
		return $this->find()->order('name')->fetchPairs('id', 'name');
	}

}
