<?php

/* @var $container \Nette\DI\Container */
$container = include __DIR__ . '/bootstrap.php';

Tracy\Debugger::enable(FALSE);


$context = $container->getByType(Salamium\Database\Context::class);

class Dsn
{

	/** @var string */
	private $dsn;

	/** @var string */
	private $dbname;

	/** @var string */
	private $db;

	public function __construct($dsn)
	{
		$this->dsn = $dsn;
	}

	public function getDbname()
	{
		$this->explode();
		return $this->dbname;
	}

	public function getDb()
	{
		$this->explode();
		return $this->db;
	}

	private function explode()
	{
		if ($this->db !== NULL) {
			return;
		}
		$dsn = explode(':', $this->dsn);
		list($this->db, $path) = $dsn;
		$out = [];
		if (in_array($this->db, ['sqlite', 'sqlite2'])) {
			throw new \Exception('not implemented');
		}

		parse_str(str_replace(';', '&', $path), $out);
		foreach ($out as $property => $value) {
			if (!property_exists($this, $property)) {
				continue;
			}
			$this->{$property} = $value;
		}
	}

}

interface DatabaseInterface
{

	/**
	 *
	 * @param callback $filter
	 * @return Generator {original table name} => {filtred table name}
	 */
	function loadTables($filter = NULL);

	/**
	 * @param string $table
	 * @return Type[] {column name} => {object Type}
	 */
	function loadTableType($table);
}

class Mysql implements DatabaseInterface
{

	/** @var \Nette\Database\Connection */
	private $connection;

	/** @var Dsn */
	private $dsn;

	public function __construct(\Nette\Database\Connection $connection, Dsn $dsn)
	{
		$this->connection = $connection;
		$this->dsn = $dsn;
	}

	public function loadTables($filter = NULL)
	{
		$sql = $this->connection->query("SHOW TABLES FROM {$this->dsn->getDbname()}");
		$key = "Tables_in_{$this->dsn->getDbname()}";
		foreach ($sql as $row) {
			$outFilter = $filter($row->{$key});
			if (!$outFilter) {
				continue;
			} elseif (!is_bool($outFilter)) {
				yield $row->{$key} => $outFilter;
			} else {
				yield $row->{$key} => $row->{$key};
			}
		}
	}

	public function loadTableType($table)
	{
		$tableInfoSql = $this->connection->query("SHOW COLUMNS FROM {$table};");
		$dbTypes = [];
		foreach ($tableInfoSql as $row) {
			$dbTypes[$row->Field] = new Type($row->Null !== 'NO', [self::typeMysqlToPhp($row->Type)], $row->Field);
		}
		return $dbTypes;
	}

	public static function typeMysqlToPhp($type)
	{
		switch (preg_replace('/(\(\d+\))?( .*)?/', '', $type)) {
			case 'tinyint':
			case 'int':
			case 'bigint':
			case 'mediumint':
				return 'int';
			case 'date':
			case 'datetime':
			case 'timestamp':
				return '\Datetime';
			case 'varchar':
			case 'longtext':
			case 'mediumtext':
			case 'text':
			case 'char':
				return 'string';
		}

		throw new \RuntimeException('Unknown type ' . $type);
	}

}

/**
 *
 * @param \Nette\Database\Connection $connection
 * @param Dsn $dsn
 * @return \DbSchema
 * @throws \RuntimeException
 */
function createDatabase(\Nette\Database\Connection $connection)
{
	$dsn = new Dsn($connection->getDsn());
	if (!$dsn->getDbname()) {
		throw new \RuntimeException('Unknown database name.');
	}
	$class = ucfirst($dsn->getDb());
	return new DbSchema(new $class($connection, $dsn));
}

class DbSchema
{

	/** @var DatabaseInterface */
	private $database;

	/** @var array */
	private $tables;

	public function __construct(DatabaseInterface $database)
	{
		$this->database = $database;
	}

	public function loadTables($filter = NULL)
	{
		if ($this->tables !== NULL) {
			return $this->tables;
		}
		$tables = $this->database->loadTables($filter);
		$this->tables = [];
		foreach ($tables as $original => $table) {
			$this->tables[$original] = \Salamium\DataType\Basic\Strings::toPascal($table);
		}
		return $this->tables;
	}

	/**
	 * @param string $table
	 * @return Type[]
	 */
	public function loadTableType($table)
	{
		return $this->database->loadTableType($table);
	}

}

// list of tables

$database = createDatabase($context->getConnection());

$filter = function($table) {
	// @todo dependency
	if (substr($table, 0, 7) === 'copy_CV') {
		return FALSE;
	}
	return str_replace(['CV_'], '', $table);
};
$repositories = $database->loadTables($filter);

function mergeProperty($reflection)
{

	$annotationClass = \Nette\Reflection\AnnotationsParser::getAll($reflection);
	$annotations = [];
	foreach (['property', 'property-read', 'property-write'] as $property) {
		if (!isset($annotationClass[$property])) {
			continue;
		}
		foreach ($annotationClass[$property] as $annotation) {
			if (!preg_match('/(?P<type>.*?) \$(?P<field>\w+)/i', $annotation, $find)) {
				throw new InvalidArgumentException('Unknown property: ' . $annotation);
			}

			$isNull = FALSE;
			$types = array_filter(explode('|', $find['type']), function($value) use (&$isNull) {
				if (strtolower($value) === 'null') {
					$isNull = TRUE;
					return FALSE;
				}
				return TRUE;
			});

			$annotations[$find['field']] = new Type($isNull, $types, $find['field']);
		}
	}
	return $annotations;
}

class Type
{

	/** @var bool */
	public $isNull;

	/** @var string[] */
	public $type;

	/** @var string */
	public $field;

	/** @var bool */
	public $isVirtual = FALSE;

	public function __construct($isNull, array $type, $field)
	{
		$this->isNull = $isNull;
		$this->type = $type;
		$this->field = $field;
	}

	public function __toString()
	{
		$property = $this->isVirtual ? 'property-read' : 'property';
		$null = $this->isNull ? '|NULL' : '';
		return '@' . $property . ' ' . implode('|', $this->type) . $null . ' $' . $this->field;
	}

}

function getMethods($reflection)
{
	$methods = $reserved = [];
	$classReserv = [Salamium\Database\Table\Entity::class, Nette\Database\Table\ActiveRow::class];
	foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
		if (in_array($method->class, $classReserv)) {
			$reserved[$method->name] = $method->name;
		} elseif (substr($method->name, 0, 3) === 'get') {
			$methods[$method->name] = $method->name;
		}
	}
	$intersect = array_intersect_key($reserved, $methods);
	if ($intersect) {
		// warning
		throw new \Nette\InvalidStateException('You using reserved method, be careful ' . implode(', ', $intersect));
	}
	return $methods;
}

$existsRepository = $existsEntity = NULL;
$repositoryMissing = $entityMissing = [];
foreach ($repositories as $table => $repository) {
	try {
		$r = 'App\Repository\\' . $repository;
		$container->getByType($r);
		$existsRepository = $r;
	} catch (Nette\DI\MissingServiceException $e) {
		$repositoryMissing[$table] = $repository;
		// repository does not exists
		continue;
	}

	/* @var $convention \Salamium\Database\Conventions\StaticConventions */
	$convention = $context->getConventions();

	$entityClass = $convention->getEntityClass($table);
	if (!$entityClass) {
		$entityMissing[$table] = $repository;
		continue;
	}

	$existsEntity = $entityClass;

	$reflection = new ReflectionClass($entityClass);
	$phpTypes = mergeProperty($reflection);

	$dbTypes = $database->loadTableType($table);

	$methods = getMethods($reflection);
	foreach (array_diff_key($phpTypes, $dbTypes) as $type) {
		/* @var $type Type */
		$findMethod = \Salamium\Database\Table\Entity::propertyToMethod($type->field);
		if (isset($methods[$findMethod])) {
			$type->isVirtual = TRUE;
		} else {
			// warning
			echo "For this method {$entityClass}::{$findMethod}() missing annotation or is too old.\n";
		}
	}

	$missingInAnnotation = array_diff_key($dbTypes, $phpTypes);

	$diff = [];
	foreach ($dbTypes as $name => $type) {
		if (!isset($phpTypes[$name])) {
			continue;
		}
		if ((string) $type === (string) $phpTypes[$name]) {
			continue;
		}
		$diff[] = "db: $type != php: {$phpTypes[$name]}\n";
	}
	if ($diff || $missingInAnnotation) {
		echo "Missing in Entity {$entityClass}:\n";
		if ($missingInAnnotation) {
			echo "Missing annotation:\n";
			foreach ($missingInAnnotation as $type) {
				echo " * {$type}\n";
			}
		}
		if ($diff) {
			echo "\nDifferent types:\n" . implode($diff);
		}
	}
}

if ($repositoryMissing) {
	echo "\nMissing repository:\n";
	echo implode(', ', $repositoryMissing) . "\n";
}

if ($entityMissing) {
	echo "\nMissing Entity for these repositories:\n";
	echo implode(', ', $entityMissing) . "\n";
}

function isYes($response)
{
	return in_array(strtolower($response), ['y', 'yes']);
}

function isNo($response)
{
	return in_array(strtolower($response), ['n', 'no']);
}

function isBreak($response)
{
	return $response === 'b';
}

function answerToPath(\ReflectionClass $reflection = NULL)
{
	$path = NULL;
	if ($reflection) {
		$path = dirname($reflection->getFileName());
	}
	do {
		if ($path === NULL) {
			echo "\nAdd new path:";
		} else {
			echo "\nCould I use this path? {$path}: [y][add new path]:";
		}
		$next = FALSE;
		$line = readline();

		if (!isYes($line)) {
			if (is_dir($line)) {
				$path = $line;
			} else {
				echo "\nCould I create path? {$line}: [y][n]:";
				$response = readline();
				if (isYes($response)) {
					\Nette\Utils\FileSystem::createDir($line);
					$path = $line;
				} else {
					$next = TRUE;
				}
			}
		}
	} while ($next);
	return $path;
}

$reflectionRepo = NULL;
if ($existsRepository) {
	$reflectionRepo = new ReflectionClass($existsRepository);
} else {
	throw new \Exception('Not implemented, create first Repository.');
}

$repoNamespace = $reflectionRepo->getNamespaceName();

$repoPath = answerToPath($reflectionRepo);

foreach ($repositoryMissing as $table => $repository) {
	echo "\nCould I create this repository? {$repository}: [y][n][b]:";
	$response = readline();
	if (isBreak($response)) {
		break;
	}
	if (!isYes($response)) {
		continue;
	}

	$phpFile = new Nette\PhpGenerator\ClassType($repository, new Nette\PhpGenerator\PhpNamespace($repoNamespace));
	$phpFile->addProperty('table', $filter($table))->setVisibility('protected'); // @todo dependency
	$parent = $reflectionRepo->getParentClass();
	if ($parent) {
		$phpFile->addExtend($parent->name);
	}

	$header = "<?php\n\nnamespace {$repoNamespace};\n\n";

	file_put_contents($repoPath . DIRECTORY_SEPARATOR . $repository . '.php', $header . $phpFile);
	$entityMissing[$table] = $repository;
}

$reflectionEntity = NULL;
if ($existsEntity) {
	$reflectionEntity = new ReflectionClass($existsEntity);
} else {
	throw new \Exception('Not implemented, create first Entity.');
}
$entityPath = answerToPath($reflectionEntity);

ksort($entityMissing);
foreach ($entityMissing as $table => $repository) {
	echo "\nCould I create entity for this repository? {$repository}: [entity name][n][b]:";
	$className = readline();
	if (isBreak($className)) {
		break;
	}
	if (isNo($className)) {
		continue;
	}

	$phpFile = new Nette\PhpGenerator\ClassType($className, new Nette\PhpGenerator\PhpNamespace($reflectionEntity->getNamespaceName()));
	$phpFile->addComment(implode("\n", $database->loadTableType($table)));
	$parent = $reflectionEntity->getParentClass();
	if ($parent) {
		$phpFile->addExtend($parent->name);
	}

	$header = "<?php\n\nnamespace {$reflectionEntity->getNamespaceName()};\n\n";

	file_put_contents($entityPath . DIRECTORY_SEPARATOR . $className . '.php', $header . $phpFile);

	echo "Neon: - " . $repoNamespace . "\\$repository('$className')"; // @todo dependency
}
