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
		'core/heading',
		[
			'level' => 3,
			'textAlign' => 'left',
			'content' => 'Card title'
		],
	],
	[
		'core/paragraph',
		[
			'textAlign' => 'left',
			'content' => 'Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make'
		],
	],
];
$innerblocks = [
	'core/heading',
	'core/paragraph',
	'core/buttons'
];

// Support custom "anchor" values.
$anchor = '';
if (!empty($block['anchor'])) {
	$anchor = 'id="' . esc_attr($block['anchor']) . '" ';
}

// Create class attribute allowing for custom "className" and "align" values.
$class_name = ['theme-card', 'theme-column-element'];
if (!empty($block['className'])) {
	array_push($class_name, $block['className']);
}

$fields = get_fields();
$class_name = array_merge($class_name, get_default_values([
	'type' => 'text-only',
	'size' => 'small',
	'color' => 'g400'
], $fields));

$show_image = (in_array($fields['type'], ['image-right', 'image-bottom']) && !empty($fields['image']));
?>
<div <?php echo $anchor; ?> class="<?php echo esc_attr(join(' ', $class_name)); ?>">
	<div class="inner">
		<InnerBlocks class="text-wrapper theme-text-element" allowedBlocks="<?php echo esc_attr(json_encode($innerblocks)); ?>"
			template="<?php echo esc_attr(json_encode($innerTemplate)); ?>" />
		<?php if ($show_image): ?>
			<div class="image">
				<?php echo wp_get_attachment_image($fields['image'], 'thumbnail', false, ['loading' => 'lazy']) ?>
			</div>
		<?php endif; ?>
	</div>
</div>