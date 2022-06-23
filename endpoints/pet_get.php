<?php 

// Get only pet
function photo_data($post) {
  $post_meta = get_post_meta($post->ID);
  $src = wp_get_attachment_image_src($post_meta['img'][0], 'large')[0];
  $user = get_userdata($post->post_author);
  $total_comments = get_comments_number($post->ID);

  return [
    'id' => $post->ID,
    'author' => $user->display_name,
    'title' => $post->post_title,
    'date' => $post->post_date,
    'src' => $src,
    'specie' => $post_meta['specie'][0],
    'sex' => $post_meta['sex'][0],
    'region' => $post_meta['region'][0],
    'comment' => $post->post_content,
    'status' => $post_meta['status'][0],
  ];
}

function api_pet_get($request) {
  $post_id = $request['id'];
  $post = get_post($post_id);

  if (!isset($post) || empty($post_id)) {
    $response = new WP_Error('error', 'Pet não encontrado.', ['status' => 404]);
    return rest_ensure_response($response);
  }

  $photo = photo_data($post);

  return rest_ensure_response($photo);
}

function register_api_pet_get() {
  register_rest_route('api', '/pet/(?P<id>[0-9]+)', [
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'api_pet_get',
  ]);
}
add_action('rest_api_init', 'register_api_pet_get');

// Get all pets
function api_pets_get($request) {
  $_total = sanitize_text_field($request['_total']) ?: 9;
  $_page = sanitize_text_field($request['_page']) ?: 1;
  $_user = sanitize_text_field($request['_user']) ?: 0;

  if (!is_numeric($_user)) {
    $user = get_user_by('login', $_user);
    if (!$user) {
      $response = new WP_Error('error', 'Usuário não encontrado.', ['status' => 404]);
      return rest_ensure_response($response);
    }
    $_user = $user->ID;
  }

  $args = [
    'post_type' => 'post',
    'author' => $_user,
    'posts_per_page' => $_total,
    'paged' => $_page,
  ];

  $query = new WP_Query($args);
  $posts = $query->posts;

  $photos = [];
  if ($posts) {
    foreach ($posts as $post) {
      $photos[] = photo_data($post);
    }
  }

  return rest_ensure_response($photos);
}

function register_api_pets_get() {
  register_rest_route('api', '/pet', [
    'methods' => WP_REST_Server::READABLE,
    'callback' => 'api_pets_get',
  ]);
}
add_action('rest_api_init', 'register_api_pets_get');

?>