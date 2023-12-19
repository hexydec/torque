<?php
/**
 * A class for requesting pages and gathering the contained assets
 *
 * @package hexydec/torque
 */
namespace hexydec\torque;

class assets {

	/**
	 * @var array $pages Caches the result of any page requests
	 */
	protected static array $pages = [];

	/**
	 * @var array$assets Caches the result any assets that have been gathered from a page
	 */
	protected static array $assets = [];

	/**
	 * Retrieves the requested page content, and optionally sends the response headers back
	 *
	 * @param string $url The URL of the page to retrieve
	 * @param array &$headers Any headers to send with the page, will also be filled with the response headers
	 * @param array &$output A reference to the response headers, which will be filled as key => value
	 * @return string|bool The contents of the requested page or false if it could not be fetched
	 */
	protected static function getPage(string $url, array &$headers = []) {
		$key = \md5($url.\json_encode($headers));
		if (!isset(self::$pages[$key])) {
			self::$pages[$key] = false;

			// create context
			$context = \stream_context_create([
				'http' => [
					'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 ('.PHP_OS.') hexydec\\torque '.packages::VERSION, // use browser agent if set
					'header' => $headers,
					'verify_peer_name' => \in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) // enables self-signed SSL on localhost
				]
			]);

			// get the HTML and headers
			if (($fp = \fopen($url, 'rb', false, $context)) !== false && ($file = \stream_get_contents($fp)) !== false) {

				// retrieve and compile the headers
				$headers = [];
				$success = true;
				if (($meta = \stream_get_meta_data($fp)) !== false && isset($meta['wrapper_data'])) {
					foreach ($meta['wrapper_data'] AS $item) {
						if (\mb_strpos($item, ': ') !== false) {
							list($name, $value) = \explode(': ', $item, 2);
							$lower = \mb_strtolower($name);
							if (isset($headers[$lower])) {
								$headers[$lower] .= '; '.$value;
							} else {
								$headers[$lower] = $value;
							}
						} elseif (\mb_strpos($item, 'HTTP/') === 0) {
							$headers['status'] = \explode(' ', $item)[1];
							$success = $headers['status'] == 200;
						}
					}

					// cache the page
					if ($success) {
						self::$pages[$key] = [
							'page' => $file,
							'headers' => $headers
						];
					}
				}
			}
		}

		// copy the output and send back page
		$headers = self::$pages[$key]['headers'] ?? [];
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
						'selector' => 'link[rel=stylesheet][href!=""]',
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
				if (($temp = \mb_strstr($prefix, '?', true)) !== false) {
					$prefix = $temp;
				}
				$noscheme = \mb_strstr($url, '//'); // if the scheme is removed but not the hostname

				// extract each type
				$assets = [];
				$groups = [];
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
							} elseif (\mb_strpos($name, $noscheme) === 0) {
								$name = \mb_substr($name, \mb_strlen($noscheme));
							}

							// check if url is local
							if (\mb_strpos($name, '//') === false && !\in_array($name, $assets)) {

								// add to asset list
								$assets[] = $name;
								if (!isset($groups[$key])) {
									$groups[$key] = [];
								}
								$groups[$key][] = [
									'id' => $name,
									'group' => $key,
									'name' => $name
								];

								// extract assets from stylesheets
								if ($key === 'Stylesheets' && ($items = self::getStylesheetAssets($prefix.$name)) !== false) {
									foreach ($items AS $value) {
										if (!\in_array($value['id'], $assets)) {
											$assets[] = $value['id'];
											if (!isset($groups[$value['group']])) {
												$groups[$value['group']] = [];
											}
											$groups[$value['group']][] = $value;
										}
									}
								}
							}
						}
					}
				}
				foreach ($groups AS $item) {
					self::$assets[$url] = \array_merge(self::$assets[$url], $item);
				}
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
		$file = WP_CONTENT_DIR.\mb_substr($url, \mb_strlen(\content_url()));
		$assets = [];
		if (\file_exists($file) && ($css = \file_get_contents($file)) !== false) {
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
				\chdir(\dirname($file));
				$root = \get_home_path();
				$len = \mb_strlen($root);
				$webroot = \home_url();
				$weblen = \mb_strlen($webroot);
				foreach ($match AS $item) {
					if (\mb_strpos($item[1], '//'.$_SERVER['HTTP_HOST'].'/') !== false) {
						$path = \mb_substr($item[1], $weblen + 1);
					} elseif (\mb_strpos($item[1], '/') === 0) {
						$path = \trim($item[1], '/');
					} elseif (($path = \realpath($item[1])) !== false) {
						$path = \str_replace('\\', '/', \mb_substr($path, $len));
					}
					if ($path !== false) {
						$assets[] = [
							'id' => $path,
							'group' => $types[$item[2]] ?? null,
							'name' => $path
						];
					}
				}
			}
		}
		return $assets ? $assets : false;
	}

	/**
	 * Builds the requested CSS files into a single compressed file
	 *
	 * @param array $files An array of absolute file address to combine
	 * @param string $target The absolute file address of the target document
	 * @param array $minify Minification option array or null to not minify at all
	 * @return bool Whether the output file was created
	 */
	public static function buildCss(array $files, string $target, ?array $minify = null) : bool {
		$css = '';

		// get the CSS documents and rewrite the URL's
		$dir = \dirname(\dirname(\dirname(__DIR__))).'/'; // can't use get_home_path() here
		foreach ($files AS $item) {
			$url = $dir.$item;
			if (\file_exists($url) && ($file = \file_get_contents($url)) !== false) {

				// add before styles
				if (($styles = self::getInlineStyles($item)) !== null && $styles['type'] === 'before') {
					$css .= $styles['content'];
				}

				// extract and rework asset URLs
				$css .= \preg_replace_callback('/url\\([\'"]?+([^\\)"\':]++)[\'"]?\\)/i', function (array $match) use ($url) : string {
					\chdir(\dirname($url));
					$path = \realpath(\dirname($match[1])).'/'.\basename($match[1]);
					return 'url('.\str_replace('\\', '/', \substr($path, \strlen($_SERVER['DOCUMENT_ROOT']))).')';
				}, $file);

				// add after styles
				if (($styles['type'] ?? '') === 'after') {
					$css .= $styles['content'];
				}
			}
		}

		// write the file
		if ($css) {

			// minify
			if ($minify !== null) {
				$obj = new \hexydec\css\cssdoc();
				if ($obj->load($css)) {
					$obj->minify($minify);
					$css = $obj->compile();
				}
			}

			// create the directory if it doesn't exist
			$dir = \dirname($target);
			if (!\is_dir($dir)) {
				\mkdir($dir, 0755);
			}

			// write the file
			if (\file_put_contents($target, $css) !== false) {
				return true;
			}
		}
		return false;
	}

	protected static function getStyleAssets() {
		static $style = null;
		if ($style === null) {
			$doc = new \hexydec\html\htmldoc();
			$url = \home_url().'/?notorque';
			if (($html = self::getPage($url)) !== false && $doc->load($html)) {
				$style = [];
				foreach ($doc->find('link[rel=stylesheet][id],style[id]') AS $item) {
					$style[$item->attr('id')] = [
						'href' => $item->attr('href'),
						'content' => $item->html()
					];
				}
			}
		}
		return $style;
	}

	protected static function getInlineStyles(string $url) : array|null {
		if (($style = self::getStyleAssets()) !== null) {
			$keys = \array_flip(\array_keys($style));
			foreach ($style AS $key => $item) {
				$inline = \substr($key, 0, -3).'inline-css';
				if (\mb_strpos($item['href'] ?? '', $url) !== false && isset($style[$inline])) {
					return [
						'content' => \mb_substr($style[$inline]['content'], \mb_strpos($style[$inline]['content'], '>') + 1, -9),
						'type' => $keys[$key] > $keys[$inline] ? 'before' : 'after'
					];
				}
			}
		}
		return null;
	}

	/**
	 * Builds the requested Javascript files into a single compressed file
	 *
	 * @param array $files An array of absolute file address to combine
	 * @param string $target The absolute file address of the target document
	 * @param array $minify Minification option array or null to not minify at all
	 * @return bool Whether the output file was created
	 */
	public static function buildJavascript(array $files, string $target, ?array $minify = null) : bool {
		$js = '';

		// minify each file
		$dir = \dirname(\dirname(\dirname(__DIR__))).'/'; // can't use get_home_path() here
		foreach ($files AS $item) {
			if (\file_exists($dir.$item) && ($file = \file_get_contents($dir.$item)) !== false) {

				// add before script
				if (($script = self::getExtraScript($item)) !== null && $script['type'] === 'before') {
					$js .= ($js ? "\n\n" : '').$script['content'];
				}

				// add script
				$js .= ($js ? "\n\n" : '').$file;

				// add after script
				if (($script['type'] ?? '') === 'after') {
					$js .= ($js ? "\n\n" : '').$script['content'];
				}
			}
		}

		// write the file
		if ($js) {

			// minify
			if ($minify !== null) {
				$obj = new \hexydec\jslite\jslite();
				if ($obj->load($js)) {
					$obj->minify($minify);
					$js = $obj->compile();
				}
			}

			// create directory if it doesn't exist
			$dir = \dirname($target);
			if (!\is_dir($dir)) {
				\mkdir($dir, 0755);
			}

			// write the file
			if (\file_put_contents($target, $js) !== false) {
				return true;
			}
		}
		return false;
	}

	protected static function getScriptAssets() {
		static $scripts = null;
		if ($scripts === null) {
			$doc = new \hexydec\html\htmldoc();
			$url = \home_url().'/?notorque';
			if (($html = self::getPage($url)) !== false && $doc->load($html)) {
				$scripts = [];
				foreach ($doc->find('script[id]') AS $item) {
					$scripts[$item->attr('id')] = [
						'src' => $item->attr('src'),
						'content' => $item->html()
					];
				}
			}
		}
		return $scripts;
	}

	protected static function getExtraScript(string $url) : array|null {
		if (($scripts = self::getScriptAssets()) !== null) {
			$keys = \array_flip(\array_keys($scripts));
			foreach ($scripts AS $key => $item) {
				if (\mb_strpos($item['src'] ?? '', $url) !== false && isset($scripts[$key.'-extra'])) {
					return [
						'content' => \mb_substr($scripts[$key.'-extra']['content'], \mb_strpos($scripts[$key.'-extra']['content'], '>') + 1, -9),
						'type' => $keys[$key] > $keys[$key.'-extra'] ? 'before' : 'after'
					];
				}
			}
		}
		return null;
	}

	/**
	 * Rebuilds the configured combined assets
	 *
	 * @return bool Whether the assets were regenerated
	 */
	public static function rebuildAssets() : bool {
		if (($options = \get_option(packages::SLUG)) !== false) {
			$success = true;
			$dir = \dirname(\dirname(\dirname(__DIR__))).'/'; // can't use get_home_path() here

			// rebuild CSS
			if (!empty($options['combinestyle']) && \is_array($options['combinestyle'])) {
				$files = [];
				foreach ($options['combinestyle'] AS $item) {
					$files[] = $dir.$item;
				}
				$target = __DIR__.'/build/'.\md5(\implode(',', $options['combinestyle'])).'.css';
				if (!self::buildCss($files, $target, $options['minifystyle'] ? ($options['style'] ?? []) : null)) {
					$success = false;
				}
			}

			// rebuild javascript
			if (!empty($options['combinescript']) && \is_array($options['combinescript'])) {
				$files = [];
				foreach ($options['combinescript'] AS $item) {
					$files[] = $dir.$item;
				}
				$target =  __DIR__.'/build/'.\md5(\implode(',', $options['combinescript'])).'.js';
				if (!self::buildJavascript($files, $target, $options['minifyscript'] ? ($options['script'] ?? []) : null)) {
					$success = false;
				}
			}
			return $success;
		}
		return false;
	}
}
