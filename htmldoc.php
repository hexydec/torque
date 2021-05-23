<?php
namespace hexydec\wordpress;
/*
Plugin Name: Hexydec HTMLdoc Minifier
Plugin URI:  https://github.com/hexydec/htmldoc-wordpress
Description: Take your website optimisation to the next level! Other minification plugins blindly find and replace patterns within your code to make it smaller, often using outdated 3rd-party libraries. <strong>HTMLdoc is a compiler</strong>, it parses your code to an internal representation, optimises it, and then compiles it back to code. The result is better reliability, compression, and performance. It also bundles CSSdoc and JSlite, compilers from the same author for minifying your inline CSS and Javascript.
Version:     0.2.0
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
	protected const SLUG = 'htmldoc';

	/**
	 * @var string $slug The slug for the configuration page
	 */
	protected const VERSION = '0.2.0';

	/**
	 * @var array $packages A list of external dependencies to be installed when the plugin is activated
	 */
	protected $packages = [
		'htmldoc' => [
			'file' => 'https://github.com/hexydec/htmldoc/archive/refs/tags/1.2.3.zip',
			'dir' => __DIR__.'/htmldoc-1.2.3/',
			'autoload' => 'src/autoload.php'
		],
		'cssdoc' => [
			'file' => 'https://github.com/hexydec/cssdoc/archive/refs/tags/0.3.0.zip',
			'dir' => __DIR__.'/cssdoc-0.3.0/',
			'autoload' => 'src/autoload.php'
		],
		'jslite' => [
			'file' => 'https://github.com/hexydec/jslite/archive/refs/tags/0.4.1.zip',
			'dir' => __DIR__.'/jslite-0.4.1/',
			'autoload' => 'src/autoload.php'
		],
		'tokenise' => [
			'file' => 'https://github.com/hexydec/tokenise/archive/refs/tags/0.4.0.zip',
			'dir' => __DIR__.'/tokenise-0.4.0/',
			'autoload' => 'src/autoload.php'
		]
	];

	/**
	 * @var array $options A list of configuration options for the plugin
	 */
	protected $options = [
		'options' => [
			'name' => 'Plugin Options',
			'desc' => 'Edit the plugin settings',
			'options' => [
				'minifyhtml' => [
					'label' => 'Minify HTML',
					'description' => 'Enables or disables the whole plugin',
					'default' => false
				],
				'minifystyle' => [
					'label' => 'Minify CSS',
					'description' => 'Enable minification of inline CSS within a <style> tag'
				],
				'minifyscript' => [
					'label' => 'Minify Javascript',
					'description' => 'Enable minification of inline Javascript within a <script> tag'
				],
				'admin' => [
					'label' => 'Minify Admin System',
					'description' => 'Minify the admin system',
					'default' => false
				],
				'stats' => [
					'label' => 'Show Stats',
					'description' => 'Show stats in the console',
					'default' => false
				]
			]
		],
		'basic' => [
			'name' => 'HTML Basic Minification',
			'desc' => 'Edit the general HTML minification settings',
			'options' => [
				'whitespace' => [
					'label' => 'Whitespace',
					'description' => 'Strip unnecessary whitespace'
				],
				'lowercase' => [
					'label' => 'Tags/Attributes',
					'description' => 'Lowercase tag/attribute names'
				],
				'singleton' => [
					'label' => 'Singleton Tags',
					'description' => 'Remove trailing slash from singletons'
				],
				'close' => [
					'label' => 'Closing Tags',
					'description' => 'Omit closing tags where possible'
				]
			]
		],
		'attributes' => [
			'name' => 'HTML Attribute Minification',
			'desc' => 'Edit how HTML attributes are minified',
			'options' => [
				'quotes' => [
					'label' => 'Attribute Quotes',
					'description' => 'Remove quotes from attributes where possible, also unifies the quote style'
				],
				'attributes_trim' => [
					'label' => 'Trim Attributes',
					'description' => 'Trims whitespace fro the start and end of attribute values'
				],
				'attributes_default' => [
					'label' => 'Default Values',
					'description' => 'Remove attributes that specify the default value'
				],
				'attributes_empty' => [
					'label' => 'Empty Attributes',
					'description' => 'Remove empty attributes where possible'
				],
				'attributes_option' => [
					'label' => '<option> Tag',
					'description' => 'Remove "value" Attribute from <option> where the value and the textnode are equal'
				],
				'attributes_style' => [
					'label' => '<style> Tag', // minify the style tag
					'description' => 'Minify inline styles in the <style> tag'
				],
				'attributes_sort' => [
					'label' => 'Sort Attributes', // sort attributes for better gzip
					'description' => 'Sort attributes into most used order for better gzip compression'
				],
				'attributes_class' => [
					'label' => 'Minify Class Names', // sort classes
					'description' => 'Removes unnecessary whitespace from the class attribute'
				],
				'attributes_boolean' => [
					'label' => 'Boolean Attributes', // minify boolean attributes
					'description' => 'Minify boolean attributes'
				]
			]
		],
		'urls' => [
			'name' => 'HTML URL Minification',
			'desc' => 'Edit how URLs are minified',
			'options' => [
				'urls_scheme' => [
					'label' => 'Scheme', // remove the scheme from URLs that have the same scheme as the current document
					'description' => 'Remove the scheme where it is the same as the current document'
				],
				'urls_host' => [
					'label' => 'Internal Links', // remove the host for own domain
					'description' => 'Remove hostname from internal links'
				],
				'urls_relative' => [
					'label' => 'Absolute URLs', // process absolute URLs to make them relative to the current document
					'description' => 'Make absolute URLs relative to current document'
				],
				'urls_parent' => [
					'label' => 'Parent URLs', // process relative URLs to use relative parent links where it is shorter
					'description' => 'Use "../" to reference parent URLs where shorter'
				]
			]
		],
		'comments' => [
			'name' => 'Comment Minification',
			'desc' => 'Edit how comments are minified',
			'options' => [
				'comments_remove' => [
					'label' => 'Comments',
					'description' => 'Remove Comments'
				],
				'comments_ie' => [
					'label' => 'internet Explorer',
					'description' => 'Preserve Internet Explorer specific comments'
				]
			]
		],
		'style' => [
			'name' => 'CSS Minification',
			'desc' => 'Manage how inline CSS is minified',
			'options' => [
				'style_semicolons' => [
					'label' => 'Semicolons',
					'description' => 'remove last semicolon from each rule'
				],
				'style_zerounits' => [
					'label' => 'Zero Units',
					'description' => 'Remove unit declaration from zero values (e.g. 0px becomes 0)'
				],
				'style_leadingzero' => [
					'label' => 'Leading Zero\'s',
					'description' => 'Remove Leading Zero\'s (e.g 0.3s becomes .3s)'
				],
				'style_quotes' => [
					'label' => 'Quotes',
					'description' => 'Remove quotes where possible'
				],
				'style_convertquotes' => [
					'label' => 'Quote Style',
					'description' => 'Convert quotes to the same quote style'
				],
				'style_colors' => [
					'label' => 'Colours',
					'description' => 'Shorten hex values or replace with colour names where shorter'
				],
				'style_time' => [
					'label' => 'Colours',
					'description' => 'Shorten time values where shorter (e.g. 500ms becomes .5s)'
				],
				'style_fontweight' => [
					'label' => 'Font Weight',
					'description' => 'Shorten font weight values (e.g. font-weight: bold; becomes font-weight:700)'
				],
				'style_none' => [
					'label' => 'None Values',
					'description' => 'Shorten the value none to 0 where possible (e.g. border: none; becomes border:0)'
				],
				'style_lowerproperties' => [
					'label' => 'Properties',
					'description' => 'Lowercase property names'
				],
				'style_lowervalues' => [
					'label' => 'Values',
					'description' => 'Lowercase values where possible'
				],
				'style_cache' => [
					'label' => 'Cache',
					'description' => 'Cache minified output for faster execution'
				]
			]
		],
		'script' => [
			'name' => 'Javascript Minification',
			'desc' => 'Manage how inline Javascript is minified',
			'options' => [
				'script_whitespace' => [
					'label' => 'Whitespace', // strip whitespace around javascript
					'description' => 'Remove unnecessary whitespace'
				],
				'script_comments' => [
					'label' => 'Comments', // strip comments
					'description' => 'Remove Comments'
				],
				'script_eol' => [
					'label' => 'Semicolons',
					'description' => 'Remove semicolons where possible'
				],
				'script_quotestyle' => [
					'label' => 'Quote Style',
					'description' => 'Convert quotes to the same quote style',
					'value' => '"'
				],
				'script_cache' => [
					'label' => 'Cache',
					'description' => 'Cache minified output for faster execution'
				]
			]
		]
	];

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

	/**
	 * Renders the plugin administration panel
	 *
	 * @return void
	 */
	public function initAdmin() : void {

		// register field controls
		register_setting(self::SLUG, self::SLUG, [
			'sanitize_callback' => function (array $value = null) {
				$setting = [];
				foreach ($this->options AS $option) {
					foreach ($option['options'] AS $key => $item) {
						$val = empty($value[$key]) ? false : ($item['value'] ?? true);

						// build the options in the format HTMLdoc expects
						$parts = \explode('_', $key, 2);

						// root level
						if (!isset($parts[1])) {
							$setting[$parts[0]] = $val;

						// sub levels
						} elseif ($setting[$parts[0]] ?? true) {
							if (!isset($setting[$parts[0]]) || !is_array($setting[$parts[0]])) {
								$setting[$parts[0]] = [];
							}
							$setting[$parts[0]][$parts[1]] = $val;
						}
					}
				}
				return $setting;
			}
		]);
	    $options = get_option(self::SLUG);

		// render field controls
		foreach ($this->options AS $g => $group) {

			// add section
			add_settings_section('htmldoc_options_'.$g, $group['name'], function () use ($group) {
				echo htmlspecialchars($group['desc']);
			}, self::SLUG);

			// add options
			foreach ($group['options'] AS $key => $item) {

				// get the current setting
				$parts = \explode('_', $key, 2);
				if (isset($parts[1], $options[$parts[0]][$parts[1]])) {
					$value = $options[$parts[0]][$parts[1]];
				} elseif (isset($options[$parts[0]])) {
					$value = $options[$parts[0]];
				} else {
					$value = $item['default'] ?? true;
				}
				add_settings_field($key, htmlspecialchars($item['label']), function () use ($key, $value, $item) { ?>
					<input type="checkbox" id="<?= htmlspecialchars(self::SLUG.'-'.$key); ?>" name="<?= htmlspecialchars(self::SLUG.'['.$key.']'); ?>" value="1"<?= $value ? ' checked="checked"' : ''; ?> />
					<label for="<?= htmlspecialchars(self::SLUG.'-'.$key); ?>"><?= htmlspecialchars($item['description']); ?></label>
				<?php }, self::SLUG, 'htmldoc_options_'.$g);
			}
		}
	}

	/**
	 * Adds the configuration link to the admin menu
	 *
	 * @return void
	 */
	public function setAdminMenu() : void {
		add_options_page('HTMLdoc - HTML Minifier Configuration', 'HTMLdoc Minifier', 'manage_options', self::SLUG, function () { ?>
			<h1>HTMLdoc Minification Options</h1>
			<form action="options.php" method="post" accept-charset="<?= htmlspecialchars(mb_internal_encoding()); ?>">
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
			$cache = empty($minify['cache']) ? null : 'htmldoc-style-'.md5(self::VERSION.$json.$css);

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
		if (($options = get_option(self::SLUG)) !== false && !empty($options['minifyhtml']) && (!empty($options['admin']) || !is_admin())) {

			// autoload files
			foreach ($this->packages AS $item) {
				if (\file_exists($item['dir'].$item['autoload'])) {
					require($item['dir'].$item['autoload']);
				}
			}

			// create output buffer
			if (\class_exists('\\hexydec\\html\\htmldoc')) {
				\ob_start(function ($html) use ($options) {

					// collect timing for stats
					$timing = ['Initialise' => \microtime(true)];

					// get the config and create the object
					$config = $this->getConfig($options);
					$doc = new \hexydec\html\htmldoc($config);

					// load from a variable
					$timing['Parse'] = \microtime(true);
					if ($doc->load($html)) {

						// build the minification options
						$timing['Minify'] = \microtime(true);
						$doc->minify($options);

						// compile back to HTML
						$timing['Compile'] = \microtime(true);
						$min = $doc->html();

						// show stats in the console
						if (!empty($options['stats'])) {
							$timing['Complete'] = \microtime(true);
							$min .= $this->drawStats($html, $min, $timing);
						}
						return $min;
					}
					return false;
				});
			}
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
			'console.groupCollapsed("HTMLdoc Stats");',
			'console.table('.\json_encode($table).');',
			'console.table('.\json_encode($sizes).');',
			'console.groupEnd()'
		];
		return '<script>'.\implode('', $console).'</script>';
	}
}

$obj = new htmldoc();
$obj->install();
