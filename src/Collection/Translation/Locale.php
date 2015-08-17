<?php
namespace DasRed\Finder\Collection\Translation;

use DasRed\Finder\CollectionAbstract;
use DasRed\Zend\Log\Logger\Console as ConsoleLogger;
use DasRed\Finder\Collection\Translation;
use DasRed\Finder\Counter;
use DasRed\Finder\Config;

/**
 *
 * @method Key first()
 */
class Locale extends CollectionAbstract
{

	/**
	 *
	 * @param Translation $translation
	 * @return self
	 */
	public function add(Translation $translation)
	{
		$key = $translation->getLocale();
		if ($key === null)
		{
			return $translation;
		}

		if ($this->offsetExists($key) === false)
		{
			$this->offsetSet($key, new File());
		}

		/* @var $collection File */
		$collection = $this->offsetGet($key);
		$collection->add($translation);

		return $this;
	}

	/**
	 *
	 * @param ConsoleLogger $logger
	 * @param string $path
	 * @param Config $config
	 * @param Counter $counter
	 * @return self
	 */
	public function write(ConsoleLogger $logger, $path, Config $config, Counter $counter)
	{
		/* @var $collection File */
		foreach ($this as $locale => $collection)
		{
			$collection->write($logger, $path . $locale . '/', $config, $counter);
		}

		return $this;
	}
}