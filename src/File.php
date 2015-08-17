<?php
namespace DasRed\Finder;

class File
{

	/**
	 *
	 * @var Config
	 */
	protected $config;

	/**
	 *
	 * @var string
	 */
	protected $content;

	/**
	 *
	 * @var string
	 */
	protected $file;

	/**
	 *
	 * @var string
	 */
	protected $fileRelative;

	/**
	 *
	 * @param string $file
	 * @param string $fileRelative
	 * @param Config $config
	 */
	public function __construct($file, $fileRelative, Config $config)
	{
		$this->setFile($file)->setFileRelative($fileRelative)->setConfig($config);
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
		if ($this->content === null)
		{
			$this->getConfig()->getLogger()->debug('loading file content of: ' . $this->getFile());
			$this->content = file_get_contents($this->getFile());
		}

		return $this->content;
	}

	/**
	 *
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 *
	 * @return string
	 */
	public function getFileRelative()
	{
		return $this->fileRelative;
	}

	/**
	 *
	 * @param string $replacement
	 * @param int $start
	 * @param int $length
	 * @return self
	 */
	public function replace($replacement, $start, $length)
	{
		$this->getConfig()->getLogger()->debug('replacing "' . substr($this->getContent(), $start, $length) . '" with "' . $replacement . '" in file: ' . $this->getFile());

		$this->setContent(substr_replace($this->getContent(), $replacement, $start, $length));

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
	 * @param string $content
	 * @return self
	 */
	protected function setContent($content)
	{
		$this->content = $content;

		return $this;
	}

	/**
	 *
	 * @param string $file
	 * @return self
	 */
	protected function setFile($file)
	{
		$this->file = $file;

		return $this;
	}

	/**
	 *
	 * @param string $fileRelative
	 * @return self
	 */
	protected function setFileRelative($fileRelative)
	{
		$this->fileRelative = trim($fileRelative, '/\\');

		return $this;
	}

	/**
	 *
	 * @return self
	 */
	public function write()
	{
		$this->getConfig()->getLogger()->debug('writing file content to: ' . $this->getFile());
		if ($this->getConfig()->isDryRun() === false)
		{
			file_put_contents($this->getFile(), $this->getContent());
		}

		return $this;
	}
}