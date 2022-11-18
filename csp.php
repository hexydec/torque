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
		return $key ? $csp[$key] ?? null : $csp;
	}

	public static function recommendations(string $file, string $key) {
		if (($data = self::violations($file, $key)) !== null) {
			$keywords = ["'unsafe-inline'", "'unsafe-eval'", 'data' => 'data:', 'self' => "'self"];
			$recs = [];
			foreach (\array_keys($data) AS $href) {
				if (!\in_array($href, $keywords)) {
					$value = \mb_substr($href, 0, \mb_strrpos($href, '/') + 1);
	
					// compare the urls folder by folder
					$found = false;
					foreach ($recs AS $r => $item) {
						$last = null;
						$pos = 7;
						while (($pos = \strpos($value, '/', $pos + 1)) !== false || ($pos = \strlen($value)) < ($last ?? 0)) {
							if (\strncmp($item, $value, $pos) === 0) {
								$last = $pos;
							} elseif ($last) {
								$recs[$r] = \substr($value, 0, $pos);
								$found = true;
								break 2;
							}
						}
					}
					if (!$found) {
						$recs[] = $value;
						break;
					}

				// add keyword
				} elseif (!in_array($href, $recs)) {
					$recs[] = $href;
				}
			}
			return $recs;
		}
		return null;
	}
}