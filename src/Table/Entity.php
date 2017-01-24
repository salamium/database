<?php

namespace Salamium\Database\Table;

abstract class Entity extends \Nette\Database\Table\ActiveRow
{
	/** @var array */
	private $map = [];

	public function &__get($key)
	{
		$method = $this->getMethod($key);

		if ($method) {
			$value = $this->{$method}();
			return $value;
		}
		return parent::__get($key);
	}

	private function getMethod($key)
	{
		if (!isset($this->map[$key])) {
			$this->map[$key] = $method = self::protpertyToMethod($key);
			if (!method_exists($this, $method)) {
				$this->map[$key] = FALSE;
			}
		}

		return $this->map[$key];
	}

	public static function protpertyToMethod($field)
	{
		return 'get' . ucfirst($field);
	}

}
