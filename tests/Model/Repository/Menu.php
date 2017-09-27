<?php

namespace Salamium\Test\Repository;

use Salamium\Database\Extension;

class Menu extends \Salamium\Database\Repository
{

	use Extension\TreeTrait;


	protected function construct()
	{
		$this->setColumnMapper(new Extension\Tree\TreeColumnMapper);
	}


	public function freshTable()
	{
		if (!preg_match('~(?P<db>.*):(?P<path>.*)$~U', $this->context->getConnection()->getDsn(), $find)) {
			throw new \RuntimeException('Bad dsn: ' . $this->context->getConnection()->getDsn());
		}
		switch ($find['db']) {
			case 'pgsql':
				$this->context->query('DROP TABLE IF EXISTS menu;');
			case 'mysql':
				break;
			case 'sqlite':
				$this->freshSqlite($find['path']);
				break;
			default:
				throw new \RuntimeException('Unknown database: ' . $find['db']);
		}
		$this->context->query(file_get_contents(__DIR__ . '/../../config/tree-' . $find['db'] . '.sql'));
	}


	private function freshSqlite($path)
	{
		if (trim($path, ':') === 'memory') {
			$this->context->query('DROP TABLE IF EXISTS menu;');
			return;
		}
		is_file($path) && unlink($path);
	}

}
