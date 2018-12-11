<?php

/**
 Any filters related to the Site Induction Plugin
 Note: The ACF field group for this plugin must be called "Intellicentre Induction Quiz"
 **/

// Add filters to modify how ACF reads and saves config for the site induction plugin
add_filter('acf/settings/load_json', 'site_induction_acf_json_load'); 
add_filter('acf/settings/save_json', 'site_induction_acf_json_save');

// Add our plugin acf-json directory to ACF's search path
function site_induction_acf_json_load($paths) {
  $paths[] = plugin_dir_path(dirname(__FILE__)) . 'acf-json';
  return $paths;
}

// Override the save path for this plugin if it's related to quiz field group
function site_induction_acf_json_save($paths) {
  if (isset($_POST['acf_field_group']['title']) && ($_POST['acf_field_group']['title'] == "Intellicentre Induction Quiz")) {
    return plugin_dir_path(dirname(__FILE__)) . 'acf-json';
  }
  return $paths;
}