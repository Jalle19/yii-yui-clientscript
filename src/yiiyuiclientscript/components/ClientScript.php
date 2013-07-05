<?php

/**
 * Combines and compresses all registered CSS and script files.
 *
 * @author Sam Stenvall <sam@supportersplace.com>
 */

namespace yiiyuiclientscript\components;

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
		$this->combineScripts(self::POS_END);

		parent::renderBodyEnd($output);
	}

	/**
	 * Combines the CSS files and the scripts at the head position
	 * @param string $output
	 */
	public function renderHead(&$output)
	{
		$combiner = new CSSCombiner($this->combinedCssPrefix);
		$this->cssFiles = $combiner->combine($this->cssFiles);

		$this->combineScripts(self::POS_HEAD);

		parent::renderHead($output);
	}

	/**
	 * Combines the scripts at the specified position
	 * @param int $position the position (@see CClientScript)
	 */
	private function combineScripts($position)
	{
		if ($this->enableJavaScript && isset($this->scriptFiles[$position]))
		{
			if ($this->_javascriptCombiner === null)
			{
				$this->_javascriptCombiner = new JavaScriptCombiner(
						$this->combinedScriptPrefix);
			}

			$this->scriptFiles[$position] = $this->_javascriptCombiner->
					combine($this->scriptFiles[$position]);
		}
	}

}