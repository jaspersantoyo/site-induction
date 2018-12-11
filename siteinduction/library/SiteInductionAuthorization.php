<?php

class SiteInductionAuthorization
{

  private $siteInduction;
  private $wpQuery;

  function __construct( $siteInduction )
  {
    $this->siteInduction = $siteInduction;
  }

  public function processRequest( $template ) {
    // Instantiate $wp_query and pass it to a variable
    global $wp_query;
    $this->wpQuery = $wp_query;

    // Check if API Request
    if ($this->wpQuery->get('site_induction_api')) {
      // Get the apps that were integrated to site induction tool.
      $apps = $this->siteInduction->staticData->settings->general->apps;
      $this->processApiRequest($apps);
    } else {
      // Process site request
      return $template;
    }
  }

  protected function processApiRequest( $apps ) {
    $authenticated = false;

    // Authenticate the provided App ID & App Secret in all the integrated apps
    foreach ($apps as $app) {
      if ($authenticated = $this->authenticateApp($app)) {
        break;
      }
    }

    if (!$authenticated) {
      // 404 Page
      return $this->redirectTo404();
    }

    // Create valid Ste Induction URL
    $data = $this->createUrl($this->siteInduction->baseUrl, $this->siteInduction->path);

    // Send JSON Response
    $this->sendResponse($data);
  }

  protected function authenticateApp( $appKey ) {
    // Get the App ID and App Secret from the URL
    $providedAppId = $this->wpQuery->get('app_id');
    $providedAppSecret = $this->wpQuery->get('app_secret');

    // Get the stored App ID and App Secret
    $storedAppId = get_option($appKey . '_app_id', '');
    $storedAppSecret = get_option($appKey . '_app_secret', '');

    // Compare the provided credentials to the stored credentials
    return $providedAppId === $storedAppId && $providedAppSecret === $storedAppSecret;
  }

  protected function createUrl( $baseUrl, $path ) {
    // Use the Site Induction URL creator
    return $this->siteInduction->createSiteInductionTokenLink($baseUrl, $path, '');
  }

  protected function sendResponse( $data ) {
    // Establish header
    header('Content-Type: application/json; charset=UTF-8');

    // Process response
    $response = array('url' => $data);

    // Return response
    echo stripslashes(json_encode($response));
    die;
  }

  protected function redirectTo404() {
    $this->wpQuery->set_404();
    status_header(404);
    get_template_part(404);
  }

}