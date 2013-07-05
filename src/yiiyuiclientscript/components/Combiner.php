<?php

/**
 * Base class for combiners. It provides a YUI compressor instance as well as 
 * functionality common to all combiners.
 *
 * @author Sam Stenvall <sam@supportersplace.com>
 */

namespace yiiyuiclientscript\components;

abstract class Combiner
{
	
	const FILE_EXT_CSS = 'css';
	const FILE_EXT_JS = 'js';

	/**
	 * @var string the prefix to use for combined files
	 */
	protected $filePrefix;
	
	/**
	 * @var array options for the YUI compressor
	 */
	protected $compressorOptions;

	/**
	 * @var \YUI\Compressor the YUI compressor
	 */
	private $_compressor;

	/**
	 * Class constructor
	 * @param string $filePrefix the prefix for combined files
	 * @param array $compressorOptions options for the YUI compressor
	 * @see \YUI\Compressor
	 */
	public function __construct($filePrefix, $compressorOptions)
	{
		$this->filePrefix = $filePrefix;
		$this->_compressor = new \YUI\Compressor($compressorOptions);
		$this->compressorOptions = $compressorOptions;
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
				.implode($this->compressorOptions));
		
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
		$baseUrl = \Yii::app()->request->baseUrl.'/';

		if (!strncmp($url, $baseUrl, strlen($baseUrl)))
		{
			$basePath = dirname(\Yii::app()->request->scriptFile).DIRECTORY_SEPARATOR;
			$url = $basePath.substr($url, strlen($baseUrl));
			return $url;
		}

		return false;
	}

	/**
	 * Compresses the specified contents using the specified content type
	 * @see \YUI\Compressor
	 * @param string $contentType content type for the compressor
	 * @param string $contents the contents to compress
	 * @return string the compressed contents
	 */
	protected function compress($contentType, $contents)
	{
		$this->_compressor->setType($contentType);

		foreach ($contents as &$content)
			$content = $this->_compressor->compress($content);

		return $contents;
	}

}