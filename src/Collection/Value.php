<?php
namespace DasRed\Finder\Collection;

use DasRed\Finder\CollectionAbstract;
use DasRed\Finder\Translation\Entry;

/**
 *
 * @method Translation first()
 */
class Value extends CollectionAbstract
{

	/**
	 *
	 * @param Entry $value
	 * @return Translation
	 */
	public function add(Entry $entry)
	{
		$key = $entry->getLocale() . '.' . $entry->getContent();
		if ($this->offsetExists($key) === false)
		{
			$this->offsetSet($key, new Translation());
		}

		/* @var $translation Translation */
		$translation = $this->offsetGet($key);
		$translation->add($entry);

		return $translation;
	}
}