[![Downloads this Month](https://img.shields.io/packagist/dm/salamium/database.svg)](https://packagist.org/packages/salamium/database)
[![Latest stable](https://img.shields.io/packagist/v/salamium/database.svg)](https://packagist.org/packages/salamium/database)

Simple extension for nette/database.
-----------------------------------

Extension allow own ActiveRow like Entity, where you can write annotation and your IDE will suggestion to you. And Entity you can write own method, for manipulation with data.

Install via composer
```
composer require salamium/database
```

Register this extension like is in [test neon](tests/config/config.neon)

```neon
extensions:
	databaseExtension: Salamium\Database\DI\DatabaseExtension

databaseExtension:
    entityMap:
        default: # name of connection, must be same like defined database connection
            # table in database: Entity name with namespace
            users: Entity\User
```

## Entity

Create entity by extending. Anotation isn't necessary but your IDE will suggestion. I recomended it.

### User
```php
<?php

namespace Entity;

/**
 * @property int $id
 * @property string $name
 * @property string $surname
 * @property UserSettings $settings
 * @property string $fullName - virtual, prefix get and first letter upper then call method getFullName()
 */
class User extends \Salamium\Database\Table\Entity
{

	public function getFullName()
	{
		return trim($this->name . ' ' . $this->surname);
	}

	public function getSettings()
	{
	   return $this->ref('user_settings');
	}

}

```
### UserSettings
```php
<?php

namespace Entity;

/**
 * @property int $id
 * @property int $user_id
 * @property \Datetime $reg_date
 * @property int $login_count
 */
class UserSettings extends \Salamium\Database\Table\Entity
{

}
```

### Example
```php
$user = $context->table('users')->where('id', 1)->fetch();
dump($user instanceof Entity\User); // TRUE
dump($user->settings); // Entity\UserSettings
```

## Repository

The Repository object provides very nice and easy [API](src/Repository.php) for CRUD with tables. One repository is for one table. It is't necessary for entity. Feature is provides [transaction](src/Transaction.php) by method **getTransaction()**.

**TIP** Create own BaseRepository whose extends.

```php
<?php

namespace Repository;

class Users extends \Salamium\Database\Repository // or your BaseRepository extended \Salamium\Database\Repository
{
    // own methods for manipulation
}
```

Only repository are need to register by neon.
```neon
services:
	- Repository\Users('users') # parameter is table name
```

# Trait Extension
In this [directory](src/Extension/) are extension written like trait for your class whose must extend from [Repository](src/Repository.php).

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