<?php
/*
Plugin Name: Site Induction
Plugin URI:
Description: 
Version:     1.0.0
Author:      
Author URI:  
License:     
License URI: 
*/

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );

define('WPAR_PLUGIN', __FILE__ );

define('WPAR_PLUGIN_BASENAME', plugin_basename(WPAR_PLUGIN));

define('WPAR_PLUGIN_NAME', trim(dirname(WPAR_PLUGIN_BASENAME), '/'));

define('WPAR_PLUGIN_DIR', untrailingslashit(dirname(WPAR_PLUGIN)));

define('WPAR_PLUGIN_URL', plugin_dir_url(WPAR_PLUGIN));

register_activation_hook(__FILE__, 'siteInductionInstall');

require_once WPAR_PLUGIN_DIR . '/library/functions.php';
require_once WPAR_PLUGIN_DIR . '/library/SiteInduction.php';