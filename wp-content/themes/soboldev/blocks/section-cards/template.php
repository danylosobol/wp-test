<?php

/**
 * Base Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $post_id The post ID the block is rendering content against.
 *          This is either the post ID currently being displayed inside a query loop,
 *          or the post ID of the post hosting this block.
 * @param   array $context The context provided to the block by the post or its parent block.
 */

if (!defined('ABSPATH')) {
	exit;
}

// Setting up allowed inner blocks.
$innerTemplate = [
	[
		'theme/card',
	],
];
$innerblocks = [
	'theme/card',
];

// Support custom "anchor" values.
$anchor = '';
if (!empty($block['anchor'])) {
	$anchor = 'id="' . esc_attr($block['anchor']) . '" ';
}

// Create class attribute allowing for custom "className" and "align" values.
$class_name = ['theme-section-cards'];
if (!empty($block['className'])) {
	array_push($class_name, $block['className']);
}

$fields = get_fields();
?>
<section <?php echo $background_attr ?> <?php echo $anchor; ?>class="<?php echo esc_attr(join(' ', $class_name)); ?>">
	<div class="theme-container">
		<div class="content">
			<?php if (!empty($fields['title'])): ?>
				<h1 class="title"><?php echo esc_html($fields['title']) ?></h1>
			<?php endif; ?>
			<InnerBlocks class="blocks" allowedBlocks="<?php echo esc_attr(json_encode($innerblocks)); ?>"
				template="<?php echo esc_attr(json_encode($innerTemplate)); ?>" />
		</div>
	</div>
</section>