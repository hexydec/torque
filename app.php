<?php
namespace hexydec\torque;

class app extends config {

	public function __construct() {
	}

	/**
	 * Renders Javascript that outputs the minification stats to the console
	 *
	 * @param array $options The stored configuration for this plugin
	 * @return array A configuration array for the HTMLdoc object
	 */
	protected function getHtmldocConfig(array $options) : array {
		$json = json_encode($options);

		// callback for minifying and caching
		$minify = function (string $code, array $minify, string $tag) use ($json) {

			// transient key
			$cache = empty($minify['cache']) ? null : self::SLUG.'-style-'.md5(self::VERSION.$json.$code);

			// not caching or there wasn't a cache
			if (!$cache || ($min = get_transient($cache)) === false) {

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
						set_transient($cache, $min, 604800); // 7 days
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

	public function optimise() {

		// are we going to minify the page?
		if (($options = \get_option(self::SLUG)) !== false && (!empty($options['admin']) || !\is_admin())) {

			// turn off default cache control header
			if ($options['maxage'] ?? false) {
				\session_cache_limiter('');
			}

			// set X-Content-Type-Options header
			if ($options['typeoptions'] ?? false) {
				header('X-Content-Type-Options: nosniff');
			}

			// set X-XSS-Protection header
			if ($options['xssprotection'] ?? false) {
				header('X-XSS-Protection: 1');
			}

			// set X-Iframe-Options header
			if ($options['embedding'] ?? false) {
				header('X-Iframe-Options: '.$options['embedding']);
			}

			// set X-Iframe-Options header
			if (($options['hsts'] ?? false) && \is_ssl()) {
				header('Strict-Transport-Security: max-age='.$options['hsts']);
			}

			// set CSP
			if (isset($options['csp']['setting']) && ($options['csp']['setting'] === 'enabled' || $options['csp']['setting'] == get_current_user_id())) {
				header('Content-Security-Policy: '.$this->getContentSecurityPolicy($options['csp']));
			}

			// HTTP/2.0 preload
			if (!empty($options['preload']) || !empty($options['preloadstyle'])) {

				// add combined stylesheet
				if ($options['preloadstyle'] && $options['combinestyle']) {
					$options['preload'][] = \str_replace('\\', '/', \mb_substr(__DIR__, \mb_strlen(ABSPATH)).'/build/'.\md5(implode(',', $options['combinestyle'])).'.css');
				}

				// set header
				header('Link: '.$this->getPreloadLinks($options['preload']));
			}

			// create output buffer
			\ob_start(function (string $html) use ($options) {

				// make sure the output is text/html, so we are not trying to minify javascript or something
				foreach (\headers_list() AS $item) {
					if (\mb_stripos($item, 'Content-Type:') === 0 && \mb_stripos($item, 'Content-Type: text/html') === false) {
						return false;
					}
				}

				// only load HTMLdoc if minifying or lazyloading
				if (\class_exists('\\hexydec\\html\\htmldoc') && (!empty($options['minifyhtml']) || !empty($options['lazyload']))) {

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
					http_response_code(304);
					return '';
				}

				// set content length header
				header('Content-length: '.strlen($html), true);

				// return HTML
				return $html;
			});
		}
	}

	protected function matchesEtag(string $html) {

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
		$etag = '"'.md5($html).'"';
		if (isset($headers[$key]) && \mb_stripos($headers[$match], $etag) !== false) {
			return true;
		} else {
			header('Etag: '.$etag);
			return false;
		}
	}

	/**
	 * Generates a content security policy
	 *
	 * @param array $config The CSP configuration array generated by admin.php
	 * @return string The CSP header value
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
				$csp[] = $item.' '.implode(' ', explode(',', str_replace(["\r", "\n"], ['', ','], $config[$key])));
			}
		}
		return $csp ? implode('; ', $csp) : null;
	}

	protected function getPreloadLinks(array $preload) {

		// types
		$as = [
			'.css' => 'style',
			'.woff' => 'font',
			'.woff2' => 'font'
		];

		// build links
		$base = \get_home_url().'/';
		$links = [];
		foreach ($preload AS $item) {
			$links[] = '<'.$base.$item.'>; rel="preload"; as="'.($as[strrchr($item, '.')] ?? 'image').'"';
		}

		// set header
		return implode(', ', $links);
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