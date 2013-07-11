<?php

/**
 * Combines and compresses all registered CSS files and scripts (both files and 
 * inline scripts).
 *
 * @author Sam Stenvall <sam@supportersplace.com>
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace yiiyuiclientscript\components;
use yiiyuiclientscript\exceptions\Exception as Exception;

class ClientScript extends \CClientScript
{

	/**
	 * @var string the file prefix for combined stylesheets
	 */
	public $combinedCssPrefix = 'styles';

	/**
	 * @var string the file prefix for combined scripts
	 */
	public $combinedScriptPrefix = 'scripts';
	
	/**
	 * @var array options for the YUI compressor. Defaults to an empty array, 
	 * meaning the standard options will be used
	 * @see \YUI\Compressor
	 */
	public $compressorOptions = array();

	/**
	 * @var JavaScriptCombiner reusable JavaScript combiner
	 */
	private $_javascriptCombiner;

	/**
	 * Combines the scripts at the beginning of the body section
	 * @param string $output
	 */
	public function renderBodyBegin(&$output)
	{
		$this->combineScripts(self::POS_BEGIN);

		parent::renderBodyBegin($output);
	}

	/**
	 * Combines the scripts at the end of the body section
	 * @param string $output
	 */
	public function renderBodyEnd(&$output)
	{
		$this->combineScripts(array(
			self::POS_END, self::POS_LOAD, self::POS_READY));

		parent::renderBodyEnd($output);
	}

	/**
	 * Combines the CSS files and the scripts at the head position
	 * @param string $output
	 */
	public function renderHead(&$output)
	{
		$combiner = new CSSCombiner($this->combinedCssPrefix, $this->compressorOptions);
		$this->cssFiles = $combiner->combine($this->cssFiles);

		$this->combineScripts(self::POS_HEAD);

		parent::renderHead($output);
	}

	/**
	 * Combines the scripts at the specified positions
	 * @param mixed $position a single position specified as an integer, or 
	 * multiple positions specified as an array of integers.
	 */
	private function combineScripts($positions)
	{
		if (!$this->enableJavaScript)
			return;
		
		if (!is_array($positions))
			$positions = array($positions);
		
		foreach($positions as $position) 
		{
			// Combine and compress script files
			if (isset($this->scriptFiles[$position]))
			{
				if ($this->_javascriptCombiner === null)
				{
					$this->_javascriptCombiner = new JavaScriptCombiner(
							$this->combinedScriptPrefix, $this->compressorOptions);
				}

				$this->scriptFiles[$position] = $this->_javascriptCombiner->
						combine($this->scriptFiles[$position]);
			}

			$this->combineInlineScripts($position);
		}
	}

	/**
	 * Combines and compresses all inline scripts at the specified position. 
	 * The combined contents is stored as a file in the assets folder so that 
	 * the combination only happens when the contents change.
	 * @param int $position the position of the scripts
	 * @throws Exception if the combined script file can't be created
	 */
	private function combineInlineScripts($position)
	{
		if (!isset($this->scripts[$position]))
			return;

		$scriptHash = md5(serialize($this->scripts[$position]));
		$combinedScript = \Yii::app()->assetManager->basePath
				.'/'.$this->combinedScriptPrefix
				.'-'.$scriptHash.'.js';

		// Create a compressed version of the scripts if it doesn't exist
		if (!file_exists($combinedScript))
		{
			$combiner = new Combiner($this->compressorOptions);

			$contents = $combiner->compress(
					\YUI\Compressor::TYPE_JS, $this->scripts[$position]);

			if (@file_put_contents($combinedScript, $contents) === false)
				throw new Exception('Failed to compress inline scripts: Could not write to file '.$combinedScript);
		}

		// Replace the originals
		$this->scripts[$position] = array(
			$scriptHash=>file_get_contents($combinedScript));
	}

}