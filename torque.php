<?php
namespace hexydec\torque;
/*
Plugin Name: Torque - Optimise the transport of your Website
Plugin URI:  https://github.com/hexydec/htmldoc-wordpress
Description: Take your website optimisation to the next level! Other minification plugins blindly find and replace patterns within your code to make it smaller, often using outdated 3rd-party libraries. <strong>Torque is a compiler</strong>, it parses your code to an internal representation, optimises it, and compiles it back to code. The result is better reliability, compression, and performance. Add in header management and other tools, your website will be noticably faster!
Version:     0.3.1
Requires PHP: 7.3
Author:      Hexydec
Author URI:  https://github.com/hexydec/
License:     GPL
License URI: https://github.com/hexydec/htmldoc/blob/master/LICENSE
*/

require(__DIR__.'/autoload.php');

// do actions
register_activation_hook(__FILE__, '\\hexydec\\torque\\installExternal::install');
add_action('admin_menu', function () {
	$obj = new \hexydec\torque\admin();
	$obj->menu();
	$obj->draw();
});
add_action('wp_loaded', function () {
	$obj = new \hexydec\torque\app();
	$obj->optimise();
});
register_uninstall_hook(__FILE__, '\\hexydec\\torque\\install::uninstall');
