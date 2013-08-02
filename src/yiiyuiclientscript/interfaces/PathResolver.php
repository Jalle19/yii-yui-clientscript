<?php

namespace yiiyuiclientscript\interfaces;

/**
 * Path resolver interface
 *
 * @author Sam Stenvall <sam@supportersplace.com>
 * @author Peter Buri <peter.buri@netpositive.hu>
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
interface PathResolver
{
	/**
	 * Based on the specified URL, the method should either return the absolute 
	 * path to the file on the server, or false if the URL points to an 
	 * external resource (e.g. //cdn.example.com/script.js)
	 * @param $url string
	 * @return string|boolean
	 */
	public function resolveAssetPath($url);
}