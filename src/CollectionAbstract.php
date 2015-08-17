<?php
namespace DasRed\Finder;

abstract class CollectionAbstract extends \ArrayIterator
{

	/**
	 *
	 * @param array $array
	 */
	public function __construct($array = [])
	{
		parent::__construct($array);
	}

	/**
	 *
	 * @return mixed
	 */
	public function first()
	{
		if ($this->count() === 0)
		{
			return null;
		}

		return $this[$this->keys()[0]];
	}

	/**
	 * @return array
	 */
	public function keys()
	{
		return array_keys($this->getArrayCopy());
	}
}