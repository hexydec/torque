<?php
namespace hexydec\wordpress;
/*
Plugin Name: Hexydec HTML Minifier
Plugin URI:  https://github.com/hexydec/htmldoc
Description: Minify your HTML output and any inline CSS/Javascript. Uses custom written HTML/CSS/JS compilers to produce reliable minification, designed with performance in mind. <a href="https://hexydec.com/htmldoc/" target="_blank">Try it out here</a>
Version:     0.1.0
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
	protected $slug = 'htmldoc';

	/**
	 * @var array $packages A list of external dependencies to be installed when the plugin is activated
	 */
	protected $packages = [
		'htmldoc' => [
			'file' => 'https://github.com/hexydec/htmldoc/archive/1.0.1.zip',
			'autoload' => __DIR__.'/htmldoc-1.0.1/src/autoload.php'
		],
		'cssdoc' => [
			'file' => 'https://github.com/hexydec/cssdoc/archive/0.1.1.zip',
			'autoload' => __DIR__.'/cssdoc-0.1.1/src/autoload.php'
		],
		'jslite' => [
			'file' => 'https://github.com/hexydec/jslite/archive/v0.2.0.zip',
			'autoload' => __DIR__.'/jslite-0.2.0/src/autoload.php'
		]
	];

	/**
	 * @var array $options A list of configuration options for the plugin
	 */
	protected $options = [
		'general' => [
			'name' => 'General Minification',
			'desc' => 'Edit the general minification settings',
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
				],
				'admin' => [
					'label' => 'Admin System',
					'description' => 'Minify the admin system'
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
			'name' => 'URL Minification',
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
				'style' => [
					'label' => 'Minify CSS',
					'description' => 'Enable minification of inline CSS within a <style> tag'
				],
				'style_removesemicolon' => [
					'label' => 'Semicolons',
					'description' => 'remove last semicolon from each rule'
				],
				'style_removezerounits' => [
					'label' => 'Zero Units',
					'description' => 'Remove unit declaration from zero values (e.g. 0px becomes 0)'
				],
				'style_removeleadingzero' => [
					'label' => 'Leading Zero\'s',
					'description' => 'Remove Leading Zero\'s (e.g 0.3s becomes .3s)'
				],
				'style_convertquotes' => [
					'label' => 'Quote Style',
					'description' => 'Convert quotes to the same quote style'
				],
				'style_removequotes' => [
					'label' => 'Quotes',
					'description' => 'Remove quotes where possible'
				],
				'style_shortenhex' => [
					'label' => 'Hex Values',
					'description' => 'Shorten hex values where possible'
				],
				'style_lowerproperties' => [
					'label' => 'Properties',
					'description' => 'Lowercase property names'
				],
				'style_lowervalues' => [
					'label' => 'Values',
					'description' => 'Lowercase values where possible'
				],
			]
		],
		'script' => [
			'name' => 'Javascript Minification',
			'desc' => 'Manage how inline Javascript is minified',
			'options' => [
				'script' => [
					'label' => 'Minify Javascript',
					'description' => 'Enable minification of inline Javascript within a <script> tag'
				],
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
					'description' => 'Convert quotes to the same quote style'
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
		register_uninstall_hook(__FILE__, [$this, 'uninstall']);
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
			$it = new \RecursiveDirectoryIterator(__DIR__, \RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
			$len = strlen(__DIR__) + 1;
			foreach ($files AS $item) {
				$path = $item->getRealPath();
				if ($item->isDir()) {
					rmdir($path);
				} elseif (strlen($path) > $len && strpos(str_replace('\\', '/', $path), '/', $len) !== false) {
					unlink($path);
				}
			}

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
	 * Renders the plugin administration panel
	 *
	 * @return void
	 */
	public function initAdmin() : void {

		// register field controls
		register_setting($this->slug, $this->slug, [
			'sanitize_callback' => function (array $value = null) {
				$setting = [];
				foreach ($this->options AS $option) {
					foreach ($option['options'] AS $key => $item) {
						$setting[$key] = !empty($value[$key]);
					}
				}
				return $setting;
			}
		]);
	    $options = get_option($this->slug);

		// render field controls
		foreach ($this->options AS $g => $group) {

			// add section
			add_settings_section('htmldoc_options_'.$g, $group['name'], function () use ($group) {
				echo htmlspecialchars($group['desc']);
			}, $this->slug);

			// add options
			foreach ($group['options'] AS $key => $item) {
				$value = $options[$key] ?? true;
				add_settings_field($key, htmlspecialchars($item['label']), function () use ($key, $value, $item) { ?>
					<input type="checkbox" id="<?= htmlspecialchars($this->slug.'-'.$key); ?>" name="<?= htmlspecialchars($this->slug.'['.$key.']'); ?>" value="1"<?= $value ? ' checked="checked"' : ''; ?> />
					<label for="<?= htmlspecialchars($this->slug.'-'.$key); ?>"><?= htmlspecialchars($item['description']); ?></label>
				<?php }, $this->slug, 'htmldoc_options_'.$g);
			}
		}
	}

	/**
	 * Adds the configuration link to the admin menu
	 *
	 * @return void
	 */
	public function setAdminMenu() : void {
		add_options_page('HTMLdoc - HTML Minifier Configuration', 'HTMLdoc Minifier', 'manage_options', $this->slug, function () { ?>
			<h1>HTMLdoc Minification Options</h1>
			<form action="options.php" method="post" accept-charset="<?= htmlspecialchars(mb_internal_encoding()); ?>">
		        <?php
		        settings_fields($this->slug);
		        do_settings_sections($this->slug);
				submit_button(); ?>
	    	</form>
		<?php });
	}

	public function uninstall() {
		delete_option($this->slug);
	}

	/**
	 * Minifies the page
	 *
	 * @return void
	 */
	public function minify() : void {

		// autoload files
		foreach ($this->packages AS $item) {
			if (file_exists($item['autoload'])) {
				require($item['autoload']);
			}
		}

		// create output buffer
		if (class_exists('\\hexydec\\html\\htmldoc') && ($options = get_option($this->slug)) !== false) {
			if (!empty($options['admin']) || !is_admin()) {
				ob_start(function ($html) use ($options) {
					$doc = new \hexydec\html\htmldoc();

					// load from a variable
					if ($doc->load($html)) {

						// build the minification options
						$minify = [];
						foreach ($this->options AS $key => $item) {
							foreach (array_keys($item['options']) AS $option) {
								if (isset($options[$option])) {
									$parts = explode('_', $option, 2);

									// root level options
									if (!isset($parts[1])) {
										$minify[$option] = $options[$option];

									// sub level options
									} elseif (!isset($minify[$parts[0]]) || $minify[$parts[0]] !== false) {

										// set up array
										if (!isset($minify[$parts[0]]) || !is_array($minify[$parts[0]])) {
											$minify[$parts[0]] = [];
										}
										$minify[$parts[0]][$parts[1]] = $options[$option];
									}
								}
							}
						}
						$doc->minify($minify);

						// compile back to HTML
						$html = $doc->save();
					}
					return $html;
				});
			}
		}
	}
}

$obj = new htmldoc();
$obj->install();
