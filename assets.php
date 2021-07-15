<?php
namespace hexydec\torque;

class assets {

	protected static $assets = [];

	public static function getPageAssets(string $url) {
		if (!isset(self::$assets[$url])) {
			self::$assets[$url] = [];

			// parse the page
			$obj = new \hexydec\html\htmldoc();
			if ($obj->open($url.'?notorque')) {

				// define what we are going to extract
				$extract = [
					'Stylesheets' => [
						'selector' => 'link[rel=stylesheet]',
						'attr' => 'href'
					],
					'Scripts' => [
						'selector' => 'script[src]',
						'attr' => 'src'
					],
					'Images' => [
						'selector' => 'img',
						'attr' => 'src'
					]
				];

				// extract each type
				foreach ($extract AS $key => $item) {
					if (($nodes = $obj->find($item['selector'])) !== false) {

						// lopp through all the found nodes
						foreach ($nodes AS $node) {

							// extract the attribute value
							$name = \strstr($node->attr($item['attr']), '?', true);

							// normalise URL
							if (\mb_strpos($name, $url) === 0) {
								$name = \mb_substr($name, \mb_strlen($url));
							}

							// add to asset list
							self::$assets[$url][] = [
								'id' => $name,
								'group' => $key,
								'name' => $name
							];

							// extract assets from stylesheets
							if (($items = self::getStylesheetAssets($url.$name)) !== null) {
								self::$assets[$url] = array_merge(self::$assets[$url], $items);
							}
						}
					}
				}
			}
		}
		return self::$assets[$url];
	}

	protected static function getStylesheetAssets(string $url) : ?array {
		$assets = [];
		if (($css = \file_get_contents($url)) !== false) {
			$types = [
				'svg' => 'Images',
				'gif' => 'Images',
				'jpg' => 'Images',
				'jpeg' => 'Images',
				'png' => 'Images',
				'webp' => 'Images',
				'woff' => 'Fonts',
				'woff2' => 'Fonts',
				'eot' => 'Fonts',
				'ttf' => 'Fonts'
			];
			$re = '/url\\(\\s*(.+\\.('.\implode('|', \array_keys($types)).'))(?:\\?[^\\s\\)])?\\s*\\)/i';
			if (\preg_match_all($re, $css, $match, PREG_SET_ORDER)) {
				\chdir($_SERVER['DOCUMENT_ROOT'].\parse_url(\dirname($url), PHP_URL_PATH));
				$len = \strlen(ABSPATH);
				foreach ($match AS $item) {
					$path = \str_replace('\\', '/', \substr(\realpath($item[1]), $len));
					$assets[] = [
						'id' => $path,
						'group' => $types[$item[2]],
						'name' => $path
					];
				}
			}
		}
		return $assets ? $assets : null;
	}
}
