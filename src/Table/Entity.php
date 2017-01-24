<?php

namespace Salamium\Database\Table;

abstract class Entity extends \Nette\Database\Table\ActiveRow
{

	private static $map = [];

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
		if (!isset(self::$map[static::class])) {
			self::$map[static::class] = [];
		}

		if (!isset(self::$map[static::class][$key])) {
			self::$map[static::class][$key] = $method = self::protpertyToMethod($key);
			if (!method_exists($this, $method)) {
				self::$map[static::class][$key] = FALSE;
			}
		}

		return self::$map[static::class][$key];
	}

	public static function protpertyToMethod($field)
	{
		return 'get' . ucfirst($field);
	}

}
