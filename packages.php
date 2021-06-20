<?php
namespace hexydec\torque;

class packages {

	/**
	 * @var string $slug The slug for the configuration page
	 */
	public const SLUG = 'torque';

	/**
	 * @var string $slug The slug for the configuration page
	 */
	public const VERSION = '0.3.1';

	/**
	 * @var array $packages A list of external dependencies to be installed when the plugin is activated
	 */
	protected static $packages = [
		'hexydec\\html\\htmldoc' => [
			'file' => 'https://github.com/hexydec/htmldoc/archive/refs/tags/v1.2.5.zip',
			'dir' => __DIR__.'/htmldoc-1.2.5/',
			'autoload' => 'src/autoload.php'
		],
		'hexydec\\css\\cssdoc' => [
			'file' => 'https://github.com/hexydec/cssdoc/archive/refs/tags/0.5.1.zip',
			'dir' => __DIR__.'/cssdoc-0.5.1/',
			'autoload' => 'src/autoload.php'
		],
		'hexydec\\jslite\\jslite' => [
			'file' => 'https://github.com/hexydec/jslite/archive/refs/tags/0.5.1.zip',
			'dir' => __DIR__.'/jslite-0.5.1/',
			'autoload' => 'src/autoload.php'
		],
		'hexydec\\tokens\\tokenise' => [
			'file' => 'https://github.com/hexydec/tokenise/archive/refs/tags/0.4.1.zip',
			'dir' => __DIR__.'/tokenise-0.4.1/',
			'autoload' => 'src/autoload.php'
		]
	];

	public static function autoload() {
		spl_autoload_register(function (string $class) : bool {
			foreach (self::$packages AS $key => $item) {
				if ($key === $class && \file_exists($item['dir'].$item['autoload'])) {
					return require($item['dir'].$item['autoload']);
				}
			}
			return false;
		});
	}
}
