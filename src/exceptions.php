<?php

namespace Salamium\Database;

abstract class DatabaseException extends \Exception {}

class NoTransactionException extends DatabaseException {}

class InvalidArgumentException extends DatabaseException {}

