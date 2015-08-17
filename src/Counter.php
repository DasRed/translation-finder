<?php
namespace DasRed\Finder;

use DasRed\Zend\Log\Logger\Console as ConsoleLogger;
use Zend\Console\Console;
use Zend\Console\ColorInterface;

class Counter
{

	/**
	 *
	 * @var int
	 */
	protected $matches = 0;

	/**
	 *
	 * @var int
	 */
	protected $sourceFilesChanged = 0;

	/**
	 *
	 * @var int
	 */
	protected $sourceFilesParsed = 0;

	/**
	 *
	 * @var int
	 */
	protected $translationFiles = 0;

	/**
	 *
	 * @var int
	 */
	protected $translationKeys = 0;

	/**
	 *
	 * @var int
	 */
	protected $words = 0;

	/**
	 *
	 * @return the $matches
	 */
	public function getMatches()
	{
		return $this->matches;
	}

	/**
	 *
	 * @return the $sourceFilesChanged
	 */
	public function getSourceFilesChanged()
	{
		return $this->sourceFilesChanged;
	}

	/**
	 *
	 * @return int
	 */
	public function getSourceFilesParsed()
	{
		return $this->sourceFilesParsed;
	}

	/**
	 *
	 * @return the $translationFiles
	 */
	public function getTranslationFiles()
	{
		return $this->translationFiles;
	}

	/**
	 *
	 * @return the $translationKeys
	 */
	public function getTranslationKeys()
	{
		return $this->translationKeys;
	}

	/**
	 *
	 * @return the $words
	 */
	public function getWords()
	{
		return $this->words;
	}

	/**
	 *
	 * @param int $inc
	 * @return self
	 */
	public function incMatches($inc = 1)
	{
		$this->matches += $inc;

		return $this;
	}

	/**
	 *
	 * @param int $inc
	 * @return self
	 */
	public function incSourceFilesChanged($inc = 1)
	{
		$this->sourceFilesChanged += $inc;

		return $this;
	}

	/**
	 *
	 * @param int $inc
	 * @return self
	 */
	public function incSourceFilesParsed($inc = 1)
	{
		$this->sourceFilesParsed += $inc;

		return $this;
	}

	/**
	 *
	 * @param int $inc
	 * @return self
	 */
	public function incTranslationFiles($inc = 1)
	{
		$this->translationFiles += $inc;

		return $this;
	}

	/**
	 *
	 * @param int $inc
	 * @return self
	 */
	public function incTranslationKeys($inc = 1)
	{
		$this->translationKeys += $inc;

		return $this;
	}

	/**
	 *
	 * @param string $string
	 * @return self
	 */
	public function incWords($string)
	{
		$this->words += str_word_count($string);

		return $this;
	}

	/**
	 *
	 * @param ConsoleLogger $logger
	 * @return self
	 */
	public function output(ConsoleLogger $logger)
	{
		// stats
		$stats = [
			'Matches found' => number_format($this->getMatches(), 0, ',', '.'),
			'Source Files found' => number_format($this->getSourceFilesParsed(), 0, ',', '.'),
			'Source Files changed' => number_format($this->getSourceFilesChanged(), 0, ',', '.'),
			'Translation Files' => number_format($this->getTranslationFiles(), 0, ',', '.'),
			'Translation Keys added' => number_format($this->getTranslationKeys(), 0, ',', '.'),
			'Words add' => number_format($this->getWords(), 0, ',', '.')
		];

		// finds max length of text and number
		$lengthKey = 0;
		$lengthValue = 0;
		foreach ($stats as $key => $value)
		{
			$lengthKey = max($lengthKey, strlen($key));
			$lengthValue = max($lengthValue, strlen($value));
		}

		// output
		$logger->always(PHP_EOL);
		foreach ($stats as $key => $value)
		{
			$text = str_pad($key . ':', $lengthKey + 3, ' ', STR_PAD_RIGHT);
			$text = Console::getInstance()->colorize($text, ColorInterface::GREEN);
			$text .= str_pad($value, $lengthValue, ' ', STR_PAD_LEFT);

			$logger->always($text);
		}

		return $this;
	}

	/**
	 *
	 * @param int $matches
	 * @return self
	 */
	public function setMatches($matches)
	{
		$this->matches = $matches;

		return $this;
	}

	/**
	 *
	 * @param int $sourceFilesChanged
	 * @return self
	 */
	public function setSourceFilesChanged($sourceFilesChanged)
	{
		$this->sourceFilesChanged = $sourceFilesChanged;

		return $this;
	}

	/**
	 *
	 * @param int $sourceFilesParsed
	 * @return self
	 */
	public function setSourceFilesParsed($sourceFilesParsed)
	{
		$this->sourceFilesParsed = $sourceFilesParsed;

		return $this;
	}

	/**
	 *
	 * @param int $translationFiles
	 * @return self
	 */
	public function setTranslationFiles($translationFiles)
	{
		$this->translationFiles = $translationFiles;

		return $this;
	}

	/**
	 *
	 * @param int $translationKeys
	 * @return self
	 */
	public function setTranslationKeys($translationKeys)
	{
		$this->translationKeys = $translationKeys;

		return $this;
	}

	/**
	 *
	 * @param int $words
	 * @return self
	 */
	public function setWords($words)
	{
		$this->words = $words;

		return $this;
	}
}