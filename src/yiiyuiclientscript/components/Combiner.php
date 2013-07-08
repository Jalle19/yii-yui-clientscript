<?php

/**
 * Base class for all combiners. This class provides the interface to the YUI 
 * compressor (the compress() method).
 *
 * @author Sam Stenvall <sam@supportersplace.com>
 */

namespace yiiyuiclientscript\components;
use yiiyuiclientscript\exceptions\Exception as Exception;

class Combiner
{

	/**
	 * @var array options for the YUI compressor
	 */
	protected $compressorOptions;

	/**
	 * @var \YUI\Compressor the YUI compressor
	 */
	protected $compressor;

	/**
	 * Class constructor
	 * @param array $compressorOptions options for the YUI compressor
	 * @see \YUI\Compressor
	 */
	public function __construct($compressorOptions)
	{
		$this->compressor = new \YUI\Compressor($compressorOptions);
		$this->compressorOptions = $compressorOptions;
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

}