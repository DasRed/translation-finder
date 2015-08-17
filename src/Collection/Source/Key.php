<?php
namespace DasRed\Finder\Collection\Source;

use DasRed\Finder\CollectionAbstract;
use DasRed\Finder\Translation\Entry;

/**
 *
 * @method Entry first()
 */
class Key extends CollectionAbstract
{

	/**
	 *
	 * @param Entry $entry
	 * @return self
	 */
	public function add(Entry $entry)
	{
		$key = $entry->getPositionFull()->getPosition();
		if ($this->offsetExists($key) === false)
		{
			$this->offsetSet($key, $entry);
		}

		return $this;
	}

	/**
	 * @return self
	 */
	public function replace()
	{
		// sort reverse... the last position is the first one
		$this->uasort(function (Entry $a, Entry $b)
		{
			$positionA = $a->getPositionFull()->getPosition();
			$positionB = $b->getPositionFull()->getPosition();

			if ($positionA == $positionB)
			{
				return 0;
			}

			return ($positionA < $positionB) ? 1 : -1;
		});

		// loop through entries and replace step by step
		/* @var $entry Entry */
		foreach ($this as $entry)
		{
			$entry->replace();
		}

		$entry->getFile()->write();

		return $this;
	}
}