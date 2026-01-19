<?php declare(strict_types=1);
require_once get_theme_file_path('/vendor/autoload.php');

if (!defined('ABSPATH')) {
	exit;
}

if (!defined('STYLES_INLINE_SIZE_LIMIT')) {
	define('STYLES_INLINE_SIZE_LIMIT', 1024);
}

// Feature which allows to combine all CSS files inside the page into one and prioritize it loading.
// If it would not be able to copy destination file - it will just embed all the css code inside <head>, check this to be sure it functionate correct!
if (!defined('COMBINE_CSS_ENABLED')) {
	define('COMBINE_CSS_ENABLED', false);
}

// This option will embed main.js file into the page, instead of loading it as a separate file from header.
if (!defined('EMBED_MAIN_JS')) {
	define('EMBED_MAIN_JS', false);
}

// require_once __DIR__ . '/enable-woocommerce-guttenberg.php';

require_once __DIR__ . '/disable-emoji.php';
require_once __DIR__ . '/disable-oembed.php';
require_once __DIR__ . '/disable-jquery.php';
require_once __DIR__ . '/disable-search.php';
require_once __DIR__ . '/disable-auto-update.php';
require_once __DIR__ . '/disable-comments.php';
require_once __DIR__ . '/disable-authors-archive.php';
require_once __DIR__ . '/disable-json-api.php';
require_once __DIR__ . '/cleanup.php';
require_once __DIR__ . '/vite.php';
require_once __DIR__ . '/register-blocks.php';
require_once __DIR__ . '/reusable-blocks.php';
require_once __DIR__ . '/enable-smtp.php';
// require_once __DIR__ . '/disable-wp-css-library.php';
// require_once __DIR__ . '/preload-fonts.php';

add_filter('after_setup_theme', function () {
	add_theme_support('title-tag');
	add_theme_support('menus');
	add_theme_support('html5', ['script', 'style']);
	add_theme_support('custom-logo');
	add_theme_support('post-thumbnails');
	add_theme_support('align-wide');
	add_theme_support('editor-styles');
	add_theme_support('wp-block-styles');
	add_editor_style('style-editor.css');
});

// Register the three useful image sizes for use in Add Media modal
add_filter('image_size_names_choose', function ($sizes) {
	return array_merge($sizes, array(
		'medium-width' => __('Medium Width'),
		'medium-height' => __('Medium Height'),
		'medium-something' => __('Medium Something'),
	));
});

add_action(
	is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts',
	function () {
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;

		wp_register_style('soboldev-constants-css', get_theme_file_uri('assets/css/_constants.scss'), [], null);
		wp_enqueue_style('soboldev-constants-css');

		if (is_admin()) {
			wp_register_style('soboldev-constants-editor-css', get_theme_file_uri('assets/css/_constants_editor.scss'), [], null);
			wp_enqueue_style('soboldev-constants-editor-css');

			wp_register_style('soboldev-admin-css', get_theme_file_uri('assets/css/admin.scss'), [], null);
			wp_enqueue_style('soboldev-admin-css');

			wp_register_style('soboldev-admin-main-css', get_theme_file_uri('assets/css/style-editor.scss'), [], null);
			wp_enqueue_style('soboldev-admin-main-css');

			wp_register_style('soboldev-admin-single-post-css', get_theme_file_uri('assets/css/single-editor.scss'), [], null);
			wp_enqueue_style('soboldev-admin-single-post-css');
		} else {
			wp_register_style('soboldev-main-css', get_theme_file_uri('assets/css/style.scss'), [], null);
			wp_enqueue_style('soboldev-main-css');

			wp_register_style('soboldev-single-post-css', get_theme_file_uri('assets/css/single-style.scss'), [], null);
			wp_enqueue_style('soboldev-single-post-css');
		}

		wp_register_script('soboldev-main', get_theme_file_uri('assets/js/scripts.js'), [], null, true);
		wp_enqueue_script('soboldev-main');

		wp_localize_script(
			'soboldev-main',
			'SobolDevThemeVars',
			[
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'ajaxNonce' => wp_create_nonce('soboldev-ajax-nonce'),
				'i18n' => [
					'sort_button' => [
						'ASC' => __('Sort By price (Low to High)', 'soboldev'),
						'DESC' => __('Sort By price (High to Low)', 'soboldev')
					]
				]
			]
		);
	},
);

function theme_env($var, $default)
{
	$res = getenv($var);
	return $res != "" ? $res : $default;
}

// Registration of navigation menus
add_action('after_setup_theme', function () {
	register_nav_menus([
		'header_menu' => __('Header Menu'),
	]);
});

//Remove margin top -32 for authorized users
add_action('get_header', function () {
	remove_action('wp_head', '_admin_bar_bump_cb');
}, PHP_INT_MAX);

// Remove tag <p> from wpcf7 forms
add_filter('wpcf7_autop_or_not', '__return_false');

// Registration of custom post types: Events, Learning  Center, Benefits
add_action('init', function () {
	$cpts = [
		[
			'labels' => [
				'name' => __('Properties', 'soboldev'),
				'singular_name' => __('Property', 'soboldev'),
				'add_new_item' => __('Add New Property', 'soboldev'),
				'edit_item' => __('Edit Property', 'soboldev'),
				'new_item' => __('New Property', 'soboldev'),
				'all_items' => __('All Properties', 'soboldev'),
				'menu_name' => __('Properties', 'soboldev')
			],
			'args' => [
				'public' => true,
				'publicly_queryable' => false,
				'show_ui' => true,
				'show_in_menu' => true,
				'show_in_nav_menus' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'has_archive' => false,
				'hierarchical' => false,
				'show_in_rest' => true,
				'supports' => ['editor', 'title', 'thumbnail', 'excerpt']
			],
			'type' => 'property'
		],
	];

	foreach ($cpts as $cpt) {
		if (is_array($cpt['labels']) && is_array($cpt['args']) && !empty($cpt['type'])) {
			$args = [
				...$cpt['args'],
				'labels' => $cpt['labels']
			];
			register_post_type($cpt['type'], $args);
		}
	}

}, 0, PHP_INT_MAX);

