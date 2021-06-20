<?php
\spl_autoload_register(function (string $class) : bool {
	$namespace = 'hexydec\\torque\\';
	$classes = [
		$namespace.'packages' => __DIR__.'/packages.php',
		$namespace.'config' => __DIR__.'/config.php',
		$namespace.'admin' => __DIR__.'/admin.php',
		$namespace.'app' => __DIR__.'/app.php',
		$namespace.'installExternal' => __DIR__.'/install-external.php',
		$namespace.'install' => __DIR__.'/install.php',
	];
	if (isset($classes[$class])) {
		return require($classes[$class]);
	}
	return false;
});

// autoload external packages
\hexydec\torque\packages::autoload();
