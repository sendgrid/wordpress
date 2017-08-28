<?php

require_once plugin_dir_path( __FILE__ ) . 'interfaces/class-sendgrid-interface.php';
require_once plugin_dir_path( __FILE__ ) . 'class-sendgrid-translator.php';
require_once plugin_dir_path( __FILE__ ) . '../../vendor/autoload.php';

class Sendgrid_API implements Sendgrid_Send {
  const URL = "https://api.sendgrid.com/v3/mail/send";

  private $username;
  private $password;
  private $apikey;
  private $method;

  public function __construct( $username, $password_or_apikey ) {
    if ( "apikey" == $username ) {
      $this->method = "apikey";
      $this->apikey = $password_or_apikey;
    } else {
      $this->method   = "credentials";
      $this->username = $username;
      $this->password = $password_or_apikey;
    }
  }

  public function send(SendGrid\Email $email) {
    $data = array(
      'headers' => array(
        'Content-Type' => 'application/json',
        'User-Agent' => 'sendgrid/wordpress;php',
        'Authorization' => 'Bearer ' . $this->apikey
      ),
      'body' => Sendgrid_Translator::to_api_v3( $email ),
      'decompress' => false,
      'timeout' => Sendgrid_Tools::get_request_timeout()
    );

    // Send the request
    $response = wp_remote_post( self::URL, $data );

    // Check that the response fields are set
    if ( !is_array( $response ) or
      !isset( $response['response'] ) or
      !isset( $response['response']['code'] ) ) {
      return false;
    }

    // Check for success code range (200-299)
    $response_code = (int) $response['response']['code'];
    if ( $response_code >= 200 and
      $response_code < 300 ) {
      return true;
    }

    return false;
  }
}