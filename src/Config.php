<?php
namespace DasRed\Finder;

use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use Zend\Console\Exception\RuntimeException;
use DasRed\Zend\Console\Getopt;
use DasRed\Zend\Log\Logger\Console as ConsoleLogger;
use DasRed\Zend\Log\Writer\Console as ConsoleWriter;
use Zend\Console\Console;

class Config
{

	/**
	 *
	 * @var array
	 */
	protected $allowedTranslationFilePathParts = [];

	/**
	 *
	 * @var array
	 */
	protected $cliOptions = [
		'fileSuffix|s-s' => 'File suffix to find and parse. Can be given multiple times. Default: .php .phtml .js',
		'exclude|e-s' => 'Exclude given path. Can be given multiple times. Per default the Translation Path will be added to exclued. If exclude defined, translation path will not be added.',
		'verbose|v' => 'Increase the verbosity of messages',
		'help|h' => 'Display this help message',
		'quiet|q' => 'Do not output any message',
		'version|V' => 'Display this application version',
		'dry-run' => 'Outputs the operations but will not execute anything.',
		'pattern|p-s' => 'Regular expression pattern to find translation content. Can be given multiple times. Default: #(?<!->)__\(\'(.*?)\'\)#',
		'replacement|r-s' => 'Defines the replacement in code with translation. Default: $this->__(\'$1\')',
		'locale|l-s' => 'Local to write. Can be given multiple times Default de-DE',
		'matchesIndexFull' => 'Position in regular expression result set for get the full translation part (eg. __(\'Text\') ). Default: 0',
		'matchesIndexKey' => 'Position in regular expression result set for get the full translation part (eg. \'Text\' of __(\'Text\') ). Default: 1',
		'removeTranslationFilePath|t-s' => 'Removes parts of translation file path. Can be given multiple times.',
		'allowedTranslationFilePathParts|a-s' => 'allowed parts of translation file pathes. Can be given multiple times.',
		'gettextFile|g-s' => 'An optional gettext file to every locale. Can be given multiple times. Default none'
	];

	/**
	 *
	 * @var AdapterInterface
	 */
	protected $console;

	/**
	 *
	 * @var string
	 */
	protected $destinationPath;

	/**
	 *
	 * @var bool
	 */
	protected $dryRun = false;

	/**
	 *
	 * @return array
	 */
	protected $exclude = [];

	/**
	 *
	 * @var array
	 */
	protected $fileSuffix = [
		'.php',
		'.phtml',
		'.js'
	];

	/**
	 *
	 * @var Getopt
	 */
	protected $getopt;

	/**
	 * @var string[]
	 */
	protected $gettextFiles = [];

	/**
	 *
	 * @var bool
	 */
	protected $help = false;

	/**
	 *
	 * @var string[]
	 */
	protected $locales = ['de-DE'];

	/**
	 *
	 * @var ConsoleLogger
	 */
	protected $logger;

	/**
	 *
	 * @var ConsoleWriter
	 */
	protected $logWriter;

	/**
	 *
	 * @var int
	 */
	protected $matchesIndexFull = 0;

	/**
	 *
	 * @var int
	 */
	protected $matchesIndexKey = 1;

	/**
	 *
	 * @var array
	 */
	protected $patterns = [
		'#(?<!->)__\(\'([^\']*?)\'\)#'
	];

	/**
	 *
	 * @var bool
	 */
	protected $quiet = false;

	/**
	 *
	 * @var array
	 */
	protected $removeTranslationFilePath = [];

	/**
	 *
	 * @var array
	 */
	protected $replacement = [
		'$this->__(\'$1\')'
	];

	/**
	 *
	 * @var array
	 */
	protected $sourcePath;

	/**
	 *
	 * @var int
	 */
	protected $verbose = 0;

	/**
	 *
	 * @var bool
	 */
	protected $version = false;


	/**
	 * constructor
	 */
	public function __construct()
	{
		try
		{
			$this->getGetopt()->parse();

			if (count($this->getGetopt()->getRemainingArgs()) < 2)
			{
				throw new RuntimeException('missing params');
			}
		}
		catch (\Exception $exception)
		{
			throw new \Exception($this->getHelpMessage());
		}

		$remainingArgs = $this->getGetopt()->getRemainingArgs();

		// set config
		$this->setDestinationPath(array_pop($remainingArgs))
			->setSourcePath($remainingArgs)
			->setAllowedTranslationFilePathParts($this->getGetopt()->allowedTranslationFilePathParts)
			->setDryRun($this->getGetopt()->{'dry-run'})
			->setExclude($this->getGetopt()->exclude)
			->setFileSuffix($this->getGetopt()->fileSuffix)
			->setGettextFiles($this->getGetopt()->gettextFile)
			->setHelp($this->getGetopt()->help)
			->setLocales($this->getGetopt()->locale)
			->setMatchesIndexFull($this->getGetopt()->matchesIndexFull)
			->setMatchesIndexKey($this->getGetopt()->matchesIndexKey)
			->setPatterns($this->getGetopt()->pattern)
			->setQuiet($this->getGetopt()->quiet)
			->setRemoveTranslationFilePath($this->getGetopt()->removeTranslationFilePath)
			->setReplacement($this->getGetopt()->replacement)
			->setVerbose($this->getGetopt()->verbose)
			->setVersion($this->getGetopt()->version);

		// set LogWrite
		$this->getLogWriter()->setVerboseLevel($this->getVerbose())->setQuiet($this->isQuiet());
	}

	/**
	 *
	 * @return array
	 */
	public function getAllowedTranslationFilePathParts()
	{
		return $this->allowedTranslationFilePathParts;
	}

	/**
	 *
	 * @return array
	 */
	protected function getCliOptions()
	{
		return $this->cliOptions;
	}

	/**
	 *
	 * @return AdapterInterface
	 */
	public function getConsole()
	{
		if ($this->console === null)
		{
			$this->console = Console::getInstance();
		}

		return $this->console;
	}

	/**
	 *
	 * @return string
	 */
	public function getDestinationPath()
	{
		return $this->destinationPath;
	}

	/**
	 *
	 * @return array
	 */
	public function getExclude()
	{
		return $this->exclude;
	}

	/**
	 *
	 * @return array
	 */
	public function getFileSuffix()
	{
		return $this->fileSuffix;
	}

	/**
	 *
	 * @return Getopt
	 */
	protected function getGetopt()
	{
		if ($this->getopt === null)
		{
			$this->getopt = (new Getopt($this->getCliOptions()))->setOptions([
				Getopt::CONFIG_CUMULATIVE_PARAMETERS => true
			]);
		}

		return $this->getopt;
	}

	/**
	 * @return string[]
	 */
	public function getGettextFiles()
	{
		return $this->gettextFiles;
	}

	/**
	 *
	 * @return string
	 */
	public function getHelpMessage()
	{
		$message = 'sourcePath [sourcePath] [sourcePath] [...] destinationPath' . PHP_EOL;
		$message .= PHP_EOL;
		$message .= $this->getConsole()->colorize('Arguments:', ColorInterface::YELLOW) . PHP_EOL;
		$message .= $this->getConsole()->colorize(' sourcePath', ColorInterface::GREEN) . '           Path to search in. can be given multiple times' . PHP_EOL;
		$message .= $this->getConsole()->colorize(' destinationPath', ColorInterface::GREEN) . '   Path to write the result' . PHP_EOL;

		return $this->getGetopt()->getUsageMessage($message);
	}

	/**
	 *
	 * @return string[]
	 *
	 */
	public function getLocales()
	{
		return $this->locales;
	}

	/**
	 *
	 * @return ConsoleLogger
	 */
	public function getLogger()
	{
		if ($this->logger === null)
		{
			$this->logger = new ConsoleLogger();
			$this->logger->addWriter($this->getLogWriter());
		}

		return $this->logger;
	}

	/**
	 *
	 * @return ConsoleWriter
	 */
	protected function getLogWriter()
	{
		if ($this->logWriter === null)
		{
			$this->logWriter = new ConsoleWriter($this->getConsole());
		}

		return $this->logWriter;
	}

	/**
	 *
	 * @return int
	 */
	public function getMatchesIndexFull()
	{
		return $this->matchesIndexFull;
	}

	/**
	 *
	 * @return int
	 */
	public function getMatchesIndexKey()
	{
		return $this->matchesIndexKey;
	}

	/**
	 *
	 * @return array
	 */
	public function getPatterns()
	{
		return $this->patterns;
	}

	/**
	 *
	 * @return array
	 */
	public function getRemoveTranslationFilePath()
	{
		return $this->removeTranslationFilePath;
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
	 * @param int $index
	 * @return string
	 */
	public function getReplacementAtIndex($index)
	{
		$replacement = $this->getReplacement();
		$index = min(count($replacement) - 1, $index);

		return $replacement[$index];
	}

	/**
	 *
	 * @return array
	 */
	public function getSourcePath()
	{
		return $this->sourcePath;
	}

	/**
	 *
	 * @return int
	 */
	public function getVerbose()
	{
		return $this->verbose;
	}

	/**
	 *
	 * @return bool
	 */
	public function isDryRun()
	{
		return $this->dryRun;
	}

	/**
	 *
	 * @return bool
	 */
	public function isQuiet()
	{
		return $this->quiet;
	}

	/**
	 *
	 * @return bool
	 */
	public function isHelp()
	{
		return $this->help;
	}

	/**
	 *
	 * @return bool
	 */
	public function isVersion()
	{
		return $this->version;
	}

	/**
	 *
	 * @param array $allowedTranslationFilePathParts
	 * @return self
	 */
	protected function setAllowedTranslationFilePathParts($allowedTranslationFilePathParts)
	{
		if ($allowedTranslationFilePathParts === null)
		{
			return $this;
		}

		if (is_array($allowedTranslationFilePathParts) === false)
		{
			$allowedTranslationFilePathParts = [
				$allowedTranslationFilePathParts
			];
		}

		$this->allowedTranslationFilePathParts = $allowedTranslationFilePathParts;

		return $this;
	}

	/**
	 *
	 * @param string $destinationPath
	 * @return self
	 */
	protected function setDestinationPath($destinationPath)
	{
		$this->destinationPath = str_replace('/', '\\', trim($destinationPath, '\\/')) . DIRECTORY_SEPARATOR;
		$this->setExclude($destinationPath);

		return $this;
	}

	/**
	 *
	 * @param bool $dryRun
	 * @return self
	 */
	protected function setDryRun($dryRun)
	{
		$this->dryRun = (bool)$dryRun;

		return $this;
	}

	/**
	 *
	 * @param array|string|null $exclude
	 * @return self
	 */
	protected function setExclude($exclude)
	{
		if ($exclude === null)
		{
			return $this;
		}

		if (is_array($exclude) === false)
		{
			$exclude = [
				$exclude
			];
		}

		$this->exclude = $exclude;

		return $this;
	}

	/**
	 *
	 * @param array|string|null $fileSuffix
	 * @return self
	 */
	protected function setFileSuffix($fileSuffix)
	{
		if ($fileSuffix === null)
		{
			return $this;
		}

		if (is_array($fileSuffix) === false)
		{
			$fileSuffix = [
				$fileSuffix
			];
		}

		$this->fileSuffix = $fileSuffix;

		return $this;
	}

	/**
	 * @param string[]|string|null $gettextFiles
	 */
	protected function setGettextFiles($gettextFiles)
	{
		if ($gettextFiles === null)
		{
			return $this;
		}

		if (is_array($gettextFiles) === false)
		{
			$gettextFiles = [
				$gettextFiles
			];
		}

		$this->gettextFiles = $gettextFiles;

		return $this;
	}

	/**
	 *
	 * @param bool $help
	 * @return self
	 */
	protected function setHelp($help)
	{
		$this->help = (bool)$help;

		return $this;
	}

	/**
	 *
	 * @param string[]|string|null $locales
	 * @return self
	 */
	protected function setLocales($locales)
	{
		if ($locales === null)
		{
			return $this;
		}

		if (is_array($locales) === false)
		{
			$locales = [
				$locales
			];
		}

		$this->locales = $locales;

		return $this;
	}

	/**
	 *
	 * @param int $matchesIndexFull
	 * @return self
	 */
	protected function setMatchesIndexFull($matchesIndexFull)
	{
		if ($matchesIndexFull === null)
		{
			return $this;
		}

		$this->matchesIndexFull = (int)$matchesIndexFull;

		return $this;
	}

	/**
	 *
	 * @param int $matchesIndexKey
	 * @return self
	 */
	protected function setMatchesIndexKey($matchesIndexKey)
	{
		if ($matchesIndexKey === null)
		{
			return $this;
		}

		$this->matchesIndexKey = $matchesIndexKey;

		return $this;
	}

	/**
	 *
	 * @param string $patterns
	 * @return self
	 */
	protected function setPatterns($patterns)
	{
		if ($patterns === null)
		{
			return $this;
		}

		if (is_array($patterns) === false)
		{
			$patterns = [
				$patterns
			];
		}

		$this->patterns = $patterns;

		return $this;
	}

	/**
	 *
	 * @param bool $quiet
	 * @return self
	 */
	protected function setQuiet($quiet)
	{
		$this->quiet = (bool)$quiet;

		return $this;
	}

	/**
	 *
	 * @param array $removeTranslationFilePath
	 * @return self
	 */
	protected function setRemoveTranslationFilePath($removeTranslationFilePath)
	{
		if ($removeTranslationFilePath === null)
		{
			return $this;
		}

		if (is_array($removeTranslationFilePath) === false)
		{
			$removeTranslationFilePath = [
				$removeTranslationFilePath
			];
		}

		$this->removeTranslationFilePath = $removeTranslationFilePath;

		return $this;
	}

	/**
	 *
	 * @param array $replacement
	 * @return self
	 */
	protected function setReplacement($replacement)
	{
		if ($replacement === null)
		{
			return $this;
		}

		if (is_array($replacement) === false)
		{
			$replacement = [
				$replacement
			];
		}

		$this->replacement = $replacement;

		return $this;
	}

	/**
	 *
	 * @param array $sourcePath
	 * @return self
	 */
	protected function setSourcePath($sourcePath)
	{
		if (is_array($sourcePath) === false)
		{
			$sourcePath = [
				$sourcePath
			];
		}

		$this->sourcePath = [];

		foreach ($sourcePath as $path)
		{
			$path = realpath($path);
			if (is_dir($path) === false)
			{
				continue;
			}

			$this->sourcePath[] = $path;
		}

		return $this;
	}

	/**
	 *
	 * @param arreay|int|null $verbose
	 * @return self
	 */
	protected function setVerbose($verbose)
	{
		if ($verbose === null)
		{
			return $this;
		}

		if (is_array($verbose) === true)
		{
			$verbose = count($verbose);
		}

		$this->verbose = (int)$verbose;

		return $this;
	}

	/**
	 *
	 * @param bool $version
	 * @return self
	 */
	protected function setVersion($version)
	{
		$this->version = (bool)$version;

		return $this;
	}
}