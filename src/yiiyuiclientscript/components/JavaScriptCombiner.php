<?php

/**
 * JavaScript combiner
 *
 * @author Sam Stenvall <sam@supportersplace.com>
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace yiiyuiclientscript\components;

class JavaScriptCombiner extends FileCombiner
{
	
	/**
	 * Combines the specified files
	 * @param string $files
	 * @return array the combined script files
	 */
	public function combine($files)
	{
		// Store the contents of all local scripts in an array (which we'll 
		// combine later) and store external scripts in an other array which 
		// will remain untouched.
		$externalScripts = array();
		$contents = array();

		foreach ($files as $url)
		{
			$file = $this->resolveAssetPath($url);

			if ($file !== false)
				$contents[$file] = file_get_contents($file);
			else
				$externalScripts[$url] = $url;
		}

		// Detect files that use global strict mode
		$strictFiles = array();
		$normalFiles = array();

		foreach ($contents as $file=> $content)
		{
			if (preg_match('/^[\'"]use strict[\'"]/im', $content) > 0)
				$strictFiles[$file] = $content;
			else
				$normalFiles[$file] = $content;
		}

		$combinedFiles = array();

		// Combine strict and non-strict files separately
		if (!empty($strictFiles))
			$combinedFiles = array_merge($combinedFiles, $this->combineFiles($strictFiles));
		if (!empty($normalFiles))
			$combinedFiles = array_merge($combinedFiles, $this->combineFiles($normalFiles));

		// Finally, merge the external scripts as is
		return array_merge($externalScripts, $combinedFiles);
	}
	
	/**
	 * Combines the specified files and returns the URL to it as an array
	 * @param array $files list of files and their contents
	 * @return array
	 */
	private function combineFiles($files)
	{
		$combinedProps = $this->getCombinedFileProperties(self::FILE_EXT_JS, 
				array_keys($files));

		if (!file_exists($combinedProps['path']))
		{
			file_put_contents($combinedProps['path'], implode(PHP_EOL,
					$this->compress(\YUI\Compressor::TYPE_JS, $files)));
		}

		return array($combinedProps['url']=>$combinedProps['url']);
	}

}