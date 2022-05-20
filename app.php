<?php
/**
 * Implements the Torque plugin functionality into your Wordpress website
 *
 * @package hexydec/torque
 */
namespace hexydec\torque;

class app extends config {

	/**
	 * Renders Javascript that outputs the minification stats to the console
	 *
	 * @param array $options The stored configuration for this plugin
	 * @return array A configuration array for the HTMLdoc object
	 */
	protected function getHtmldocConfig(array $options) : array {
		$json = \json_encode($options);

		// callback for minifying and caching
		$minify = function (string $code, array $minify, string $tag) use ($json) : string {

			// transient key
			$cache = empty($minify['cache']) ? null : self::SLUG.'-style-'.\md5(self::VERSION.$json.$code);

			// not caching or there wasn't a cache
			if (!$cache || ($min = \get_transient($cache)) === false) {

				// get the minifier object
				switch ($tag) {
					case 'style':
						$obj = new \hexydec\css\cssdoc();
						break;
					case  'script':
						$obj = new \hexydec\jslite\jslite();
						break;
					default:
						return false;
				}

				// parse, minify, compile
				if ($obj->load($code)) {
					$obj->minify($minify);
					$min = $obj->compile();

					// cache the output
					if ($cache) {
						\set_transient($cache, $min, 604800); // 7 days
					}
				} else {
					return false;
				}
			}
			return $min;
		};

		// return config
		return [
			'custom' => [
				'style' => [
					'minifier' => empty($options['minifystyle']) ? null : $minify
				],
				'script' => [
					'minifier' => empty($options['minifyscript']) ? null : $minify
				]
			]
		];
	}

	/**
	 * Optimises the output webpage by minifying the code, and setting headers
	 *
	 * @return void
	 */
	public function optimise() : void {

		// are we going to minify the page?
		if (!isset($_GET['notorque']) && ($options = \get_option(self::SLUG)) !== false && (!empty($options['admin']) || !\is_admin())) {

			// turn off default cache control header
			if ($options['maxage'] ?? false) {
				\session_cache_limiter('');
			}

			// set X-Content-Type-Options header
			if ($options['typeoptions'] ?? false) {
				\header('X-Content-Type-Options: nosniff');
			}

			// set X-XSS-Protection header
			if ($options['xssprotection'] ?? false) {
				\header('X-XSS-Protection: 1; mode=block');
			}

			// set X-Iframe-Options header
			if ($options['embedding'] ?? false) {
				\header('X-Iframe-Options: '.$options['embedding']);
			}

			// set X-Iframe-Options header
			if (($options['hsts'] ?? false) && \is_ssl()) {
				\header('Strict-Transport-Security: max-age='.$options['hsts']);
			}

			// set CSP
			if (isset($options['csp']['setting']) && \in_array($options['csp']['setting'], ['enabled', \strval(\get_current_user_id())])) {
				\header('Content-Security-Policy: '.$this->getContentSecurityPolicy($options['csp']));
			}

			// HTTP/2.0 preload
			$key = 'torque-preload';
			if (empty($_COOKIE[$key]) && (!empty($options['preload']) || !empty($options['preloadstyle']))) {

				// add combined stylesheet
				if ($options['preloadstyle'] && $options['combinestyle']) {
					$file = __DIR__.'/build/'.\md5(\implode(',', $options['combinestyle'])).'.css';
					$root = \dirname(\dirname(\dirname(__DIR__)));
					$options['preload'][] = \str_replace('\\', '/', \mb_substr($file, \mb_strlen($root)).'?'.\filemtime($file));
				}

				// set header
				\header('Link: '.$this->getPreloadLinks($options['preload']));
				\setcookie($key, '1', [
					'expires' => \time() + 31536000,
					'path' => '/',
					'domain' => $_SERVER['HTTP_HOST'],
					'secure' => true,
					'httponly' => true,
					'samesite' => 'Lax'
				]);
			}

			// check some options are set that require htmldoc
			$htmldoc = false;
			foreach (['minifyhtml', 'lazyload', 'combinestyle', 'combinescript'] AS $item) {
				if (!empty($options[$item])) {
					$htmldoc = true;
					break;
				}
			}
			if ($htmldoc && \class_exists('\\hexydec\\html\\htmldoc')) {

				// create output buffer
				\ob_start(function (string $html) use ($options) {

					// make sure the output is text/html, so we are not trying to minify javascript or something
					foreach (\headers_list() AS $item) {
						if (\mb_stripos($item, 'Content-Type:') === 0 && \mb_stripos($item, 'Content-Type: text/html') === false) {
							return false;
						}
					}

					// collect timing for stats
					$timing = ['Initialise' => \microtime(true)];

					// get the config and create the object
					$config = $this->getHtmldocConfig($options);
					$doc = new \hexydec\html\htmldoc($config);

					// load from a variable
					$timing['Parse'] = \microtime(true);
					if ($doc->load($html, \mb_internal_encoding())) {

						// add lazyload attributes
						if (!empty($options['lazyload'])) {
							$doc->find('img,iframe')->attr('loading', 'lazy');
						}

						// combine style
						if (!empty($options['combinestyle'])) {
							foreach ($options['combinestyle'] AS $item) {
								$len = \strlen($html);
								$doc->remove('link[rel=stylesheet][href*="'.$item.'"]');
							}
							$file = \str_replace('\\', '/', __DIR__).'/build/'.\md5(\implode(',', $options['combinestyle'])).'.css';
							$url = \mb_substr($file, \mb_strlen($_SERVER['DOCUMENT_ROOT'])).'?'.\filemtime($file);
							$doc->find('head')->append('<link rel="stylesheet" href="'.\esc_html($url).'" />');
						}

						// combine style
						if (!empty($options['combinescript'])) {
							global $wp_scripts;
							$js = $wp_scripts->registered;

							// remove scripts we are combining
							$before = [];
							$after = [];
							$anchor = null;
							foreach ($options['combinescript'] AS $item) {
								$script = $doc->find('script[src*="'.$item.'"]');
								if (($id = $script->attr("id")) !== null) {
									$extra = \substr($id, 0, -3);
									if (!empty($js[$extra]->extra['before']) || !empty($js[$extra]->extra['data'])) {
										$before[] = $id.'-extra';
									} elseif (!empty($js[$extra]->extra['after'])) {
										$after[] = $id.'-extra';
									}
								}
								if ($anchor) {
									$script->remove();
								} else {
									$anchor = $script;
								}
							}
							$scripts = '';

							// move the before inline scripts to the bottom
							if ($before) {
								$inline = $doc->find('script[id='.\implode('],script[id=', $before).']');
								$scripts .= $inline->html();
								$inline->remove();
							}

							// append the combined file to the body tag
							$file = \str_replace('\\', '/', __DIR__).'/build/'.\md5(\implode(',', $options['combinescript'])).'.js';
							$url = \mb_substr($file, \mb_strlen($_SERVER['DOCUMENT_ROOT'])).'?'.\filemtime($file);
							$scripts .= '<script src="'.\esc_html($url).'"></script>';

							// move the after inline scripts to the bottom
							if ($after) {
								$inline = $doc->find('script[id='.\implode('],script[id=', $after).']');
								$scripts .= $inline->html();
								$inline->remove();
							}

							// append them to the anchor point
							$anchor->after($scripts);
							$anchor->remove();
						}

						// build the minification options
						if (!empty($options['minifyhtml'])) {
							$timing['Minify'] = \microtime(true);
							$doc->minify($options);
						}

						// compile back to HTML
						$timing['Compile'] = \microtime(true);
						$min = $doc->html();

						// show stats in the console
						if (!empty($options['stats']) && !empty($options['minifyhtml'])) {
							$timing['Complete'] = \microtime(true);
							$min .= $this->drawStats($html, $min, $timing);
						}
						$html = $min;
					}

					// cache control
					if (($options['maxage'] ?? null) !== null) {
						if (!empty($_POST) || isset($_COOKIE[\session_name()]) || \is_user_logged_in()) {
							\header('Cache-control: private,max-age=0');
							$options['maxage'] = 0;
						} else {
							\header('Cache-Control: max-age='.$options['maxage'].($options['smaxage'] === null ? '' : ',s-maxage='.$options['smaxage']), true);
						}
						\header('Expires: '.\gmdate('D, d M Y H:i:s \G\M\T', \time() + $options['maxage']), true);
					}

					// check etags
					if (($options['etags'] ?? null) !== null && empty($_POST) && $this->matchesEtag($html)) {
						\http_response_code(304);
						return '';
					}

					// set content length header
					\header('Content-length: '.\strlen($html), true);

					// return HTML
					return $html;
				});
			}
		}
	}

	/**
	 * Checks the input HTML to see if it matches the hash sent by the browser as an Etag
	 *
	 * @param string $html The HTML to be checked against the Etag
	 * @return bool Whether the input HTML matches the Etag sent by the browser
	 */
	protected function matchesEtag(string $html) : bool {

		// get etag id
		$key = 'If-None-Match';
		if (!\function_exists('apache_request_headers')) {
			$headers = $_ENV;
			if (isset($headers['HTTP_IF_NONE_MATCH'])) {
				$key = 'HTTP_IF_NONE_MATCH';
			}
		} else {
			$headers = \apache_request_headers();
		}

		// check against hash of input HTML
		$etag = '"'.\md5($html).'"';
		if (isset($headers[$key]) && \mb_stripos($headers[$key], $etag) !== false) {
			return true;
		} else {
			\header('Etag: '.$etag);
			return false;
		}
	}

	/**
	 * Generates a content security policy
	 *
	 * @param array $config The CSP configuration array generated by admin.php
	 * @return string The CSP header value, or null if no policy has been configured
	 */
	protected function getContentSecurityPolicy(array $config) : ?string {
		$fields = [
			'default' => 'default',
			'style' => 'style-src',
			'script' => 'script-src',
			'image' => 'image-src',
			'font' => 'font-src',
			'media' => 'media-src',
			'object' => 'object-src',
			'frame' => 'frame-src',
			'connect' => 'connect-src',
		];
		$csp = [];
		foreach ($fields AS $key => $item) {
			if (!empty($config[$key])) {
				$csp[] = $item.' '.\implode(' ', \explode(',', \str_replace(["\r", "\n"], ['', ','], $config[$key])));
			}
		}
		return $csp ? \implode('; ', $csp) : null;
	}

	/**
	 * Generates a Link header containing the assets the should be preloaded
	 *
	 * @param array $preload An array specifying the assets to be preloaded
	 * @return string The value that should be set in the Link header
	 */
	protected function getPreloadLinks(array $preload) : string {

		// types
		$as = [
			'.css' => 'style',
			'.woff' => 'font',
			'.woff2' => 'font'
		];

		// build links
		$base = \parse_url(\get_home_url().'/', PHP_URL_PATH);
		$links = [];
		foreach ($preload AS $item) {
			$ext = \strrchr($item, '.');
			if (($tmp = \strstr($ext, '?', true)) !== false) {
				$ext = $tmp;
			}
			$type = isset($as[$ext]) ? $as[$ext] : 'image';
			$links[] = '<'.$base.$item.'>; rel="preload"; as="'.$type.'"'.($type === 'font' ? '; crossorigin' : '');
		}

		// set header
		return \implode(', ', $links);
	}

	/**
	 * Renders Javascript that outputs the minification stats to the console
	 *
	 * @param string $input The input HTML
	 * @param string $output The output HTML
	 * @param array $timings An array of timings for each stage of the process
	 * @return string An HTML script tag containing Javascript to log the stats to the console
	 */
	protected function drawStats(string $input, string $output, array $timing) : string {

		// calculate timings
		$table = [
			'Timing' => [],
			'% Time' => []
		];
		$total = $timing['Complete'] - $timing['Initialise'];
		$last = null;
		foreach ($timing AS $key => $item) {
			if ($last) {
				$table['Timing'][$last] = \round($item - $timing[$last], 8);
				$table['% Time'][$last] = \round((100 / $total) * $table['Timing'][$last], 2).'%';
			}
			$last = $key;
		}
		$table['Timing']['Total'] = \round($total, 8);
		$table['% Time']['Total'] = '100%';

		// calculate sizes
		$sizes = [
			'Compression' => [
				'Input' => \strlen($input),
				'Output' => \strlen($output)
			],
			'Compression (Gzip)' => [
				'Input' => \strlen(\gzencode($input)),
				'Output' => \strlen(\gzencode($output))
			]
		];
		$sizes['Compression']['Diff'] = $sizes['Compression']['Input'] - $sizes['Compression']['Output'];
		$sizes['Compression']['Ratio %'] = \round(100 - ((100 / $sizes['Compression']['Input']) * $sizes['Compression']['Output']), 2).'%';
		$sizes['Compression (Gzip)']['Diff'] = $sizes['Compression (Gzip)']['Input'] - $sizes['Compression (Gzip)']['Output'];
		$sizes['Compression (Gzip)']['Ratio %'] = \round(100 - ((100 / $sizes['Compression (Gzip)']['Input']) * $sizes['Compression (Gzip)']['Output']), 2).'%';

		// render javascript
		$console = [
			'console.groupCollapsed("'.self::SLUG.' Stats");',
			'console.table('.\json_encode($table).');',
			'console.table('.\json_encode($sizes).');',
			'console.groupEnd()'
		];
		return '<script>'.\implode('', $console).'</script>';
	}
}
