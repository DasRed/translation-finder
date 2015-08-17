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
class File extends CollectionAbstract
{

	/**
	 *
	 * @param Translation $translation
	 * @return self
	 */
	public function add(Translation $translation)
	{
		$key = $translation->getTranslationFileName();
		if ($key === null)
		{
			return $translation;
		}

		if ($this->offsetExists($key) === false)
		{
			$this->offsetSet($key, new Key());
		}

		/* @var $collection Key */
		$collection = $this->offsetGet($key);
		$collection->add($translation);

		return $this;
	}

	/**
	 *
	 * @param ConsoleLogger $logger
	 * @param string $path
	 * @param , Config $config
	 * @param Counter $counter
	 * @return self
	 */
	public function write(ConsoleLogger $logger, $path, Config $config, Counter $counter)
	{
		/* @var $keys Key */
		foreach ($this as $keys)
		{
			// create file and path
			$file = trim($path, '\\/') . DIRECTORY_SEPARATOR . $keys->getTranslationFileName() . '.php';
			$dir = dirname($file);
			if (is_dir($dir) === false)
			{
				$logger->debug('Creating path: ' . $dir);

				if ($config->isDryRun() === false)
				{
					mkdir($dir, 0777, true);
				}
			}

			$keys->write($file, $config, $counter);

			$logger->always('.', false);
		}

		return $this;
	}
}