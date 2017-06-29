<?php

namespace Salamium\Test\Repository;

class Users extends \Salamium\Database\Repository
{

	use \Salamium\Database\Extension\TempItemTrait;

	protected function prepareData($data)
	{
		if (isset($data['name'])) {
			$data['name'] .= '1';
		}
		return $data;
	}

}
