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
	 * @param $url string
	 * @return string
	 */
	public function resolveAssetPath($url);
}