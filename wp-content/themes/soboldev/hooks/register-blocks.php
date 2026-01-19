<?php declare(strict_types=1);

namespace SobolDev\Blocks;

if (!defined('ABSPATH')) {
	exit;
}

function includeIfExists($file) {
	return file_exists($file) ? include $file : false;
}

function get_blocks()
{
	static $blocks;

	if (isset($blocks)) {
		return $blocks;
	}

	$blocks_root_folder = get_template_directory() . '/blocks/';

	foreach (scandir($blocks_root_folder) as $entity) {
		if ($entity === '.' or $entity === '..' or !is_dir($blocks_root_folder . $entity)) {
			continue;
		}

		$blocks[] = $blocks_root_folder . $entity;
	}

	return $blocks;
}

function preview($dir) {
	$path = get_template_directory_uri() . str_replace(get_template_directory(), '', $dir) . '/';
	if (theme_env("SITE_ENV", "production") === "production" && is_array(\SobolDev\Vite\Data::$manifest)) {
		$assets = [];
		$only_path = ltrim(str_replace(get_option('siteurl'), '', $path), '/');

		foreach (array_keys(\SobolDev\Vite\Data::$manifest) as $asset) {
			if (str_starts_with($asset, $only_path)) {
				$src = get_theme_file_uri(DIRECTORY_SEPARATOR.'dist'.DIRECTORY_SEPARATOR. \SobolDev\Vite\Data::$manifest[$asset]['file']);
				if (str_ends_with($asset, '.js')) {
					echo '<script type="module" src="' . esc_attr($src) . '"></script>';
				} else {
					echo '<link rel="stylesheet" href="' . esc_attr($src) . '" media="all" />';
				}
			}
		}
	} else {
		$path = str_replace(get_option('siteurl'), 'https://' . getenv('SITE_URL') . ':' . getenv('DOCKER_VITE_PORT'), $path);
		$assets = [
			$path . 'styles.scss',
			$path . 'script.js',
			$path . 'script-editor.js',
		];
		echo '<script type="module">' . join('', array_map(function ($a) {
			return 'import("' . esc_attr($a) . '");';
		}, $assets)) . '</script>';
	}
}

add_action('init', function () {
	foreach (get_blocks() as $path) {
		if (!file_exists($path . DIRECTORY_SEPARATOR . 'block.json')) {
			continue;
		}

		if (file_exists($path . DIRECTORY_SEPARATOR . 'class.php')) {
			$blockClassName = require_once ($path . DIRECTORY_SEPARATOR . 'class.php');
			$blockClass = new $blockClassName($path);
			$blockClass->register(
				json_decode(file_get_contents($path . DIRECTORY_SEPARATOR . 'block.json'), true)
			);
		} else {
			$settings = [];
			if (is_readable($path . DIRECTORY_SEPARATOR . 'block.json')) {
				$block_settings = json_decode(file_get_contents($path . DIRECTORY_SEPARATOR . 'block.json'), true);
				if (array_key_exists('acf', $block_settings) && array_key_exists('default_settings', $block_settings['acf'])) {
					$settings = $block_settings['acf']['default_settings'];
				}
			}
			register_block_type($path . DIRECTORY_SEPARATOR . 'block.json', ['data' => $settings]);
		}
		includeIfExists($path . DIRECTORY_SEPARATOR . 'functions.php');
	}
});

add_filter('acf/settings/load_json', function ($paths) {
	return array_merge($paths, [get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'acf-json'], get_blocks());
});

add_filter('acf/settings/save_json', function ($path) {
	if (array_key_exists('acf_field_group', $_REQUEST) && array_key_exists('key', $_REQUEST['acf_field_group'])) {
		$paths = acf_get_setting( 'load_json' );
		$file = sprintf('/%s.json', $_REQUEST['acf_field_group']['key']);
		foreach($paths as $_path) {
			if (file_exists($_path . $file)) {
				return $_path;
			}
		}
	}

	return $path;
});

add_filter(
	'acf/pre_save_block',
	function ($attributes) {
		if (!array_key_exists('anchor', $attributes) or $attributes['anchor'] === '') {
			$attributes['anchor'] = 'block-' . uniqid();
		}
		return $attributes;
	}
);
