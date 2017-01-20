<?php

namespace Salamium\Database\Table;

abstract class Entity extends \Nette\Database\Table\ActiveRow
{

	private static $map = [];

	/**
	 * @param string $key
	 * @param string|NULL $throughColumn
	 * @return GroupedSelection|Selection
	 */
	public function related($key, $throughColumn = NULL)
	{
		if ($throughColumn && substr($throughColumn, 0, 1) === ':') {
			return $this->getTable()->createSelectionInstance($key)
					->where($throughColumn, $this->getPrimary());
		}
		return parent::related($key, $throughColumn);
	}

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
			$method = self::protpertyToMethod($key);
			self::$map[static::class][$key] = $method;
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
