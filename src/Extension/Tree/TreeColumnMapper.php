<?php

namespace Salamium\Database\Extension\Tree;

/**
 * @property-read string parentId
 * @property-read string deep
 * @property-read string left
 * @property-read string right
 */
final class TreeColumnMapper extends \h4kuna\DataType\Immutable\Messenger implements \Iterator
{

	public function __construct($parentId = 'parent_id', $deep = 'deep', $left = 'left', $right = 'right')
	{
		parent::__construct([
			'parentId' => $parentId,
			'deep' => $deep,
			'left' => $left,
			'right' => $right
		]);
	}

}
