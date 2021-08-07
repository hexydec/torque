<?php
/**
 * Installs the default configuration into the database and activates the plugin
 *
 * @package hexydec/torque
 */
namespace hexydec\torque;

class install {

	/**
	 * Installs the default configuration into the wordpress database
	 *
	 * @return void
	 */
	public function install() : void {

		// install the config options
		$obj = new config();
		$config = $obj->buildConfig();
		\update_option(config::SLUG, $config, true);
	}

	/**
	 * Uninstalls the plugin
	 *
	 * @return void
	 */
	public static function uninstall() : void {
		\delete_option(config::SLUG);
	}
}
