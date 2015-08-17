<?php
namespace DasRed\Finder\Collection\Translation;

use DasRed\Finder\CollectionAbstract;
use DasRed\Finder\Collection\Translation;
use DasRed\Finder\Counter;
use DasRed\Finder\Config;

/**
 *
 * @method Translation first()
 */
class Key extends CollectionAbstract
{

	/**
	 *
	 * @var array
	 */
	protected $bbCodes;

	/**
	 *
	 * @param Translation $translation
	 * @return self
	 */
	public function add(Translation $translation)
	{
		$key = $translation->getTranslationFileKey();
		if ($this->offsetExists($key) === false)
		{
			$this->offsetSet($key, $translation);
		}

		return $this;
	}

	/**
	 *
	 * @return array
	 */
	protected function getBbCodes()
	{
		if ($this->bbCodes === null)
		{
			$bbCodes = require __DIR__ . '/../../../config/bbcodes.php';

			$this->bbCodes = [];
			foreach ($bbCodes['bbcodes'] as $bbcode)
			{
				$this->bbCodes[$bbcode['regex']] = $bbcode['replacement'];
			}
		}

		return $this->bbCodes;
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
	 * @param string $path
	 * @param Config $config
	 * @param Counter $counter
	 * @return self
	 */
	public function write($file, Config $config, Counter $counter)
	{
		if ($this->count() === 0)
		{
			return $this;
		}

		$this->uasort(function (Translation $translationA, Translation $translationB)
		{
			return strnatcasecmp($translationA->getTranslationFileKey(), $translationB->getTranslationFileKey());
		});

		$translations = [];

		// if exists... append new content
		if (file_exists($file) === true)
		{
			$config->getLogger()->info('Translation file already exists. Loading translations from: ' . $file);
			$translations = require $file;
		}

		// loop through and append to TR File
		/* @var $translation Translation */
		foreach ($this as $translation)
		{
			// get
			$translationFileKey = $translation->getTranslationFileKey();
			$translationFileContent = $translation->getContent();

			// convert
			$translationFileContent = str_replace([
				'\'',
				chr(13)
			], [
				'"',
				''
			], $translationFileContent);
			foreach ($this->getBbCodes() as $key => $val)
			{
				$translationFileContent = preg_replace($key, $val, $translationFileContent);
			}

			// find free Translation Key name
			$index = '';
			while (array_key_exists($translationFileKey . $index, $translations) === true)
			{
				// if current Tr key content identical with the content in the file, then do nothing
				if ($translations[$translationFileKey . $index] == $translationFileContent)
				{
					$config->getLogger()->notice('Translation key "' . $translationFileKey . $index . '" already exists in: ' . $file);
					continue 2;
				}

				$index = ($index === '' ? 1 : $index + 1);
			}

			// update TR Key
			if ($index !== '')
			{
				$translation->setTranslationFileKey($translationFileKey . $index);
			}

			$translations[$translation->getTranslationFileKey()] = $translationFileContent;
			$counter->incTranslationKeys()->incWords($translationFileContent);
		}

		// create the lines
		$fileContent = [];
		foreach ($translations as $key => $translation)
		{
			$fileContent[] = chr(9) . '\'' . $key . '\' => \'' . $translation . '\'';
			$config->getLogger()->debug('Translation key added, updated or reused: ' . $key);
		}

		// create file content
		$content = '<?php' . chr(10);
		$content .= 'return [' . chr(10);
		$content .= implode(',' . chr(10), $fileContent) . chr(10);
		$content .= '];' . chr(10);

		// write file content
		$config->getLogger()->debug('Writing translation file content: ' . $file);
		if ($config->isDryRun() === false)
		{
			file_put_contents($file, $content);
		}

		$counter->incTranslationFiles();

		return $this;
	}
}