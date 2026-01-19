<?php declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

if (is_admin()) {
	return;
}

add_action('wp_enqueue_scripts', function () {
	wp_deregister_script('jquery-core');
	wp_deregister_script('jquery-migrate');
});
