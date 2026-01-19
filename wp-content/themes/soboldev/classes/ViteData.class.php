<?php declare(strict_types=1);
namespace SobolDev\Vite;

if (!defined('ABSPATH') || (defined('DOING_CRON') && DOING_CRON) || (defined('DOING_AJAX') && DOING_AJAX) || (defined('REST_REQUEST') && REST_REQUEST)) {
	return;
}

class Data
{
	public static $assets_to_move = [];
	public static $manifest;
	public static $tmp_cssfile;

	public static function addAssetToMoveList($asset)
	{
		self::$assets_to_move[] = $asset;
	}

	public static function getAssertsToMoveList()
	{
		return self::$assets_to_move;
	}

	public static function getManifest()
	{
		if (is_null(self::$manifest)) {
			$file = get_theme_file_path('/dist/.vite/manifest.json');
			if (is_file($file)) {
				self::$manifest = json_decode(file_get_contents($file), true);
			}
		}
		return self::$manifest;
	}
}
