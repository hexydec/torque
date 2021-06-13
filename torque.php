<?php
namespace hexydec\wordpress;
/*
Plugin Name: Torque - Optimise the transport of your Website
Plugin URI:  https://github.com/hexydec/htmldoc-wordpress
Description: Take your website optimisation to the next level! Other minification plugins blindly find and replace patterns within your code to make it smaller, often using outdated 3rd-party libraries. <strong>Torque is a compiler</strong>, it parses your code to an internal representation, optimises it, and compiles it back to code. The result is better reliability, compression, and performance. Add in header management and other tools, your website will be noticably faster!
Version:     0.3.1
Requires PHP: 7.3
Author:      Hexydec
Author URI:  https://github.com/hexydec/
License:     GPL
License URI: https://github.com/hexydec/htmldoc/blob/master/LICENSE
*/

class htmldoc {

	/**
	 * @var string $slug The slug for the configuration page
	 */
	protected const SLUG = 'torque';

	/**
	 * @var string $slug The slug for the configuration page
	 */
	protected const VERSION = '0.3.1';

	/**
	 * @var array $packages A list of external dependencies to be installed when the plugin is activated
	 */
	protected $packages = [
		'htmldoc' => [
			'file' => 'https://github.com/hexydec/htmldoc/archive/refs/tags/v1.2.5.zip',
			'dir' => __DIR__.'/htmldoc-v1.2.5/',
			'autoload' => 'src/autoload.php'
		],
		'cssdoc' => [
			'file' => 'https://github.com/hexydec/cssdoc/archive/refs/tags/0.5.1.zip',
			'dir' => __DIR__.'/cssdoc-0.5.1/',
			'autoload' => 'src/autoload.php'
		],
		'jslite' => [
			'file' => 'https://github.com/hexydec/jslite/archive/refs/tags/0.5.1.zip',
			'dir' => __DIR__.'/jslite-0.5.1/',
			'autoload' => 'src/autoload.php'
		],
		'tokenise' => [
			'file' => 'https://github.com/hexydec/tokenise/archive/refs/tags/0.4.1.zip',
			'dir' => __DIR__.'/tokenise-0.4.1/',
			'autoload' => 'src/autoload.php'
		]
	];

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
					'description' => 'Enable minification of inline CSS within a <style> tag',
					'default' => true
				],
				'minifyscript' => [
					'label' => 'Minify Javascript',
					'type' => 'checkbox',
					'description' => 'Enable minification of inline Javascript within a <script> tag',
					'default' => true
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
			'name' => 'HTML Basic Minification',
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
			'tab' => 'Attributes',
			'name' => 'HTML Attribute Minification',
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
			'tab' => 'URL\'s',
			'name' => 'HTML URL Minification',
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
			'tab' => 'Comments',
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
		]
	];
	protected $tab;

	/**
	 * Registers the plugins with the relevant workpress hooks
	 *
	 * @return void
	 */
	public function install() : void {
		register_activation_hook(__FILE__, [$this, 'activate']);
		add_action('admin_init', [$this, 'initAdmin']);
		add_action('admin_menu', [$this, 'setAdminMenu']);
		add_action('wp_loaded', [$this, 'minify']);
		register_uninstall_hook(__FILE__, __CLASS__.'::uninstall');
	}

	/**
	 * Activates the plugin
	 *
	 * @return void
	 */
	public function activate() : void {

		// reserve a temporary file
		if (($tmp = tempnam(sys_get_temp_dir(), 'hxd')) === false) {
			wp_die('Could not create temporary file');
		} else {

			// delete all directories
			$this->cleanupDirectories(__DIR__, '/.git');

			// install external assets
			$zip = new \ZipArchive();
			foreach ($this->packages AS $key => $item) {
				$dir = __DIR__;

				// download the asset bundle and copy to temp
				if (!copy($item['file'], $tmp)) {
					wp_die('Plugin activation failed: Could not download file "'.$item['file'].'". Is URL FOpen enabled?');

				// open the zip file
				} elseif (!$zip->open($tmp) === true) {
					wp_die('Plugin activation failed: Could not open file "'.$item['file'].'"');

				// extract the files
				} elseif (!$zip->extractTo($dir)) {
					wp_die('Plugin activation failed: Could not extract file "'.$item['file'].'"');
				}
			}

			// install the config options
			$config = $this->buildConfig();
			update_option(self::SLUG, $config, true);
		}
	}

	/**
	 * Removes an existing directories from the plugin folder
	 *
	 * @param string $dir The absolute address of the plugin directory
	 * @param string $exclude A directory name relative to $dir that shold be excluded from deletion
	 * @return bool Whether any files or directories were removed
	 */
	protected function cleanupDirectories(string $dir, ?string $exclude = null) : bool {
		$deleted = false;
		$dir = str_replace('\\', '/', $dir);
		$it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
		$len = strlen($dir) + 1;
		foreach ($files AS $item) {
			$path = str_replace('\\', '/', $item->getRealPath());
			if (!$exclude || mb_strpos($path, $dir.$exclude) !== 0) {
				if ($item->isDir()) {
					rmdir($path);
					$deleted = true;
				} elseif (strlen($path) > $len && strpos($path, '/', $len) !== false) {
					unlink($path);
					$deleted = true;
				}
			}
		}
		return $deleted;
	}

	protected function getTabs() {
		$tabs = [];
		$keys = [];
		foreach ($this->options AS $key => $item) {
			if (!in_array($key, $keys)) {
				$keys[] = $item['tab'];
				$tabs[] = $key;
			}
		}
		return $tabs;
	}

	/**
	 * Renders the plugin administration panel
	 *
	 * @return void
	 */
	public function initAdmin() : void {
		$tabs = $this->getTabs();
		$tab = $_POST['tab'] ?? ($_GET['tab'] ?? null);
		$tab = $this->tab = isset($tab) && in_array($tab, $tabs) ? $tab : $tabs[0];

		// register field controls
		register_setting(self::SLUG, self::SLUG, [
			'sanitize_callback' => function (array $value = null) use ($tab) {
				$options = [];
				foreach ($this->options AS $i => $option) {
					foreach ($option['options'] AS $key => $item) {
						if ($i === $tab) {

							// get the value
							if ($item['type'] === 'checkbox') {
								$options[$key] = empty($value[$key]) ? false : ($item['value'] ?? true);
							} elseif ($item['type'] === 'number') {
								if (isset($value[$key]) && is_numeric($value[$key]) && $value[$key] >= 0) {
									$options[$key] = $value[$key];
								} else {
									add_settings_error(self::SLUG, self::SLUG, 'The value entered for '.$item['label'].' is invalid');
								}
							}
						}
					}
				}
				return $this->buildConfig($options);
			}
		]);
	    $options = get_option(self::SLUG);

		// render field controls
		foreach ($this->options AS $g => $group) {
			if ($g === $tab) {

				// add section
				add_settings_section(self::SLUG.'_options_'.$g, $group['name'], function () use ($group) {
					echo $group['desc'];
				}, self::SLUG);

				// add options
				foreach ($group['options'] AS $key => $item) {

					// get the current setting
					$parts = \explode('_', $key, 2);
					if (isset($parts[1])) {
						$value = $options[$parts[0]][$parts[1]] ?? $item['default'];
					} elseif (isset($options[$parts[0]])) {
						$value = $options[$parts[0]];
					} else {
						$value = $item['default'] ?? true;
					}
					add_settings_field($key, htmlspecialchars($item['label']), function () use ($key, $value, $item) {
						$checkbox = $item['type'] === 'checkbox'; ?>
						<input type="<?= $item['type']; ?>" id="<?= htmlspecialchars(self::SLUG.'-'.$key); ?>" name="<?= htmlspecialchars(self::SLUG.'['.$key.']'); ?>" value="<?= $checkbox ? '1' : htmlspecialchars($value); ?>"<?= $checkbox && $value ? ' checked="checked"' : ''; ?> />
						<label for="<?= htmlspecialchars(self::SLUG.'-'.$key); ?>"><?= htmlspecialchars($item['description']); ?></label>
					<?php }, self::SLUG, self::SLUG.'_options_'.$g);
				}
			}
		}
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

	/**
	 * Adds the configuration link to the admin menu
	 *
	 * @return void
	 */
	public function setAdminMenu() : void {
		add_options_page('Torque - Optimise the transport of your website', 'Torque', 'manage_options', self::SLUG, function () { ?>
			<h1>Torque Configuration</h1>
			<form action="options.php" method="post" accept-charset="<?= htmlspecialchars(mb_internal_encoding()); ?>">
				<input type="hidden" name="tab" value="<?= htmlspecialchars($this->tab); ?>" />
				<nav class="nav-tab-wrapper">
			        <?php
					$tabs = [];
					foreach ($this->options AS $key => $item) {
						if (!in_array($key, $tabs)) {
							$tabs[] = $item['tab'];
							?><a href="?page=<?= self::SLUG; ?>&amp;tab=<?= $key; ?>" class="nav-tab<?= $key === $this->tab ? ' nav-tab-active' : '' ?>" title="<?= htmlspecialchars($item['desc']); ?>"><?= htmlspecialchars($item['tab']); ?></a><?php
						}
					} ?>
				</nav>
				<?php
		        settings_fields(self::SLUG);
		        do_settings_sections(self::SLUG);
				submit_button(); ?>
	    	</form>
		<?php });
	}

	/**
	 * uninstalls the plugin
	 *
	 * @return void
	 */
	public static function uninstall() : void {
		delete_option(self::SLUG);
	}

	/**
	 * Renders Javascript that outputs the minification stats to the console
	 *
	 * @param array $options The stored configuration for this plugin
	 * @return array A configuration array for the HTMLdoc object
	 */
	protected function getConfig(array $options) : array {
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

	/**
	 * Minifies the page
	 *
	 * @return void
	 */
	public function minify() : void {

		// are we going to minify the page?
		if (($options = get_option(self::SLUG)) !== false && (!empty($options['admin']) || !is_admin())) {

			// turn off default cache control header
			if (($options['maxage'] ?? null) !== null) {
				\session_cache_limiter('');
			}

			// autoload files
			foreach ($this->packages AS $item) {
				if (\file_exists($item['dir'].$item['autoload'])) {
					require($item['dir'].$item['autoload']);
				}
			}

			// create output buffer
			\ob_start(function ($html) use ($options) {

				// make sure the output is text/html, so we are not trying to minify javascript or something
				foreach (headers_list() AS $item) {
					if (mb_stripos($item, 'Content-Type:') === 0 && mb_stripos($item, 'Content-Type: text/html') === false) {
						return false;
					}
				}

				// only load HTMLdoc if minifying or lazyloading
				if (\class_exists('\\hexydec\\html\\htmldoc') && (!empty($options['minifyhtml']) || !empty($options['lazyload']))) {

					// collect timing for stats
					$timing = ['Initialise' => \microtime(true)];

					// get the config and create the object
					$config = $this->getConfig($options);
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
						if (!empty($options['stats'])) {
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

$obj = new htmldoc();
$obj->install();
