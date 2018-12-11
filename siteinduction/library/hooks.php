<?php

$mailHostSettings = array(
  'host' => getenv('SMTP_HOST') ? getenv('SMTP_HOST') : 'localhost',
  'username' => getenv('SMTP_USERNAME'),
  'password' => getenv('SMTP_PASSWORD'),
  'smtpAuth' => getenv('SMTP_AUTH') ? (boolean) "false" : false,
  'smptAutoTLS' => getenv('SMTP_AUTO_TLS') ? (boolean) getenv('SMTP_AUTO_TLS') : false,
  'encryption' => getenv('SMTP_ENCRYPTION') ? getenv('SMTP_ENCRYPTION') : '',
  'port' => getenv('SMTP_PORT') ? getenv('SMTP_PORT') : 25
);
$mailer = new CustomMailer($mailHostSettings);

/** SiteInduction **/
$siteInduction = new SiteInduction($mailer);
add_action('wp_ajax_request_site_induction_data', array($siteInduction, 'requestSiteInductionData'));
add_action('wp_ajax_nopriv_request_site_induction_data', array($siteInduction, 'requestSiteInductionData'));
add_action('wp_ajax_get_disabled_dates', array($siteInduction, 'getDisabledDates'));
add_action('wp_ajax_nopriv_get_disabled_dates', array($siteInduction, 'getDisabledDates'));
add_action('wp_ajax_process_dci_data', array($siteInduction, 'processDciData'));
add_action('wp_ajax_nopriv_process_dci_data', array($siteInduction, 'processDciData'));
add_action('wp_ajax_validate_date_not_full', array($siteInduction, 'validateDateNotFull'));
add_action('wp_ajax_nopriv_validate_date_not_full', array($siteInduction, 'validateDateNotFull'));
add_action('wp_enqueue_scripts', 'plugin_frontend_styles');

$siteInductionAuthorization = new SiteInductionAuthorization($siteInduction);
add_action('query_vars', 'query_vars');
add_action('init', 'token_generator_endpoint');
add_action('template_include', array($siteInductionAuthorization, 'processRequest'));

/** Admin **/
if (is_admin()) {
  $admin = new Admin($siteInduction, $mailer);
  add_action('admin_menu', array($admin, 'renderPluginAdminMenu'));
  add_action('init', 'appointment_reservation_custom_post_types');
  add_action('admin_enqueue_scripts', 'plugin_admin_styles');
  add_action('admin_enqueue_scripts', 'plugin_admin_scripts');
  add_action('do_meta_boxes', 'remove_unnecessary_metaboxes');
  add_action('admin_init','add_site_induction_role');
}