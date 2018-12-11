<?php
/**
 * @todo Think of a better way on handling additional table fields or creating a new table        * @todo Extract SQL from PHP
 * @todo Code refactoring
 * @todo Automated testing
 */
function siteInductionInstall() {
    global $wpdb;
    global $site_induction_db_version;

    $table_name_reservation = $wpdb->prefix . 'site_induction_reservation';
    $sql_reservation = "CREATE TABLE " . $table_name_reservation . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      fullname tinytext NOT NULL,
      firstname VARCHAR(100) NOT NULL,
      lastname VARCHAR(100) NOT NULL,
      email VARCHAR(100) NOT NULL,
      facility_servicing_contractor VARCHAR(10) NULL,
      company VARCHAR(100) NULL,
      contracted_to_company VARCHAR(100) NULL,
      date_sent datetime NULL,
      date_completed datetime NULL,
      link VARCHAR(200) NOT NULL,
      uuid VARCHAR(15) NULL,
      location VARCHAR(200) NOT NULL,
      PRIMARY KEY  (id)
    );";

    $table_name_appointments = $wpdb->prefix . 'site_induction_appointments';
    $sql_appointments = "CREATE TABLE " . $table_name_appointments . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      uuid VARCHAR(15) NOT NULL,
      location VARCHAR(200) NOT NULL,
      appointment_date TIMESTAMP NOT NULL,
      PRIMARY KEY  (id)
    );";

    $view_name_disabled_dates = $wpdb->prefix . 'disabled_dates';
    $sql_disabled_dates = "CREATE VIEW $view_name_disabled_dates AS SELECT r.uuid,LOWER(IF(r.contracted_to_company IS NULL OR r.contracted_to_company = '',r.company,r.contracted_to_company)) AS company, a.location, a.appointment_date FROM  $table_name_reservation AS r LEFT JOIN $table_name_appointments AS a ON r.uuid = a.uuid;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_reservation);
    dbDelta($sql_appointments);
    $wpdb->get_results($sql_disabled_dates);
    add_option('site_induction_db_version', $site_induction_db_version);
  }