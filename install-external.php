<?php
/**
 * When the plugin is not bundled with dependencies (and this file), this downloads the dependencies
 *
 * @package hexydec/torque
 */
namespace hexydec\torque;

class installExternal extends packages {

	/**
	 * Cleans up the packages directory, installs dependencies, and installs the default configuration
	 *
	 * @return void
	 */
	public function install() : void {

		// reserve a temporary file
		if (($tmp = \tempnam(\sys_get_temp_dir(), 'hxd')) === false) {
			\wp_die('Could not create temporary file');
		} else {
			$dir = self::INSTALLDIR;

			// delete all directories
			if (\is_dir($dir)) {
				$this->cleanupDirectories(self::INSTALLDIR);
			} else {
				\mkdir($dir, 0755);
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
				} else {
					$files = [];
					$len = \mb_strlen($item['extract']);
					for ($i = 0; $i < $zip->numFiles; $i++) {
						$file = $zip->getNameIndex($i);

						// only extract files from the specified folder
						if (($pos = \mb_strpos($file, $item['extract'])) === 0 && \mb_substr($file, -1) !== '/') {
							$source = 'zip://'.$tmp."#".$file;
							$target = $dir.$key.\mb_substr($file, $len - 1);
							$targetdir = \dirname($target);
							if (!\is_dir($targetdir)) {
								\mkdir($targetdir, 0755);
							}
							if (!\copy($source, $target)) {
								\wp_die('Plugin activation failed: Could not extract file "'.$file.'"');
							}
						}
					}
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
