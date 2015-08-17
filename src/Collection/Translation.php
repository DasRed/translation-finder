<?php
namespace DasRed\Finder\Collection;

use DasRed\Finder\CollectionAbstract;
use DasRed\Finder\Translation\Entry;

/**
 *
 * @method Entry first()
 */
class Translation extends CollectionAbstract
{

	/**
	 *
	 * @var string
	 */
	protected $translationFileKey;

	/**
	 *
	 * @param Entry $entry
	 * @return self
	 */
	public function add(Entry $entry)
	{
		$entryFirst = $this->first();
		if ($entryFirst !== null)
		{
			$entry->setTranslationFileKey($entryFirst->getTranslationFileKey())->setTranslationFileName($entryFirst->getTranslationFileName());
		}

		$this->append($entry->setTranslation($this));

		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getContent()
	{
		if ($this->first() === null)
		{
			return null;
		}

		return $this->first()->getContent();
	}

	/**
	 * @return string
	 */
	public function getLocale()
	{
		if ($this->translationFileKey !== null)
		{
			return $this->translationFileKey;
		}

		if ($this->first() === null)
		{
			return null;
		}

		return $this->first()->getLocale();
	}

	/**
	 *
	 * @return string
	 */
	public function getTranslationFileKey()
	{
		if ($this->translationFileKey !== null)
		{
			return $this->translationFileKey;
		}

		if ($this->first() === null)
		{
			return null;
		}

		return $this->first()->getTranslationFileKey();
	}

	/**
	 *
	 * @return string
	 */
	public function getTranslationFileName()
	{
		if ($this->first() === null)
		{
			return null;
		}

		return $this->first()->getTranslationFileName();
	}

	/**
	 *
	 * @return string
	 */
	public function getTranslationKey()
	{
		if ($this->first() === null)
		{
			return null;
		}

		return $this->first()->getTranslationKey();
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
}