<?php 

function api_pet_post($request) {
  $user = wp_get_current_user();
  $user_id = $user->ID;

  if ($user_id === 0) {
    $response = new WP_Error('error', 'O usuário não possui permissão.', ['status' => 401]);
    return rest_ensure_response($response);
  }

  $name = sanitize_text_field($request['name']);
  $specie = sanitize_text_field($request['specie']);
  $sex = sanitize_text_field($request['sex']);
  $region = sanitize_text_field($request['region']);
  $comment = sanitize_text_field($request['comment']);
  $files = $request->get_file_params();

  if (empty($name) 
  || empty($specie) 
  || empty($sex) 
  || empty($region) 
  || empty($files)) {
    $response = new WP_Error('error', 'Dados incompletos.', ['status' => 422]);
    return rest_ensure_response($response);
  }

  $response = [
    'post_author' => $user_id,
    'post_type' => 'post',
    'post_status' => 'publish',
    'post_title' => $name,
    'post_content' => $comment,
    'files' => $files,
    'meta_input' => [
      'specie' => $specie,
      'sex' => $sex,
      'sex' => $sex,
      'region' => $region,
      'status' => 'lost'
    ],
  ];
  $post_id = wp_insert_post($response);

  require_once ABSPATH . 'wp-admin/includes/image.php';
  require_once ABSPATH . 'wp-admin/includes/file.php';
  require_once ABSPATH . 'wp-admin/includes/media.php';

  $photo_id = media_handle_upload('img', $post_id);
  update_post_meta($post_id, 'img', $photo_id);

  return rest_ensure_response($response);
}

function register_api_pet_post() {
  register_rest_route('api', '/pet', [
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => 'api_pet_post',
  ]);
}
add_action('rest_api_init', 'register_api_pet_post');

?>