<?php

namespace Salamium\Test\Entity;

/**
 * @property int $id
 * @property string $name
 * @property string $surname
 * @property string $fullName
 */
class User extends \Salamium\Database\Table\Entity
{

	public function getFullName()
	{
		return trim($this->name . ' ' . $this->surname);
	}

}
