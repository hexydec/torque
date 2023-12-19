<?php
/**
 * Manages the Content-Security-Policy log for the Torque Wordpress plugin
 *
 * @package hexydec/torque
 */
namespace hexydec\torque;

class csp {

	public static function get(string $file, ?string $key = null) : ?array {
		static $csp = null;
		if (!$csp && \file_exists($file) && ($data = \file_get_contents($file)) !== false) {
			$csp = [];
			foreach (\explode("\n", $data) AS $item) {
				$digest = \md5($item);
				if (($item = \json_decode($item, true)) !== null && !empty($item['csp-report'])) {
					if (isset($item['csp-report']['effective-directive'], $item['csp-report']['blocked-uri'])) {
						$parts = \explode('-', $item['csp-report']['effective-directive']);
						$directive = $parts[0].'-'.$parts[1];
						if (!isset($csp[$directive])) {
							$csp[$directive] = [];
						}
						if (!isset($csp[$directive][$digest])) {
							$csp[$directive][$digest] = $item['csp-report'];
						}
					}
				}
			}
		}
		return $key ? $csp[$key] ?? null : $csp;
	}

	public static function violations(string $file, ?string $key = null) : ?array {
		static $csp = null;
		if ($csp === null && ($data = self::get($file)) !== null) {
			$csp = [];
			$replacements = [
				'inline' => "'unsafe-inline'",
				'eval' => "'unsafe-eval'",
				'data' => 'data:',
				'self' => "'self"
			];
			foreach ($data AS $key => $item) {
				$csp[$key] = [];
				foreach ($item AS $row) {
					$value = $replacements[$row['blocked-uri']] ?? $row['blocked-uri'];
					if (!isset($csp[$key][$value])) {
						$csp[$key][$value] = [];
					}
					if (!\in_array($row['document-uri'], $csp[$key][$value], true)) {
						$csp[$key][$value][] = $row['document-uri'];
					}
				}
			}
		}
		return $key ? ($csp[$key] ?? null) : $csp;
	}

	public static function recommendations(string $file, string $key) : ?array {
		if (($data = self::violations($file, $key)) !== null) {

			// define kewords
			$keywords = ["'unsafe-inline'", "'unsafe-eval'", 'data' => 'data:', "'self'", 'blob:'];

			// build recommendations
			$recs = [];
			foreach (\array_keys($data) AS $href) {
				if (!\in_array($href, $keywords)) {
					$value = \mb_substr($href, 0, \mb_strrpos($href, '/') + 1);
	
					// compare the urls folder by folder
					$found = false;
					foreach ($recs AS $r => $item) {
						$last = null;
						$pos = 7;

						// compare one more folder each time
						while (($pos = \strpos($value, '/', $pos + 1)) !== false) {

							// match
							if (\strncmp($item, $value, $pos + 1) === 0) {
								$last = $pos;

							// matched previous
							} elseif ($last) {
								break;
							}
						}

						// overwrite found item with last match as may now be shorter
						if ($last) {
							$recs[$r] = \substr($value, 0, $last + 1);
							$found = true;
							break;
						}
					}

					// new item that doesn't match any previous items
					if (!$found) {
						$recs[] = $href;
					}

				// add keyword
				} elseif (!\in_array($href, $recs)) {
					$recs[] = $href;
				}
			}

			// build root URL
			$self = (($_SERVER['HTTPS'] ?? 'off') !== 'off' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
			$folder = \str_replace('\\', '/', \mb_substr(\ABSPATH, \mb_strlen($_SERVER['DOCUMENT_ROOT'])));
			$base = $self.$folder;

			// replace root with 'self'
			foreach ($recs AS $key => $item) {
				if ($item === $base) {
					$recs[$key] = "'self'";
				}
			}
			return $recs;
		}
		return null;
	}
}