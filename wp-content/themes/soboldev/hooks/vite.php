<?php declare(strict_types=1);
require_once get_theme_file_path('/classes/ViteData.class.php');

if (!defined('ABSPATH') || (defined('DOING_CRON') && DOING_CRON) || (defined('DOING_AJAX') && DOING_AJAX) || (defined('REST_REQUEST') && REST_REQUEST)) {
	return;
}

function handle_dev_asset($assets, $handle)
{
	$asset_type = 'script';
	if (get_class($assets) === 'WP_Styles') {
		$asset_type = 'style';
	}

	if (is_string($assets->registered[$handle]->src) === false) {
		return;
	}

	$src = strtok($assets->registered[$handle]->src, '?');
	if ($src === false || str_contains($src, 'wp-content/themes/') === false) {
		return;
	}

	$src = preg_replace('/.*wp-content\/([^\?]+)/', '/wp-content/\1', $src);
	if (@key_exists('data', $assets->registered[$handle]->extra) && '' !== $assets->registered[$handle]->extra['data']) {
		echo '<script>' . $assets->registered[$handle]->extra['data'] . '</script>';
	}

	call_user_func('wp_dequeue_' . $asset_type, $handle);
	call_user_func('wp_deregister_' . $asset_type, $handle);

	if (@filesize(ABSPATH . $src) < 1) {
		return;
	}

	$return = '<script type="module" async crossorigin src="https://' . esc_attr(getenv('SITE_URL') . ':' . getenv('DOCKER_VITE_PORT') . $src) . '"></script>' . "\n";
	if ($asset_type === 'script' && $handle === 'soboldev-main') {
		\SobolDev\Vite\Data::addAssetToMoveList($return);
		return;
	}

	echo $return;
}

function css_combine_condition($asset_type)
{
	return (defined('COMBINE_CSS_ENABLED') && COMBINE_CSS_ENABLED && !is_admin() && $asset_type === 'style');
}

function handle_asset($assets, $handle)
{
	if (is_null(\SobolDev\Vite\Data::$manifest) || is_string($assets->registered[$handle]->src) === false) {
		return;
	}

	$asset_type = 'script';
	if (get_class($assets) === 'WP_Styles') {
		$asset_type = 'style';
	}

	if (css_combine_condition($asset_type) && \SobolDev\Vite\Data::$tmp_cssfile === null) {
		\SobolDev\Vite\Data::$tmp_cssfile = tmpfile();
	}

	$src = strtok($assets->registered[$handle]->src, '?');
	if (strpos($src, 'wp-content/themes/') === false) {
		return;
	}
	$src = preg_replace('/.*wp-content\/([^\?]+)/', '\1', $src);

	$found = 0;
	foreach (array_keys(\SobolDev\Vite\Data::$manifest) as $asset) {
		if (str_ends_with($asset, $src)) {
			if (@key_exists('data', $assets->registered[$handle]->extra) && '' !== $assets->registered[$handle]->extra['data']) {
				echo '<script>' . $assets->registered[$handle]->extra['data'] . '</script>';
			}

			$deps = $assets->registered[$handle]->deps;
			$ver = $assets->registered[$handle]->ver;

			call_user_func('wp_dequeue_' . $asset_type, $handle);
			call_user_func('wp_deregister_' . $asset_type, $handle);

			$filesize = @filesize(get_theme_file_path('/dist/' . \SobolDev\Vite\Data::$manifest[$asset]["file"]));
			if ($filesize < 1) {
				return;
			}

			if (css_combine_condition($asset_type)) {
				fwrite(\SobolDev\Vite\Data::$tmp_cssfile, file_get_contents(get_theme_file_path('/dist/' . \SobolDev\Vite\Data::$manifest[$asset]["file"])));
				return;
			}

			if ($asset_type === 'script' && $handle === 'soboldev-main') {
				if (defined('EMBED_MAIN_JS') && EMBED_MAIN_JS) {
					\SobolDev\Vite\Data::addAssetToMoveList('<script>' . file_get_contents(get_theme_file_path('/dist/' . \SobolDev\Vite\Data::$manifest[$asset]["file"])) . '</script>');
				} else {
					\SobolDev\Vite\Data::addAssetToMoveList('<script src="' . get_theme_file_uri('/dist/' . \SobolDev\Vite\Data::$manifest[$asset]["file"]) . '"></script>');
				}
				return;
			}

			call_user_func('wp_register_' . $asset_type, $handle, get_theme_file_uri('/dist/' . \SobolDev\Vite\Data::$manifest[$asset]["file"]), $deps, $ver, ($asset_type === 'script'));
			call_user_func('wp_enqueue_' . $asset_type, $handle);

			if ($asset_type === 'style' && !is_admin()) {
				ob_start();
				$assets->do_item($handle);
				\SobolDev\Vite\Data::addAssetToMoveList(ob_get_clean());
				call_user_func('wp_dequeue_' . $asset_type, $handle);
				call_user_func('wp_deregister_' . $asset_type, $handle);
			}

			$found = 1;
			break;
		}
	}

	if ($found === 0) {
		if (@filesize(ABSPATH . 'wp-content/' . $src) < 1) {
			call_user_func('wp_dequeue_' . $asset_type, $handle);
			call_user_func('wp_deregister_' . $asset_type, $handle);
		}
	}
}

function process_assets($action)
{
	global $wp_scripts, $wp_styles;
	$fn_name = theme_env("SITE_ENV", "production") === "production" ? 'handle_asset' : 'handle_dev_asset';

	foreach ($wp_styles->queue as $handle) {
		call_user_func("$fn_name", $wp_styles, $handle);
	}
	foreach ($wp_scripts->queue as $handle) {
		call_user_func("$fn_name", $wp_scripts, $handle);
	}
}

function vite_move_to_head($buffer)
{
	if (\SobolDev\Vite\Data::$tmp_cssfile) {
		$tmp_cssfilename = stream_get_meta_data(\SobolDev\Vite\Data::$tmp_cssfile)['uri'];
		$md5 = md5_file($tmp_cssfilename);
		$md5_file = get_theme_file_path('/dist/' . $md5 . '.css');
		$md5_url = get_theme_file_uri('/dist/' . $md5 . '.css');

		$can_include = true;

		if (!is_file($tmp_cssfilename)) {
			\SobolDev\Vite\Data::addAssetToMoveList("\n\n\n" . '<style>// SOMETHING WRONG WITH SYSTEM TMP PATH, UNABLE TO CREATE FILE: use `sys_get_temp_dir()` to identify the path.' . "\n\n\n" . '</style>');
			$can_include = false;
		}
		if (!is_file($md5_file)) {
			if (!@copy($tmp_cssfilename, $md5_file)) {
				\SobolDev\Vite\Data::addAssetToMoveList('<style>' . @file_get_contents($tmp_cssfilename) . '</style>');
				$can_include = false;
			}
		}

		if ($can_include) {
			\SobolDev\Vite\Data::addAssetToMoveList('<link fetchpriority="high" rel="stylesheet" href="' . $md5_url . '" media="all" />');
		}
	}

	if (theme_env("SITE_ENV", "production") === "production") {
		return str_replace('</head>', join('', \SobolDev\Vite\Data::getAssertsToMoveList()) . '</head>', $buffer);
	}
	return str_replace('<head>', '<head>' . join('', \SobolDev\Vite\Data::getAssertsToMoveList()), $buffer);
}

add_action('init', function () {
	\SobolDev\Vite\Data::getManifest();

	// Intercept enqueued assets to deliver via Vite.
	$actions = [
		[
			'action' => is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts',
			'prio' => PHP_INT_MAX,
		],
		[
			'action' => 'wp_print_header_scripts',
			'prio' => PHP_INT_MIN,
		],
		[
			'action' => 'wp_footer',
			'prio' => PHP_INT_MIN,
		],
		[
			'action' => 'wp_print_footer_scripts',
			'prio' => PHP_INT_MIN,
		],
		[
			'action' => 'wp_print_styles',
			'prio' => PHP_INT_MIN,
		],
		[
			'action' => 'wp_head',
			'prio' => PHP_INT_MIN,
		]
	];

	foreach ($actions as $item) {
		add_action(
			$item['action'],
			function () use ($item) {
				process_assets($item['action']); },
			$item['prio']
		);
	}

	if (theme_env("SITE_ENV", "production") === "production") {
		add_filter('script_loader_tag', function ($tag, $handle, $src) {
			if (is_null(\SobolDev\Vite\Data::$manifest) || strpos($src, 'wp-content/themes/') === false) {
				return $tag;
			}

			$src = preg_replace('/.*wp-content\/([^\?]+).*$/', '\1', $src);

			foreach (array_keys(\SobolDev\Vite\Data::$manifest) as $asset) {
				if (str_ends_with($src, \SobolDev\Vite\Data::$manifest[$asset]['file'])) {
					return str_replace(' src', ' type="module" ' . (str_contains($tag, 'id="acf-') ? 'defer async ' : '') . ' src', $tag);
				}
			}
			return $tag;
		}, 10, 3);
		add_filter('styles_inline_size_limit', function () {
			return STYLES_INLINE_SIZE_LIMIT; });
	} else {
		add_filter('styles_inline_size_limit', '__return_zero'); // To avoid inline css as we are using Vite.
	}

	ob_start('vite_move_to_head');
	add_action("shutdown", function () {
		@ob_end_flush(); }, PHP_INT_MAX);

	// This filters turning on ability to load only assets for blocks that are being used.
	add_filter('should_load_separate_core_block_assets', '__return_true');
}, PHP_INT_MIN);
