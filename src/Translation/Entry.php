<?php
namespace DasRed\Finder\Translation;

use DasRed\Finder\Translation\Position\Full;
use DasRed\Finder\Translation\Position\Key;
use DasRed\Finder\Collection\Translation;
use DasRed\Finder\File;
use DasRed\Finder\Exception;
use DasRed\Finder\Config;

class Entry
{

	const MAX_KEY_LENGTH = 40;

	const MAX_WORDS_IN_KEY = 5;

	/**
	 *
	 * @var Config
	 */
	protected $config;

	/**
	 *
	 * @var string
	 */
	protected $locale;

	/**
	 *
	 * @var string
	 */
	protected $pattern;

	/**
	 *
	 * @var Full
	 */
	protected $positionFull;

	/**
	 *
	 * @var Key
	 */
	protected $positionKey;

	/**
	 *
	 * @var string
	 */
	protected $replacement;

	/**
	 *
	 * @var Translation
	 */
	protected $translation;

	/**
	 *
	 * @var string
	 */
	protected $translationFileKey;

	/**
	 *
	 * @var string
	 */
	protected $translationFileName;

	/**
	 *
	 * @param string $locale
	 * @param string $pattern
	 * @param string $replacement
	 * @param Full $positionFull
	 * @param Key $positionKey
	 * @param Config $config
	 */
	public function __construct($locale, $pattern, $replacement, Full $positionFull, Key $positionKey, Config $config)
	{
		$this->setLocale($locale)
			->setPattern($pattern)
			->setReplacement($replacement)
			->setPositionFull($positionFull)
			->setPositionKey($positionKey)
			->setConfig($config);
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
	 * @return string
	 */
	public function getContent()
	{
		return $this->getPositionKey()->getValue();
	}

	/**
	 *
	 * @return File
	 */
	public function getFile()
	{
		return $this->getPositionFull()->getFile();
	}

	/**
	 *
	 * @return string
	 */
	public function getLocale()
	{
		return $this->locale;
	}

	/**
	 *
	 * @return string
	 */
	public function getPattern()
	{
		return $this->pattern;
	}

	/**
	 *
	 * @return Full
	 */
	public function getPositionFull()
	{
		return $this->positionFull;
	}

	/**
	 *
	 * @return Key
	 */
	public function getPositionKey()
	{
		return $this->positionKey;
	}

	/**
	 *
	 * @return string
	 */
	public function getReplacement()
	{
		return $this->replacement;
	}

	/**
	 *
	 * @return Translation
	 */
	public function getTranslation()
	{
		return $this->translation;
	}

	/**
	 *
	 * @return string
	 */
	public function getTranslationFileKey()
	{
		if ($this->translationFileKey === null)
		{
			// get value
			$value = $this->getPositionKey()->getValue();

			// remove trailing whitespaces
			$value = trim($value, ' ' . chr(9) . chr(10) . chr(13));

			// convert to ASCII
			try
			{
				$value = iconv('UTF-8', 'ASCII//IGNORE//TRANSLIT', $value);
			}
			catch (Exception $exception)
			{
				throw new Exception('Conversion from UTF-8 to ASCII is not possible for the translation key "' . $this->getPositionKey()->getValue() . '" in file "' . $this->getFile()->getFile() . '" at start position ' . $this->getPositionFull()->getPosition() . '!');
			}

			// convert underscore and dashed to words
			$value = str_replace([
				'-',
				'_'
			], ' ', $value);

			// uppercase first char of every word
			$value = lcfirst(ucwords(strip_tags($value)));

			// limit word count to self::MAX_WORDS_IN_KEY
			$value = explode(' ', $value);
			$value = implode(' ', array_slice($value, 0, self::MAX_WORDS_IN_KEY));

			// replace some other chars
			$value = str_replace([
				' ',
				'\\',
				'"',
				'*',
				'$',
				'(',
				')',
				'&',
				'!',
				'.',
				':',
				'/',
				'\'',
				chr(8),
				chr(9),
				chr(10),
				chr(13)
			], '', $value);

			// limit to max chars in key
			$value = substr($value, 0, self::MAX_KEY_LENGTH);

			// store
			$this->translationFileKey = $value;
		}

		return $this->translationFileKey;
	}

	/**
	 *
	 * @return string
	 */
	public function getTranslationFileName()
	{
		if ($this->translationFileName === null)
		{
			// remove file extension
			$value = $this->getPositionKey()->getFile()->getFileRelative();
			$value = explode('.', strrev($value), 2);
			$value = strrev(array_pop($value));

			// lower case first char for every path part name
			$value = implode('/', array_map('lcfirst', explode('/', str_replace([
				'/',
				'\\'
			], '/', $value))));

			// remove defined translation file path parts
			if (count($this->getConfig()->getRemoveTranslationFilePath()) !== 0)
			{
				$value = str_replace($this->getConfig()->getRemoveTranslationFilePath(), '', $value);
			}

			// only allow some parts
			if (count($this->getConfig()->getAllowedTranslationFilePathParts()) !== 0)
			{
				$basename = basename($value);
				$value = implode('/', array_filter(explode('/', dirname($value)), function ($pathPart)
				{
					$pathPart = trim($pathPart);
					if ($pathPart === '')
					{
						return false;
					}

					return in_array($pathPart, $this->getConfig()->getAllowedTranslationFilePathParts());
				})) . '/' . $basename;
			}

			// remove trailing path chars
			$value = trim($value, '/\\ ');

			$this->translationFileName = $value;
		}

		return $this->translationFileName;
	}

	/**
	 *
	 * @return string
	 */
	public function getTranslationKey()
	{
		return $this->getTranslationFileName() . '.' . $this->getTranslationFileKey();
	}

	/**
	 *
	 * @return self
	 */
	public function replace()
	{
		$replacement = str_replace('$1', $this->getTranslationKey(), $this->getReplacement());
		$start = $this->getPositionFull()->getPosition();
		$length = strlen($this->getPositionFull()->getValue());

		$this->getFile()->replace($replacement, $start, $length);

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
	 * @param string $locale
	 * @return self
	 */
	protected function setLocale($locale)
	{
		$this->locale = $locale;

		return $this;
	}

	/**
	 *
	 * @param string $pattern
	 * @return self
	 */
	protected function setPattern($pattern)
	{
		$this->pattern = $pattern;

		return $this;
	}

	/**
	 *
	 * @param Full $positionFull
	 * @return self
	 */
	protected function setPositionFull($positionFull)
	{
		$this->positionFull = $positionFull;

		return $this;
	}

	/**
	 *
	 * @param Key $positionKey
	 * @return self
	 */
	protected function setPositionKey($positionKey)
	{
		$this->positionKey = $positionKey;

		return $this;
	}

	/**
	 *
	 * @param string $replacement
	 * @return self
	 */
	protected function setReplacement($replacement)
	{
		$this->replacement = $replacement;

		return $this;
	}

	/**
	 *
	 * @param Translation $translation
	 * @return self
	 */
	public function setTranslation(Translation $translation)
	{
		$this->translation = $translation;

		return $this;
	}

	/**
	 *
	 * @param string $translationFileKey
	 * @return self
	 */
	public function setTranslationFileKey($translationFileKey)
	{
		$this->translationFileKey = $translationFileKey;

		return $this;
	}

	/**
	 *
	 * @param string $translationFileName
	 * @return self
	 */
	public function setTranslationFileName($translationFileName)
	{
		$this->translationFileName = $translationFileName;

		return $this;
	}
}