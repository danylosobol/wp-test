<?php declare(strict_types=1);

require_once __DIR__ . '/hooks/setup.php'; // Setting up theme hooks.

add_action('after_setup_theme', function () {
  load_theme_textdomain('soboldev', get_template_directory() . '/languages');
});

function get_default_values(array $default_values, array|bool $fields = []): array
{
  if (!is_array($default_values)) {
    return [];
  }

  $result = [];

  foreach ($default_values as $key => $value) {
    $default_value = (is_array($fields) && !empty($fields[$key])) ? $fields[$key] : $value;
    $result[] = sprintf('%s-%s', $key, $default_value);
  }

  return $result;
}