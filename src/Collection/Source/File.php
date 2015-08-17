<?php
namespace DasRed\Finder\Collection\Source;

use DasRed\Finder\CollectionAbstract;
use DasRed\Finder\Translation\Entry;
use DasRed\Zend\Log\Logger\Console as ConsoleLogger;
use DasRed\Finder\Counter;

/**
 *
 * @method Key first()
 */
class File extends CollectionAbstract
{

	/**
	 *
	 * @param Entry $file
	 * @return self
	 */
	public function add(Entry $entry)
	{
		$key = $entry->getFile()->getFileRelative();

		if ($this->offsetExists($key) === false)
		{
			$this->offsetSet($key, new Key());
		}

		/* @var $file Key */
		$file = $this->offsetGet($key);
		$file->add($entry);

		return $this;
	}

	/**
	 *
	 * @param ConsoleLogger $logger
	 * @param Counter $counter
	 * @return self
	 */
	public function replace(ConsoleLogger $logger, Counter $counter)
	{
		/* @var $keys Key */
		foreach ($this as $keys)
		{
			$logger->always('.', false)->info($keys->first()->getFile()->getFile());

			$counter->incSourceFilesChanged();
			$keys->replace($logger);
		}

		return $this;
	}
}