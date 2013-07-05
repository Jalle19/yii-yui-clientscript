yii-yui-clientscript
====================

The end-all, be-all minifying client script implementation for Yii. It is able to combine your CSS and script files as well as minimize them using the YUI compressor (available via the [php-yui-compressor](https://github.com/Jalle19/php-yui-compressor) package).

Features
--------

* drop-in replacement for the standard CClientScript (no changes to the way you register scripts and stylesheets)
* uses the native Java-based YUI compressor instead of one of the countless more-or-less unmaintained ports of it
* detects global strict mode in JavaScript files and combines such scripts separately from standard scripts
* uses unique filenames for the combined files, meaning you won't have to manually implement cache-busting

Installation
------------

Use Composer to install, then add the following to your application configuration:

```
'aliases'=>array(
		// change this path if necessary
		'yiiyuiclientscript'=>realpath(__DIR__.'/../../vendor/jalle19/yii-yui-clientscript/src/yiiyuiclientscript'),
		...
),
...
'components'=>array(
		'clientScript'=>array(
			'class'=>'yiiyuiclientscript\components\ClientScript',
		),
		...
),

```

Configuration
-------------

See the documentation for [php-yui-compressor](https://github.com/Jalle19/php-yui-compressor) for which options are available for the YUI compressor. The options are specified by "compressorOptions" in the component configuration, e.g.:

```
'components'=>array(
		'clientScript'=>array(
			'class'=>'yiiyuiclientscript\components\ClientScript',
			'compressorOptions'=>array(
				'line-break'=>80,
				'disable-optimizations'=>true,
			)
		),
		...
),
```

License
-------

TODO
