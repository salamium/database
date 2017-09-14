<?php

namespace Salamium\Database;

abstract class DatabaseException extends \Exception {}

class NoTransactionException extends DatabaseException {}

class InvalidArgumentException extends \InvalidArgumentException {}

abstract class TreeException extends DatabaseException {}

class TreeParentDoesNotExistsException extends TreeException {}

class TreeLogicException extends TreeException {}