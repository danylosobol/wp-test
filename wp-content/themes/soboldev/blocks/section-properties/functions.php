<?php

if (!function_exists('get_properties')) {
  function get_properties(int $per_page = 3, int $current_page = 1, string $sort = 'ASC'): array
  {
    $base_args = [
      'post_type' => 'property',
      'posts_per_page' => $per_page,
      'paged' => $current_page,
      'fields' => 'ids',
      'post_status' => 'publish',

    ];

    if (!in_array($sort, ['ASC', 'DESC'])) {
      $sort = 'DESC';
    }

    $base_args['meta_key'] = 'price';
    $base_args['orderby'] = 'meta_value_num';
    $base_args['order'] = $sort;

    $query = new WP_Query($base_args);

    return [
      'posts' => $query->posts,
      'total' => $query->found_posts,
      'per_page' => $per_page,
      'current_page' => $current_page,
      'total_pages' => $query->max_num_pages,
    ];
  }
}

if (!function_exists('ajax_properties_handler')) {
  function ajax_properties_handler()
  {
    $sort = sanitize_text_field($_POST['sort']);
    $current_page = (int) sanitize_text_field($_POST['current_page']);
    $per_page = (int) sanitize_text_field($_POST['per_page']);

    $properties = get_properties($per_page, $current_page, $sort);

    if (!is_array($properties['posts'])) {
      wp_send_json_error(['message' => __('Error during get properties.')]);
    }

    $html = [];

    foreach ($properties['posts'] as $property) {
      ob_start();
      get_template_part('template-parts/property', 'card', ['ID' => $property]);
      $html[] = ob_get_clean();
    }

    $response = $properties;
    $response['html'] = $html;

    wp_send_json_success($response);
  }


  add_action('wp_ajax_load_properties', 'ajax_properties_handler');
  add_action('wp_ajax_nopriv_load_properties', 'ajax_properties_handler');
}