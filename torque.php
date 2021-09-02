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
Version:		0.5.2
Requires PHP:	7.3
Author:			Hexydec
Author URI:		https://github.com/hexydec/
License:		GPL
License URI:	https://github.com/hexydec/htmldoc/blob/master/LICENSE
*/

require(__DIR__.'/autoload.php');

// activate the plugin
\register_activation_hook(__FILE__, function () {

	// from GitHub, download packages
	if (\class_exists('\\hexydec\\torque\\installExternal')) {
		$obj = new \hexydec\torque\installExternal();

	// for Wordpress, packages are included
	} else {
		$obj = new \hexydec\torque\install();
	}
	$obj->install();
});

// install the admin menu
\add_action('admin_menu', function () {
	$obj = new \hexydec\torque\admin();
	$obj->update();
	$obj->draw();
});

// load the app
\add_action('wp_loaded', function () {
	$obj = new \hexydec\torque\app();
	$obj->optimise();
});

// uninstall
\register_uninstall_hook(__FILE__, '\\hexydec\\torque\\install::uninstall');
