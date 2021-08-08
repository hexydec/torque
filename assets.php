<?php
/**
 * A class for requesting pages and gathering the contained assets
 *
 * @package hexydec/torque
 */
namespace hexydec\torque;

class assets {

	/**
	 * @var $pages Caches the result of any page requests
	 */
	protected static $pages = [];

	/**
	 * @var $assets Caches the result any assets that have been gathered from a page
	 */
	protected static $assets = [];

	/**
	 * Retrieves the requested page content, and optionally sends the response headers back
	 *
	 * @param string $url The URL of the page to retrieve
	 * @param array $headers Any headers to send with the page
	 * @param array &$output A reference to the response headers, which will be filled as key => value
	 * @return string|bool The contents of the requested page or false if it could not be fetched
	 */
	protected static function getPage(string $url, array $headers = [], array &$output = []) {
		$key = \md5($url.\json_encode($headers));
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

				// retrieve and compile the headers
				$outputHeaders = [];
				$success = true;
				if (($meta = \stream_get_meta_data($fp)) !== false && isset($meta['wrapper_data'])) {
					foreach ($meta['wrapper_data'] AS $item) {
						if (\mb_strpos($item, ': ') !== false) {
							list($name, $value) = \explode(': ', $item, 2);
							$outputHeaders[\mb_strtolower($name)] = $value;
						} elseif (\mb_strpos($item, 'HTTP/') === 0) {
							$outputHeaders['status'] = \explode(' ', $item)[1];
							$success = $outputHeaders['status'] == 200;
						}
					}

					// cache the page
					if ($success) {
						self::$pages[$key] = [
							'page' => $file,
							'headers' => $outputHeaders
						];
					}
				}
			}
		}

		// copy the output and send back page
		$output = self::$pages[$key]['headers'] ?? [];
		return self::$pages[$key]['page'] ?? false;
	}

	/**
	 * Collects and groups a list of linked assets from the requested page
	 *
	 * @param string $url The URL of the page to retrieve
	 * @return array|bool An array of assets, each an array with 'id', 'group', and 'name', or false if the page could not be retrieved
	 */
	public static function getPageAssets(string $url) {

		// only retrieve if not cached
		if (!isset(self::$assets[$url]) && ($html = self::getPage($url)) !== false) {
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

				// remove any query string
				$prefix = $url;
				if (($temp = \strstr($prefix, '?', true)) !== false) {
					$prefix = $temp;
				}

				// extract each type
				$assets = [];
				foreach ($extract AS $key => $item) {
					if (($nodes = $obj->find($item['selector'])) !== false) {

						// lopp through all the found nodes
						foreach ($nodes AS $node) {

							// extract the attribute value
							$name = $node->attr($item['attr']);

							// remove any query string
							if (($temp = \strstr($name, '?', true)) !== false) {
								$name = $temp;
							}

							// normalise URL
							if (\mb_strpos($name, $prefix) === 0) {
								$name = \mb_substr($name, \mb_strlen($prefix));
							}

							// check if url is local
							if (\mb_strpos($name, '//') === false && !\in_array($name, $assets)) {

								// add to asset list
								$assets[] = $name;
								self::$assets[$url][] = [
									'id' => $name,
									'group' => $key,
									'name' => $name
								];

								// extract assets from stylesheets
								if ($key === 'Stylesheets' && ($items = self::getStylesheetAssets($prefix.$name)) !== false) {
									foreach ($items AS $value) {
										if (!\in_array($value['id'], $assets)) {
											$assets[] = $value['id'];
											self::$assets[$url][] = $value;
										}
									}
								}
							}
						}
					}
				}
				$keys = \array_column(self::$assets[$url], 'group');
				\array_multisort($keys, SORT_DESC, self::$assets[$url]);
			}
		}
		return self::$assets[$url] ?? false;
	}

	/**
	 * Collects and groups a list of linked assets from the requested stylesheet
	 *
	 * @param string $url The URL of the stylesheet to retrieve
	 * @return array|bool An array of assets, each an array with 'id', 'group', and 'name', or false if the stylesheet could not be retrieved
	 */
	protected static function getStylesheetAssets(string $url) {
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

			// extract the URLs from the CSS
			$re = '/url\\(\\s*["\']?([^\\)]+\\.('.\implode('|', \array_keys($types)).'))(?:\\?[^\\s\\)])?["\']?\\s*\\)/i';
			if (\preg_match_all($re, $css, $match, PREG_SET_ORDER)) {

				// work out the path relative to the webroot
				\chdir($_SERVER['DOCUMENT_ROOT'].\parse_url(\dirname($url), PHP_URL_PATH));
				$len = \strlen(\get_home_path());
				foreach ($match AS $item) {
					if (($path = \realpath($item[1])) !== false) {
						$path = \str_replace('\\', '/', \substr($path, $len));
						$assets[] = [
							'id' => $path,
							'group' => $types[$item[2]],
							'name' => $path
						];
					}
				}
			}
		}
		return $assets ? $assets : false;
	}
}
