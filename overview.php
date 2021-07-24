<?php
namespace hexydec\torque;

class overview extends assets {

	protected $config = [
		'headers' => [
			'server' => 'Server',
			'x-powered-by' => 'Environment',
			'encoding' => 'Compression'
		]
	];

	public function __construct() {
		$this->config = [
			[
				'title' => 'Environment',
				'params' => [
					[
						'title' => 'Operating System',
						'badge' => PHP_OS
					],
					[
						'title' => 'Server',
						'badge' => function (array $data) {
							return $data['server'];
						}
					],
					[
						'title' => 'PHP Version',
						'badge' => phpversion()
					]
				]
			],
			[
				'title' => 'Page',
				'params' => [
					[
						'title' => 'MIME Type',
						'badge' => function (array $data) {
							return $data['content-type'];
						}
					],
					[
						'title' => 'HTML Size (Uncompressed)',
						'badge' => function (array $data, bool &$status = null) {
							$status = $data['uncompressed'] < 100000;
							return \number_format($data['uncompressed']).' bytes';
						},
						'html' => '<p>Reducing the overall size of your HTML will ensure the browser doesn\'t have to work too hard to render your page onto the user\'s screen</p>'
					],
					[
						'title' => 'HTML Size (Compressed)',
						'badge' => function (array $data) {
							if (!empty($data['encoding'])) {
								return $data['compressed'] ? \number_format($data['compressed']) : null;
							}
							return null;
						}
					],
					[
						'title' => 'Compression Ratio',
						'badge' => function (array $data) {
							if (!empty($data['encoding'])) {
								return number_format((100 / $data['uncompressed']) * $data['compressed'], 1).'%';
							}
							return null;
						}
					],
					[
						'title' => 'Generation Time',
						'badge' => function (array $data, bool &$status = null) {
							$status = $data['time'] < 0.3;
							return number_format($data['time'], 4).'secs';
						},
						'html' => '<p>This is the amount of time it takes to generate your page, the lower this is, the better for users and the more users you will be able to serve with your infrastructure.</p>
							<p>You can drastically reduce this value by caching your pages, your server can then just serve a static file instead of having to generate it each time.</p>'
					],
				]
			],
			[
				'title' => 'Assets',
				'params' => [
					[
						'title' => 'Stylesheets',
						'header' => 'assets',
						'badge' => function (array $data, bool &$status = null) {
							$count = 0;
							foreach ($data['assets'] AS $item) {
								if ($item['group'] === 'Stylesheets') {
									$count++;
								}
							}
							$status = $count < 10;
							return $count.' Stylesheets';
						},
						'html' => function (array $data) {
							if ($data['assets']) {
								$base = \get_home_url();
								$total = 0;
								$count = 0;
								$html = '<ul>';
								foreach ($data['assets'] AS $item) {
									if ($item['group'] === 'Stylesheets') {
										$size = \filesize(ABSPATH.$item['name']);
										$total += $size;
										$count++;
										$html .= '<li>'.basename($item['name']).' ('.number_format($size).' bytes)</li>';
									}
								}
								$html .= '</ul>';
								$html = '<p>Your site links to '.$count.' stylesheets, '.($count < 10 ? 'which isn\'t too bad' : 'which is a little high').'. The total size of the assets was '.\number_format($total).' bytes.'.($count ? ' The linked assets are:' : '').'</p>'.$html;
								$html .= '<p>Reducing the number and size of your stylesheets not only makes the download faster, but will require less work for the browser to consume. This is especially important on mobile devices where resources are limited.</p>';
								return $html;
							}
							return null;
						}
					],
					[
						'title' => 'Scripts',
						'badge' => function (array $data, bool &$status = null) {
							$count = 0;
							foreach ($data['assets'] AS $item) {
								if ($item['group'] === 'Scripts') {
									$count++;
								}
							}
							$status = $count < 10;
							return $count.' Scripts';
						},
						'html' => function (array $data) {
							if ($data['assets']) {
								$base = \get_home_url();
								$total = 0;
								$count = 0;
								$html = '<ul>';
								foreach ($data['assets'] AS $item) {
									if ($item['group'] === 'Scripts') {
										$size = \filesize(ABSPATH.$item['name']);
										$total += $size;
										$count++;
										$html .= '<li>'.basename($item['name']).' ('.number_format($size).' bytes)</li>';
									}
								}
								$html .= '</ul>';
								$html = '<p>Your site links to '.$count.' scripts, '.($count < 10 ? 'which isn\'t too bad' : 'which is a little high').'. The total size of the assets was '.\number_format($total).' bytes.'.($count ? ' The linked assets are:' : '').'</p>'.$html;
								$html .= '<p>Reducing the number and size of your scripts not only makes the download faster, but will require less work for the browser to consume. This is especially important on mobile devices where resources are limited.</p>';
								return $html;
							}
							return null;
						}
					],
					[
						'title' => 'Fonts',
						'badge' => function (array $data, bool &$status = null) {
							$count = 0;
							foreach ($data['assets'] AS $item) {
								if ($item['group'] === 'Fonts') {
									$count++;
								}
							}
							$status = $count < 10;
							return $count.' Fonts';
						},
						'html' => function (array $data) {
							if ($data['assets']) {
								$base = \get_home_url();
								$total = 0;
								$count = 0;
								$html = '<ul>';
								foreach ($data['assets'] AS $item) {
									if ($item['group'] === 'Fonts') {
										$size = \filesize(ABSPATH.$item['name']);
										$total += $size;
										$count++;
										$html .= '<li>'.basename($item['name']).' ('.number_format($size).' bytes)</li>';
									}
								}
								$html .= '</ul>';
								$html = '<p>Your site links to '.$count.' fonts. The total size of the assets is '.\number_format($total).' bytes'.($total > 100000 ? ', which is probably larger than it needs to be' : '').'.'.($count ? ' The linked assets are:' : '').'</p>'.$html;
								$html .= '<p>Improve your font usage by using less fonts in your design, optimising the size of the fonts by using the WOFF2 format, and reducing the number of symbols in the font file by building it with only common characters and discarding extra characters such as symbols.</p>';
								return $html;
							}
							return null;
						}
					],
					[
						'title' => 'Images',
						'badge' => function (array $data, bool &$status = null) {
							$count = 0;
							foreach ($data['assets'] AS $item) {
								if ($item['group'] === 'Images') {
									$count++;
								}
							}
							$status = $count < 10;
							return $count.' Images';
						},
						'html' => function (array $data) {
							if ($data['assets']) {
								$base = \get_home_url();
								$total = 0;
								$count = 0;
								$html = '<ul>';
								foreach ($data['assets'] AS $item) {
									if ($item['group'] === 'Images') {
										$size = \filesize(ABSPATH.$item['name']);
										$total += $size;
										$count++;
										$html .= '<li>'.basename($item['name']).' ('.number_format($size).' bytes)</li>';
									}
								}
								$html .= '</ul>';
								$html = '<p>Your site links to '.$count.' images'.($count > 20 ? ', which is a little high' : '').'. The total size of the assets is '.\number_format($total).' bytes'.($total > 1000000 ? ', which is probably larger than it needs to be' : '').', by enabling lazy loading of images can help offset the download size and number of assets by only loading then when the user scrolls them into view.'.($count ? ' The linked assets are:' : '').'</p>'.$html;
								$html .= '<p>Images normally consume the largest percentage of download size and assets in most web pages, so reducing the number and optimising them can be an easy win to improve your websites performance.</p>';
								return $html;
							}
							return null;
						}
					],
				]
			],
			[
				'title' => 'Performance',
				'params' => [
					[
						'title' => 'Compression',
						'header' => 'encoding',
						'badge' => function (array $data, ?bool &$enabled = null) {
							$encodings = [
								'deflate' => 'Deflate',
								'gzip' => 'GZip',
								'br' => 'Brotli'
							];
							$enabled = !empty($data['encoding']);
							return $enabled ? ($encodings[$data['encoding']] ?? 'Unknown') : 'Not Enabled';
						},
						'html' => function (array $data) {
							return '<p>Enabling compression tells your server to zip up your HTML (and other compressible assets) before they are sent to the client, who then inflates the content after it is received, thus sending less bytes down the wire. You can normally achieve around 70% or more compression.</p>
							<p>'.(empty($data['encoding']) ? 'You can enable compression by editing your .htaccess file or your websites Nginx config.' : ($data['encoding'] !== 'br' ? 'You have compression enabled, but upgrading your server to use Brotli compression will increase the compression ratio. You may have to add a module to your webserver to enable this algorithm.' : '')).'</p>';
						}
					],
					[
						'title' => 'ETags',
						'header' => 'etag',
						'badge' => function (array $data, ?bool &$enabled = null) {
							$enabled = !empty($data['etag']);
							return $enabled ? 'Enabled' : 'Disabled';
						},
						'html' => '<p>An Etag is a hash string that is sent with each HTML page. If the HTML changes, it will produce a different hash.</p>
							<p>When a browser requests a page it has previously visited, it sends the previous hash back, the server will compare the old hash and the new one, and if they are the same, the server sends back a 304 response with no body.</p>
							<p>This tells the browser to use the page stored in local cache, resulting in less data transfer and a faster loading page.</p>'
					],
					[
						'title' => 'Browser Cache',
						'badge' => function (array $data, ?bool &$enabled = null) {
							if ($data['cache-control'] && ($pos = strpos($data['cache-control'], 'max-age=')) !== false) {
								$pos += 8;
								$end = strpos($data['cache-control'], ',', $pos);
								return ($end !== false ? substr($data['cache-control'], $pos, $end - $pos) : substr($data['cache-control'], $pos)).' Secs';
							}
							$enabled = false;
							return 'disabled';
						},
						'html' => '<p>This tells the browser how long to store a webpage in local cache before asking the server if there is a fresh copy.</p>'
					],
					[
						'title' => 'Shared Cache Life',
						'badge' => function (array $data, bool &$status = null) {
							if ($data['cache-control'] && ($pos = \strpos($data['cache-control'], 's-maxage=')) !== false) {
								$pos += 9;
								$end = \strpos($data['cache-control'], ',', $pos);
								$value = $end !== false ? \substr($data['cache-control'], $pos, $end - $pos) : \substr($data['cache-control'], $pos);
								$status = $value >= 0;
								return $value.' Secs';
							}
							$status = false;
							return 'Not Set';
						},
						'html' => '<p>Tells shared caches how long to store webpages for before asking the root server to generate the page again.</p>
							<p>If you are using a local cache system such as Nginx Cache or Varnish, or a proxy cache/CDN, setting a value greater than 0 will make sure your pages get served from cache rather than being generated on each request.</p>
							<p>Once cached, the cache server then only has to read the cached file, so your pages will be served faster and will have more capacity.</p>
							<p>For high traffic sites, even a shared cache time of 5 seconds (a micrcache) can greatly increase your servers capacity and response time whilst still keeping content fresh.</p>'
					],
					[
						'title' => 'Static Cache',
						'header' => 'x-cache-status',
						'badge' => function (array $data, bool &$status = null) {
							if (empty($data['x-cache-status']) && empty($data['cf-cache-status'])) {
								$status = false;
								return 'Not Configured';
							} elseif (($data['x-cache-status'] ?? $data['cf-cache-status']) === 'BYPASS') {
								$status = false;
								return 'Not configured correctly';
							}
							$status = true;
							return 'Enabled';
						},
						'html' => function (array $data) {
							$status = $data['x-cache-status'] ?? ($data['cf-cache-status'] ?? null);
							return '<p>We tested your page to see if it sent back a header that indicated whether a static cache was enabled</p>';
						}
					],
					[
						'title' => 'Preload',
						'badge' => function (array $data, bool &$status = null) {
							if (!empty($data['link']) && ($assets = $this->getLinkAssets($data['link'], 'preload')) !== null) {
								$status = true;
								return count($assets).' Assets';
							}
							$status = false;
							return 'No assets';
						},
						'html' => function (array $data) {
							$html = '<p>Preloading assets along with HTTP/2.0 Push sends the client the selected assets along with the requested page without the client requesting them, then when the page loads and the assets are required, they are already available.</p>
								<p>Even without HTTP/2.0 Push this should enable pages to load faster, as it will tell the browser to request the selected assets at the earliest opportunity, and flattens any chained assets.</p>';
							if (!empty($data['link']) && ($assets = $this->getLinkAssets($data['link'], 'preload')) !== null) {
								$html .= '<p>The following assets are being preloaded:</p><ul>';
								foreach ($assets AS $item) {
									$html .= '<li>'.htmlspecialchars($item['url']).'</li>';
								}
								$html .= '</ul>';
							}
							return $html;
						}
					]
				]
			],
			[
				'title' => 'Security',
				'params' => [
					[
						'title' => 'Transport Encrypted (HTTPS)',
						'badge' => function (array $data, bool &$status = null) {
							$status = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
							return $status ? 'Enabled' : 'Not Enabled';
						}
					],
					[
						'title' => 'MIME Type Sniffing',
						'badge' => function (array $data, bool &$status = null) {
							$status = $data['x-content-type-options'] === 'nosniff';
							return $status ? 'Enabled' : 'Not Enabled';
						}
					],
					[
						'title' => 'XSS Protection',
						'badge' => function (array $data, bool &$status = null) {
							$status = !empty($data['x-xss-protection']);
							return $status ? 'Enabled' : 'Not Enabled';
						}
					],
					[
						'title' => 'Website Embedding',
						'badge' => function (array $data, bool &$status = null) {
							$status = !empty($data['x-iframe-options']);
							$options = [
								'allow' => 'Allowed',
								'deny' => 'Disllowed',
								'sameorigin' => 'Allowed only for own domain'
							];
							return $options[$data['x-iframe-options']] ?? 'Not Enabled';
						}
					],
					[
						'title' => 'Content Security Policy',
						'badge' => function (array $data, bool &$status = null) {
							$status = !empty($data['content-security-policy']);
							return $status ? 'Setup' : 'Not Setup';
						}
					],
					[
						'title' => 'Force SSL',
						'badge' => function (array $data, bool &$status = null) {
							$status = !empty($data['strict-transport-security']);
							return $status ? \number_format($data['strict-transport-security']).' secs' : 'Not Enabled';
						}
					]
				]
			]
		];
	}

	protected function getLinkAssets(string $link, ?string $type = null) : ?array {
		$assets = [];
		if (!empty($link)) {
			foreach (explode(',', $link) AS $item) {
				$props = [];
				foreach (explode(';', $item) AS $value) {
					$value = trim($value);
					if ($value[0] === '<') {
						$props['url'] = trim($value, '<>');
					} elseif (strpos($value, '=') !== false) {
						list($key, $val) = explode('=', $value, 2);
						$props[$key] = trim($val, '"');
					} else {
						$props[$value] = true;
					}
				}
				if (!$type || ($props['rel'] ?? '') === $type) {
					$assets[] = $props;
				}
			}
		}
		return $assets ? $assets : null;
	}

	protected function drawTable(array $config, array $data) {
		\wp_enqueue_style('admin-styles', \mb_substr(str_replace('\\', '/', __DIR__), mb_strlen(ABSPATH) - 1).'/overview.css');

		$html = '<section class="torque-overview">';
		foreach ($config AS $g => $group) {
			$html .= '<h2>'.\htmlspecialchars($group['title']).'</h2>
				<div class="torque-overview__list">';
			foreach ($group['params'] AS $p => $item) {
				if (isset($item['header'])) {
					$item['value'] = $data[$item['header']] ?? null;
				}
				if (isset($item['decorator'])) {
					$item['value'] = call_user_func($item['decorator'], $item['value'], $data);
				}
				$enabled = null;
				$badge = isset($item['badge']) ? ($item['badge'] instanceof \Closure ? $item['badge']($data, $enabled) : $item['badge']) : null;
				if (($item['value'] ?? null) !== null || $badge) {
					$html .= '<input type="checkbox" class="torque-overview__switch" id="torque-'.$g.'-'.$p.'" />';
					$html .= '<label class="torque-overview__heading" for="torque-'.$g.'-'.$p.'">
						<span class="torque-overview__heading-title">'.\htmlspecialchars($item['title']).'</span>';
					if ($badge) {
						$html .= '<span class="torque-overview__heading-status'.($enabled === null ? '' : ' torque-overview__heading-status--'.($enabled ? 'enabled' : 'disabled')).'">
							'.htmlspecialchars($badge).'
						</span>';
					}
					if (($item['html'] ?? null) !== null) {
						$html .= '<span class="torque-overview__heading-icon"></span>';
					}
					$html .= '</label>';
					if (($item['html'] ?? null) !== null) {
						$html .= '<div class="torque-overview__item">
							<div class="torque-overview__item-inner">
								'.($item['html'] instanceof \Closure ? $item['html']($data) : $item['html']).'
							</div>
						</div>';
					}
				}
			}
			$html .= '</div>';
		}
		$html .= '</section>';
		return $html;
	}

	public function draw() : string {
		$url = \get_home_url().'/';
		$headers = [
			'Accept-Encoding: deflate,gzip,br'
		];
		$output = [];
		$time = microtime(true);
		if (($html = $this->getPage($url, $headers, $output)) !== false) {
			$output['time'] = microtime(true) - $time;
			$output['compressed'] = \strlen($html);
			$uncompressed = $this->getPage($url);
			$output['uncompressed'] = \strlen($uncompressed);
			$output['assets'] = $this->getPageAssets($url);
			// var_dump($output);
			return $this->drawTable($this->config, $output);
		}
	}
}
