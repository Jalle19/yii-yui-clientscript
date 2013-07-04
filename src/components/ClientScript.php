<?php

/**
 * Description of ClientScript
 *
 * @author Sam Stenvall <sam@supportersplace.com>
 */
namespace YIIYUIClientScript\components;

class ClientScript extends \CClientScript
{

	public $combinedCssPrefix = 'styles';
	
	public $combinedScriptPrefix = 'scripts';
	
	private $_compressor;
	
	public function init()
	{
		$this->_compressor = new \YUI\Compressor();

		parent::init();
	}

	public function renderBodyBegin(&$output)
	{
		if ($this->enableJavaScript)
			$this->combineScriptFiles(\CClientScript::POS_BEGIN);
		
		parent::renderBodyBegin($output);
	}

	public function renderBodyEnd(&$output)
	{
		if ($this->enableJavaScript)
			$this->combineScriptFiles(\CClientScript::POS_END);
		
		parent::renderBodyEnd($output);		
	}

	public function renderHead(&$output)
	{
		// Turn the array around
		$cssFiles = array();
		foreach ($this->cssFiles as $url=> $media)
			$cssFiles[$media][] = $url;

		// Reset cssFiles, it will be repopulated by combineCssFiles()
		$this->cssFiles = array();
		
		foreach ($cssFiles as $media=> $files)
			$this->combineCssFiles($files, $media);
		
		if ($this->enableJavaScript)
			$this->combineScriptFiles(\CClientScript::POS_HEAD);

		parent::renderHead($output);
	}
	
	protected function combineCssFiles($files, $media)
	{
		// Get the contents of all local CSS files and store the external ones
		// in a separate array
		$externalFiles = array();
		$contents = array();

		foreach ($files as $url)
		{
			$file = $this->resolveAssetPath($url);

			if ($file !== false)
				$contents[$file] = $this->remapCssUrls(file_get_contents($file), $url);
			else
				$externalFiles[$url] = $url;
		}
		
		// Check if we need to perform combination
		$combinedProps = $this->getCombinedStyleProperties($media);
		
		if ($this->needsCombination($combinedProps['path'], array_keys($contents)))
		{
			file_put_contents($combinedProps['path'], implode(PHP_EOL, 
					$this->compress(\YUI\Compressor::TYPE_CSS, $contents)));
		}

		foreach ($externalFiles as $externalFile)
			$this->cssFiles[$externalFile] = $media;

		$this->cssFiles[$combinedProps['url']] = $media;
	}
	
	public function combineScriptFiles($position)
	{
		if (!isset($this->scriptFiles[$position]))
			return;

		// Store the contents of all local scripts in an array (which we'll 
		// combine later) and store external scripts in an other array which 
		// will remain untouched.
		$externalScripts = array();
		$contents = array();

		foreach ($this->scriptFiles[$position] as $url)
		{
			$file = $this->resolveAssetPath($url);

			if ($file !== false)
				$contents[$file] = file_get_contents($file);
			else
				$externalScripts[$url] = $url;
		}

		// Check if we need to combine the files
		$combinedProps = $this->getCombinedScriptProperties($position);

		if ($this->needsCombination($combinedProps['path'], array_keys($contents)))
		{
			file_put_contents($combinedProps['path'], implode(PHP_EOL, 
					$this->compress(\YUI\Compressor::TYPE_JS, $contents)));
		}

		// Replace the local scripts with the combined one
		$this->scriptFiles[$position] = array_merge($externalScripts, array(
			$combinedProps['url']=>$combinedProps['url']));
	}

	private function getCombinedStyleProperties($media)
	{
		if (empty($media))
			$file = $this->combinedCssPrefix.'.css';
		else
			$file = $this->combinedCssPrefix.'-'.$media.'.css';

		return array(
			'file'=>$file,
			'path'=>\Yii::app()->assetManager->basePath.'/'.$file,
			'url'=>\Yii::app()->assetManager->baseUrl.'/'.$file,
		);
	}

	private function getCombinedScriptProperties($position)
	{
		$file = $this->combinedScriptPrefix.'-'.$position.'.js';

		return array(
			'file'=>$file,
			'path'=>\Yii::app()->assetManager->basePath.'/'.$file,
			'url'=>\Yii::app()->assetManager->baseUrl.'/'.$file,
		);
	}
	
	private function needsCombination($combinedFile, $files)
	{
		if (!file_exists($combinedFile))
			return true;

		$lastModified = filemtime($combinedFile);

		foreach ($files as $file)
			if (filemtime($file) > $lastModified)
				return true;

		return false;
	}
	
	private function compress($contentType, $contents)
	{
		$this->_compressor->setType($contentType);

		foreach ($contents as &$content)
			$content = $this->_compressor->compress($content);

		return $contents;
	}

	/**
	 * Returns the local path of a published file based on its URL, or false if 
	 * the URL is not local.
	 * @param the URL to the file
	 * @return string local file path for the published file
	 */
	private function resolveAssetPath($url)
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
	 * The combined file will not necessarily be located in its 
	 * original position so we must re-write relative URLs to point 
	 * to the new location.
	 * 
	 * @param type $contents
	 * @param type $url
	 * @return type
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