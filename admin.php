<?php
/**
 * Renders the administration interface for the Torque Wordpress plugin
 *
 * @package hexydec/torque
 */
namespace hexydec\torque;

class admin extends config {

	/**
	 * @var $template Stores the name of the currently compiling template, see self::compile()
	 */
	protected static string $template;

	/**
	 * A function to compile an array into a template
	 * 
	 * @param array $content An array of items to compile
	 * @param string $template The absolute path of the template to compile
	 * @return string The compiled template
	 */
	public static function compile(array $content, string $template) : string {
		self::$template = $template;
		\extract($content);
		\ob_start();
		require self::$template;
		$html = \ob_get_contents();
		\ob_end_clean();
		return $html;
	}

	/**
	 * Retrieves the currently selected tab from the querystring or POST data, or the first avaialble tab
	 *
	 * @return string The code name of the current tab
	 */
	protected function getCurrentTab() : string {

		// get the current tab
		$tabs = $this->getTabs(); // allowed tabs
		$value = $_POST['tab'] ?? $_GET['tab'] ?? null; // current user value - can be GET or POST
		$tab = \in_array($value, $tabs, true) ? $value : $tabs[0]; // check tab against list or use first tab
		return $tab;
	}

	/**
	 * Registers the update callback to sanitise and update the configuration settings
	 *
	 * @return void
	 */
	public function update() : void {

		// register field controls
		\register_setting(self::SLUG, self::SLUG, [
			'sanitize_callback' => function (array $value = null) {

				// check the form
				$tab = $this->getCurrentTab();
				$options = [];
				$current = $this->options[$tab]['tab'];
				foreach ($this->options AS $i => $option) {
					if ($option['tab'] === $current) {
						foreach ($option['options'] AS $key => $item) {

							// get the value
							switch ($item['type']) {
								case 'input':
								case 'text':
									$options[$key] = $value[$key] ?? null;
									break;
								case 'checkbox':
									$options[$key] = empty($value[$key]) ? false : ($item['value'] ?? true);
									break;
								case 'number':
									if (\is_numeric($value[$key] ?? null) && $value[$key] >= 0) {
										$options[$key] = $value[$key];
									} else {
										\add_settings_error(self::SLUG, self::SLUG, 'The value entered for '.$item['label'].' is invalid');
									}
									break;
								case 'select':
									if (!isset($item['values'])) {
										$item['values'] = $this->getDatasource($i, $key);
									}
									$ids = \array_column($item['values'], 'id');
									$options[$key] = isset($value[$key]) && \in_array($value[$key], $ids) ? $value[$key] : null;
									break;
								case 'multiselect':
									if (!isset($item['values'])) {
										$item['values'] = $this->getDatasource($i, $key);
									}
									$ids = \array_column($item['values'], 'id');
									$options[$key] = \is_array($value[$key] ?? null) ? \array_intersect($value[$key], $ids) : [];
									break;
								case 'list':
									$options[$key] = \is_array($value[$key] ?? null) ? \implode("\n", \array_filter($value[$key])) : '';
									break;
							}
						}
					}
				}

				// build config
				$config = $this->buildConfig($options);

				// on save callback
				foreach ($this->options AS $i => $option) {
					if ($option['tab'] === $current) {
						foreach ($option['options'] AS $key => $item) {
							if (!empty($item['onsave'])) {
								$item['onsave']($options[$key], $config);
							}
						}
					}
				}
				return $config;
			}
		]);
	}

	/**
	 * Renders the admin page
	 *
	 * @return void
	 */
	public function draw() : void {
		$tab = $this->getCurrentTab();

		// add admin page
		\add_options_page('Torque - Optimise the transport of your website', 'Torque', 'manage_options', self::SLUG, function () use ($tab) {

			// include styles
			$css = \str_replace('\\', '/', __DIR__).'/stylesheets/csp.css';
			\wp_enqueue_style('torque-csp', \get_home_url().\mb_substr($css, \mb_strlen(\get_home_path()) - 1), [], \filemtime($css));

			// include script
			$js = \str_replace('\\', '/', __DIR__).'/javascript/csp.js';
			\wp_enqueue_script('torque-csp', \get_home_url().\mb_substr($js, \mb_strlen(\get_home_path()) - 1), [], \filemtime($js));

			// doc root
			$folder = \str_replace('\\', '/', \mb_substr(__DIR__, \mb_strlen($_SERVER['DOCUMENT_ROOT']))).'/';

			// render headers and tabs ?>
			<h1 style="display:flex;align-items:center;"><img src="<?php echo \esc_html($folder); ?>graphics/torque-icon.svg" alt="Torque" style="width:40px;margin-right:10px" />Torque Configuration</h1>
			<form action="options.php" method="post" accept-charset="<?php echo \esc_html(\mb_internal_encoding()); ?>">
				<input type="hidden" name="tab" value="<?php echo \esc_html($tab); ?>" />
				<nav class="nav-tab-wrapper">
					<?php
					$tabs = [];
					foreach ($this->options AS $key => $item) {
						if (!\in_array($item['tab'], $tabs)) {
							$tabs[] = $item['tab'];
							?><a href="?page=<?php echo \esc_html(self::SLUG); ?>&amp;tab=<?php echo $key; ?>" class="nav-tab<?php echo $key === $tab ? ' nav-tab-active' : '' ?>" title="<?php echo \esc_html($item['desc']); ?>"><?php echo \esc_html($item['tab']); ?></a><?php
						}
					} ?>
				</nav>
				<?php

				// render current tab and submit button
				\settings_fields(self::SLUG);
				\do_settings_sections(self::SLUG);
				if (!empty($this->options[$tab]['options'])) {
					\submit_button();
				} ?>
			</form>
		<?php });

		// get options
		$options = \get_option(self::SLUG);

		// render field controls
		$current = $this->options[$tab]['tab'];
		$allowed = \array_merge(\wp_kses_allowed_html('post'), [
			'input' => [
				'type' => 'checkbox',
				'id' => true,
				'value' => true,
				'class' => true
			]
		]);
		foreach ($this->options AS $g => $group) {
			if ($group['tab'] === $current) {

				// add section
				\add_settings_section(self::SLUG.'_options_'.$g, $group['name'], function () use ($g, $group, $allowed) {

					// echo precompiled HTML or generate on the fly - not generated from user input
					echo \wp_kses(($group['html'] instanceof \Closure ? $group['html']() : $group['html']), $allowed);
				}, self::SLUG);

				// add options
				foreach ($group['options'] AS $key => $item) {
					\add_settings_field($key, \esc_html($item['label']), function () use ($g, $key, $item, $options, $allowed) {

						// before HTML
						if (!empty($item['before'])) {
							echo $item['before'] instanceof \Closure ? $item['before']($item) : $item['before'];
						}

						// get the current setting
						$parts = \explode('_', $key, 2);
						if (isset($parts[1])) {
							$value = $options[$parts[0]][$parts[1]] ?? $item['default'];
						} elseif (isset($options[$parts[0]])) {
							$value = $options[$parts[0]];
						} else {
							$value = $item['default'] ?? true;
						}

						// render attributes
						$item['attributes'] = \array_merge($item['attributes'] ?? [], [
							'name' => self::SLUG.'['.$key.']'.($item['type'] === 'multiselect' ? '[]' : ''),
							'id' => self::SLUG.'-'.$key
						]);
						$attr = '';
						foreach ($item['attributes'] AS $name => $attribute) {
							$attr .= ' '.$name.'="'.\esc_html($attribute).'"';
						}

						// render the controls
						switch ($item['type']) {
							case 'input':
							case 'checkbox':
							case 'number':
								$checkbox = $item['type'] === 'checkbox'; ?>
								<input type="<?php echo $item['type']; ?>"<?php echo $attr; ?> value="<?php echo $checkbox ? '1' : \esc_html($value); ?>"<?php echo $checkbox && $value ? ' checked="checked"' : ''; ?> />
								<?php
								if ($checkbox && !empty($item['description'])) { ?>
									<label for="<?php echo \esc_html(self::SLUG.'-'.$key); ?>"><?php echo \esc_html($item['description']); ?></label>
									<?php
									$item['description'] = null;
								}
								break;
							case 'text':
								?>
								<textarea<?php echo $attr; ?> rows="5" cols="30"><?php echo \esc_html($value); ?></textarea>
								<?php
								break;
							case 'list':
								$values = $value ? explode("\n", \str_replace("\r", "", $value)) : [''];
								$delete = \count($values) > 1;
								?><ul class="torque-csp__list">
									<?php foreach ($values AS $i => $val) { ?>
										<li class="torque-csp__list-item">
											<button class="torque-csp__add dashicons dashicons-insert" title="Add item to list">Add</button>
											<input name="<?php echo \esc_html($item['attributes']['name']); ?>[]" class="torque-csp__value" value="<?php echo \esc_html($val	); ?>" />
											<?php if ($delete) { ?>
												<button class="torque-csp__delete dashicons dashicons-remove" title="Remove item from list">Delete</button>
											<?php } ?>
										</li>
									<?php } ?>
								</ul><?php
								break;
							case 'multiselect':
							case 'select':
								if (!\is_array($value)) {
									$value = [$value];
								}
								if (!isset($item['values'])) {
									$item['values'] = $this->getDatasource($g, $key);
								}
								$group = null; ?>
								<select<?php echo $attr; ?><?php echo $item['type'] === 'multiselect' ? ' multiple="multiple" style="height:200px;width:95%;max-width:600px"' : ''; ?>>
									<?php foreach ($item['values'] AS $option) {
										if (($option['group'] ?? null) !== $group) {
											if ($group) {
												echo '</optgroup>';
											}
											$group = $option['group']; ?>
											<optgroup label="<?php echo \esc_html($option['group']); ?>">
										<?php } ?>
										<option value="<?php echo \esc_html($option['id']); ?>"<?php echo \in_array($option['id'], $value) ? ' selected="selected"' : ''; ?>><?php echo \esc_html($option['name']); ?></option>
									<?php } ?>
									<?php if ($group) {
										echo '</optgroup>';
									} ?>
								</select>
								<?php
								break;
						}

						// description
						if (!empty($item['after'])) {
							echo $item['after'] instanceof \Closure ? $item['after']($item) : $item['after'];
						}

						// description
						if (!empty($item['description'])) { ?>
							<p><?php echo empty($item['descriptionhtml']) ? \esc_html($item['description']) : \wp_kses($item['description'], $allowed); ?></p>
						<?php }
					}, self::SLUG, self::SLUG.'_options_'.$g);
				}
			}
		}
	}

	/**
	 * Retrieves the datasource attached to a configured control
	 *
	 * @param string $group The key of the group in config::$options to retrieve the data from
	 * @param string $key The key of the field within the selected group to retrieve the data from
	 * @return array An array containing arrays of values, each with 'id', 'group', and 'name'
	 */
	protected function getDatasource(string $group, string $key) : array {
		$options = $this->options;
		if (isset($options[$group]['options'][$key])) {
			$item = $options[$group]['options'][$key];
			if (empty($item['values']) && !empty($item['datasource'])) {
				$item['values'] = \call_user_func($item['datasource']);
			}
			if ($item['values']) {
				return $item['values'];
			}
		}
		return [];
	}
}
