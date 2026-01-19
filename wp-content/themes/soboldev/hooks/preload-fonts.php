<?php declare(strict_types=1);
add_filter('wp_preload_resources', function ($preload_resources = []) {
	$preload_resources = [];
	foreach (array_keys(\SobolDev\Vite\Data::$manifest) as $asset) {
		if (preg_match('/\.(ttf|woff2?)$/', $asset)) {
			$preload_resources[] = [
				'href' => get_theme_file_uri('/dist/' . \SobolDev\Vite\Data::$manifest[$asset]['file']),
				'as' => 'font'
			];
		}
	}
	return $preload_resources;
}, 10, 1);
