<?php

namespace Salamium\Test\Entity;

/**
 * @property int $id
 * @property string $name
 * @property string $surname
 * @property string $fullName
 * @property Country $country
 * @property Country[] $countries
 */
class User extends \Salamium\Database\Table\Entity
{

	public function getCountry()
	{
		return $this->ref('countries');
	}

	public function getCountries()
	{
		foreach ($this->related('users_x_countries', 'users_id') as $item) {
			yield $item->ref('countries', 'countries_id');
		}
	}

	public function getFullName()
	{
		return trim($this->name . ' ' . $this->surname);
	}

}
