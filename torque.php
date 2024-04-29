<?php
/**
 * Initialises the Torque Wordpress plugin
 *
 * @package hexydec/torque
 */
namespace hexydec\torque;
/*
Plugin Name:	Torque - Optimise the transport of your Website
Plugin URI:		https://github.com/hexydec/torque
Description:	Make your Wordpress website noticably faster by optimising how it is delivered. Analyse your website's performance and security, minify and combine your assets, and configure an array of performance and security settings quickly and easily with this comprehensive plugin. Achieves the best compression of any minification plugin.
Version:		0.7.5
Requires PHP:	8.1
Author:			Hexydec
Author URI:		https://github.com/hexydec/
License:		GPL
License URI:	https://github.com/hexydec/htmldoc/blob/master/LICENSE
*/

require(__DIR__.'/autoload.php');

// activate the plugin
\register_activation_hook(__FILE__, function () : void {

	// from GitHub, download packages
	if (\class_exists('\\hexydec\\torque\\installExternal')) {
		$obj = new installExternal();

	// for Wordpress, packages are included
	} else {
		$obj = new install();
	}
	$obj->install();
});

// install the admin menu
\add_action('admin_menu', function () : void {
	$obj = new admin();
	$obj->update();
	$obj->draw();
});

// load the app
\add_action('wp_loaded', function () : void {
	$obj = new app();
	$obj->optimise();
});

// uninstall
\register_uninstall_hook(__FILE__, '\\hexydec\\torque\\install::uninstall');

// rebuild files when a plugin is updated
\add_action('upgrader_process_complete', function () : void {
	assets::rebuildAssets();
});

// add rebuild command
if (\class_exists('WP_CLI')) {
	\WP_CLI::add_command('torque rebuild', function () : bool {
		return \hexydec\torque\assets::rebuildAssets();
	}, [
		'shortdesc' => 'Rebuild the configured combined CSS and Javascript files'
	]);
}
