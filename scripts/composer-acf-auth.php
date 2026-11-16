<?php declare(strict_types=1);

/**
 * Writes Composer credentials for the ACF Pro repository (connect.advancedcustomfields.com)
 * into a local, gitignored auth.json, using ACF_PRO_KEY and SITE_URL from .env.
 *
 * Usage: composer acf-auth && composer install
 */

$root = dirname(__DIR__);
$env_file = $root . '/.env';

if (!is_readable($env_file)) {
	fwrite(STDERR, ".env not found. Run: cp .env.sample .env\n");
	exit(1);
}

$env = parse_ini_file($env_file, false, INI_SCANNER_RAW) ?: [];
$key = trim((string) ($env['ACF_PRO_KEY'] ?? ''));
$site_url = trim((string) ($env['SITE_URL'] ?? ''));

if ($key === '') {
	fwrite(STDERR, "ACF_PRO_KEY is empty in .env.\n");
	exit(1);
}

// ACF expects the license key as username and the site URL as password.
$password = $site_url !== '' ? 'https://' . $site_url : 'https://localhost';

$cmd = sprintf(
	'composer config --working-dir=%s --auth http-basic.connect.advancedcustomfields.com %s %s',
	escapeshellarg($root),
	escapeshellarg($key),
	escapeshellarg($password)
);

passthru($cmd, $code);

if ($code === 0) {
	echo "ACF Pro credentials saved to auth.json\n";
}

exit($code);
