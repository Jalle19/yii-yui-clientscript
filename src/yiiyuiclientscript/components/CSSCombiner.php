<?php

/**
 * CSS combiner
 *
 * @author Sam Stenvall <sam@supportersplace.com>
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

namespace yiiyuiclientscript\components;

class CSSCombiner extends FileCombiner
{
	
	/**
	 * Combines the specified files into one file per media type
	 * @param string $files
	 * @return array the combined CSS files
	 */
	public function combine($files)
	{
		// Turn the array around
		$cssFiles = array();
		foreach ($files as $url=> $media)
			$cssFiles[$media][] = $url;

		// We will store the new list of registered files here
		$combinedFiles = array();

		// Produce one file for each media type
		foreach ($cssFiles as $media=> $files)
		{
			// Get the contents of all local CSS files and store the external 
			// and excluded ones in a separate array
			$untouchedFiles = array();
			$contents = array();

			foreach ($files as $url)
			{
				$file = $this->resolveAssetPath($url);

				if ($file !== false && !$this->shouldExclude($url))
					$contents[$file] = $this->remapCssUrls(file_get_contents($file), $url);
				else
					$untouchedFiles[$url] = $url;
			}

			$combinedProps = $this->getCombinedFileProperties(
					self::FILE_EXT_CSS, array_keys($contents));

			// Check if we need to perform combination
			if (!file_exists($combinedProps['path']))
			{
				file_put_contents($combinedProps['path'], implode(PHP_EOL, 
						$this->compress(\YUI\Compressor::TYPE_CSS, $contents)));
			}

			foreach ($untouchedFiles as $untouchedFile)
				$combinedFiles[$untouchedFile] = $media;

			$combinedFiles[$combinedProps['url']] = $media;
		}

		return $combinedFiles;
	}

	/**
	 * Rewrites URLs in the specified contents so they match the location of 
	 * the combined CSS file (since it will be placed in another directory than 
	 * the source files)
	 * @param string $contents the CSS contents
	 * @param string $url the URL to the source CSS file
	 * @return string the remapped contents
	 */
	private function remapCssUrls($contents, $url)
	{
		$regex = '#url\s*\(\s*([\'"])?(?!/|http://)([^\'"\s])#i';

		if (preg_match($regex, $contents))
		{
			$relativeUrl = $this->getRelativeUrl(\Yii::app()->assetManager->baseUrl, dirname($url));
			$contents = preg_replace($regex, 'url(${1}'.$relativeUrl.'/${2}', $contents);
		}

		return $contents;
	}

	/**
	 * Calculate the relative URL from source to target
	 * @param string $source source URL
	 * @param string $target the target URL
	 * @return string the relative URL
	 */
	private function getRelativeUrl($source, $target)
	{
		$relative = '';
		while (true)
		{
			if ($source === $target)
				return $relative;
			else if ($source === dirname($source))
				return $relative.substr($target, 1);
			if (!strncmp($source.'/', $target, strlen($source) + 1))
				return $relative.substr($target, strlen($source) + 1);

			$source = dirname($source);
			$relative .= '../';
		}
	}

}