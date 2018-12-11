<?php

function appointment_reservation_custom_post_types() {

  $labels = array(
    'name' => __( 'Intellicentres', 'intellicentre' ),
    'singular_name' => __( 'Intellicentre', 'intellicentre' ),
    'add_new' => __( 'Add New Intellicentre', 'intellicentre' ),
    'add_new_item' => __( 'Add New Intellicentre', 'intellicentre' ),
    'edit_item' => __( 'Edit Intellicentre', 'intellicentre' ),
    'new_item' => __( 'New Intellicentre', 'intellicentre' ),
    'view_item' => __( 'View Intellicentre', 'intellicentre' ),
    'search_items' => __( 'Search Intellicentre', 'intellicentre' ),
    'not_found' => __( 'No intellicentre found', 'intellicentre' ),
    'not_found_in_trash' => __( 'No intellicentre found in Trash', 'intellicentre' ),
    'menu_name' => __( 'Intellicentres', 'intellicentre' ),
  );

  $args = array(
    'labels' => $labels,
    'hierarchical' => false,
    'description' => 'List of Intellicentres',
    'supports' => array( 'title' ),
    'taxonomies' => array(''),
    'public' => false,
    'show_ui' => true,
    'show_in_nav_menus' => true,
    'publicly_queryable' => false,
    'exclude_from_search' => true,
    'has_archive' => false,
    'query_var' => true,
    'can_export' => true,
    'rewrite' => true,
    'show_in_menu' => false,
    'capability_type' => array('intellicentre', 'intellicentres'),
    'capabilities' => array('delete_posts' => 'delete_intellicentres')
  );
  register_post_type( 'intellicentre', $args );

}

function plugin_frontend_styles() {
  wp_enqueue_style('jquery-ui', WPAR_PLUGIN_URL . 'admin/library/assets/css/jquery-ui.min.css');
}

function plugin_admin_styles() {
  wp_enqueue_style('admin-style', WPAR_PLUGIN_URL . 'admin/library/assets/css/admin-style.css');
}

function plugin_admin_scripts() {
  wp_enqueue_media();
  wp_enqueue_script('admin-scripts', WPAR_PLUGIN_URL . 'admin/library/assets/js/admin-scripts.js');
}

function remove_unnecessary_metaboxes() {
  remove_meta_box( 'mymetabox_revslider_0', 'intellicentre', 'normal' );
  remove_meta_box( 'relevanssi_hidebox', 'intellicentre', 'normal' );
}

function add_site_induction_role() {
  // Create Site Induction Role
  add_role('site_induction', 'Site Induction', array('read' => true));

  // Create Capabilities
  $capabilities = array(
    'edit_intellicentre' => true,
    'read_intellicentre' => true,
    'delete_intellicentre' => true,
    'edit_intellicentres' => true,
    'edit_other_intellicentres' => true,
    'edit_others_intellicentres' => true,
    'edit_published_intellicentres' => true,
    'publish_intellicentres' => true,
    'read_private_intellicentres' => true,
    'delete_intellicentres' => true
  );
    
  // Apply Capabilities to Site Induction & Administrator
  $users = array('site_induction', 'administrator');
  foreach($users as $user) { 
    add_capabilities($user, $capabilities);
  }
}

function add_capabilities( $user, $capabilities ) {
  $role = get_role($user);
  foreach($capabilities as $cap => $value) { 
    $role->add_cap($cap);
  }
}

function token_generator_endpoint() {
  add_rewrite_tag('%app_id%', '([^&]+)');
  add_rewrite_tag('%app_secret%', '([^&]+)');
  add_rewrite_rule(
    'site-induction/api/auth/app-id/([^&]+)/app-secret/([^&]+)/?', 
    'index.php?site_induction_api=1&app_id=$matches[1]&app_secret=$matches[2]', 
    'top'
  );
}

function query_vars( $vars ) {
  $vars[] = 'site_induction_api';
  return $vars;
}