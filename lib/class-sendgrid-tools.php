<?php

class Sendgrid_Tools
{
  /**
   * Check username/password
   *
   * @param   string  $username   sendgrid username
   * @param   string  $password   sendgrid password
   * @return  bool
   */
  public static function check_username_password( $username, $password )
  {
    $url = 'https://sendgrid.com/api/profile.get.json?';
    $url .= "api_user=" . urlencode($username) . "&api_key=" . urlencode($password);

    $response = wp_remote_get( $url );
    
    if ( !is_array($response) or !isset( $response['body'] ) )
    {
      return false;
    }

    $response = json_decode( $response['body'], true );

    if ( isset( $response['error'] ) )
    {
      return false;
    }

    return true;
  }

  /**
   * Check api_key
   *
   * @param   string  $api_key   sendgrid api_key
   * @return  bool
   */
  public static function check_api_key( $api_key )
  {
    $url = 'https://api.sendgrid.com/v3/user/profile';

    $args = array(
      'headers' => array(
        'Authorization' => 'Bearer ' . $api_key )
    );

    $response = wp_remote_get( $url, $args );
    
    if ( ! is_array( $response ) or ! isset( $response['body'] ) ) {
      return false;
    }

    $response = json_decode( $response['body'], true );

    if ( isset( $response['errors'] ) ) {
      return false;
    }

    return true;
  }

  /**
   * Make cURL request to SendGrid API
   *
   * @param type $api
   * @param type $parameters
   * @return json
   */
  public static function curl_request( $api = 'v3/stats', $parameters = array() )
  {
    $args = array();
    if ( ! $parameters['apikey'] ) {
      $creds = base64_encode($parameters['api_user'] . ':' . $parameters['api_key']);

      $args = array(
        'headers' => array(
          'Authorization' => 'Basic ' . $creds 
        )
      );

    } else {
      $args = array(
        'headers' => array(
          'Authorization' => 'Bearer ' . $parameters['apikey'] 
        )
      );
    }

    $data = urldecode( http_build_query( $parameters ) );
    $url = "https://api.sendgrid.com/$api?$data";

    $response = wp_remote_get( $url, $args );

    if ( !is_array($response) or !isset( $response['body'] ) )
    {
      return false;
    }

    return $response['body'];
  }

  /**
   * Return username from the database or global variable
   *
   * @return string username
   */
  public static function get_username()
  {
    if ( defined('SENDGRID_USERNAME') and defined('SENDGRID_PASSWORD') ) {
      return SENDGRID_USERNAME;
    } else {
      return get_option('sendgrid_user');
    }
  }

  /**
   * Return password from the database or global variable
   *
   * @return string password
   */
  public static function get_password()
  {
    if ( defined('SENDGRID_USERNAME') and defined('SENDGRID_PASSWORD') ) {
      return SENDGRID_PASSWORD;
    } else {
      return get_option('sendgrid_pwd');
    }
  }

  /**
   * Return api_key from the database or global variable
   *
   * @return string api key
   */
  public static function get_api_key()
  {
    if ( defined('SENDGRID_API_KEY') ) {
      return SENDGRID_API_KEY;
    } else {
      return get_option('sendgrid_api_key');
    }
  }

  /**
   * Return send method from the database or global variable
   *
   * @return string send_method
   */
  public static function get_send_method()
  {
    if ( defined('SENDGRID_SEND_METHOD') ) {
      return SENDGRID_SEND_METHOD;
    } elseif ( get_option('sendgrid_api') ) {
      return get_option('sendgrid_api');
    } else {
      return 'api';
    }
  }

  /**
   * Return port from the database or global variable
   *
   * @return string port
   */
  public static function get_port()
  {
    if ( defined('SENDGRID_PORT') ) {
      return SENDGRID_PORT;
    } else {
      return get_option('sendgrid_port');
    }
  }

  /**
   * Return auth method from the database or global variable
   *
   * @return string auth_method
   */
  public static function get_auth_method()
  {
    if ( defined('SENDGRID_AUTH_METHOD') ) {
      return SENDGRID_AUTH_METHOD;
    } elseif ( Sendgrid_Tools::get_api_key() ) {
      return 'apikey';
    } elseif ( Sendgrid_Tools::get_username() and Sendgrid_Tools::get_password() and ! Sendgrid_Tools::get_api_key() ) {
      return 'username';
    } elseif ( get_option('sendgrid_auth_method') ) {
      return get_option('sendgrid_auth_method');
    } else {
      return 'apikey';
    }
  }

  /**
   * Return from name from the database or global variable
   *
   * @return string from_name
   */
  public static function get_from_name()
  {
    if ( defined('SENDGRID_FROM_NAME') ) {
      return SENDGRID_FROM_NAME;
    } else {
      return get_option('sendgrid_from_name');
    }
  }

  /**
   * Return from email address from the database or global variable
   *
   * @return string from_email
   */
  public static function get_from_email()
  {
    if ( defined('SENDGRID_FROM_EMAIL') ) {
      return SENDGRID_FROM_EMAIL;
    } else {
      return get_option('sendgrid_from_email');
    }
  }

  /**
   * Return reply to email address from the database or global variable
   *
   * @return string reply_to
   */
  public static function get_reply_to()
  {
    if ( defined('SENDGRID_REPLY_TO') ) {
      return SENDGRID_REPLY_TO;
    } else {
      return get_option('sendgrid_reply_to');
    }
  }

  /**
   * Return categories from the database or global variable
   *
   * @return string categories
   */
  public static function get_categories()
  {
    if ( defined('SENDGRID_CATEGORIES') ) {
      return SENDGRID_CATEGORIES;
    } else {
      return get_option('sendgrid_categories');
    }
  }

  /**
   * Return categories array
   *
   * @return array categories
   */
  public static function get_categories_array()
  {
    $categories = Sendgrid_Tools::get_categories();
    if ( strlen( trim( $categories ) ) )
    {
      return explode( ',', $categories );
    }

    return array();
  }
}