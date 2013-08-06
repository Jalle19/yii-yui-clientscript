<?php

/**
 * Base class for combiners that operate on files.
 *
 * @author Sam Stenvall <sam@supportersplace.com>
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace yiiyuiclientscript\components;

use yiiyuiclientscript\exceptions\Exception as Exception;
use yiiyuiclientscript\interfaces\PathResolver;

abstract class FileCombiner extends Combiner
{
	
	const FILE_EXT_CSS = 'css';
	const FILE_EXT_JS = 'js';

	/**
	 * @var string the prefix to use for combined files
	 */
	protected $filePrefix;

	/**
	 * @var PathResolver
	 */
	protected $pathResolver;

	/**
	 * Class constructor
	 * @param string $filePrefix the prefix for combined files
	 * @param PathResolver $pathResolver path resolver
	 */
	public function __construct($filePrefix, PathResolver $pathResolver)
	{
		$this->filePrefix = $filePrefix;
		$this->pathResolver = $pathResolver;

		parent::__construct();
	}

	/**
	 * Combines the files specified and returns them in the same format
	 */
	abstract public function combine($files);
	
	/**
	 * Determines the named of a combined file based based on the specified list 
	 * of files. The filename varies depending on the files specified, the time 
	 * the files were last modified and the compressor options used.
	 * @param string $extension the extension to use for the file
	 * @param array $files the files that will be combined
	 * @return array absolute path and URL to the combined file
	 */
	protected function getCombinedFileProperties($extension, $files)
	{
		$identifier = sha1(implode($files)
				.$this->getLastModification($files)
				.implode(\Yii::app()->clientScript->compressorOptions));
		
		$file = $this->filePrefix.'-'.$identifier.'.'.$extension;

		return array(
			'path'=>\Yii::app()->assetManager->basePath.'/'.$file,
			'url'=>\Yii::app()->assetManager->baseUrl.'/'.$file,
		);
	}
	
	/**
	 * Returns the timestamp of the last modification made to the specified 
	 * files
	 * @param array $files
	 */
	protected function getLastModification($files)
	{
		$lastModification = 0;
		
		foreach ($files as $file)
		{
			$mtime = filemtime($file);
			if ($mtime > $lastModification)
				$lastModification = $mtime;
		}
		
		return $lastModification;
	}

	/**
	 * Returns the local path of a published file based on its URL, or false if 
	 * the URL is not local.
	 * @param the URL to the file
	 * @return string local file path for the published file
	 */
	protected function resolveAssetPath($url)
	{
		$result = $this->pathResolver->resolveAssetPath($url);
		if ($result)
			return $this->assertFileExists($result);
		else
            return false;
	}

	/**
	 * Checks if the specified file exists and returns the parameter as is if 
	 * it exists, otherwise an exception is thrown
	 * @param string $file the absolute path to the file
	 * @return string the absolute path to the file
	 * @throws Exception if the file doesn't exist
	 */
	private function assertFileExists($file)
	{
		if (!file_exists($file))
			throw new Exception('Unable to combine files, '.$file.' does not exist');

		return $file;
	}

}