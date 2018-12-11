<?php

/**
 * @todo Create new classes and move contents to its correct respective classes
 * @todo Code refactoring
 * @todo Automated testing
 */

class SiteInduction
{
  const SALT = "SOME_SECRET_CODE_SALT";
  const TEMPLATE_DIR = WPAR_PLUGIN_DIR . '/admin/inc/templates/';
  const IMAGES_DIR = WPAR_PLUGIN_URL . 'library/dist/assets/img/';
  const JSON = WPAR_PLUGIN_DIR . '/library/static_data.json';
  const IP_WHITELIST = WPAR_PLUGIN_DIR . '/library/ip_whitelist.txt';
  const RESTRICTED_ACCESS = 0;
  const VALID_TOKEN = 1;
  const EXPIRED_TOKEN = 2;
  const INVALID_TOKEN = 3;
  const COOKIE = 'site-induction';

  public $staticData;
  public $baseUrl;
  public $path;
  private $customMailer;
  private $senderEmail;
  private $senderName;
  private $invitationSubject;
  private $invitationDefaultBody;
  private $invitationDefaultLinkText;
  private $dateCentreEmails;
  private $scheduleConfirmationRecipientName;
  private $scheduleConfirmationSubject;
  private $timezone;
  private $tokenValidityInDays;
  private $isCookieExists;
  private $guuid;
  private $whitelist;
  private $isWhitelisted;
  private $company;

  function __construct($mailer = array()) {
    $this->init($mailer);
  }

  protected function init( $mailer ) {
    $this->staticData = $this->getStaticData(self::JSON);
    $whitelistString = file_exists(self::IP_WHITELIST) ? preg_replace('/\s+/', '', file_get_contents(self::IP_WHITELIST)) : "";
    $this->baseUrl = 'https://' . $_SERVER['SERVER_NAME'];
    $this->path = $this->staticData->settings->general->siteInductionPath;
    $this->timezone = $this->staticData->settings->general->timezone;
    $this->tokenValidityInDays = $this->staticData->settings->general->tokenValidityInDays;
    $this->isCookieExists = isset($_COOKIE[self::COOKIE]);
    $this->whitelist = explode(",",$whitelistString);
    $this->isWhitelisted = false;
    $this->guuid = '';
    if (!empty($mailer)) {
      $this->mapMailerStaticData($this->staticData);
      $this->customMailer = $mailer;
      $this->customMailer->setEmailSender($this->senderEmail, $this->senderName);
    }
  }

  public function renderSiteInductionContent() {
    require WPAR_PLUGIN_DIR . "/library/dist/index.html";
  }

  public function requestSiteInductionData() {
    // Pass the URL of Site Induction Tool and Get Site Induction Data
    $this->getSiteInductionData($_SERVER['HTTP_REFERER']);
  }

  protected function getSiteInductionData($siteInductionUrl) {
    $siteInductionData = array('entryFormData' => $this->getEntryFormData());

    switch($this->validateSiteInductionAccess($siteInductionUrl)) {
      case self::VALID_TOKEN:
        $siteInductionData = $this->parseData();
        break;
      case self::EXPIRED_TOKEN:
        $message = $this->fillPlaceholder(
          'token_validity_in_days',
          $this->staticData->settings->general->tokenValidityInDays,
          $this->staticData->settings->general->messages->expiredToken
        );
        $siteInductionData['message'] = $message;
        break;
      case self::INVALID_TOKEN:
        $siteInductionData['message'] = $this->staticData->settings->general->messages->invalidToken;
        break;
      default:
        $siteInductionData['message'] = $this->staticData->settings->general->messages->restrictedAccess;
    }
    $siteInductionData['footer'] = $this->getFooterData();

    echo json_encode($siteInductionData);
    die();
  }

  // Validate the request and will return:
  // 0 = restricted access, 1 = token is valid, 2 = token is expired, 3 = invalid token
  protected function validateSiteInductionAccess($url) {

    // Check if there are parameters in the url
    $parameters = $this->parseUrl($url);
    if ($parameters) {
      // Validate the token and pass the result to $result
      return $this->validateToken($parameters);
    }

    // Check if IP is in whitelist
    if ($this->checkIfInWhitelist()) {
      return self::VALID_TOKEN;
    }

    // If you have skip-token-validation in your header request
    $developerMode = $this->skipTokenValidation() || $this->isCookieExists;
    if ($developerMode) {
      // Will set $result to 1 to grant access
      return self::VALID_TOKEN;
    }

    return self::RESTRICTED_ACCESS;
  }

  protected function getGeneralSettings() {
    $settings = array(
      'heading' => stripslashes(get_option('plugin_heading', '')),
      'subheading' => wpautop(stripslashes(get_option('plugin_subheading', ''))),
      'bannerImage' => get_option('plugin_image_banner', ''),
      'logo' => get_business_unit_default_logo(),
    );
    return $settings;
  }

  protected function getEntryFormData() {
    $data = array(
      'preheader' => stripslashes(get_option('entry_form_preheader', '')),
      'heading' => stripslashes(get_option('entry_form_heading', '')),
      'subheading' => wpautop(stripslashes(get_option('entry_form_subheading', ''))),
      'background' => get_option('entry_form_background_image', ''),
      'logo' => get_business_unit_default_logo(),
      'company' => $this->company
    );
    return $data;
  }

  protected function getIntellicentres() {
    $intellicentres = get_posts(
      array(
        'post_type' => 'intellicentre',
        'orderby' => 'title',
        'order' => 'ASC',
      )
    );
    // Get the sub_content value of each intellicentre and add the value to the query results
    $this->pushIntellicentresSubcontent($intellicentres);
    return $intellicentres;
  }

  protected function pushIntellicentresSubcontent($intellicentres) {
    foreach ($intellicentres as $intellicentre) {
      // Add the sub_content value then add it to the intellicentre post object
      $intellicentre->sub_content = $this->getIntellicentreSubcontent(get_field('site_induction_sections', $intellicentre->ID));
      $intellicentre->disclaimer = get_field('data_center_disclaimer', $intellicentre->ID);
      $intellicentre->location = get_field('data_centre_location', $intellicentre->ID);
      $intellicentre->seat_capacity = get_field('data_centre_seat_capacity', $intellicentre->ID);
      $intellicentre->data_centre_induction_schedule = $this->mapSchedulesByTypeOfUser((array)get_field('data_centre_induction_schedule', $intellicentre->ID));
    }
    return $intellicentres;
  }

  private function mapFacilityServiceSchedule($schedules){

    $mappedSched =  array(
      'facility_servicing_contractor_schedule' => array(),
      'internal_member_schedule' => array(),
      'customer_schedule' => array(),
    );

    foreach ($schedules as $sched) {
      $objSched = (object)$sched;
      $field = $objSched->acf_fc_layout;
      switch ($field) {
        case "facility_servicing_contractor_schedule":
            $mappedSched['facility_servicing_contractor_schedule'] = $this->mapScheduleDetails($sched);
            break;
        case "internal_member_schedule":
            $mappedSched['internal_member_schedule'] = $this->mapScheduleDetails($sched);
            break;
        default:
            $mappedSched['customer_schedule'] = $this->mapScheduleDetails($sched);
      }
    }
    return $mappedSched;
  }

  protected function getIntellicentreSubcontent($layouts) {
    $subContent = null;
    foreach ($layouts as $layout) {
      // find sub_content sub_field, get the value then break loop
      if (isset($layout['sub_content'])) {
        $subContent = $layout['sub_content'];
        break;
      }
    }
    return $subContent;
  }

  protected function getIntellicentreSettings($data) {
    global $wpdb;
    $fcs = (object)array(
      'facility_servicing_contractor' => 'No'
    );

    if($this->guuid != ''){
      $table = $this->getReservationTableName();
      $fcs = $wpdb->get_row("SELECT facility_servicing_contractor FROM $table WHERE uuid = '". $this->guuid ."'");
    }

    $settings = array(
      'general' => array(
        'inspection_days' => $data->settings->general->inspectionDays,
        'inspection_hours' => $data->settings->general->inspectionHours,
        'holidays' => $this->formatHolidays($data->settings->general->holidays),
        'facility_servicing_contractor' => $fcs->facility_servicing_contractor,
        'is_whitelisted' => $this->isWhitelisted
      )
    );
    return $settings;
  }

  protected function formatHolidays($holidays) {
    foreach ($holidays as $holiday) {
      $date = new DateTime($holiday);
      $result[] = array(
        'day' => (int) $date->format('j'),
        'month' => (int) $date->format('n'),
        'year' => (int) $date->format('Y')
      );
    }
    return $result;
  }

  protected function getIntellicentreData() {
    $data = array();
    $intellicentres = $this->getIntellicentres();
    foreach ($intellicentres as $key => $intellicentre) {
      $data[$key]['intellicentre'] = $intellicentre->post_title;
      $fields = get_fields($intellicentre->ID);
      foreach ($fields['site_induction_sections'] as $layout) {
        // Map Intellicentre Data
        if ($layout['acf_fc_layout'] === 'introduction_section') {
          $data[$key]['introduction'] = $layout['reading_material_introduction'];
        }

        if ($layout['acf_fc_layout'] === 'topic_section') {
          $data[$key]['sections'][] = $layout;
        }

        if ($layout['acf_fc_layout'] === 'quiz_section') {
          $layout['question_category'] = $this->getRandomQuizQuestions($layout);
          $data[$key]['sections'][] = $layout;
        }
      }
    }
    return $data;
  }

  // Get Summary Data from Plugin Settings
  protected function getSummaryData() {
    $data = array(
      'heading' => stripslashes(get_option('summary_page_heading', '')),
      'success_message' => stripslashes(get_option('summary_page_success_message', '')),
      'finish_button_link' => get_option('callback_page', ''),
      'uuid' => $this->guuid
    );
    return $data;
  }

  // Get Footer Widget from Wordpress
  protected function getFooterWidget($widget) {
    if (is_active_sidebar($widget)) {
      ob_start();
      dynamic_sidebar($widget);
      $widgetData = ob_get_clean();
      return $widgetData;
    }
  }

  // Get Footer Data from Wordpress
  protected function getFooterData() {
    $widget = $this->staticData->settings->general->footerWidget;
    $data = array(
      'copyright' => $this->fillPlaceholder(
        'year',
        date('Y'),
        $this->staticData->settings->general->copyright
      ),
      'footerLinks' => $this->getFooterWidget($widget)
    );
    return $data;
  }

  // Compile all Data we get from Plugin Settings and Wordpress to an array
  protected function parseData() {
    $data = array(
      'generalSettings' => $this->getGeneralSettings(),
      'entryFormData' => $this->getEntryFormData(),
      'intellicentres' => $this->getIntellicentres(),
      'intellicentreSettings' => $this->getIntellicentreSettings($this->staticData),
      'intellicentreData' => $this->getIntellicentreData(),
      'downloadAndPrintData' => $this->getDownloadAndPrintData(),
      'summaryData' => $this->getSummaryData(),
      'duplicateWindowMessage' => $this->staticData->settings->general->messages->duplicateWindow
    );

    return $data;
  }

  public function getDateTime() {
    $date = new DateTime("now", new DateTimeZone('Australia/Sydney'));
    $dateTime = $date->format('Y-m-d H:i:s');

    return $dateTime;
  }

  public function getEmailLogo() {
    $admin = new Admin($mailer = array());

    return $admin->emailLogo;
  }

  // Sending Email Invitation
  public function sendEmailInvitation($data) {
    $message = null;
    $emailContent = array(
      'recipientEmail' => $data['customer_email'],
      'recipientName' => $data['customer_firstname'].' '.$data['customer_lastname'],
      'subject' => $this->invitationSubject,
      'body' => $this->renderEmailInvitation($data),
      'siteInductionLinkUrl' => $data['site_induction_link_url'],
      'errorMessage' => $this->staticData->emailInvitation->errorMessage,
    );

    if (isset($_POST['send_invitation']) || isset($_POST['resend_invitation'])) {
      $resend = isset($_POST['resend_invitation']);
      $invitationData = $this->getInvitationData($resend, $data);
      $emailContent['successMessage'] = $invitationData;
      $message = $this->customMailer->sendEmail($emailContent);
    }

    return $message;
  }

  // Method for Action Hook of get_disabled_dates
  public function getDisabledDates() {

    $post = file_get_contents("php://input");
    $data = json_decode($post);

    $location = $data->location;

    global $wpdb;

    $table_name = $wpdb->prefix . 'disabled_dates';

    $select_clause = " *,COUNT(*) as count ";
    $where_clause  = "uuid IS NOT NULL AND location IS NOT NULL AND appointment_date <> '0000-00-00 00:00:00' AND appointment_date > CURDATE() AND location = '$location'";
    $group_clause = "company,location,appointment_date";
    $order_clause = " location, appointment_date";

    $query = "SELECT $select_clause FROM $table_name WHERE $where_clause GROUP BY $group_clause ORDER BY $order_clause";

    $result = $wpdb->get_results($query, ARRAY_A);

    echo json_encode($result);
    die();
  }

  // Method for Action Hook of validate_date_not_full
  public function validateDateNotFull(){
    $post = file_get_contents("php://input");
    $data = json_decode($post);
    global $wpdb;

    $arr = array();

    $table_name = $wpdb->prefix . 'disabled_dates';
    $select_clause = " *,COUNT(*) as count ";
    $group_clause = " company,location,appointment_date ";

    $appointments = $data->appointments;

    foreach($appointments as $appointment) {
      $location = $appointment->location;
      $appointmentDate = $appointment->appointmentDate;
      $company = strtolower($appointment->company);
      $seatCapacity = $appointment->seatCapacity;

      $where_clause = " location = '$location' AND appointment_date = '$appointmentDate' ";

      $query = "SELECT $select_clause FROM $table_name WHERE $where_clause GROUP BY $group_clause";

      $result =  (object)$wpdb->get_row($query);

      $apntmnt = array();

      $apntmnt['appointment'] = $appointmentDate;
      $apntmnt['location'] = $location;
      if (empty($result->count)||($result->count < $seatCapacity && $result->company == $company)){
        $apntmnt['isValid'] = true;
      }
      else {
        $apntmnt['isValid'] = false;
      }

      array_push($arr,  $apntmnt);
    }

    echo json_encode($arr);
    die();
  }

  private function getInvitationData($resend = false, $data) {
    if ($resend) {
      return $this->fillPlaceholder(
        'site_induction_resend_link_url',
        $data['site_induction_resend_link_url'],
        $this->staticData->emailInvitation->successResendMessage
     );
   }

    return $this->fillPlaceholder(
      'site_induction_link_url',
      $data['site_induction_link_url'],
      $this->staticData->emailInvitation->successMessage
    );
  }

  public function processEmailInvitationQuery($data) {
    // TODO: Refactor Post Data to decouple unrelated code and can be reuse and test.
    $resending = isset($_POST['resend_invitation']);
    $dataUuid = isset($data['customer_uuid']) ? $data['customer_uuid'] : uniqid();
    $uuid =  $resending ? uniqid() : $dataUuid;
    $link = $this->createSiteInductionTokenLink($this->baseUrl, $this->path,  $uuid);
    $invitationIsSending = isset($_POST['send_invitation']) || $resending;
    if ($invitationIsSending) {
      $this->saveReservation(array(
        'fullname' => $data['customer_firstname'] .' '. $data['customer_lastname'],
        'firstname' => $data['customer_firstname'],
        'lastname' => $data['customer_lastname'],
        'company' => isset($data['customer_company'])?$data['customer_company']:'',
        'contracted_to_company' => isset($data['customer_contracted_to_company'])?$data['customer_contracted_to_company']:'',
        'email' => $data['customer_email'],
        'facility_servicing_contractor' => $data['facility_servicing_contractor'],
        'date_sent' => $this->getDateTime(),
        'link'=> $link,
        'uuid' => $uuid
      ));

      if ($resending){
        $this->redirectToInvitationView($uuid);
      }
    }
  }

  // Render Email Invitation template and replace all placeholders with appropriate values
  protected function renderEmailInvitation($data) {
    $template = $this->getTemplate('email', 'email-invitation.html');
    foreach ($data as $key => $value) {
      $template = $this->fillPlaceholder($key, $value, $template);
    }
    return $template;
  }

  // Process the data after completing Data Centre Induction
  public function processDciData() {
    // Get POST Data from the Site Induction Tool
    $post = file_get_contents("php://input");
    $data = json_decode($post);

    $data->customer = (object)$data->customer;

    // Set Subject
    $this->scheduleConfirmationSubject = $this->renderTemplate(
      $this->staticData->emailScheduleConfirmation->subject,
      array(
        'fullname' => $data->customer->fullname,
        'company' => $data->customer->company,
        'contractedCompany' => $data->customer->contractedCompany
      )
    );

    // Customer's Email Confirmation
    $this->sendEmailConfirmationToCustomer($data);

    // Data Centres' Email Confirmation
    $this->sendEmailConfirmationToDataCentres($data);

    $this->processReservation($data);

    $this->trySaveThirdPartyReservation($data);

    die();
  }

  // Update Item in Invitation Log Table
  private function processReservation($data) {
    global $wpdb;
    $uuid = $data->customer->uuid ? $data->customer->uuid : '';
    if ($uuid){
        $table_name_reservation = $this->getReservationTableName();
        $data_reservation = array(
          'date_completed' => $this->getDateTime(),
          'location' => $this->getLocation($data),
          'company' => $data->customer->company,
          'contracted_to_company' => $data->customer->contractedCompany
        );
        $where_reservation = array(
          'uuid' => $uuid
        );

      $result = $wpdb->update($table_name_reservation, $data_reservation, $where_reservation);

      $this->saveAppointmentInDb($data);
    }
  }

  public function trySaveThirdPartyReservation($data) {
    global $wpdb;
    $uuid = $data->customer->uuid;
    if($this->isUuidNotExisting($uuid)) {
      $id = uniqid();
      $this->saveReservation(array(
        'firstname' => $data->customer->firstname,
        'lastname' => $data->customer->lastname,
        'fullname' => $data->customer->fullname,
        'email' => $data->customer->email,
        'company' => $data->customer->company,
        'contracted_to_company' => $data->customer->contractedCompany,
        'date_sent' => $this->getDateTime(),
        'date_completed' => $this->getDateTime(),
        'link'=> $this->getReferrerLink(),
        'location' => $this->getLocation($data),
        'facility_servicing_contractor' => 'No',
        'uuid' => $id
      ));

      $data->summary->uuid = $id;
      $this->saveAppointmentInDb($data);
    }
  }

  public function getReferrerLink() {
    return $_SERVER['HTTP_REFERER'];
  }

  public function getLocation($data) {
    foreach($data->summary->appointment as $appointment) {
      $bookingData[] = $appointment->intellicentre .' - '. $this->getDateTime();
    }
    $locationData = implode(',', $bookingData);

    return $locationData;
  }

  private function isUuidNotExisting($uuid) {
    if (empty($uuid)) {
      return true;
    }

    global $wpdb;
    $table = $this->getReservationTableName();
    $query = $wpdb->get_row("SELECT * FROM $table WHERE uuid = '". $uuid ."'");

    return (empty($query) || !isset($query));
  }

  // Sending Email Confirmation to Customer after completing the Quiz
  protected function sendEmailConfirmationToCustomer($data) {
    $customerEmailOptions = array(
      'recipientEmail' => $data->customer->email,
      'recipientName' => $data->customer->fullname,
      'subject' => $this->scheduleConfirmationSubject,
      'body' => $this->renderCustomerEmailConfirmation($data),
      // Create icals for all booked intellicentres
      'icals' => $this->createIcals($data->summary->appointment),
      'successMessage' => $this->staticData->emailScheduleConfirmation->successMessage,
      'errorMessage' => $this->staticData->emailScheduleConfirmation->errorMessage
    );
    $this->customMailer->sendEmail($customerEmailOptions);
  }

  // Sending Email Confirmation to appropriate Data Centres after the Custommer completed the Quiz
  protected function sendEmailConfirmationToDataCentres($data) {
    // Loop all the booked intellicentres to send email for each one
    foreach($data->summary->appointment as $appointment) {
      $dataCentreEmailOptions = array(
        'recipientEmail' => get_field('data_centre_emails', $this->getIntellicentreId($appointment->intellicentre)),
        'recipientName' => $this->scheduleConfirmationRecipientName,
        'subject' => $this->scheduleConfirmationSubject,
        'body' => $this->renderDataCentreEmailConfirmation($appointment, $data),
        // createIcals method accepts array.
        // We have a single appoinment so we'll set it as a value of an array
        'icals' => $this->createIcals(array($appointment), $data->customer),
        'successMessage' => $this->staticData->emailScheduleConfirmation->successMessage,
        'errorMessage' => $this->staticData->emailScheduleConfirmation->errorMessage
      );
      $this->customMailer->sendEmail($dataCentreEmailOptions);
    }
  }

  protected function createIcals($appointments, $customer = null) {
    $icals = array();

    $icalTemplate = $this->getTemplate('email', 'ical.txt');

    // Set what template we need to use
    // if $customer has a value, use the data centre ical templates else use customer's
    if ($customer) {
      $filenameTemplate = $this->staticData->ical->dataCentre->filename;
      $summaryTemplate = $this->staticData->ical->dataCentre->summary;
      $descriptionTemplate = $this->staticData->ical->dataCentre->description;
    } else {
      $filenameTemplate = $this->staticData->ical->customer->filename;
      $summaryTemplate = $this->staticData->ical->customer->summary;
      $descriptionTemplate = $this->staticData->ical->customer->description;
    }

    // Loop appointments to create .ics file for each one
    foreach ($appointments as $appointment) {
      // Set start datetime
      $start = $this->createDateByTimezone(
        "{$appointment->date->year}-{$appointment->date->month}-{$appointment->date->day} {$appointment->time}",
        $this->timezone
      );

      // Set end datetime by cloning start datetime and modifying it
      $end = clone $start;
      $end->modify('+90 minutes');

      // Get Intellicentre Post ID
      $intellicentreId = $this->getIntellicentreId($appointment->intellicentre);

      // Set initial values / Set values for Data Centre's Ical
      $filenameData = array('dc_abbv' => get_field('data_centre_abbreviation', $intellicentreId));
      $summaryData = array('intellicentre' => $appointment->intellicentre);
      $descriptionData = array('intellicentre' => $appointment->intellicentre);

      // Set values for Customer's Ical
      if ($customer) {
        $filenameData['fullname'] = $customer->fullname;
        $summaryData['fullname'] = $customer->fullname;
        $summaryData['company'] = $customer->company;
        $descriptionData = array(
          'fullname' => $customer->fullname,
          'company' => $customer->company,
          'email' => $customer->email,
          'day' => $start->format('l'),
          'date' => $start->format('d/m/Y'),
          'time' => $start->format('H:i T')
        );
      }

      // Create Ical content
      $content = $this->renderTemplate($icalTemplate, array(
        'summary' => $this->renderTemplate($summaryTemplate, $summaryData),
        'description' => $this->renderTemplate($descriptionTemplate, $descriptionData),
        'location' => get_field('data_centre_address', $intellicentreId),
        'start' => $start->format('Ymd\THis'),
        'end' => $end->format('Ymd\THis')
      ));

      // Compile created icals into an array
      $icals[] = (object) array(
        'filename' => $this->renderTemplate($filenameTemplate, $filenameData),
        'content' =>  $content,
      );
    }

    // Return created icals
    return (object) $icals;
  }

  protected function renderDataCentreEmailConfirmation( $appointment, $data ) {
    $base = $this->getTemplate('email/data-centre', 'email-data-centre-confirmation.html');
    $item = $this->getTemplate('email/data-centre', 'appointment.html');
    $quiz = $this->getTemplate('email/data-centre', 'quiz.html');

    $dataCentreEmailData = clone (object) $data ->customer;
    $dataCentreEmailData->intellicentre = $appointment->intellicentre;
    $dataCentreEmailData->appointment = $this->renderAppointmentTemplate($item, array($appointment));

    $quizData = (array) $data ->quizData;
    $dataCentreEmailData->quiz = $this->renderQuizTemplate(
      $quiz, $appointment->intellicentre, $quizData[$appointment->intellicentre]
    );

    $dataCentreEmailData->logo = $this->getEmailLogo();

    $dataCentreEmailData->company = $this->checkContractedCompany($data->customer);

    return $this->renderTemplate($base, $dataCentreEmailData);
  }

  // Returns contractedCompany w/ custom string if contractedCompany is set, returns company otherwise
  private function checkContractedCompany($customer){

    if (!isset($customer->contractedCompany) || trim($customer->contractedCompany) === '') {
      return $customer->company;
    }
    return  $customer->company ." on behalf of ". $customer->contractedCompany;
  }

  protected function renderCustomerEmailConfirmation($idata) {

    $data = clone (object)$idata;

    $base = $this->getTemplate('email/customer', 'email-customer-confirmation.html');
    $appointment = $this->getTemplate('email/customer', 'appointment.html');
    $customerEmailData = (object) array_merge((array) $data->customer, (array) $data->summary);
    $customerEmailData->logo = $this->getEmailLogo();
    $customerEmailData->time_icon = self::IMAGES_DIR . 'time-icon.png';
    $customerEmailData->calendar_icon = self::IMAGES_DIR . 'calendar-icon.png';
    $customerEmailData->appointment = $this->renderAppointmentTemplate($appointment, $data->summary->appointment);
    $customerEmailData->summary_email_instruction_text = wpautop(stripslashes(
      get_option('summary_email_instruction_text', '')));
    $customerEmailData->intellicentre_access_form_link = get_option('intellicentre_access_form', '');

    return $this->renderTemplate($base, $customerEmailData);
  }

  protected function getRandomQuizQuestions($quizSection) {
    $categories = $quizSection['question_category'];
    return $this->mapRandomQuestions($quizSection['question_category']);
  }

  protected function mapRandomQuestions($categories) {
    $data = array();
    $randomQuestions = array();

    foreach ($categories as $category) {
      // Get all categories and compile them in an array
      foreach ($category['question_type'] as $type) {
        $type['category_name'] = $category['category_name'];
        $data[] = $type;
      }
    }

    // Check how many categories we have
    $elementsCount = sizeof($data);

    // If questions are more than or equal to 10, get 10 randomly, else get all questions
    $randomKeys = ($elementsCount >= 10) ? array_rand($data, 10) : array_rand($data, $elementsCount);
    $randomKeys = is_array($randomKeys) ? $randomKeys : array($randomKeys);

    // Get the random categories and compile them in a tmp array
    foreach ($randomKeys as $key) {
      $category = $data[$key]['category_name'];
      unset($data[$key]['category_name']);
      $tmp[$category][] = $data[$key];
    }

    // Map the categories and questions in an array
    foreach ($tmp as $key => $value) {
      $randomQuestions[] = array(
        'category_name' => $key,
        'question_type' => $value
      );
    }

    // Return mapped random questions
    return $randomQuestions;
  }

  protected function mapQuizCategoryQuestions($items) {
    $questions = array();
    foreach ($items as $item) {
      $questions[] = (object) array(
        'question' => $item->question,
        'answer' => $item->usersAnswer
      );
    }
    return $questions;
  }

  protected function mergeQuizQuestions($categories) {
    $questions = array();
    foreach ($categories as $category) {
      $questions = array_merge($questions, $this->mapQuizCategoryQuestions($category->questions));
    }
    return (object) $questions;
  }

  protected function renderQuizTemplate($template, $intellicentre, $quizData) {
    $result = '';
    $item = $this->getTemplate('email/data-centre', 'item.html');
    $data = new \stdClass;
    $data->intellicentre = $intellicentre;
    $data->items = $this->renderQuizItemsTemplate($item, $this->mergeQuizQuestions($quizData));
    return $this->renderTemplate($template, $data);
  }

  protected function renderQuizItemsTemplate($template, $items) {
    $result = '';
    foreach ($items as $key => $item) {
      $item->number = $key + 1;
      $result .= $this->renderTemplate($template, $item);
    }
    return $result;
  }

  protected function renderAppointmentTemplate($template, $appointments) {
    $result = '';
    $dc = new \stdClass;
    foreach ($appointments as $appointment) {
      $date = new DateTime("{$appointment->date->year}-{$appointment->date->month}-{$appointment->date->day}");
      $result .= $this->renderTemplate($template, (object) array(
        'intellicentre' => $appointment->intellicentre,
        'subcontent' => $appointment->subcontent,
        'time' => $appointment->time,
        'date' => $date->format('j F Y'),
        'day' => $date->format('l')
      ));
    }
    return $result;
  }

  protected function getTemplate($dir, $template) {
    return file_get_contents(self::TEMPLATE_DIR . $dir . '/' . $template);
  }

  protected function renderTemplate($template, $data) {
    foreach ($data as $key => $value) {
      $template = $this->fillPlaceholder($key, $value, $template);
    }
    return $template;
  }

  protected function fillPlaceholder($placeholder, $value, $template) {
    return str_replace(
      '{' . strtolower($placeholder) . '}',
      $value,
      $template
    );
  }

  protected function skipTokenValidation() {
    if (isset($_SERVER['HTTP_SKIP_TOKEN_VALIDATION'])) {
      return $_SERVER['HTTP_SKIP_TOKEN_VALIDATION'];
    }
  }

  // Validate the token and will return:
  // 1 = token is valid, 2 = token is expired, 3 = invalid token
  protected function validateToken($parameters) {
    $result = self::INVALID_TOKEN;

    $actualHash = $parameters['hash'];
    unset($parameters['hash']);
    $expectedHash = $this->createHash(join('', $parameters));
    $validTokenAge = $this->checkTokenAge($parameters['date'], $this->tokenValidityInDays);

    if ($expectedHash === $actualHash) {
      $result = ($validTokenAge) ? self::VALID_TOKEN : self::EXPIRED_TOKEN ;
    }

    return $result;
  }

  protected function checkTokenAge( $dateTokenCreated, $tokenValidityInDays ) {
    if (empty($dateTokenCreated)) {
      return;
    }

    $token = $this->createDateByTimezone($dateTokenCreated, $this->timezone);

    if (!$token) {
      return;
    }

    $today = $this->createDateByTimezone("NOW", $this->timezone);

    return $today->diff($token)->days <= $tokenValidityInDays;
  }

  protected function createHash( $stringToHash ) {
    return hash("sha256", $stringToHash . self::SALT);
  }

  protected function parseUrl($url) {
    $urlParams = parse_url($url);
    $queryString = isset($urlParams['query'])? $urlParams['query'] : '';
    $requiredParametersIsMissing = empty($queryString);

    if ($requiredParametersIsMissing) {
      return;
    }
    parse_str($queryString, $queryParams);

    // Check if query params are set
    $isIdSet = isset($queryParams['id']);
    $isUuidSet = isset($queryParams['uuid']);
    $isTokenSet = isset($queryParams['token']);
    $isCompanySet = isset($queryParams['company']);

    // Initialize config
    $config = array(
      'base_url' => $urlParams['scheme'] . '://' . $urlParams['host'],
      'path' => $urlParams['path'],
      'date' => $isIdSet ? $queryParams['id'] : '',
      'uuid' => $isUuidSet ? $queryParams['uuid'] : '',
      'hash' => $isTokenSet ? $queryParams['token'] : ''
    );

    // Set class level variables
    $this->guuid = $isUuidSet ? $queryParams['uuid'] : '' ;
    $this->company = $isCompanySet ? $queryParams['company'] : '' ;

    $onlyCompanyIsSpecified = !$isIdSet && !$isUuidSet && !$isTokenSet && $isCompanySet;
    if ($onlyCompanyIsSpecified){
      return;
    }

    return $config;
  }

  protected function createParameterArray($baseUrl, $path, $date, $uuid) {
    return array(
      'base_url' => $baseUrl,
      'path' => $path,
      'date' => $date,
      'uuid' => $uuid
    );
  }

  // TODO: Refactor, can be extracted out of Site Induction Class to a seperate Util class
  public function createSiteInductionTokenLink($baseUrl, $path, $uuid) {
    if (empty($uuid)){
      $uuid = $this->guuid;
    }
    $date = date('Ymdhis');
    $parameters = $this->createParameterArray($baseUrl, $path, $date, $uuid);
    $token = $this->createHash(join('', $parameters));
    return $baseUrl . $path . "?token=$token&id=$date&uuid=$uuid";
  }

  protected function createDateByTimezone($date, $timezone) {
    try {
      $result = new DateTime($date, new DateTimeZone($timezone));
    } catch (Exception $e) {
      return;
    }
    return $result;
  }

  protected function mapMailerStaticData($staticData) {
    // Sender
    $this->senderEmail = $staticData->senderEmail;
    $this->senderName = $staticData->senderName;

    // Email Invitation
    $this->invitationSubject = $staticData->emailInvitation->subject;
    $this->invitationDefaultBody = $staticData->emailInvitation->body;
    $this->invitationDefaultLinkText = $staticData->emailInvitation->linkText;

    // Email Schedule Confirmation
    $this->scheduleConfirmationRecipientName = $staticData->emailScheduleConfirmation->recipientName;

    // Data Centre Emails
    $this->dataCentreEmails = get_option('plugin_data_centre_emails', '');
  }

  protected function getStaticData($jsonFile) {
    $json = file_get_contents($jsonFile);
    return json_decode($json);
  }

  protected function getIntellicentreId($intellicentre) {
    return get_page_by_title($intellicentre, OBJECT, 'intellicentre')->ID;
  }

  // Map the schedules of Intellicentres depending on the type of user
  private function mapSchedulesByTypeOfUser($schedules){

    $mappedSched =  array(
      'facility_servicing_contractor' => array(),
      'macquarie_staff' => array(),
      'customer' => array(),
    );

    foreach ($schedules as $sched) {
      $objSched = (object)$sched;
      $field = $objSched->acf_fc_layout;
      switch ($field) {
        case "facility_servicing_contractor":
            $mappedSched['facility_servicing_contractor'] = $this->mapScheduleDetails($sched);
            break;
        case "macquarie_staff":
            $mappedSched['macquarie_staff'] = $this->mapScheduleDetails($sched);
            break;
        default:
            $mappedSched['customer'] = $this->mapScheduleDetails($sched);
      }
    }
    return $mappedSched;
  }

  // Add appointment details in Appointment Table
  private function saveAppointmentInDb($appointmentData){
    global $wpdb;
    $table_name_appointment = $this->getAppointmentTableName();
    foreach($appointmentData->summary->appointment as $appointment) {
      $data_appointment = array(
        'location' => $appointment->intellicentre,
        'appointment_date' => $appointment->utcDate,
        'uuid' => $appointmentData->summary->uuid
      );
      $wpdb->insert($table_name_appointment, $data_appointment);
    }
  }

  // Save Item in Invitation Log Table
  public function saveReservation($fields) {
    global $wpdb;
    $wpdb->insert($this->getReservationTableName(), $fields);
  }

  // Return true if the current IP exist in the whitelist, false otherwise
  private function checkIfInWhitelist(){
    $userIp = $_SERVER['REMOTE_ADDR'];
    foreach ($this->whitelist as $ip){
    $ipArr = explode('/',$ip);
      if (count($ipArr) <= 1){
        if ($ip == $userIp) {
          $this->isWhitelisted = true;
          return true;
        }
      }
      else {
        if ($this->checkIpInRange($userIp, $ip)){
          $this->isWhitelisted = true;
          return true;
        }
      }
    }
    return false;
  }

  // Check IP if its in IP Range
  private function checkIpInRange( $ip, $range ) {
      if ( strpos( $range, '/' ) == false ) {
        $range .= '/32';
      }
      // $range is in IP/CIDR format eg 127.0.0.1/24
      list( $range, $netmask ) = explode( '/', $range, 2 );
      $range_decimal = ip2long( $range );
      $ip_decimal = ip2long( $ip );
      $wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
      $netmask_decimal = ~ $wildcard_decimal;
      return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
  }

  // Returns the SQL table name of Invitation Log
  private function getReservationTableName() {
    global $wpdb;
    return $wpdb->prefix . 'site_induction_reservation';
  }

  // Returns the SQL table name of Intellicentre Appointments
  private function getAppointmentTableName() {
    global $wpdb;
    return $wpdb->prefix . 'site_induction_appointments';
  }

  // Returns an object array of the schedule details
  private function mapScheduleDetails($sched) {
    $objSched = (object)$sched;
    return array(
      'day' => $objSched->schedule_day,
      'time' => $objSched->schedule_time,
    );
  }

  // Get Summary Data from Plugin Settings
  private function getDownloadAndPrintData() {
    $data = array(
      'heading' => stripslashes(get_option('download_print_heading', '')),
      'content' => stripslashes(get_option('download_print_content', '')),
      'button_label' => stripslashes(get_option('download_print_button_label', '')),
      'form_link' => stripslashes(get_option('intellicentre_access_form', ''))
    );
    return $data;
  }

  // Redirect page to the invitaion view based on passed id
  private function redirectToInvitationView($uuid) {
    //TODO: refactoring this to pass uuid instead of id, will eliminate db query
    global $wpdb;
    $table_name = $this->getReservationTableName();
    $query = $wpdb->get_row("SELECT id FROM $table_name WHERE uuid = '". $uuid ."'");
    if ($query){
      $id = $query->id;
      $url = get_site_url().'/wp-admin/edit.php?page=invitation-view&post_type=intellicentre&id='.$id;
      wp_redirect( $url, 200 );
      die();
    }
  }
}
