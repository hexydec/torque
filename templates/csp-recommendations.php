<?php if ($recommendations !== null) { ?>
	<div class="torque-csp__recommendations">
		<input type="checkbox" class="torque-csp__collapse-switch" id="<?php echo $type; ?>-recommendations" value="" />
		<h4 class="torque-csp__heading">
			<label for="<?php echo $type; ?>-recommendations" class="torque-csp__heading-label">
				<span class="torque-csp__heading-icon dashicons dashicons-plus-alt2"></span>
				<span class="torque-csp__heading-icon dashicons dashicons-minus"></span>
				Recommendations
			</label>
		</h4>
		<section class="torque-csp__collapse-content">
			<ul class="torque-csp__recommendations-list">
				<?php foreach ($recommendations AS $row) { ?>
					<li class="torque-csp__recommendations-item" title="Add recommendation to Content Security Policy">
						<a href="#" class="torque-csp__recommendations-add">
							<span class="torque-csp__heading-icon dashicons dashicons-insert"></span>
							<?php echo \esc_html($row); ?>
						</a>
					</li>
				<?php } ?>
			</ul>
		</section>
	</div>
<?php }
if ($violations !== null) {
	$len = 60;
	$max = 10; ?>
	<div class="torque-csp__log">
		<input type="checkbox" class="torque-csp__collapse-switch" id="<?php echo $type; ?>-log" value="" />
		<h4 class="torque-csp__heading">
			<label for="<?php echo $type; ?>-log" class="torque-csp__heading-label">
				<span class="torque-csp__heading-icon dashicons dashicons-plus-alt2"></span>
				<span class="torque-csp__heading-icon dashicons dashicons-minus"></span>
				Violations Log
			</label>
		</h4>
		<section class="torque-csp__collapse-content">
			<table class="torque-csp__log-table" cellspacing="8">
				<thead>
					<tr>
						<th class="torque-csp__log-header">Blocked URI</th>
						<th class="torque-csp__log-header">Pages</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($violations AS $blocked => $urls) {
						$isurl = \mb_stripos($blocked, '://') !== false; ?>
						<tr>
							<td class="torque-csp__log-item">
								<?php if ($isurl) { ?>
									<a href="<?php echo \esc_attr($blocked); ?>" target="_blank">
								<?php } ?>
								<?php echo \esc_html(\mb_strlen($blocked) > $len ? \mb_substr($blocked, 0, $len).'...' : $blocked); ?>
								<?php if ($isurl) { ?>
									</a>
								<?php } ?>
							</td>
							<td class="torque-csp__log-item torque-csp__log-item--pages">
								<?php foreach ($urls AS $i => $url) {
									if ($i < $max) { ?>
										<a href="<?php echo \esc_attr($url); ?>" class="dashicons dashicons-admin-page"></a>
									<?php } elseif ($i === $max) {
										echo '+'.(\count($urls) - $i);
										break;
									}
								} ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</section>
	</div>
<?php } ?>