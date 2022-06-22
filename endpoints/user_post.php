<?php 

function api_user_post($request) {
  $email = sanitize_email($request['email']);
  $name = sanitize_text_field($request['name']);
  $phone = sanitize_text_field($request['phone']);
  $password = $request['password'];

  if (empty($email) || empty($name) || empty($password)) {
    $response = new WP_Error('error', 'Dados incompletos', ['status' => 406]);
    return rest_ensure_response($response);
  }

  if (username_exists($email) || email_exists($email)) {
    $response = new WP_Error('error', 'E-mail já cadastrado', ['status' => 403]);
    return rest_ensure_response($response);
  }

  $user_id = wp_create_user($email, $password, $email);

  $response = [
    'ID' => $user_id,
    'display_name' => $name,
    'first_name' => $name,
    'role' => 'subscriber',
  ];
  wp_update_user($response);

  update_user_meta($user_id, 'phone', $phone);

  return rest_ensure_response($response);
}

function register_api_user_post() {
  register_rest_route('api', '/user', [
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => 'api_user_post',
  ]);
}
add_action('rest_api_init', 'register_api_user_post');

?>