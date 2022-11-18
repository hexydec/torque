<?php
$dir = \dirname(\dirname(__DIR__)).'/uploads/torque/';
$file = $dir.'csp-reports.json';

// we have collected enough data
if (\filesize($file) > 1000000) {
	\http_response_code(204);

// no report
} elseif (($report = \file_get_contents('php://input')) === false) {
	\http_response_code(400);

// not valid json
} elseif (\json_decode($report) === null || \json_last_error() !== JSON_ERROR_NONE) {
	\http_response_code(400);

// read the report
} else {
	if (!\is_dir($dir)) {
		\mkdir($dir, 0755, true);
	}
	\file_put_contents($file, $report."\n", FILE_APPEND | LOCK_EX);
	\http_response_code(204);
};