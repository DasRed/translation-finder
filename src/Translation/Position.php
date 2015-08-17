<?php
namespace DasRed\Finder\Translation;

use DasRed\Finder\File;

class Position
{

	/**
	 *
	 * @var File
	 */
	protected $file;

	/**
	 *
	 * @var int
	 */
	protected $position;

	/**
	 *
	 * @var string
	 */
	protected $value;

	/**
	 *
	 * @param File $file
	 * @param int $position
	 * @param string $value
	 */
	public function __construct(File $file, $position, $value)
	{
		$this->setFile($file)->setPosition($position)->setValue($value);
	}

	/**
	 *
	 * @return File
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 *
	 * @return int
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 *
	 * @param File $file
	 * @return self
	 */
	protected function setFile(File $file)
	{
		$this->file = $file;

		return $this;
	}

	/**
	 *
	 * @param int $position
	 * @return self
	 */
	protected function setPosition($position)
	{
		$this->position = (int)$position;

		return $this;
	}

	/**
	 *
	 * @param string $value
	 * @return self
	 */
	protected function setValue($value)
	{
		$this->value = $value;

		return $this;
	}
}