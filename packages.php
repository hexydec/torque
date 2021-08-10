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
	public const VERSION = '0.4.1';

	/**
	 * @var string INSTALLDIR The folder where the dependencies are stored
	 */
	public const INSTALLDIR = __DIR__.'/packages/';

	/**
	 * @var array $packages A list of external dependencies to be installed when the plugin is activated and where their autoloaders are
	 *
	 * Note that external dependencies are only installed when install-external.php is available and the packages are not already bundled
	 */
	protected static $packages = [
		'hexydec\\html\\htmldoc' => [
			'file' => 'https://github.com/hexydec/htmldoc/archive/refs/heads/master.zip',
			'autoload' => 'htmldoc-master/src/autoload.php'
		],
		'hexydec\\css\\cssdoc' => [
			'file' => 'https://github.com/hexydec/cssdoc/archive/refs/heads/master.zip',
			'autoload' => 'cssdoc-master/src/autoload.php'
		],
		'hexydec\\jslite\\jslite' => [
			'file' => 'https://github.com/hexydec/jslite/archive/refs/heads/master.zip',
			'autoload' => 'jslite-master/src/autoload.php'
		],
		'hexydec\\tokens\\tokenise' => [
			'file' => 'https://github.com/hexydec/tokenise/archive/refs/heads/master.zip',
			'autoload' => 'tokenise-master/src/autoload.php'
		]
	];

	/**
	 * Defines an autoloader to load the application's dependencies
	 *
	 * @return void
	 */
	public static function autoload() : void {
		\spl_autoload_register(function (string $class) : bool {
			$dir = self::INSTALLDIR;
			foreach (self::$packages AS $key => $item) {
				if ($key === $class && \file_exists($dir.$item['autoload'])) {
					return require($dir.$item['autoload']);
				}
			}
			return false;
		});
	}
}
