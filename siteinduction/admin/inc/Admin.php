<?php

include "wp-list-table-custom.php";

class Admin
{
  const TEMPLATE_DIR = WPAR_PLUGIN_DIR . '/admin/inc/templates/';

  // vars
  protected $slug = 'edit.php?post_type=intellicentre';
  protected $cap = 'read_intellicentre';

  private $customMailer;
  private $siteInduction;
  private $editorSettings;
  private $baseUrl;
  private $path;
  public $emailLogo;

  function __construct($siteInduction, $mailer = array())
  {
    $this->siteInduction = $siteInduction;
    $staticData = $siteInduction->staticData;
    $this->editorSettings = (object)$staticData->settings->general->editorSettings;
    $this->customMailer = $mailer;
    $this->baseUrl = $siteInduction->baseUrl;
    $this->path = $siteInduction->path;
    $this->emailLogo = get_option('plugin_email_logo', '');
  }

  public function renderPluginAdminMenu() {
    // Register plugin main menu
    add_menu_page(
      'Macquarie Intellicentre Induction Quiz',
      'Intellicentre Induction Quiz',
      $this->cap,
      $this->slug,
      false,
      'dashicons-welcome-write-blog',
      80
    );
    // Register intellicentre plugin sub menu
    add_submenu_page(
      $this->slug,
      'Intellicentres',
      'Intellicentres',
      $this->cap,
      $this->slug
    );
    // Register add new intellicentre button
    add_submenu_page(
      $this->slug,
      'Add New Intellicentre',
      'Add New Intellicentre',
      $this->cap,
      'post-new.php?post_type=intellicentre'
    );
    // Register settings plugin sub menu
    add_submenu_page(
      $this->slug,
      'Settings',
      'Settings',
      $this->cap,
      'appointment-reservation-settings',
      array($this, 'renderSettingsPage')
    );
    // Register email invitation plugin sub Menu
    add_submenu_page(
      $this->slug,
      'Email Invitation',
      'Email Invitation',
      $this->cap,
      'email-invitation',
      array($this, 'renderEmailInvitationPage')
    );
    // Register Intellicentre plugin sub menu
    add_submenu_page(
      $this->slug,
      'Invitation Log',
      'Invitation Log',
      $this->cap,
      'invitation-log',
      array($this, 'renderInvitationLogPage')
    );
    //Register invitation view page
    add_submenu_page(
      $this->slug,
      'invitation-view',
      false,
      $this->cap,
      'invitation-view',
      array($this, 'renderInvitationViewPage')
    );
  }

  public function renderSettingsPage() {
    $message = $this->processData();
    include_once 'templates/settings.php';
  }

  protected function getGeneralSettings() {
    $editor = $this->editorSettings;
    $plugin_heading = stripslashes(get_option('plugin_heading', ''));
    $plugin_subheading = stripslashes(get_option('plugin_subheading', ''));
    $plugin_image_banner = get_option('plugin_image_banner', '');
    $macquarie_view_app_id = get_option('macquarie_view_app_id', '');
    $macquarie_view_app_secret = get_option('macquarie_view_app_secret', '');
    $plugin_email_logo = $this->emailLogo;
    include_once 'templates/general-settings.php';
  }

  protected function getEntryFormSettings() {
    $editor = $this->editorSettings;
    $entry_form_preheader = stripslashes(get_option('entry_form_preheader', ''));
    $entry_form_heading = stripslashes(get_option('entry_form_heading', ''));
    $entry_form_subheading = stripslashes(get_option('entry_form_subheading', ''));
    $entry_form_background_image = get_option('entry_form_background_image', '');
    include_once 'templates/entry-form-settings.php';
  }

  protected function getDownloadPrintSettings() {
    $editor = $this->editorSettings;
    $download_print_heading = stripslashes(get_option('download_print_heading', ''));
    $download_print_content = stripslashes(get_option('download_print_content', ''));
    $download_print_button_label = stripslashes(get_option('download_print_button_label', ''));
    $intellicentre_access_form = stripslashes(get_option('intellicentre_access_form', ''));
    include_once 'templates/download-print-settings.php';
  }


  protected function getSummaryPageSettings() {
    $editor = $this->editorSettings;
    $summary_page_heading = stripslashes(get_option('summary_page_heading', ''));
    $summary_page_success_message = stripslashes(get_option('summary_page_success_message', ''));
    $summary_email_instruction_text = stripslashes(get_option('summary_email_instruction_text', ''));
    $callback_page_url_type = get_option('callback_page_url_type', '');
    $callback_page = get_option('callback_page', '');
    $pages = get_pages();
    include_once 'templates/summary-page-settings.php';
  }

  protected function processData() {
    $message = null;
    if (isset($_POST['save_data'])) {
      $this->saveData($_POST);
      $message = '<div class="updated fade"><p>Settings saved!</p></div>';
    }
    return $message;
  }

  protected function saveData($data) {
    foreach ($data as $key => $value) {
      if ($key !== 'save_data')
        update_option($key, $value);
    }
  }

  // Get Values of Form Inputs on POST upon hitting Submit/Send, Email Invitation
  protected function getEmailInvitationData() {
    $uuid = uniqid();

    $details = array(
      'logo' => $this->emailLogo,
      'customer_firstname' =>  '',
      'customer_lastname' =>  '',
      'customer_email' =>  '',
      'facility_servicing_contractor' => 'No',
      'invitation_message' => $this->siteInduction->staticData->emailInvitation->body,
      'site_induction_link_text' => $this->siteInduction->staticData->emailInvitation->linkText,
      'site_induction_link_url' => $this->siteInduction->createSiteInductionTokenLink($this->baseUrl, $this->path,$uuid),
      'customer_uuid' => $uuid
    );

    if ( $_POST ){
      $servicingContractorValue = $_POST['facility_servicing_contractor'];
      if ($servicingContractorValue !='Yes'){
        $servicingContractorValue = 'No';
      }
      $details['customer_firstname'] = $_POST['customer_firstname'];
      $details['customer_lastname'] = $_POST['customer_lastname'];
      $details['customer_email'] = $_POST['customer_email'];
      $details['facility_servicing_contractor'] = $servicingContractorValue;
      $details['invitation_message'] = stripslashes($_POST['invitation_message']);
      $details['site_induction_link_text'] = $_POST['site_induction_link_text'];
    }

    return $details;
  }

  // Get Items From DB using ID, Invitation View
  protected function processInvitationLogQuery() {
    global $wpdb;
    $url_query_id = $_GET['id'];
    $table_name = $wpdb->prefix . 'site_induction_reservation';
    $tbl_query = "SELECT * FROM $table_name WHERE id = '". $url_query_id ."' ";
    $logData = $wpdb->get_row($tbl_query);
    $logDetails = array(
      'logo' => get_business_unit_default_logo(),
      'customer_fullname' => $logData->fullname,
      'customer_firstname' => $logData->firstname,
      'customer_lastname' => $logData->lastname,
      'customer_email' => $logData->email,
      'customer_uuid' => $logData->uuid,
      'facility_servicing_contractor' => $logData->facility_servicing_contractor,
      'customer_company' => $logData->company,
      'customer_contracted_to_company' => $logData->contracted_to_company,
      'invitation_message' => $this->siteInduction->staticData->emailInvitation->body,
      'site_induction_link_text' => $this->siteInduction->staticData->emailInvitation->linkText,
      'site_induction_link_url' => $logData->link,
      'hide' => $logData->location ? '' : 'hide',
      'date_sent' => $logData->date_sent,
      'date_completed' => $logData->date_completed,
      'location' => $logData->location,
      'site_induction_resend_link_url' => $this->siteInduction->createSiteInductionTokenLink($this->baseUrl, $this->path,$logData->uuid)
    );

     return $logDetails;
  }

  public function renderInvitationLogPage() {
    $table = new wpListTableCustom;
    if(isset($_POST['s'])){
        $table->prepare_items($_POST['s']);
      } else {
        $table->prepare_items();
    }
    include_once 'templates/invitation-log.php';
  }

  public function renderInvitationViewPage() {
    $data = $this->processInvitationLogQuery();
    $body =  $this->siteInduction->staticData->emailInvitation->body;
    $message = $this->siteInduction->sendEmailInvitation($data);
    $this->siteInduction->processEmailInvitationQuery($data);
    include_once 'templates/invitation-view.php';
  }

  public function renderEmailInvitationPage() {
    $editor = $this->editorSettings;
    $data = $this->getEmailInvitationData();
    $message = $this->siteInduction->sendEmailInvitation($data);
    $this->siteInduction->processEmailInvitationQuery($data);
    include_once 'templates/invitation.php';
  }
}