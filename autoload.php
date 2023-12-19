<?php
/**
 * Autoloads the application
 *
 * @package hexydec/torque
 */
\spl_autoload_register(function (string $class) : void {
	$namespace = 'hexydec\\torque\\';
	$classes = [
		$namespace.'packages' => __DIR__.'/packages.php',
		$namespace.'config' => __DIR__.'/config.php',
		$namespace.'admin' => __DIR__.'/admin.php',
		$namespace.'csp' => __DIR__.'/csp.php',
		$namespace.'assets' => __DIR__.'/assets.php',
		$namespace.'app' => __DIR__.'/app.php',
		$namespace.'installExternal' => __DIR__.'/install-external.php',
		$namespace.'install' => __DIR__.'/install.php',
		$namespace.'overview' => __DIR__.'/overview.php'
	];
	if (isset($classes[$class]) && \file_exists($classes[$class])) {
		require $classes[$class];
	}
});

// autoload external packages
\hexydec\torque\packages::autoload();
