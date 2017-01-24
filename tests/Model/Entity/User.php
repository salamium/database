<?php

namespace Salamium\Test\Entity;

/**
 * @property int $id
 * @property string $name
 * @property string $surname
 * @property string $fullName
 * @property Country $country
 */
class User extends \Salamium\Database\Table\Entity
{

	public function getCountry()
	{
		return $this->ref('countries');
	}

	public function getFullName()
	{
		return trim($this->name . ' ' . $this->surname);
	}

}
