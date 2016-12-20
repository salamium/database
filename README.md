Simple extension for nette/database.
-----------------------------------

Install via composer
```
composer require salamium/database
```

This is extension for Nette\Database, when start support own class like ActiveRow.

Register this extension like is in [test neon](tests/config/config.neon)

```neon
extensions:
	databaseExtension: Salamium\Database\DI\DatabaseExtension

databaseExtension:
	enityNamespace: Entity\Namespace
	entityMap:
		table: Entity
		users: User
```

Create repository by extending.
```php
<?php

namespace Repository;

class Users extends \Salamium\Database\Repository
{

}
```

**TIP** Create own BaseRepository whose extends.

Create entity by extending. Anotation is not necessary but your IDE will suggestion. I recomended it.
```php
<?php

namespace Entity;

/**
 * @property int $id
 * @property string $name
 * @property string $surname
 * @property string $fullName - virtual, prefix get and first letter upper then call method getFullName()
 */
class User extends \Salamium\Database\Table\Entity
{

	public function getFullName()
	{
		return trim($this->name . ' ' . $this->surname);
	}

}

```

Only repository are need to register by neon.
```neon
services:
	- Repository\Users('users') # table name
```

Your new repository has easy [API](src/Repository.php) and provide transaction.

This extension support static data saved in database stored in cache.

**IMPORTANT** The storage must support Tags!

Change strorage
```sh
services:
	databaseExtension.cacheAccessor:
		arguments: [YourStorage()]
```

Use [trait RepositoryListTrait](src/RepositoryListTrait.php) prepared for this use case, define method loadDialItems() how select items by default and init cache by setCache();. Or you can create parent CacheRepositor where use trait and add dependency on CacheAccessor via constructor.

[Example](tests/Model/Repository/RepositoryCache.php) and this class is extended by [Countries](tests/Model/Repository/Countries.php)
```php
<?php

namespace Repository;

class Users extends \Salamium\Database\Repository
{
	use \Salamium\Database\RepositoryListTrait;

	// new method in API is getItems() provide cached data

	protected function loadDialItems()
	{
		return $this->find()->order('name')->fetchPairs('id');
	}
}
```