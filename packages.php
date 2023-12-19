<?php
/**
 * Defines and autoloads the application's dependencies
 *
 * @package hexydec/torque
 */
namespace hexydec\torque;

class packages {

	/**
	 * @var string SLUG A constant defining the slug that is used in the admin system to deliver the app
	 */
	public const SLUG = 'torque';

	/**
	 * @var string VERSION The version number of the application, this is used in the cache key for CSS/Javascript that is minified
	 */
	public const VERSION = '0.7.3';

	/**
	 * @var string INSTALLDIR The folder where the dependencies are stored
	 */
	public const INSTALLDIR = __DIR__.'/packages/';

	/**
	 * @var array $packages A list of external dependencies to be installed when the plugin is activated and where their autoloaders are
	 *
	 * Note that external dependencies are only installed when install-external.php is available and the packages are not already bundled
	 */
	protected static array $packages = [
		'htmldoc' => [
			'class' => 'hexydec\\html\\htmldoc',
			'file' => 'https://github.com/hexydec/htmldoc/archive/refs/heads/master.zip',
			'extract' => 'htmldoc-master/src/',
			'autoload' => 'htmldoc/autoload.php'
		],
		'cssdoc' => [
			'class' => 'hexydec\\css\\cssdoc',
			'file' => 'https://github.com/hexydec/cssdoc/archive/refs/heads/master.zip',
			'extract' => 'cssdoc-master/src/',
			'autoload' => 'cssdoc/autoload.php'
		],
		'jslite' => [
			'class' => 'hexydec\\jslite\\jslite',
			'file' => 'https://github.com/hexydec/jslite/archive/refs/heads/master.zip',
			'extract' => 'jslite-master/src/',
			'autoload' => 'jslite/autoload.php'
		],
		'tokenise' => [
			'class' => 'hexydec\\tokens\\tokenise',
			'file' => 'https://github.com/hexydec/tokenise/archive/refs/heads/master.zip',
			'extract' => 'tokenise-master/src/',
			'autoload' => 'tokenise/autoload.php'
		]
	];

	/**
	 * Defines an autoloader to load the application's dependencies
	 *
	 * @return void
	 */
	public static function autoload() : void {
		\spl_autoload_register(function (string $class) : void {
			$dir = self::INSTALLDIR;
			foreach (self::$packages AS $item) {
				if ($item['class'] === $class && \file_exists($dir.$item['autoload'])) {
					require $dir.$item['autoload'];
				}
			}
		});
	}
}
