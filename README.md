yii-yui-clientscript
====================

The end-all, be-all minifying client script implementation for Yii. It is able to combine your CSS and script files as well as minimize them using the YUI compressor (available via the [php-yui-compressor](https://github.com/Jalle19/php-yui-compressor) package).

Features
--------

* drop-in replacement for the standard CClientScript (no changes to the way you register scripts and stylesheets)
* uses the native Java-based YUI compressor instead of one of the countless more-or-less unmaintained ports of it
* detects global strict mode in JavaScript files and combines such scripts separately from standard scripts
* uses unique filenames for the combined files, meaning you won't have to manually implement cache-busting
* compresses inline scripts too

Installation
------------

Use Composer to install, then add the following to your application configuration:

```php
// change this path if necessary
Yii::setPathOfAlias('yiiyuiclientscript', realpath(__DIR__.'/../../vendor/jalle19/yii-yui-clientscript/src/yiiyuiclientscript'));
...
return array(
	...
	'components'=>array(
		...
		'clientScript'=>array(
			'class'=>'yiiyuiclientscript\components\ClientScript',
		),
		...
	),
	...
),

```

You'll also need to include the Composer autoloader in your bootstrap script. Usually this can be done using `require_once('vendor/autoload.php');`.

Configuration
-------------

See the documentation for [php-yui-compressor](https://github.com/Jalle19/php-yui-compressor) for which options are available for the YUI compressor. The options are specified by "compressorOptions" in the component configuration, e.g.:

```php
...
return array(
	...
	'components'=>array(
			...
			'clientScript'=>array(
				'class'=>'yiiyuiclientscript\components\ClientScript',
				'compressorOptions'=>array(
					'line-break'=>80,
					'disable-optimizations'=>true,
				)
			),
			...
	),
	...
),
```

### Excluding files

Sometimes you may want to combine and compress all files but one (perhaps because it changes too often causing all scripts to need re-combination). You can specify a pattern that matches these files using the "exclude" option:

```php
...
return array(
	...
	'components'=>array(
			...
			'clientScript'=>array(
				'class'=>'yiiyuiclientscript\components\ClientScript',
				'exclude'=>array(
					'MainMenu'
				),
			),
			...
	),
	...
),
```

The pattern matching is done using `strpos()` on the script URL, meaning in the example above the script `MainMenu-typeahead-1.js` (and naturally all other scripts having "MainMenu" in their name) would be registered separately.

License
-------

This code is licensed under the [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
