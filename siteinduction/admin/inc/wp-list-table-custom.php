<?php
// WP_List_Table is not loaded automatically so we need to load it in our application
if(!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class wpListTableCustom extends WP_List_Table {

  function __construct() {
    global $status, $page;
    parent::__construct(array(
        'singular' => 'log',
        'plural' => 'logs',
    ));
  }

  function column_default($item, $column_name) {
    //TODO: refactoring this to pass uuid instead of id
    return "<a href='?page=%s&post_type=intellicentre&page=invitation-view&id=$item[id]'>" . $item[$column_name]. "</a>";
  }

  function column_cb($item) {
    return sprintf(
      '<input type="checkbox" name="id[]" value="%s" />',
      $item['id']
    );
  }

  function get_columns() {
    $columns = array(
      'cb' => '<input type="checkbox" />',
      'firstname' => __('First Name', 'invitation_log_table'),
      'lastname' => __('Last Name', 'invitation_log_table'),
      'email' => __('E-Mail', 'invitation_log_table'),
      'company' => __('Company', 'invitation_log_table'),
      'contracted_to_company' => __('Contracted to Company', 'invitation_log_table'),
      'facility_servicing_contractor' => __('Facility Servicing Contractor', 'invitation_log_table'),
      'date_sent' => __('Date Sent', 'invitation_log_table'),
      'date_completed' => __('Date Completed', 'invitation_log_table'),
      'location' => __('Location', 'invitation_log_table'),
      'appointment_date' => __('Appointment Date (UTC)', 'invitation_log_table')
    );
    return $columns;
  }

  function get_sortable_columns() {
    $sortable_columns = array(
      'firstname' => array('firstname', true),
      'lastname' => array('lastname', true),
      'email' => array('email', false),
      'company' => array('company', true),
      'contracted_to_company' => array('contracted_to_company', true),
      'facility_servicing_contractor' => array('facility_servicing_contractor', true),
      'date_sent' => array('date_Sent', false),
      'date_completed' => array('date_completed', false),
      'location' => array('location', false),
      'appointment_date' => array('appointment_date', false)
    );
    return $sortable_columns;
  }

  function prepare_items($search = '') {
    global $wpdb;
    $table_name_reservation = $wpdb->prefix . 'site_induction_reservation';
    $table_name_appointment = $wpdb->prefix . 'site_induction_appointments';
    $query_settings = $this->getQuerySettings();
    $select_columns = "r.id,r.firstname,r.lastname,r.email,r.date_sent,r.date_completed,r.company,r.contracted_to_company,r.facility_servicing_contractor,a.location,a.appointment_date ";
    $where_clause = $this->process_filter_query($search);
    $from_table = " $table_name_reservation AS r LEFT JOIN $table_name_appointment AS a ON r.uuid = a.uuid ";
    $tbl_query_search = "SELECT $select_columns FROM $from_table WHERE $where_clause ORDER BY $query_settings[orderby] $query_settings[order]";

    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);

    $this->items = $wpdb->get_results($tbl_query_search, ARRAY_A);

    $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $from_table WHERE $where_clause");
    $this->set_pagination_args(array(
      'total_items' => $total_items,
      'per_page' => $query_settings['per_page'],
      'total_pages' => ceil($total_items / $query_settings['per_page'])
      )
    );
  }

  private function getQuerySettings($search = '') {
    $settings['per_page'] = 30;
    $settings['paged'] = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] -1) * $settings['per_page']) : 0;
    $settings['orderby'] = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id';
    $settings['order'] = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc';


    return $settings;
  }

  function process_filter_query($search) {
    $filter_value = $this->current_action();

    if($search) {
        $search_query = " (r.firstname LIKE '%%$search%%' OR r.lastname LIKE '%%$search%%' OR r.email LIKE '%%$search%%' OR r.company LIKE '%%$search%%' OR r.contracted_to_company LIKE '%%$search%%')";
        return $search_query;
    } else if ($filter_value)  {
        if($filter_value == 'completed') {
            $filter_query = " r.date_completed IS NOT NULL";
        } else {
            $filter_query = " r.date_completed IS NULL";
        }
      return $filter_query;
    }

    return " r.id IS NOT NULL";
  }

  function get_bulk_actions() {
    $actions = array(
      'completed' => 'Completed',
      'not_completed' => 'Not Completed'
    );
    return $actions;
  }

  protected function bulk_actions($which = '') {
    $actionIndex = '2';
    $bulkActionOption = '';
    if (is_null($this->_actions)) {
      $this->_actions = $this->get_bulk_actions();
      $actionIndex = '';
    }
    foreach ($this->_actions as $optionValue => $optionTitle) {
      $bulkActionOption .= '<option value="'. $optionValue .'">' . $optionTitle . "</option>";
    }
    include_once 'templates/bulk-action.php';
  }
}