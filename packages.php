<?php
namespace hexydec\torque;

class packages {

	public const SLUG = 'torque';
	public const VERSION = '0.4.0';
	public const INSTALLDIR = __DIR__.'/packages/';

	/**
	 * @var array $packages A list of external dependencies to be installed when the plugin is activated
	 */
	protected static $packages = [
		'hexydec\\html\\htmldoc' => [
			'file' => 'https://github.com/hexydec/htmldoc/archive/refs/heads/master.zip',
			'autoload' => 'htmldoc-master/src/autoload.php'
		],
		'hexydec\\css\\cssdoc' => [
			'file' => 'https://github.com/hexydec/cssdoc/archive/refs/tags/0.5.1.zip',
			'autoload' => 'cssdoc-0.5.1/src/autoload.php'
		],
		'hexydec\\jslite\\jslite' => [
			'file' => 'https://github.com/hexydec/jslite/archive/refs/tags/0.5.1.zip',
			'autoload' => 'jslite-0.5.1/src/autoload.php'
		],
		'hexydec\\tokens\\tokenise' => [
			'file' => 'https://github.com/hexydec/tokenise/archive/refs/tags/0.4.1.zip',
			'autoload' => 'tokenise-0.4.1/src/autoload.php'
		]
	];

	public static function autoload() {
		\spl_autoload_register(function (string $class) : bool {
			foreach (self::$packages AS $key => $item) {
				if ($key === $class && \file_exists(self::INSTALLDIR.$item['autoload'])) {
					return require(self::INSTALLDIR.$item['autoload']);
				}
			}
			return false;
		});
	}
}
