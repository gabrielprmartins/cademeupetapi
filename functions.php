<?php 

remove_action('rest_api_init', 'create_initial_rest_routes', 99);

$dirbase = get_template_directory();
require_once $dirbase . '/endpoints/user_post.php';

function change_api() {
  return 'json';
} 
add_filter('rest_url_prefix', 'change_api');

?>