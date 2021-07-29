<?php
namespace hexydec\torque;

class installExternal extends packages {

	public function install() {

		// reserve a temporary file
		if (($tmp = \tempnam(\sys_get_temp_dir(), 'hxd')) === false) {
			wp_die('Could not create temporary file');
		} else {

			// delete all directories
			if (\is_dir(self::INSTALLDIR)) {
				$this->cleanupDirectories(self::INSTALLDIR);
			} else {
				\mkdir(self::INSTALLDIR, 0755);
			}

			// install external assets
			$zip = new \ZipArchive();
			foreach (self::$packages AS $key => $item) {

				// download the asset bundle and copy to temp
				if (!\copy($item['file'], $tmp)) {
					\wp_die('Plugin activation failed: Could not download file "'.$item['file'].'". Is URL FOpen enabled?');

				// open the zip file
				} elseif (!$zip->open($tmp) === true) {
					\wp_die('Plugin activation failed: Could not open file "'.$item['file'].'"');

				// extract the files
				} elseif (!$zip->extractTo(self::INSTALLDIR)) {
					\wp_die('Plugin activation failed: Could not extract file "'.$item['file'].'"');
				}
			}

			// install the config
			$obj = new install();
			$obj->install();
		}
	}

	/**
	 * Removes an existing directories from the plugin folder
	 *
	 * @param string $dir The absolute address of the plugin directory
	 * @return bool Whether any files or directories were removed
	 */
	protected function cleanupDirectories(string $dir) : bool {
		$dir = \str_replace('\\', '/', $dir);

		// look through directories
		$deleted = false;
		$it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
		$len = \strlen($dir) + 1;
		foreach ($files AS $item) {
			$path = \str_replace('\\', '/', $item->getRealPath());
			if ($item->isDir()) {
				\rmdir($path);
				$deleted = true;
			} elseif (\strlen($path) > $len && \strpos($path, '/', $len) !== false) {
				\unlink($path);
				$deleted = true;
			}
		}
		return $deleted;
	}
}
