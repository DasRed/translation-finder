<?php
namespace DasRed\Finder;

use DasRed\Finder\Collection\Value as CollectionValue;
use DasRed\Finder\Translation\Entry as TranslationEntry;
use DasRed\Finder\Translation\Position\Full as TranslationPositionFull;
use DasRed\Finder\Translation\Position\Key as TranslationPositionKey;
use DasRed\Finder\Collection\Source\File as CollectionSourceFile;
use DasRed\Finder\Collection\Translation\Locale as CollectionTranslationLocale;
use Zend\I18n\Translator\Translator;

class Parser
{

	/**
	 *
	 * @var CollectionSourceFile
	 */
	protected $collectionSourceFile;

	/**
	 *
	 * @var CollectionTranslationLocale
	 */
	protected $collectionTranslationLocale;

	/**
	 *
	 * @var CollectionValue
	 */
	protected $collectionValue;

	/**
	 *
	 * @var Config
	 */
	protected $config;

	/**
	 *
	 * @var Counter
	 */
	protected $counter;

	/**
	 *
	 * @var Translator[]
	 */
	protected $translators;

	/**
	 *
	 * @param Config $config
	 * @param Counter $counter
	 */
	public function __construct(Config $config, Counter $counter)
	{
		$this->setConfig($config)->setCounter($counter);
	}

	/**
	 *
	 * @return CollectionSourceFile
	 */
	public function getCollectionSourceFile()
	{
		if ($this->collectionSourceFile === null)
		{
			$this->collectionSourceFile = new CollectionSourceFile();
		}

		return $this->collectionSourceFile;
	}

	/**
	 *
	 * @return CollectionTranslationLocale
	 */
	public function getCollectionTranslationLocale()
	{
		if ($this->collectionTranslationLocale === null)
		{
			$this->collectionTranslationLocale = new CollectionTranslationLocale();
		}

		return $this->collectionTranslationLocale;
	}

	/**
	 *
	 * @return CollectionValue
	 */
	public function getCollectionValue()
	{
		if ($this->collectionValue === null)
		{
			$this->collectionValue = new CollectionValue();
		}

		return $this->collectionValue;
	}

	/**
	 *
	 * @return Config
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 *
	 * @return Counter
	 */
	public function getCounter()
	{
		return $this->counter;
	}

	/**
	 *
	 * @param string $locale
	 * @return Translator[]
	 */
	protected function getTranslators($locale = null)
	{
		if ($this->translators === null)
		{
			$this->translators = [];
			$gettextFiles = $this->getConfig()->getGettextFiles();
			foreach ($this->getConfig()->getLocales() as $index => $localeConfig)
			{
				if (array_key_exists($index, $gettextFiles) === true)
				{
					$this->translators[$localeConfig] = (new Translator())->setLocale($localeConfig)->addTranslationFile('gettext', $gettextFiles[$index]);
				}
			}
		}

		// return by locale
		if ($locale !== null)
		{
			// find it by locale
			if (array_key_exists($locale, $this->translators) === true)
			{
				// return founded by locale
				return $this->translators[$locale];
			}

			// not found
			return null;
		}

		// return all
		return $this->translators;
	}

	/**
	 *
	 * @param File $file
	 * @return self
	 */
	public function parse(File $file)
	{
		$this->getConfig()->getLogger()->notice('Parsing file: ' . $file->getFile());
		$this->getCounter()->incSourceFilesParsed();

		$localeDefault = $this->getConfig()->getLocales()[0];

		foreach ($this->getConfig()->getPatterns() as $indexPattern => $pattern)
		{
			// find correct replacement
			$replacement = $this->getConfig()->getReplacementAtIndex($indexPattern);

			$this->getConfig()->getLogger()->debug('using pattern: ' . $pattern)->debug('using replacement: ' . $replacement);

			// find all matches in file
			$matchesAll = [];
			if (preg_match_all($pattern, $file->getContent(), $matchesAll, PREG_OFFSET_CAPTURE) == false)
			{
				$this->getConfig()->getLogger()->info('found 0 matches');
				continue;
			}

			$this->getConfig()->getLogger()->info('found ' . count($matchesAll) . ' matches');

			// loop through matches
			foreach ($matchesAll[$this->getConfig()->getMatchesIndexFull()] as $indexMatches => $matchesFull)
			{
				$this->getCounter()->incMatches();

				$matchesKey = $matchesAll[$this->getConfig()->getMatchesIndexKey()][$indexMatches];

				/* @var $previousEntry TranslationEntry */
				$previousEntry = null;
				foreach ($this->getConfig()->getLocales() as $indexLocales => $locale)
				{
					$valueFull = $matchesFull[0];
					$valueKey = $matchesKey[0];

					// translate if an translator exists for this locale
					$translator = $this->getTranslators($locale);
					if ($translator !== null)
					{
						$valueFull = $translator->translate($matchesFull[0]);
						$valueKey = $translator->translate($matchesKey[0]);
					}

					// value is the same in different languages
					if ($localeDefault !== $locale && $previousEntry->getPositionKey()->getValue() == $valueKey)
					{
						continue;
					}

					// create the translation entry
					$positionFull = new TranslationPositionFull($file, $matchesFull[1], $valueFull);
					$positionKey = new TranslationPositionKey($file, $matchesKey[1], $valueKey);
					$entry = new TranslationEntry($locale, $pattern, $replacement, $positionFull, $positionKey, $this->getConfig());

					// this sets the same TranslationFileKey for every locale
					if ($localeDefault !== $locale)
					{
						$entry->setTranslationFileKey($previousEntry->getTranslationFileKey())->setTranslationFileName($previousEntry->getTranslationFileName());
					}
					else
					{
						$previousEntry = $entry;
					}

					// add to the value collection and get the translation
					$translation = $this->getCollectionValue()->add($entry);

					// test if $translation key does exists in default locale
					if ($localeDefault !== $locale)
					{
						/* @var $translationFileDefault \DasRed\Finder\Collection\Translation\File */
						$translationFileDefault = $this->getCollectionTranslationLocale()->offsetGet($localeDefault);
						if ($translationFileDefault !== null)
						{
							if ($translationFileDefault->offsetExists($entry->getTranslationFileName()) === false)
							{
								continue;
							}

							/* @var $translationKeyDefault \DasRed\Finder\Collection\Translation\Key */
							$translationKeyDefault = $translationFileDefault->offsetGet($entry->getTranslationFileName());
							if ($translationKeyDefault->offsetExists($entry->getTranslationFileKey()) === false)
							{
								continue;
							}
						}
					}

					// add translation in translation file collection
					$this->getCollectionTranslationLocale()->add($translation);

					// add entry in source file collection
					$this->getCollectionSourceFile()->add($entry);
				}
			}
		}

		return $this;
	}

	/**
	 *
	 * @param Config $config
	 * @return self
	 */
	protected function setConfig(Config $config)
	{
		$this->config = $config;

		return $this;
	}

	/**
	 *
	 * @param Counter $counter
	 * @return self
	 */
	protected function setCounter(Counter $counter)
	{
		$this->counter = $counter;

		return $this;
	}
}