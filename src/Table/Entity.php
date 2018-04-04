<?php declare(strict_types=1);

namespace Salamium\Database\Table;

abstract class Entity extends \Nette\Database\Table\ActiveRow
{

	/** @var array */
	private $map = [];


	public function &__get($key)
	{
		$method = $this->getMethod($key);
		if ($method !== '') {
			$value = $this->{$method}();
			return $value;
		}
		return parent::__get($key);
	}


	private function getMethod(string $key): string
	{
		if (!isset($this->map[$key])) {
			$this->map[$key] = $method = self::propertyToMethod($key);
			if (!method_exists($this, $method)) {
				$this->map[$key] = '';
			}
		}
		return $this->map[$key];
	}


	public static function propertyToMethod(string $field): string
	{
		return 'get' . ucfirst($field);
	}

}
