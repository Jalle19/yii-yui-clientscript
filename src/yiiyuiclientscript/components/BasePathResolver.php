<?php

namespace yiiyuiclientscript\components;

use yiiyuiclientscript\interfaces\PathResolver;

/**
 * Path resolver
 *
 * @author Sam Stenvall <sam@supportersplace.com>
 * @author Peter Buri <peter.buri@netpositive.hu>
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class BasePathResolver implements PathResolver
{
	/**
	 * @param $url string
	 * @return string
	 */
	public function resolveAssetPath($url)
	{
		// Check if the script is external
		foreach (array('http', 'https', '//') as $startsWith)
			if (strpos($url, $startsWith) === 0)
				return false;

		return $_SERVER['DOCUMENT_ROOT'].$url;
	}

}