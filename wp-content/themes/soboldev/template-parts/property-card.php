<?php
if (empty($args['ID'])) {
  return;
}
$fields = get_fields($args['ID']);
$class_name = array_merge(['theme-property-card'], $args['classes'] ?? []);
$thumbnail = get_post_thumbnail_id($argc['ID']);
$excerpt = get_the_excerpt($args['ID']);
$link = get_permalink($args['ID']);
?>

<div class="<?php echo esc_attr(join(' ', $class_name)) ?>">
  <div class="inner">
    <div class="image">
      <?php if (!empty($thumbnail)): ?>
        <?php echo wp_get_attachment_image($thumbnail, 'thumbnail', false, ['loading' => 'lazy']) ?>
      <?php endif; ?>
    </div>

    <div class="meta theme-text-element">
      <div class="top">
        <div class="header-card">
          <?php if (!empty($fields['price'])): ?>
            <h2 class="price"><?php echo sprintf('$%s', number_format(intval($fields['price']), 0, '.', ',')) ?></h2>
          <?php endif; ?>

          <?php if (!empty($fields['price']) && !empty($fields['meters'])):
            $price_per_meters = intval($fields['price']) / intval($fields['meters']); ?>
            <p class="per-meter">
              <?php echo sprintf('$%d %s', number_format(intval($price_per_meters), 0, '.', ','), __('for м2', 'soboldev')) ?>
            </p>
          <?php endif; ?>
        </div>
        <?php if (!empty($fields['address'])): ?>
          <address class="address">
            <?php echo esc_html($fields['address']) ?>
          </address>
        <?php endif; ?>
      </div>
      <?php if (!empty($fields['meters']) || !empty($fields['rooms']) || (!empty($fields['floor']) && !empty($fields['max_floor']))): ?>
        <ul class="list no-list">
          <?php if (!empty($fields['meters'])): ?>
            <li class="meters"><?php echo sprintf('%s м2', esc_html($fields['meters'])) ?></li>
          <?php endif; ?>
          <?php if (!empty($fields['rooms'])): ?>
            <li class="rooms"><?php echo sprintf('%s %s', esc_html($fields['rooms']), __('rooms', 'soboldev')) ?></li>
          <?php endif; ?>
          <?php if (!empty($fields['floor']) && !empty($fields['max_floor'])): ?>
            <li class="floors">
              <?php echo sprintf('%s/%s %s', esc_html($fields['floor']), esc_html($fields['max_floor']), __('floor', 'soboldev')) ?>
            </li>
          <?php endif; ?>
        </ul>
      <?php endif; ?>
      <?php if (!empty($excerpt)): ?>
        <p class="excerpt">
          <?php echo esc_html($excerpt) ?>
        </p>
      <?php endif; ?>
      <?php if (!empty($link)): ?>
        <a class="link no-text-decoration"
          href="<?php echo esc_url($link) ?>"><?php echo __('More details', 'soboldev') ?></a>
      <?php endif; ?>
    </div>
  </div>
</div>