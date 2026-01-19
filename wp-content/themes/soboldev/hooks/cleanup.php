<?php declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

// Removes wp version from styles links.
function remove_wp_version_from_url($src)
{
	if (!is_string($src)) {
		return $src;
	}

	if (strpos($src, 'ver=' . get_bloginfo('version'))) {
		$src = remove_query_arg('ver', $src);
	}
	return $src;
}

add_action('init', function () {
	add_filter('style_loader_src', 'remove_wp_version_from_url', 9999);
	add_filter('script_loader_src', 'remove_wp_version_from_url', 9999);

	remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');

	// Remove dns-prefetch Link from WordPress Head (Frontend).
	remove_action('wp_head', 'wp_resource_hints', 2);

	// Removes Generator meta tag.
	remove_action('wp_head', 'wp_generator');

	// Removes wlwmanifest_link.
	remove_action('wp_head', 'wlwmanifest_link');

	// Remove EditURI
	remove_action('wp_head', 'rsd_link');

	// Disable XMLRPC.
	add_filter('xmlrpc_enabled', '__return_false');

	// Disable unnecessary tags from WPSEO plugin.
	add_filter( 'wpseo_debug_markers', '__return_false' );

	// Disable XMLRPC
	add_filter('xmlrpc_enabled', '__return_false');

	// Disable the links to the extra feeds such as category feeds
	remove_action( 'wp_head', 'feed_links_extra', 3 );

	// Disable the links to the general feeds: Post and Comment Feed
	remove_action( 'wp_head', 'feed_links', 2 );

	// Remove Rank Math SEO plugin comment
	add_filter( 'rank_math/frontend/remove_credit_notice', '__return_true' );
});