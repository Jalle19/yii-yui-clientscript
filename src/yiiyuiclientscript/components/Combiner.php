<?php

/**
 * Base class for all combiners. This class provides the interface to the YUI 
 * compressor (the compress() method).
 *
 * @author Sam Stenvall <sam@supportersplace.com>
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace yiiyuiclientscript\components;
use yiiyuiclientscript\exceptions\Exception as Exception;

class Combiner
{

	/**
	 * @var \YUI\Compressor the YUI compressor
	 */
	protected $compressor;

	/**
	 * Class constructor. It initializes the YUI compressor.
	 * @see \YUI\Compressor
	 */
	public function __construct()
	{
		$compressorOptions = \Yii::app()->clientScript->compressorOptions;
		$this->compressor = new \YUI\Compressor($compressorOptions);
	}

	/**
	 * Compresses the specified contents using the specified content type
	 * @see \YUI\Compressor
	 * @param string $contentType content type for the compressor
	 * @param string $contents the contents to compress
	 * @return string the compressed contents
	 */
	public function compress($contentType, $contents)
	{
		$this->compressor->setType($contentType);

		// Re-throw any errors from the compressor under our own namespace
		try
		{
			foreach ($contents as &$content)
				$content = $this->compressor->compress($content);
		}
		catch (\YUI\Exception $e)
		{
			throw new Exception('YUI compressor failed with: '.$e->getMessage(), $e->getCode(), $e);
		}

		return $contents;
	}
	
	/**
	 * Checks whether the contents from the specified URL should be excluded 
	 * from the minification process
	 * @param string $url the URL to the file
	 * @return boolean
	 */
	protected function shouldExclude($url)
	{
		foreach (\Yii::app()->clientScript->exclude as $needle)
			if (strpos($url, $needle) !== false)
				return true;

		return false;
	}

}