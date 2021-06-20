<?php
namespace hexydec\torque;

class install {

	public static function install() {

		// install the config options
		$obj = new config();
		$config = $obj->buildConfig();
		update_option(config::SLUG, $config, true);
	}

	/**
	 * uninstalls the plugin
	 *
	 * @return void
	 */
	public static function uninstall() : void {
		delete_option(config::SLUG);
	}
}
