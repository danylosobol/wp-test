<?php declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

if (function_exists('get_reusable_block_by_title')) {
	return;
}

function get_reusable_block_by_title($title)
{
	$query = new WP_Query(
		[
			'post_type'              => 'wp_block',
			'title'                  => $title,
			'post_status'            => 'all',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'orderby'                => 'post_date ID',
			'order'                  => 'ASC',
		]
	);

	if (!empty($query->post)) {
		return $query->post;
	}
	return null;
}

add_action('admin_menu', function () {
	add_menu_page('Reusable Blocks', 'Reusable Blocks', 'edit_posts', 'edit.php?post_type=wp_block', '', 'dashicons-editor-table', 33);
});
