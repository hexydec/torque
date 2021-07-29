<?php
namespace hexydec\torque;

class admin extends config {

	protected function getCurrentTab() {

		// get the current tab
		$tabs = $this->getTabs();
		$tab = $_POST['tab'] ?? ($_GET['tab'] ?? null);
		return isset($tab) && in_array($tab, $tabs) ? $tab : $tabs[0];
	}

	public function menu() {

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
									if (isset($value[$key]) && \is_numeric($value[$key]) && $value[$key] >= 0) {
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
									$options[$key] = isset($value[$key]) && \is_array($value[$key]) ? \array_intersect($value[$key], $ids) : [];
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

	public function draw() {
		$tab = $this->getCurrentTab();

		// add admin page
		\add_options_page('Torque - Optimise the transport of your website', 'Torque', 'manage_options', self::SLUG, function () use ($tab) {
			$folder = \str_replace('\\', '/', \mb_substr(__DIR__, \mb_strlen($_SERVER['DOCUMENT_ROOT']))).'/'; ?>
			<h1 style="display:flex;align-items:center;"><img src="<?= \htmlspecialchars($folder); ?>graphics/torque-icon.svg" alt="Torque" style="width:40px;margin-right:10px" />Torque Configuration</h1>
			<form action="options.php" method="post" accept-charset="<?= \htmlspecialchars(\mb_internal_encoding()); ?>">
				<input type="hidden" name="tab" value="<?= \htmlspecialchars($tab); ?>" />
				<nav class="nav-tab-wrapper">
					<?php
					$tabs = [];
					foreach ($this->options AS $key => $item) {
						if (!\in_array($item['tab'], $tabs)) {
							$tabs[] = $item['tab'];
							?><a href="?page=<?= \htmlspecialchars(self::SLUG); ?>&amp;tab=<?= $key; ?>" class="nav-tab<?= $key === $tab ? ' nav-tab-active' : '' ?>" title="<?= \htmlspecialchars($item['desc']); ?>"><?= \htmlspecialchars($item['tab']); ?></a><?php
						}
					} ?>
				</nav>
				<?php
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
		foreach ($this->options AS $g => $group) {
			if ($group['tab'] === $current) {

				// add section
				\add_settings_section(self::SLUG.'_options_'.$g, $group['name'], function () use ($g, $group) {
					echo $group['desc'];
					if (isset($group['html'])) {
						echo $group['html']();
					}
				}, self::SLUG);

				// add options
				foreach ($group['options'] AS $key => $item) {
					\add_settings_field($key, \htmlspecialchars($item['label']), function () use ($g, $key, $item, $options) {

						// get the current setting
						$parts = \explode('_', $key, 2);
						if (isset($parts[1])) {
							$value = $options[$parts[0]][$parts[1]] ?? $item['default'];
						} elseif (isset($options[$parts[0]])) {
							$value = $options[$parts[0]];
						} else {
							$value = $item['default'] ?? true;
						}

						// render the controls
						switch ($item['type']) {
							case 'input':
							case 'checkbox':
							case 'number':
								$checkbox = $item['type'] === 'checkbox'; ?>
								<input type="<?= $item['type']; ?>" id="<?= \htmlspecialchars(self::SLUG.'-'.$key); ?>" name="<?= \htmlspecialchars(self::SLUG.'['.$key.']'); ?>" value="<?= $checkbox ? '1' : \htmlspecialchars($value); ?>"<?= $checkbox && $value ? ' checked="checked"' : ''; ?> />
								<?php
								if ($checkbox && !empty($item['description'])) { ?>
									<label for="<?= \htmlspecialchars(self::SLUG.'-'.$key); ?>"><?= \htmlspecialchars($item['description']); ?></label>
									<?php
									$item['description'] = null;
								}
								break;
							case 'text':
								?>
								<textarea id="<?= \htmlspecialchars(self::SLUG.'-'.$key); ?>" name="<?= \htmlspecialchars(self::SLUG.'['.$key.']'); ?>" rows="5" cols="30"><?= \htmlspecialchars($value); ?></textarea>
								<?php
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
								<select name="<?= \htmlspecialchars(self::SLUG.'['.$key.']'.($item['type'] === 'multiselect' ? '[]' : '')); ?>"<?= $item['type'] === 'multiselect' ? ' multiple="multiple" style="height:200px;"' : ''; ?>>
									<?php foreach ($item['values'] AS $option) {
										if (($option['group'] ?? null) !== $group) {
											if ($group) {
												echo '</optgroup>';
											}
											$group = $option['group']; ?>
											<optgroup label="<?= \htmlspecialchars($option['group']); ?>">
										<?php } ?>
										<option value="<?= \htmlspecialchars($option['id']); ?>"<?= \in_array($option['id'], $value) ? ' selected="selected"' : ''; ?>><?= \htmlspecialchars($option['name']); ?></option>
									<?php } ?>
									<?= $group ? '</optgroup>' : ''; ?>
								</select>
								<?php
								break;
						}

						// description
						if (!empty($item['description'])) { ?>
							<p><?= empty($item['descriptionhtml']) ? \htmlspecialchars($item['description']) : $item['description']; ?></p>
						<?php }
					}, self::SLUG, self::SLUG.'_options_'.$g);
				}
			}
		}
	}

	protected function getDatasource(string $group, string $key) {
		if (isset($this->options[$group]['options'][$key])) {
			if (empty($this->options[$group]['options'][$key]['values']) && !empty($this->options[$group]['options'][$key]['datasource'])) {
				$this->options[$group]['options'][$key]['values'] = \call_user_func($this->options[$group]['options'][$key]['datasource']);
			}
			return $this->options[$group]['options'][$key]['values'];
		}
		return [];
	}
}
