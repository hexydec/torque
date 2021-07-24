<?php
namespace hexydec\torque;

class installExternal extends install {

	public static function install() {

		// reserve a temporary file
		if (($tmp = tempnam(sys_get_temp_dir(), 'hxd')) === false) {
			wp_die('Could not create temporary file');
		} else {

			// delete all directories
			$this->cleanupDirectories(__DIR__, ['/.git', '/templates']);

			// install external assets
			$zip = new \ZipArchive();
			foreach ($this->packages AS $key => $item) {
				$dir = __DIR__;

				// download the asset bundle and copy to temp
				if (!copy($item['file'], $tmp)) {
					wp_die('Plugin activation failed: Could not download file "'.$item['file'].'". Is URL FOpen enabled?');

				// open the zip file
				} elseif (!$zip->open($tmp) === true) {
					wp_die('Plugin activation failed: Could not open file "'.$item['file'].'"');

				// extract the files
				} elseif (!$zip->extractTo($dir)) {
					wp_die('Plugin activation failed: Could not extract file "'.$item['file'].'"');
				}
			}

			// install the config
			parent::install();
		}
	}
}
