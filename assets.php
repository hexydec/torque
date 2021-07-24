<?php
namespace hexydec\torque;

class assets {

	protected static $pages = [];
	protected static $assets = [];

	protected static function getPage(string $url, array $headers = [], array &$output = []) {
		$key = md5($url.json_encode($headers));
		if (!isset(self::$pages[$key])) {
			self::$pages[$key] = false;

			// create context
			$context = \stream_context_create([
				'http' => [
					'user_agent' => 'Mozilla/5.0 ('.PHP_OS.') hexydec\\torque '.packages::VERSION,
					'header' => $headers
				]
			]);

			// get the HTML and headers
			if (($fp = \fopen($url, 'rb', false, $context)) !== false && ($file = \stream_get_contents($fp)) !== false) {

				// retrieve headers
				$outputHeaders = [];
				$success = true; // default is for cache which won't have stream meta data
				if (($meta = \stream_get_meta_data($fp)) !== false && isset($meta['wrapper_data'])) {
					foreach ($meta['wrapper_data'] AS $item) {
						if (\mb_strpos($item, ': ') !== false) {
							list($key, $value) = \explode(': ', $item, 2);
							$outputHeaders[\mb_strtolower($key)] = $value;
						} elseif (\mb_strpos($item, 'HTTP/') === 0) {
							$outputHeaders['status'] = \explode(' ', $item)[1];
							$success = $outputHeaders['status'] == 200;
						}
					}

					if ($success) {
						self::$pages[$key] = [
							'page' => $file,
							'headers' => $outputHeaders
						];
					}
				}
			}
		}
		$output = self::$pages[$key]['headers'] ?? [];
		return self::$pages[$key]['page'] ?? false;
	}

	public static function getPageAssets(string $url) {
		$headers = [];
		if (!isset(self::$assets[$url]) && ($html = self::getPage($url.'?notorque', $headers)) !== false) {
			self::$assets[$url] = [];

			// parse the page
			$obj = new \hexydec\html\htmldoc();
			if ($obj->load($html)) {

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
