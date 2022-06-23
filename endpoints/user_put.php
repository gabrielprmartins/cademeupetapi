<?php

function api_user_put($request) {
  $user = wp_get_current_user();
  $user_id = $user->ID;

  if($user_id > 0) {
    $email = sanitize_email($request['email']);
    $name = sanitize_text_field($request['name']);
    $phone = sanitize_text_field($request['phone']);
    $password = $request['password'];

    $email_exists = email_exists($email);

    if(!$email_exists || $email_exists === $user_id) {
      $response = array(
        'ID' => $user_id,
        'user_pass' => $password,
        'user_email' => $email,
        'display_name' => $name,
        'first_name' => $name,
      );
      wp_update_user($response);

      update_user_meta($user_id, 'phone', $phone);
    } else {
      $response = new WP_Error('error', 'Email já cadastrado.', array('status' => 403));
    }
  } else {
    $response = new WP_Error('error', 'Usuário não possui permissão.', array('status' => 401));
  }
  return rest_ensure_response($response);
}

function register_api_user_put() {
  register_rest_route('api', '/user', array(
    array(
      'methods' => WP_REST_Server::EDITABLE,
      'callback' => 'api_user_put',
    ),
  ));
}

add_action('rest_api_init', 'register_api_user_put');


?>