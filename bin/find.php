<?php
use DasRed\Finder\Config;
use DasRed\Finder\Parser;
use DasRed\Finder\File;
use DasRed\Finder\Counter;
use DasRed\Finder\Exception;
use Zend\Console\ColorInterface;

set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcontext)
{
	throw new Exception($errstr, $errno);
});

require_once __DIR__ . '/../vendor/autoload.php';

try
{
	$config = new Config();
}
catch (\Exception $exception)
{
	echo $exception->getMessage();
	exit();
}

try
{
	// collect all files from given pathes
	$fileIterator = new File_Iterator_Facade();
	$files = $fileIterator->getFilesAsArray($config->getSourcePath(), $config->getFileSuffix(), '', $config->getExclude());

	$counter = new Counter();

	// walk over all files and parse them
	$parser = new Parser($config, $counter);
	$config->getLogger()->always(PHP_EOL . 'Parsing Source Files');
	foreach ($files as $file)
	{
		$parser->parse(new File($file, str_replace($config->getSourcePath(), '', $file), $config));

		$config->getLogger()->always('.', false);
	}

	// write the TR keys out
	$config->getLogger()->always(PHP_EOL . 'Writing Translation Files');
	$parser->getCollectionTranslationLocale()->write($config->getLogger(), $config->getDestinationPath(), $config, $counter);

	// replace in source files
	$config->getLogger()->always(PHP_EOL . 'Replacing Translations in Source Files');
	$parser->getCollectionSourceFile()->replace($config->getLogger(), $counter);

	// print stats
	$counter->output($config->getLogger());
}
catch (\Exception $exception)
{
	$config->getLogger()
		->always(PHP_EOL . PHP_EOL)
		->always($config->getConsole()
		->colorize($exception->getMessage(), ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED))
		->always(PHP_EOL)
		->always($config->getConsole()
		->colorize($exception->getTraceAsString(), ColorInterface::LIGHT_YELLOW, ColorInterface::LIGHT_RED));
}