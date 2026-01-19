<?php declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

define('WP_AUTO_UPDATE_CORE', false);
define('AUTOMATIC_UPDATER_DISABLED', false);
add_filter('auto_update_plugin', '__return_false');
add_filter('auto_update_theme', '__return_false');
