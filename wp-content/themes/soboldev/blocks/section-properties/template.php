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

// Support custom "anchor" values.
$anchor = '';
if (!empty($block['anchor'])) {
	$anchor = 'id="' . esc_attr($block['anchor']) . '" ';
}

// Create class attribute allowing for custom "className" and "align" values.
$class_name = ['theme-section-properties'];
if (!empty($block['className'])) {
	array_push($class_name, $block['className']);
}

$fields = get_fields();
$per_page = $fields['per_page'] ?? 3;
$properties = get_properties($per_page);
?>
<section <?php echo $background_attr ?> <?php echo $anchor; ?>class="<?php echo esc_attr(join(' ', $class_name)); ?>">
	<div class="theme-container">
		<div class="content">
			<?php if (!empty($fields['title'])): ?>
				<h1 class="title"><?php echo esc_html($fields['title']) ?></h1>
			<?php endif; ?>
			<?php if (is_array($properties['posts']) && sizeof($properties['posts']) > 0): ?>
				<div class="header">
					<button class="no-btn sort-button"
						type="button"><?php echo __('Sort By price (Low to High)', 'soboldev') ?></button>
					<?php if (!empty($properties['total'])): ?>
						<p class="no-margin total">
							<?php echo sprintf('%d %s', $properties['total'] ?? 0, __('items', 'soboldev')) ?>
						</p>
					<?php endif; ?>
				</div>
				<div class="blocks">
					<?php foreach ($properties['posts'] as $property): ?>
						<?php get_template_part('template-parts/property', 'card', ['ID' => $property]) ?>
					<?php endforeach; ?>
				</div>
				<?php if ($properties['current_page'] < $properties['total_pages']): ?>
					<div class="button-wrapper">
						<button data-per-page="<?php echo esc_attr($per_page) ?>" class="theme-button-primary load-more" type="button">
							<?php echo __('Load more', 'soboldev') ?>
						</button>
					</div>
				<?php endif; ?>
			<?php else: ?>
				<p><?php echo __('Not found', 'soboldev') ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>