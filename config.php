<?php
namespace hexydec\torque;

class config extends packages {

	/**
	 * @var array $options A list of configuration options for the plugin
	 */
	protected $options = [
		'settings' => [
			'tab' => 'Settings',
			'name' => 'Plugin Options',
			'desc' => 'Edit the plugin settings',
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
					'default' => true
				],
				'combinestyle' => [
					'label' => 'Combine CSS',
					'type' => 'multiselect',
					'description' => 'Select which CSS files to combine and minify',
				],
				'minifyscript' => [
					'label' => 'Minify Javascript',
					'type' => 'checkbox',
					'description' => 'Enable minification of inline Javascript within a <script> tag and combined scripts',
					'default' => true
				],
				'combinescripts' => [
					'label' => 'Combine Javascript',
					'type' => 'multiselect',
					'description' => 'Select which Javascript files to combine and minify',
				],
				'lazyload' => [
					'label' => 'Lazy Load Images',
					'type' => 'checkbox',
					'description' => 'Tell the browser to only load images when they are scrolled into view',
					'default' => true
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
					'description' => 'Show stats in the console (This will prevent 304 responses from working and should only be used for testing)',
					'default' => false
				]
			]
		],
		'html' => [
			'tab' => 'HTML',
			'name' => 'Basic Minification',
			'desc' => 'Edit the general HTML minification settings',
			'options' => [
				'whitespace' => [
					'label' => 'Whitespace',
					'type' => 'checkbox',
					'description' => 'Strip unnecessary whitespace',
					'default' => true
				],
				'lowercase' => [
					'label' => 'Tags/Attributes',
					'type' => 'checkbox',
					'description' => 'Lowercase tag/attribute names',
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
			'desc' => 'Edit how HTML attributes are minified. Note that syntactically these optimisations are safe, but if your CSS or Javascript depends on attributes or values being there, these options may cause styles or Javascript not to work as expected. <a href="https://github.com/hexydec/htmldoc/blob/master/docs/mitigating-side-effects.md" target="_blank">Find out more here</a>.',
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
					'description' => 'Trims whitespace fro the start and end of attribute values',
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
					'description' => 'Remove attributes that specify the default value',
					'default' => true
				],
				'attributes_empty' => [
					'label' => 'Empty Attributes',
					'type' => 'checkbox',
					'description' => 'Remove empty attributes where possible',
					'default' => true
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
			'desc' => 'Edit how URLs are minified. Note that syntactically these optimisations are safe, but if your CSS or Javascript depends on your URL\'s being a certain structure, these options may cause styles or Javascript not to work as expected. <a href="https://github.com/hexydec/htmldoc/blob/master/docs/mitigating-side-effects.md" target="_blank">Find out more here</a>.',
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
			'desc' => 'Edit how comments are minified',
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
			'desc' => 'Manage how inline CSS is minified',
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
					'description' => 'Remove quotes where possible (e.g. url("htmldoc.png") becomes url(htmldoc.png))',
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
			'desc' => 'Manage how inline Javascript is minified',
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
					'default' => true
				],
				'script_booleans' => [
					'label' => 'Booleans',
					'type' => 'checkbox',
					'description' => 'Shorten booleans (e.g. true beomes !0 and false becomes !1)',
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
		'headers' => [
			'tab' => 'Headers',
			'name' => 'Headers',
			'desc' => 'Control how your site site delivered, how proxies cache your site, and how browsers cache it',
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
			'desc' => 'Implement some security features on your website',
			'options' => [
				'typeoptions' => [
					'label' => 'Set X-Content-Type-Options',
					'description' => 'Tells the browser to use the advertised MIME type when deciding how to present content, without this the browser may "sniff" the content type, which may allow content to be delivered as a non-executable type, when the content is infact executable',
					'type' => 'checkbox',
					'default' => true
				],
				'xssprotection' => [
					'label' => 'Set X-XSS-Protection',
					'description' => 'Allows older browsers to block XSS attacks in certain circumstances (Set Content Security Polcy for modern browsers)',
					'type' => 'checkbox',
					'default' => true
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
					'default' => true
				],
				'hsts' => [
					'label' => 'Force SSL', // Strict-Transport-Security
					'description' => 'Tells the browser to only access this site with a valid SSL certificate, you must deliver your site over SSL to use this',
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
		'preload' => [
			'tab' => 'Preload',
			'name' => 'HTTP/2.0 Preload',
			'desc' => 'Push assets to the client on first load to make it appear faster',
			'options' => [
				'preload' => [
					'label' => 'Preload Assets',
					'description' => 'Select which assets to preload, make sure to pick assets that appear on EVERY page',
					'type' => 'multiselect',
				],
				'preloadstyle' => [
					'label' => 'Preload Combined Stylesheet',
					'description' => 'If you have selected to combine your stylesheets, select to preload it',
					'type' => 'checkbox',
					'default' => false
				]
			]
		]
	];

	public function __construct() {

		// bind data
		$url = get_home_url();
		$this->options['preload']['options']['preload']['datasource'] = function () use ($url) {
			if (($assets = self::getPageAssets($url)) !== false) {
				$filtered = [];
				foreach ($assets AS $item) {
					if ($item['group'] !== 'Scripts') {
						$filtered[] = $item;
					}
				}
				return $filtered;
			}
			return false;
		};
		$this->options['settings']['options']['combinestyle']['datasource'] = function () use ($url) {
			if (($assets = self::getPageAssets($url)) !== false) {
				$filtered = [];
				foreach ($assets AS $item) {
					if ($item['group'] === 'Stylesheets') {
						$filtered[] = $item;
					}
				}
				return $filtered;
			}
			return false;
		};
		$this->options['settings']['options']['combinescripts']['datasource'] = function () use ($url) {
			if (($assets = self::getPageAssets($url)) !== false) {
				$filtered = [];
				foreach ($assets AS $item) {
					if ($item['group'] === 'Scripts') {
						$filtered[] = $item;
					}
				}
				return $filtered;
			}
			return false;
		};
	}

	protected function getPageAssets(string $url) {
		static $assets = [];
		if (!isset($assets[$url])) {
			$assets[$url] = [];

			// parse the page
			$obj = new \hexydec\html\htmldoc();
			if ($obj->open($url)) {

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
						foreach ($nodes AS $node) {
							$name = strstr($node->attr($item['attr']), '?', true);
							$assets[$url][] = [
								'id' => $name,
								'group' => $key,
								'name' => $name
							];
						}
					}
				}
			}
		}
		return $assets[$url];
	}

	protected function buildConfig(array $values = []) {
		if (($config = get_option(self::SLUG)) === false) {
			$config = [];
		}
		foreach ($this->options AS $i => $option) {
			foreach ($option['options'] AS $key => $item) {

				// build the options in the format HTMLdoc expects
				$parts = \explode('_', $key, 2);

				// root level
				if (!isset($parts[1])) {
					if (isset($values[$key])) {
						$config[$parts[0]] = $values[$key];
					} elseif (!isset($config[$parts[0]])) {
						$config[$parts[0]] = $item['default'] ?? null;
					}

				// sub levels
				} else {
					if (!isset($options[$parts[0]]) || !is_array($options[$parts[0]])) {
						$config[$parts[0]] = [];
					}
					if (isset($values[$key])) {
						$config[$parts[0]][$parts[1]] = $values[$key];
					} elseif (!isset($config[$parts[0]][$parts[1]])) {
						$config[$parts[0]][$parts[1]] = $item['default'] ?? null;
					}
				}
			}
		}
		return $config;
	}

	protected function getTabs() {
		$tabs = [];
		$keys = [];
		foreach ($this->options AS $key => $item) {
			$tab = $item['tab'];
			if (!in_array($item['tab'], $tabs)) {
				$tabs[] = $item['tab'];
				$keys[] = $key;
			}
		}
		return $keys;
	}
}
