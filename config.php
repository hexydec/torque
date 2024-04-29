<?php
/**
 * A class for storing and manipulating the application configuration
 *
 * @package hexydec/torque
 */
namespace hexydec\torque;

class config extends packages {

	/**
	 * @var array $options A list of configuration options for the plugin
	 */
	protected array $options = [];

	/**
	 * @var array $config The plugin configuration
	 */
	protected array $config = [
		'output' => null, // can't be set here, see below
		'csplog' => 'csp-reports.json'
	];

	/**
	 * Constructs the config object - updates config items that require callbacks
	 */
	public function __construct() {
		$url = \get_home_url().'/?notorque';
		// $dir = \dirname(\dirname(\dirname(__DIR__))).'/'; // can't use get_home_path() here
		$this->config['output'] = WP_CONTENT_DIR.'/uploads/torque/';

		// build options
		$this->options = [
			'overview' => [
				'tab' => 'Overview',
				'name' => 'Website Overview',
				'desc' => 'An overview of your website\'s performance and security',
				'options' => [],
				'html' => function () : ?string {
					$obj = new overview();
					return $obj->draw();
				}
			],
			'settings' => [
				'tab' => 'Settings',
				'name' => 'Plugin Options',
				'desc' => 'Edit the main settings of the plugin',
				'html' => '<p>Edit the main settings of the plugin.</p>',
				'options' => [
					'minifyhtml' => [
						'label' => 'Minify HTML',
						'type' => 'checkbox',
						'description' => 'Enables minification of your HTML',
						'default' => false
					],
					'minifystyle' => [
						'label' => 'Minify CSS',
						'type' => 'checkbox',
						'description' => 'Enable minification of inline CSS within a <style> tag, and combined scripts',
						'default' => false
					],
					'combinestyle' => [
						'label' => 'Combine CSS',
						'type' => 'multiselect',
						'description' => 'Select which CSS files to combine and minify',
						'default' => [],
						'datasource' => function () use ($url) : array|false {
							if (($assets = assets::getPageAssets($url)) !== false) {
								$filtered = [];
								foreach ($assets AS $item) {
									if ($item['group'] === 'Stylesheets') {
										$filtered[] = $item;
									}
								}
								return $filtered;
							}
							return false;
						},
						'onsave' => function (array $value, array $options) : bool {
							if ($value) {
								$target =  $this->config['output'].\md5(\implode(',', $value)).'.css';
								if (!assets::buildCss($value, $target, $options['minifystyle'] ? ($options['style'] ?? []) : null)) {
									\add_settings_error(self::SLUG, self::SLUG, 'The combined CSS file could not be generated');
									return false;
								}
							}
							return true;
						}
					],
					'minifyscript' => [
						'label' => 'Minify Javascript',
						'type' => 'checkbox',
						'description' => 'Enable minification of inline Javascript within a <script> tag and combined scripts',
						'default' => false
					],
					'combinescript' => [
						'label' => 'Combine Javascript',
						'type' => 'multiselect',
						'description' => 'Select which Javascript files to combine and minify. Note that depending on the load order requirements of your inline and included scripts, this can break your Javascript. Check the console for errors after implementing.',
						'default' => [],
						'datasource' => function () use ($url) : array|false {
							if (($assets = assets::getPageAssets($url)) !== false) {
								$filtered = [];
								foreach ($assets AS $item) {
									if ($item['group'] === 'Scripts') {
										$filtered[] = $item;
									}
								}
								return $filtered;
							}
							return false;
						},
						'onsave' => function (array $value, array $options) : bool {
							if ($value) {
								$target =  $this->config['output'].\md5(\implode(',', $value)).'.js';
								if (!assets::buildJavascript($value, $target, $options['minifyscript'] ? ($options['script'] ?? []) : null)) {
									\add_settings_error(self::SLUG, self::SLUG, 'The combined Javascript file could not be generated');
									return false;
								}
							}
							return true;
						}
					],
					'lazyload' => [
						'label' => 'Lazy Load Images',
						'type' => 'checkbox',
						'description' => 'Tell the browser to only load images when they are scrolled into view',
						'default' => false
					],
					'admin' => [
						'label' => 'Minify Admin System',
						'type' => 'checkbox',
						'description' => 'Minify the admin system',
						'default' => false
					],
					'stats' => [
						'label' => 'Show Stats',
						'type' => 'checkbox',
						'description' => 'Show stats in the console (Only for yourself)',
						'default' => false,
						'value' => \get_current_user_id()
					]
				]
			],
			'html' => [
				'tab' => 'HTML',
				'name' => 'Basic Minification',
				'desc' => 'Edit the HTML minification options',
				'html' => '<p>Edit the general HTML minification settings.</p>',
				'options' => [
					'whitespace' => [
						'label' => 'Whitespace',
						'type' => 'checkbox',
						'description' => 'Strip unnecessary whitespace',
						'default' => true
					],
					'lowercase' => [
						'label' => 'Lowercase',
						'type' => 'checkbox',
						'description' => 'Lowercase tag/attribute names to improve Gzip',
						'default' => true
					],
					'singleton' => [
						'label' => 'Singleton Tags',
						'type' => 'checkbox',
						'description' => 'Remove trailing slash from singletons',
						'default' => true
					],
					'close' => [
						'label' => 'Closing Tags',
						'type' => 'checkbox',
						'description' => 'Omit closing tags where possible',
						'default' => true
					]
				]
			],
			'attributes' => [
				'tab' => 'HTML',
				'name' => 'Attribute Minification',
				'html' => '<p>Edit how HTML attributes are minified. <em>Note that syntactically these optimisations are safe, but if your CSS or Javascript depends on attributes or values being there, these options may cause styles or Javascript not to work as expected. <a href="https://github.com/hexydec/htmldoc/blob/master/docs/mitigating-side-effects.md" target="_blank">Find out more here</a>.</em></p>',
				'options' => [
					'quotes' => [
						'label' => 'Attribute Quotes',
						'type' => 'checkbox',
						'description' => 'Remove quotes from attributes where possible, also unifies the quote style',
						'default' => true
					],
					'attributes_trim' => [
						'label' => 'Trim Attributes',
						'type' => 'checkbox',
						'description' => 'Trims whitespace from the start and end of attribute values',
						'default' => true
					],
					'attributes_boolean' => [
						'label' => 'Boolean Attributes', // minify boolean attributes
						'type' => 'checkbox',
						'description' => 'Minify boolean attributes',
						'default' => true
					],
					'attributes_default' => [
						'label' => 'Default Values',
						'type' => 'checkbox',
						'description' => 'Remove attributes that specify the default value. Only enable this if your CSS doesn\'t rely on the attributes being there',
						'default' => false
					],
					'attributes_empty' => [
						'label' => 'Empty Attributes',
						'type' => 'checkbox',
						'description' => 'Remove empty attributes where possible. Only enable this if your CSS doesn\'t rely on the attributes being there',
						'default' => false
					],
					'attributes_option' => [
						'label' => '<option> Tag',
						'type' => 'checkbox',
						'description' => 'Remove "value" Attribute from <option> where the value and the textnode are equal',
						'default' => true
					],
					'attributes_style' => [
						'label' => 'Style Attribute', // minify the style tag
						'type' => 'checkbox',
						'description' => 'Minify styles in the "style" attribute',
						'default' => true
					],
					'attributes_class' => [
						'label' => 'Minify Class Names', // sort classes
						'type' => 'checkbox',
						'description' => 'Removes unnecessary whitespace from the class attribute',
						'default' => true
					],
					'attributes_sort' => [
						'label' => 'Sort Attributes', // sort attributes for better gzip
						'type' => 'checkbox',
						'description' => 'Sort attributes into most used order for better gzip compression',
						'default' => true
					]
				]
			],
			'urls' => [
				'tab' => 'HTML',
				'name' => 'URL Minification',
				'html' => '<p>Edit how URLs are minified. <em>Note that syntactically these optimisations are safe, but if your CSS or Javascript depends on your URL\'s being a certain structure, these options may cause styles or Javascript not to work as expected. <a href="https://github.com/hexydec/htmldoc/blob/master/docs/mitigating-side-effects.md" target="_blank">Find out more here</a>.</em></p>',
				'options' => [
					'urls_scheme' => [
						'label' => 'Scheme', // remove the scheme from URLs that have the same scheme as the current document
						'type' => 'checkbox',
						'description' => 'Remove the scheme where it is the same as the current document',
						'default' => true
					],
					'urls_host' => [
						'label' => 'Internal Links', // remove the host for own domain
						'type' => 'checkbox',
						'description' => 'Remove hostname from internal links',
						'default' => true
					],
					'urls_relative' => [
						'label' => 'Absolute URLs', // process absolute URLs to make them relative to the current document
						'type' => 'checkbox',
						'description' => 'Make absolute URLs relative to current document',
						'default' => true
					],
					'urls_parent' => [
						'label' => 'Parent URLs', // process relative URLs to use relative parent links where it is shorter
						'type' => 'checkbox',
						'description' => 'Use "../" to reference parent URLs where shorter',
						'default' => true
					]
				]
			],
			'comments' => [
				'tab' => 'HTML',
				'name' => 'Comment Minification',
				'html' => '<p>Edit how HTML comments are minified.</p>',
				'options' => [
					'comments_remove' => [
						'label' => 'Comments',
						'type' => 'checkbox',
						'description' => 'Remove Comments',
						'default' => true
					],
					'comments_ie' => [
						'label' => 'Internet Explorer',
						'type' => 'checkbox',
						'description' => 'Preserve Internet Explorer specific comments (unless you need to support IE, you should turn this off)',
						'default' => false
					]
				]
			],
			'style' => [
				'tab' => 'CSS',
				'name' => 'CSS Minification',
				'desc' => 'Edit the CSS minification options',
				'html' => '<p>Manage how inline CSS and combined CSS files are minified.</p>',
				'options' => [
					'style_selectors' => [
						'label' => 'Selectors',
						'type' => 'checkbox',
						'description' => 'Minify selectors, makes ::before and ::after only have one semi-colon, and removes quotes from attrubte selectors where possible',
						'default' => true
					],
					'style_semicolons' => [
						'label' => 'Semicolons',
						'type' => 'checkbox',
						'description' => 'Remove last semicolon from each rule (e.g. #id{display:block;} becomes #id{display:block})',
						'default' => true
					],
					'style_zerounits' => [
						'label' => 'Zero Units',
						'type' => 'checkbox',
						'description' => 'Remove unit declaration from zero values (e.g. 0px becomes 0)',
						'default' => true
					],
					'style_leadingzero' => [
						'label' => 'Leading Zero\'s',
						'type' => 'checkbox',
						'description' => 'Remove Leading Zero\'s (e.g 0.3s becomes .3s)',
						'default' => true
					],
					'style_trailingzero' => [
						'label' => 'Trailing Zero\'s',
						'type' => 'checkbox',
						'description' => 'Remove Trailing Zero\'s (e.g 14.0pt becomes 14pt)',
						'default' => true
					],
					'style_decimalplaces' => [
						'label' => 'Decimal Places',
						'type' => 'number',
						'description' => 'Reduce the maximum number of decimal places a value can have to 4',
						'default' => 4
					],
					'style_multiple' => [
						'label' => 'Multiples',
						'type' => 'checkbox',
						'description' => 'Reduce the specified units where values match, such as margin/padding/border-width (e.g. margin: 10px 10px 10px 10px becomes margin:10px)',
						'default' => true
					],
					'style_quotes' => [
						'label' => 'Quotes',
						'type' => 'checkbox',
						'description' => 'Remove quotes where possible (e.g. url("torque.png") becomes url(torque.png))',
						'default' => true
					],
					'style_convertquotes' => [
						'label' => 'Quote Style',
						'type' => 'checkbox',
						'description' => 'Convert quotes to the same quote style (e.g. @charset \'utf-8\' becomes @charset "utf-8")',
						'default' => true
					],
					'style_colors' => [
						'label' => 'Colours',
						'type' => 'checkbox',
						'description' => 'Shorten hex values or replace with colour names where shorter (e.g. #FF6600 becomes #F60 and #FF0000 becomes red)',
						'default' => true
					],
					'style_time' => [
						'label' => 'Colours',
						'type' => 'checkbox',
						'description' => 'Shorten time values where shorter (e.g. 500ms becomes .5s)',
						'default' => true
					],
					'style_fontweight' => [
						'label' => 'Font Weight',
						'type' => 'checkbox',
						'description' => 'Shorten font weight values (e.g. font-weight: bold; becomes font-weight:700)',
						'default' => true
					],
					'style_none' => [
						'label' => 'None Values',
						'type' => 'checkbox',
						'description' => 'Shorten the value none to 0 where possible (e.g. border: none; becomes border:0)',
						'default' => true
					],
					'style_lowerproperties' => [
						'label' => 'Properties',
						'type' => 'checkbox',
						'description' => 'Lowercase property names (e.g. FONT-SIZE: 14px becomes font-size:14px)',
						'default' => true
					],
					'style_lowervalues' => [
						'label' => 'Values',
						'type' => 'checkbox',
						'description' => 'Lowercase values where possible (e.g. display: BLOCK becomes display:block)',
						'default' => true
					],
					'style_cache' => [
						'label' => 'Cache',
						'type' => 'checkbox',
						'description' => 'Cache minified output for faster execution',
						'default' => true
					]
				]
			],
			'script' => [
				'tab' => 'Javascript',
				'name' => 'Javascript Minification',
				'desc' => 'Edit the Javascript minification options',
				'html' => '<p>Manage how inline Javascript and combined Javascript\'s are minified.</p>',
				'options' => [
					'script_whitespace' => [
						'label' => 'Whitespace', // strip whitespace around javascript
						'type' => 'checkbox',
						'description' => 'Remove unnecessary whitespace',
						'default' => true
					],
					'script_comments' => [
						'label' => 'Comments', // strip comments
						'type' => 'checkbox',
						'description' => 'Remove single-line and multi-line comments',
						'default' => true
					],
					'script_semicolons' => [
						'label' => 'Semicolons',
						'type' => 'checkbox',
						'description' => 'Remove semicolons where possible (e.g. ()=>{return "foo";} becomes ()=>{return "foo"})',
						'default' => true
					],
					'script_quotestyle' => [
						'label' => 'Quote Style',
						'type' => 'checkbox',
						'description' => 'Convert quotes to the same quote style (All quotes become double quotes for better gzip)',
						'value' => '"',
						'default' => '"'
					],
					'script_booleans' => [
						'label' => 'Booleans',
						'type' => 'checkbox',
						'description' => 'Shorten booleans (e.g. true beomes !0 and false becomes !1)',
						'default' => true
					],
					'script_numbers' => [
						'label' => 'Numbers',
						'type' => 'checkbox',
						'description' => 'Remove underscores from numbers',
						'default' => true
					],
					'script_cache' => [
						'label' => 'Cache',
						'type' => 'checkbox',
						'description' => 'Cache minified output for faster execution',
						'default' => true
					]
				]
			],
			'cache' => [
				'tab' => 'Caching',
				'name' => 'Caching',
				'desc' => 'Edit the browser cache settings',
				'html' => '<p>Control how proxies cache your site, and how browsers cache it.</p>',
				'options' => [
					'maxage' => [
						'label' => 'Browser Cache',
						'description' => 'How long browsers should cache webpages for, we recommend setting this to 0',
						'type' => 'number',
						'default' => 0
					],
					'smaxage' => [
						'label' => 'Proxy Cache',
						'description' => 'If using a front-end cache such as Nginx caching, Varnish, or Cloudflare, this tells them how long to cache content for',
						'type' => 'number',
						'default' => 600
					],
					'etags' => [
						'label' => 'Use Etags',
						'description' => 'If a client reequests a page already in cache, if the page on the server is the same, tell the client to use the cache',
						'type' => 'checkbox',
						'default' => true
					]
				]
			],
			'security' => [
				'tab' => 'Security',
				'name' => 'Security',
				'desc' => 'Edit the website security settings',
				'html' => '<p>Implement browser security features on your website.</p>',
				'options' => [
					'typeoptions' => [
						'label' => 'Set X-Content-Type-Options',
						'description' => 'Tells the browser to use the advertised MIME type when deciding how to present content, without this the browser may "sniff" the content type, which may allow content to be delivered as a non-executable type, when the content is infact executable',
						'type' => 'checkbox',
						'default' => false
					],
					'xssprotection' => [
						'label' => 'Set X-XSS-Protection',
						'description' => 'Allows older browsers to block XSS attacks in certain circumstances (Set Content Security Polcy for modern browsers)',
						'type' => 'checkbox',
						'default' => false
					],
					'embedding' => [
						'label' => 'Website Embedding',
						'description' => 'Specifies where the site can be embedded in an iframe, prevents other websites from wrapping your site and presenting the content as their own',
						'type' => 'select',
						'values' => [
							['id' => 'allow', 'name' => 'Allow site to be embedded'],
							['id' => 'deny', 'name' => 'Prevent site from being embedded'],
							['id' => 'sameorigin', 'name' => 'Allow to be embedded on this domain only']
						],
						'default' => 'allow'
					],
					'hsts' => [
						'label' => 'Force SSL', // Strict-Transport-Security
						'description' => 'Tells the browser to only access this site with a valid SSL certificate, you must deliver your site over HTTPS to use this',
						'type' => 'select',
						'values' => [
							['id' => 0, 'name' => 'Don\'t force SSL'],
							['id' => 60, 'name' => '1 minute (For testing)'],
							['id' => 15768000, 'name' => '6 Months'],
							['id' => 31536000, 'name' => '1 Year'],
							['id' => 63072000, 'name' => '2 Years']
						],
						'default' => 0
					]
				]
			],
			'csp' => [
				'tab' => 'Policy',
				'name' => 'Content Security Policy',
				'desc' => 'Manage your website\'s Content Security Policy',
				'html' => '<p>Controls what domains your site is allowed to connect and load assets from. Use "\'self\'" for the current domain, "\'unsafe-inline\'" to allow inline scripts or style, and "data:" to allow data URI\'s. <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy" target="_blank">See MDN for more options</a>.</p>
				<p class="torque-csp__warning">Note: The recommendation engine only makes suggestions based on violation reports, make sure you know what assets generated the report before adding it to your policy.</p>',
				'options' => [
					'csp_setting' => [
						'label' => 'Status',
						'description' => 'Test your CSP thoroughly before deploying, as it can break your website if not setup correctly',
						'type' => 'select',
						'values' => [
							['id' => 'disabled', 'name' => 'Disabled'],
							['id' => 'enabled', 'name' => 'Enabled'],
							['id' => \get_current_user_id(), 'name' => 'Enabled for me only (Testing)']
						],
						'default' => 'disabled'
					],
					'csp_reporting' => [
						'label' => 'Log Violations',
						'description' => 'Record violations in a log file, use this to get recommendations',
						'type' => 'checkbox',
						'default' => false
					],
					'csp_default' => [
						'label' => 'Default Sources',
						'description' => 'A list of hosts that serves as a fallback to when there are no specific settings',
						'type' => 'list',
						'default' => "'self'",
						'attributes' => [
							'class' => 'torque-csp__control'
						]
					],
					'csp_style' => [
						'label' => 'Style Sources',
						'description' => 'A list of hosts that are allowed to link stylesheets. Note that you will probably need \'unsafe-inline\' as Wordpress embeds CSS styles by default, and your plugins are likely too also',
						'type' => 'list',
						'default' => "'self'\ndata:\n'unsafe-inline'",
						'attributes' => [
							'class' => 'torque-csp__control'
						]
					],
					'csp_script' => [
						'label' => 'Script Sources',
						'description' => 'A list of hosts that are allowed to link scripts. Note that you will probably need \'unsafe-inline\' as Wordpress embeds Javascripts by default, and your plugins are likely too also (<a href="https://blog.teamtreehouse.com/unobtrusive-javascript-important" target="_blank">Even though they shouldn\'t</a>)',
						'descriptionhtml' => true,
						'type' => 'list',
						'default' => "'self'\n'unsafe-inline'",
						'attributes' => [
							'class' => 'torque-csp__control'
						]
					],
					'csp_image' => [
						'label' => 'Image Sources',
						'description' => 'A list of hosts that are allowed to link images',
						'type' => 'list',
						'default' => '',
						'attributes' => [
							'class' => 'torque-csp__control'
						]
					],
					'csp_font' => [
						'label' => 'Font Sources',
						'description' => 'Specifies valid sources for fonts loaded using @font-face',
						'type' => 'list',
						'default' => '',
						'attributes' => [
							'class' => 'torque-csp__control'
						]
					],
					'csp_media' => [
						'label' => 'Media Sources',
						'description' => 'Specifies valid sources for loading media using the <audio>, <video> and <track> elements',
						'type' => 'list',
						'default' => '',
						'attributes' => [
							'class' => 'torque-csp__control'
						]
					],
					'csp_object' => [
						'label' => 'Object Sources',
						'description' => 'Specifies valid sources for the <object>, <embed>, and <applet> elements',
						'type' => 'list',
						'default' => '',
						'attributes' => [
							'class' => 'torque-csp__control'
						]
					],
					'csp_frame' => [
						'label' => 'Frame Sources',
						'description' => 'Specifies valid sources for nested browsing contexts using the <frame> and <iframe> elements',
						'type' => 'list',
						'default' => '',
						'attributes' => [
							'class' => 'torque-csp__control'
						]
					],
					'csp_connect' => [
						'label' => 'Connect Sources',
						'description' => 'Restricts the URLs which can be loaded using script interfaces',
						'type' => 'list',
						'default' => '',
						'attributes' => [
							'class' => 'torque-csp__control'
						]
					],
					'csp_wipelog' => [
						'label' => 'Wipe Log',
						'description' => 'Once you have implemented the recommendations, you should wipe the log to see how your new setup works',
						'type' => 'checkbox',
						'default' => false,
						'onsave' => function (bool $value) : bool {
							if ($value) {
								$report = $this->config['output'].$this->config['csplog'];
								return \file_put_contents($report, '') !== false;
							}
							return false;
						},
						'save' => false
					]
				]
			],
			'preload' => [
				'tab' => 'Preload',
				'name' => 'Asset Preloading',
				'desc' => 'Edit the asset preloading settings',
				'html' => '<p>Notifies the browser as soon as possible of assets it will need to load the page, this enables it to start downloading them sooner than if it discovered them on page. For example font files are normally linked from the stylesheet, so the browser has to download and parse the stylesheet before it can request them. By preloading, when it discovers that it needs those assets, they will already be downloading. Thus your website will load faster.</p>',
				'options' => [
					'preload' => [
						'label' => 'Preload Assets',
						'description' => 'Select which assets to preload, make sure to pick assets that appear on EVERY page',
						'type' => 'multiselect',
						'default' => [],
						'datasource' => function () use ($url) : array|false {
							if (($assets = assets::getPageAssets($url)) !== false) {
				
								// get style that are combined, to disallow in preload
								$options = \get_option(self::SLUG);
								$skip = $options['combinestyle'] ?? [];
				
								// filter the available files, no point in preloading scripts, just defer them
								$filtered = [];
								foreach ($assets AS $item) {
									if ($item['group'] !== 'Scripts' && !\in_array($item['name'], $skip)) {
										$filtered[] = $item;
									}
								}
								return $filtered;
							}
							return false;
						}
					],
					'preloadstyle' => [
						'label' => 'Preload Combined Stylesheet',
						'description' => 'If you have enable the combined stylesheets, select this to preload it',
						'type' => 'checkbox',
						'default' => false
					]
				]
			]
		];

		// add CSP recommendations
		$fields = [
			'csp_style' => 'style-src',
			'csp_script' => 'script-src',
			'csp_image' => 'image-src',
			'csp_font' => 'font-src',
			'csp_media' => 'media-src',
			'csp_object' => 'object-src',
			'csp_frame' => 'frame-src',
			'csp_connect' => 'connect-src',
		];
		$report = $this->config['output'].$this->config['csplog'];
		foreach ($fields AS $key => $item) {
			$this->options['csp']['options'][$key]['after'] = function () use ($item, $report) : ?string {
				$content = [
					'type' => $item,
					'recommendations' => csp::recommendations($report, $item),
					'violations' => csp::violations($report, $item)
				];
				return admin::compile($content, __DIR__.'/templates/csp-recommendations.php');
			};
		}
	}

	/**
	 * Builds the configuration into the save format
	 *
	 * @param array $values The flattened config array that is returned by the client
	 * @return array The config array as it will be stored
	 */
	public function buildConfig(array $values = []) : array {
		if (($current = \get_option(self::SLUG)) === false) {
			$current = [];
		}
		$config = [];
		foreach ($this->options AS $option) {
			foreach ($option['options'] AS $key => $item) {
				if ($item['save'] ?? true) {

					// build the options in the format HTMLdoc expects
					$parts = \explode('_', $key, 2);

					// root level
					if (!isset($parts[1])) {
						if (isset($values[$key])) {
							$config[$parts[0]] = $values[$key];
						} elseif (isset($current[$parts[0]])) {
							$config[$parts[0]] = $current[$parts[0]];
						} else {
							$config[$parts[0]] = $item['default'] ?? null;
						}

					// sub levels
					} else {
						if (!isset($config[$parts[0]]) || !\is_array($config[$parts[0]])) {
							$config[$parts[0]] = [];
						}
						if (isset($values[$key])) {
							$config[$parts[0]][$parts[1]] = $values[$key];
						} elseif (isset($current[$parts[0]][$parts[1]])) {
							$config[$parts[0]][$parts[1]] = $current[$parts[0]][$parts[1]];
						} else {
							$config[$parts[0]][$parts[1]] = $item['default'] ?? null;
						}
					}
				}
			}
		}
		return $config;
	}

	/**
	 * Retrieves a list of tab keys
	 *
	 * @return array An array of tab keys
	 */
	protected function getTabs() : array {
		$tabs = [];
		$keys = [];
		foreach ($this->options AS $key => $item) {
			if (!\in_array($item['tab'], $tabs)) {
				$tabs[] = $item['tab'];
				$keys[] = $key;
			}
		}
		return $keys;
	}
}
