<?php
/**
 * Generates the overview page to analyse the website
 *
 * @package hexydec/torque
 */
namespace hexydec\torque;

class overview extends assets {

	/**
	 * @var array $config Stores the configuration for the overview page
	 */
	protected array $config = [];

	/**
	 * Specifies the configuration for the overview page
	 */
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
						'badge' => function (array $data) : ?string {
							return $data['server'] ?? null;
						}
					],
					[
						'title' => 'PHP Version',
						'badge' => \phpversion()
					]
				]
			],
			[
				'title' => 'Page',
				'params' => [
					[
						'title' => 'MIME Type',
						'badge' => function (array $data, ?bool &$status = null) : string {
							if (!empty($data['content-type'])) {
								$value = $data['content-type'];
								if (($pos = \mb_strpos($value, ';')) !== false) {
									$value = \mb_substr($value, 0, $pos);
								}
								$status = \in_array($value, ['text/html', 'application/xhtml+xml']);
								return $value;
							}
							$status = false;
							return 'Not Set';
						},
						'html' => '<p>This checks that your server is returning the correct MIME (Multipurpose Internet Mail Extensions) type for your web page, it is used to tell the browser what type of document you are sending the client, and should be set to <code>text/html</code> or <code>application/xhtml+xml</code>.</p>'
					],
					[
						'title' => 'HTML Size (Uncompressed)',
						'badge' => function (array $data, ?bool &$status = null) : string {
							$status = $data['uncompressed'] < 100000;
							return \number_format($data['uncompressed']).' bytes';
						},
						'html' => function (array $data) : string {
							$html = '<p>This is the size of your homepage in bytes.</p>
								<p>Reducing the overall size of your HTML will ensure the browser doesn\'t have to work too hard to render your page onto the user\'s screen.</p>
								<p>Currently your page is '.\number_format($data['uncompressed']).' bytes, '.($data['uncompressed'] < 100000 ? 'which isn\'t too big' : ($data['uncompressed'] < 200000 ? 'which is a little on the large side' : 'which is quite big, you should look at how you can reduce this to make sure the browser doesn\'t have to work so hard to render your page')).'.</p>';
							return $html;
						}
					],
					[
						'title' => 'HTML Size (Compressed)',
						'badge' => function (array $data, ?bool &$status = null) : ?string {
							if (!empty($data['compressed'])) {
								$status = $data['compressed'] < 20000;
								return \number_format($data['compressed']).' bytes';
							}
							return null;
						},
						'html' => '<p>This is the size of the payload that is sent to the client after your page has been compressed.</p>'
					],
					[
						'title' => 'Compression Ratio',
						'badge' => function (array $data, ?bool &$status = null) : ?string {
							if (!empty($data['compressed'])) {
								$ratio = 100 - ((100 / $data['uncompressed']) * $data['compressed']);
								$status = $ratio > 50;
								return \number_format($ratio, 1).'%';
							}
							return null;
						},
						'html' => '<p>This is the compression ratio achieved by compressing your HTML page when it is sent to the client. You should expect around a 70% compression ratio.</p>'
					],
					[
						'title' => 'Compression Algorithm',
						'badge' => function (array $data, ?bool &$status = null) : ?string {
							if (!empty($data['content-encoding'])) {
								$values = [
									'deflate' => 'Deflate',
									'gzip' => 'Gzip',
									'br' => 'Brotli'
								];
								$status = $data['content-encoding'] === 'gzip' ? null : $data['content-encoding'] === 'br';
								return $values[$data['content-encoding']];
							}
							return null;
						},
						'html' => function (array $data) : ?string {
							if (!empty($data['content-encoding'])) {
								$html = '<p>The algorithm that is used to compress your page will determine the compression ratio.</p>';
								if ($data['content-encoding'] === 'deflate') {
									$html .= '<p>Your page is being compressed using the Deflate algorithm, this is an older standard algorithm that has mostly been phased out due to implementation issues.</p>';
								} elseif ($data['content-encoding'] === 'gzip') {
									$html .= '<p>Your page is being compressed using the Gzip algorithm, this is the most common type of transport compression used on the internet.</p>';
								} elseif ($data['content-encoding'] === 'br') {
									$html .= '<p>Your page is being compressed using the Brotli algorithm, this is the newest compression algorithm that browsers support, and will give you the best compression ratio.</p>';
								}
								if ($data['content-encoding'] !== 'br') {
									$html .= '<p>To get better compression use the Brotli algorithm, it has a built in dictionary of common strings which doesn\'t need to be transmitted with the payload, resulting in up to 25% better compression. But it is much newer, and so your host may not support it yet.</p>';
								}
								return $html;
							}
							return null;
						}
					],
					[
						'title' => 'Generation Time',
						'badge' => function (array $data, ?bool &$status = null) : string {
							$status = $data['time'] < 0.3;
							return \number_format($data['time'], 4).'secs';
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
						'badge' => function (array $data, ?bool &$status = null) : ?string {
							if ($data['assets']) {
								$count = 0;
								foreach ($data['assets'] AS $item) {
									if ($item['group'] === 'Stylesheets') {
										$count++;
									}
								}
								$status = $count < 10;
								return $count.' Stylesheets';
							}
							return null;
						},
						'html' => function (array $data) : ?string {
							if ($data['assets']) {
								$dir = \get_home_path();
								$total = 0;
								$count = 0;
								$html = '<ul>';
								foreach ($data['assets'] AS $item) {
									if ($item['group'] === 'Stylesheets') {
										$size = \file_exists($dir.$item['name']) ? \filesize($dir.$item['name']) : false;
										$total += $size;
										$count++;
										$html .= '<li>'.\basename($item['name']).' ('.($size === false ? 'file not found' : \number_format($size).' bytes').')</li>';
									}
								}
								$html .= '</ul>';
								$html = '<p>Your site links to '.$count.' stylesheets, '.($count <= 5 ? 'which is nice and low' : ($count < 10 ? 'which isn\'t too bad' : 'which is a little high')).'. The total size of the assets was '.\number_format($total).' bytes.'.($count ? ' The linked assets are:' : '').'</p>'.$html;
								$html .= '<p>Reducing the number and size of your stylesheets not only makes the download faster, but will require less work for the browser to consume. This is especially important on mobile devices where resources are limited.</p>';
								return $html;
							}
							return null;
						}
					],
					[
						'title' => 'Scripts',
						'badge' => function (array $data, ?bool &$status = null) : ?string {
							if ($data['assets']) {
								$count = 0;
								foreach ($data['assets'] AS $item) {
									if ($item['group'] === 'Scripts') {
										$count++;
									}
								}
								$status = $count < 10;
								return $count.' Scripts';
							}
							return null;
						},
						'html' => function (array $data) : ?string {
							if ($data['assets']) {
								$dir = \get_home_path();
								$total = 0;
								$count = 0;
								$html = '<ul>';
								foreach ($data['assets'] AS $item) {
									if ($item['group'] === 'Scripts') {
										$size = \file_exists($dir.$item['name']) ? \filesize($dir.$item['name']) : false;
										$total += $size;
										$count++;
										$html .= '<li>'.\basename($item['name']).' ('.($size === false ? 'file not found' : \number_format($size).' bytes').')</li>';
									}
								}
								$html .= '</ul>';
								$html = '<p>Your site links '.$count.' scripts, '.($count <= 5 ? 'which is nice and low' : ($count < 10 ? 'which isn\'t too bad' : 'which is a little high')).'. The total size of the assets was '.\number_format($total).' bytes.'.($count ? ' The linked assets are:' : '').'</p>'.$html;
								$html .= '<p>Reducing the number and size of your scripts not only makes the download faster, but will require less work for the browser to consume. This is especially important on mobile devices where resources are limited.</p>';
								return $html;
							}
							return null;
						}
					],
					[
						'title' => 'Fonts',
						'badge' => function (array $data, ?bool &$status = null) : ?string {
							if ($data['assets']) {
								$count = 0;
								$groups = [];
								foreach ($data['assets'] AS $item) {
									if ($item['group'] === 'Fonts') {
										$name = \mb_strstr($item['name'], '.', true);
										if (!\in_array($name, $groups, true)) {
											$groups[] = $name;
											$count++;
										}
									}
								}
								$status = $count < 10;
								return $count.' Fonts';
							}
							return null;
						},
						'html' => function (array $data) : ?string {
							if ($data['assets']) {
								$dir = \get_home_path();
								$total = 0;
								$count = 0;
								$used = 0;
								$groups = [];
								$html = '<ul>';
								foreach ($data['assets'] AS $item) {
									if ($item['group'] === 'Fonts') {
										$name = \mb_strstr($item['name'], '.', true);
										$size = \file_exists($dir.$item['name']) ? \filesize($dir.$item['name']) : false;
										$count++;

										// only add up the used fonts
										$isused = !\in_array($name, $groups, true);
										if ($isused) {
											$groups[] = $name;
											$total += $size;
											$used++;
										}
										$html .= '<li>'.($isused ? '<strong>' : '').\basename($item['name']).' ('.($size === false ? 'file not found' : \number_format($size).' bytes').')'.($isused ? '*</strong>' : '').'</li>';
									}
								}
								$html .= '</ul>';
								$html = '<p>Your site links to '.$count.' fonts, of which '.$used.' will be used. The total size of the used assets is '.\number_format($total).' bytes (optimal*)'.($total > 100000 ? ', which is larger than it should be' : '').'.'.($count ? ' The linked assets are:' : '').'</p>'.$html;
								$html .= '<p>Improve your font usage by using less fonts in your design, optimising the size of the fonts by using the WOFF2 format, and reducing the number of glyphs in the font file by building it with only common characters and discarding extra characters such as symbols.</p>
								<p>If characters are on the page which do not have corresponding glyphs in the font file, a fallback font will be used, which can be specified in your CSS file.</p>';
								return $html;
							}
							return null;
						}
					],
					[
						'title' => 'Images',
						'badge' => function (array $data, bool &$status = null) : ?string {
							if ($data['assets']) {
								$count = 0;
								foreach ($data['assets'] AS $item) {
									if ($item['group'] === 'Images') {
										$count++;
									}
								}
								$status = $count < 10;
								return $count.' Images';
							}
							return null;
						},
						'html' => function (array $data) : ?string {
							if ($data['assets']) {
								$dir = \get_home_path();
								$total = 0;
								$count = 0;
								$html = '<ul>';
								foreach ($data['assets'] AS $item) {
									if ($item['group'] === 'Images') {
										$size = \file_exists($dir.$item['name']) ? \filesize($dir.$item['name']) : false;
										$total += $size;
										$count++;
										if ($size >= 50000) {
											$html .= '<li>'.\basename($item['name']).' ('.($size === false ? 'file not found' : \number_format($size).' bytes').')</li>';
										}
									}
								}
								$html .= '</ul>';
								$html = '<p>Your site links to '.$count.' images'.($count > 20 ? ', which is a little high' : '').'. The total size of the assets is '.\number_format($total).' bytes'.($total > 1000000 ? ', which is larger than it should be' : '').'.</p>
								<p>Enabling lazy loading of images can help offset the download size and number of assets by only loading then when the user scrolls them into view.'.($html ? ' The largest images are:' : '').'</p>'.$html;
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
						'badge' => function (array $data, ?bool &$enabled = null) : string {
							$encodings = [
								'deflate' => 'Deflate',
								'gzip' => 'GZip',
								'br' => 'Brotli'
							];
							$enabled = !empty($data['content-encoding']);
							return $enabled ? ($encodings[$data['content-encoding']] ?? 'Unknown') : 'Not Enabled';
						},
						'html' => function (array $data) : string {
							return '<p>Enabling compression tells your server to zip up your HTML (and other compressible assets) before they are sent to the client, who then inflates the content after it is received, thus sending less bytes down the wire. You can normally achieve around 70% or more compression.</p>
							<p>'.(empty($data['content-encoding']) ? 'You can enable compression by editing your .htaccess file or your websites Nginx config.' : ($data['content-encoding'] !== 'br' ? 'You have compression enabled, but upgrading your server to use Brotli compression will increase the compression ratio. You may have to add a module to your webserver to enable this algorithm.' : '')).'</p>';
						}
					],
					[
						'title' => 'ETags',
						'badge' => function (array $data, ?bool &$enabled = null) : string {
							$enabled = !empty($data['etag']);
							return $enabled ? 'Enabled' : 'Disabled';
						},
						'html' => '<p>An Etag is a hash string that is sent with each HTML page. If the HTML changes, it will produce a different hash.</p>
							<p>When a browser requests a page it has previously visited, it sends the previous hash back, the server will compare the old hash and the new one, and if they are the same, the server sends back a 304 response with no body.</p>
							<p>This tells the browser to use the page stored in local cache, resulting in less data transfer and a faster loading page.</p>'
					],
					[
						'title' => 'Browser Cache',
						'badge' => function (array $data, ?bool &$enabled = null) : string {
							if (!empty($data['cache-control']) && ($pos = \mb_strpos($data['cache-control'], 'max-age=')) !== false) {
								$enabled = true;
								$pos += 8;
								$end = \mb_strpos($data['cache-control'], ',', $pos);
								return ($end !== false ? \mb_substr($data['cache-control'], $pos, $end - $pos) : \mb_substr($data['cache-control'], $pos)).' Secs';
							}
							$enabled = false;
							return 'Disabled';
						},
						'html' => '<p>This tells the browser how long to store a webpage in local cache before asking the server if there is a fresh copy.</p>
							<p>Setting this to 0 is a preferred option, unless your site is not often updated. This ensures that the client always sees the most up-to-date version of your pages. If you enable Etags, the browser can check back with the server to see if it has been updated after the time has elapsed, and if it hasn\'t, use the cached version.</p>'
					],
					[
						'title' => 'Shared Cache Life',
						'badge' => function (array $data, bool &$status = null) : string {
							if (!empty($data['cache-control']) && ($pos = \mb_strpos($data['cache-control'], 's-maxage=')) !== false) {
								$pos += 9;
								$end = \mb_strpos($data['cache-control'], ',', $pos);
								$value = $end !== false ? \mb_substr($data['cache-control'], $pos, $end - $pos) : \mb_substr($data['cache-control'], $pos);
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
						'badge' => function (array $data, bool &$status = null) : string {
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
						'html' => function (array $data) : string {
							// $status = $data['x-cache-status'] ?? ($data['cf-cache-status'] ?? null);
							return '<p>We tested your page to see if it sent back a header that indicated whether a static cache was enabled. When enabled, the cache manager normally adds a header to the response that indicates whether the cache was used.</p>';
						}
					],
					[
						'title' => 'Preload',
						'badge' => function (array $data, ?bool &$status = null) : string {
							if (!empty($data['link']) && ($assets = $this->getLinkAssets($data['link'], 'preload')) !== null) {
								$status = true;
								return \count($assets).' Assets';
							}
							$status = false;
							return 'No assets';
						},
						'html' => function (array $data) : string {
							$html = '<p>Preloading assets along with HTTP/2.0 Push sends the client the selected assets along with the requested page without the client requesting them, then when the page loads and the assets are required, they are already available.</p>
								<p>Even without HTTP/2.0 Push this should enable pages to load faster, as it will tell the browser to request the selected assets at the earliest opportunity, and flattens any chained assets.</p>';
							if (!empty($data['link']) && ($assets = $this->getLinkAssets($data['link'], 'preload')) !== null) {
								$html .= '<p>The following assets are being preloaded:</p><ul>';
								foreach ($assets AS $item) {
									$html .= '<li>'.esc_html($item['url']).'</li>';
								}
								$html .= '</ul>';
							}
							$html .= '<p>Note that HTTP/2.0 preload can only be enabled over HTTPS.</p>';
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
						'badge' => function (array $data, bool &$status = null) : string {
							$status = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
							return $status ? 'Enabled' : 'Not Enabled';
						},
						'html' => '<p>You need an SSL certificate to enable the HTTPS protocol, this certificate is then used to encrypt the communications between your server and the client.</p>
							<p>There has been quite a push over the last few years to encrypt all website communications to prevent user\'s browsing habits being snooped on, and users are starting to expect websites to be encrypted.</p>
							<p>You can get a free SSL certificate through services like LetsEncrypt!, you can also get auto-update software to keep your ceritificate up to date, as they normally issue them for 3 months at a time.</p>
							<p>Hosting software such as Plesk or cPanel have an interface to generate free certificates.</p>'
					],
					[
						'title' => 'Prevent MIME Type Sniffing',
						'badge' => function (array $data, bool &$status = null) : string {
							$status = ($data['x-content-type-options'] ?? '') === 'nosniff';
							return $status ? 'Enabled' : 'Not Enabled';
						},
						'html' => '<p>When a client requests a file such as an image, your server normally also sends a MIME (Multipurpose Internet Mail Extensions) type. This is a string that tells the client what sort of file was sent (e.g. a JPEG image is <code>image/jpeg</code>) so that the browser handles it in the correct way.</p>
							<p>Sometimes the MIME type that is advertised is not correct or not sent at all, so browsers will sometimes "sniff" the MIME type to work out what sort of file has been sent.</p>
							<p>This setting prevents the browser from sniffing the MIME type, and forces it to use the advertised one. From a security perspective this is better as it prevents issues such as a file being advertised as one type, but actually being of another. For example you could send a file as an image when it is actually executable, which could enable malicious code being run on the client machine.</p>'
					],
					[
						'title' => 'Cross Site Scripting Protection',
						'badge' => function (array $data, bool &$status = null) : string {
							$status = !empty($data['x-xss-protection']);
							return $status ? 'Enabled' : 'Not Enabled';
						},
						'html' => '<p>Stops the page from loading, or filters out malicious content when a reflected Cross Site Scripting (XSS) attack is detected.</p>
							<p>This type of attack works when information sent in the querystring part of the URL (page.html?[Querystring]) exploits the code on the target page, where the querystring payload is echoed on the page without being escaped. This can enable an attacker to inject a malicious script onto the target website, normally to steal data. All the attacker has to do is get the user to click a specially crafted link.</p>
							<p>This should be disabled if you are confident that no exploitable code exists, otherwise enabled. On modern browsers, this type of attack is largely mitigated thorugh the use of a strong Content Security Policy (CSP), especially where <code>\'unsafe-inline\'</code> is disabled (Although this mostly is not possible in Wordpress, depending on your theme and plugins). However, limiting the domains that can host scripts on your site with a CSP does limit the attack vector.</p>'
					],
					[
						'title' => 'Website Embedding',
						'badge' => function (array $data, bool &$status = null) : string {
							$status = !empty($data['x-iframe-options']);
							$options = [
								'allow' => 'Allowed',
								'deny' => 'Disllowed',
								'sameorigin' => 'Allowed only for own domain',
								'notenabled' => 'Not Enabled'
							];
							return $options[$data['x-iframe-options'] ?? 'notenabled'] ?? $options['notenabled'];
						},
						'html' => '<p>Prevents other websites from embedding your website within an iframe. This is useful to stop other websites presenting your content as their own, or wrapping it with other content.</p>
							<p>Note that this setting is obsoleted by the <code>frame-ancestors</code> part of a Content-Security-Policy (CSP).</p>'
					],
					[
						'title' => 'Content Security Policy',
						'badge' => function (array $data, bool &$status = null) : string {
							$status = !empty($data['content-security-policy']);
							return $status ? 'Configured' : 'Not Configured';
						},
						'html' => '<p>A Content Security Policy (CSP) is sent to the client with each page, and tells the browser where assets are allowed to originate from, or how they can be embedded.</p>
							<p>This setting is the most important setting for improving the security of your website, as for example even if a malicious script was injected into your site that originated from an unauthorised domain, the browser would not run it.</p>
							<p>A CSP can specify authorised domains for different types of assets such as styles, scripts, images and more. It is highly recommended that your fully specify a strong CSP that only allows the domains your site requires for each asset type.</p>'
					],
					[
						'title' => 'Force SSL',
						'badge' => function (array $data, bool &$status = null) : string {
							$status = !empty($data['strict-transport-security']);
							if ($status && \preg_match('/max-age=([0-9]++)/i', $data['strict-transport-security'] ?? '', $match)) {
								$secs = \intval($match[1]);
								if ($secs > 2628000) {
									return \number_format($secs / 2628000).' months';
								} elseif ($secs > 86400) {
									return \number_format($secs, 86400).' days';
								} else {
									return \number_format($secs).' secs';
								}
							}
							return 'Not Enabled';
						},
						'html' => '<p>This setting tells the browser to only connect to this website over an encrypted channel. With this setting in place, once a user views your website, any subsequent views will only be allowed over HTTPS, for the amount of seconds specified.</p>
							<p>If you plan to deliver your website over HTTPS only, you should enable this setting.</p>'
					]
				]
			]
		];
	}

	/**
	 * Extracts assets hrefs from a Link header
	 *
	 * @param string $link A string specifying the Link header
	 * @param string $type If specified, filters the link assets by type, or null for all
	 * @return array An array of assets, each item will be a array containing 'url' and keys for any other values specified in the link header
	 */
	protected function getLinkAssets(string $link, ?string $type = null) : ?array {
		$assets = [];
		if (!empty($link)) {
			foreach (\explode(',', \trim($link, ',; ')) AS $item) {
				$props = [];
				foreach (\explode(';', $item) AS $value) {
					$value = \trim($value);
					if ($value[0] === '<') {
						$props['url'] = \trim($value, '<>');
					} elseif (\mb_strpos($value, '=') !== false) {
						list($key, $val) = \explode('=', $value, 2);
						$props[$key] = \trim($val, '"');
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

	/**
	 * Renders the overview data
	 *
	 * @param array $$config An array specifying what to render
	 * @param array $$data An array containing data colected from the headers of the retrieved page
	 * @return string HTML representing the input configuration and data
	 */
	protected function drawOverview(array $config, array $data) : string {
		$css = \str_replace('\\', '/', __DIR__).'/stylesheets/overview.css';
		\wp_enqueue_style('torque-overview', \get_home_url().\mb_substr($css, \mb_strlen(\get_home_path()) - 1), [], \filemtime($css));

		$html = '<p>A scan of your website\'s security and performance.</p>
			<section class="torque-overview">';
		foreach ($config AS $g => $group) {
			$html .= '<h2>'.\esc_html($group['title']).'</h2>
				<div class="torque-overview__list">';
			foreach ($group['params'] AS $p => $item) {
				$enabled = null;
				$badge = isset($item['badge']) ? ($item['badge'] instanceof \Closure ? $item['badge']($data, $enabled) : $item['badge']) : null;
				if (($item['value'] ?? null) !== null || $badge) {
					$html .= '<input type="checkbox" class="torque-overview__switch" id="torque-'.$g.'-'.$p.'" />';
					$html .= '<label class="torque-overview__heading" for="torque-'.$g.'-'.$p.'">
						<span class="torque-overview__heading-title">'.\esc_html($item['title']).'</span>';
					if ($badge) {
						$html .= '<span class="torque-overview__heading-status'.($enabled === null ? '' : ' torque-overview__heading-status--'.($enabled ? 'enabled' : 'disabled')).'">
							'.\esc_html($badge).'
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

	/**
	 * Compiles data about the homepage and sends it to the renderer
	 *
	 * @return string HTML representing the input configuration and data, or null if the page couldn't be retrieved
	 */
	public function draw() : ?string {
		$url = \get_home_url().'/';

		// define headers to enable compression
		$headers = ['Accept-Encoding: deflate, gzip, br'];

		// time how long it takes to get the page
		$time = \microtime(true);
		if (($html = $this->getPage($url, $headers)) !== false) {
			$headers['time'] = \microtime(true) - $time;

			// get the uncompressed page
			if (!empty($headers['content-encoding'])) {
				$uncompressed = $this->getPage($url);
				$headers['compressed'] = \strlen($html);
				$headers['uncompressed'] = \strlen($uncompressed);
			} else {
				$headers['uncompressed'] = \strlen($html);
			}
			$headers['assets'] = $this->getPageAssets($url);

			// render the page
			return $this->drawOverview($this->config, $headers);
		}
		return null;
	}
}
