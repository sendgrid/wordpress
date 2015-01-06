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
    $url .= "api_user=$username&api_key=$password";

    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );

    // cURL proxy support
    $proxy = new \WP_HTTP_Proxy();

    if ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) ) {

      curl_setopt( $ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );
      curl_setopt( $ch, CURLOPT_PROXY, $proxy->host() );
      curl_setopt( $ch, CURLOPT_PROXYPORT, $proxy->port() );

      if ( $proxy->use_authentication() ) {
        curl_setopt( $ch, CURLOPT_PROXYAUTH, CURLAUTH_ANY );
        curl_setopt( $ch, CURLOPT_PROXYUSERPWD, $proxy->authentication() );
      }
    }

    $data = curl_exec( $ch );
    curl_close( $ch );

    $response = json_decode( $data, true );

    if ( isset( $response['error'] ) )
    {
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
  public static function curl_request( $api = 'api/stats.get.json', $parameters = array() )
  {
    $data = urldecode( http_build_query( $parameters ) );
    $process = curl_init();
    curl_setopt( $process, CURLOPT_URL, "http://sendgrid.com/$api?$data" );
    curl_setopt( $process, CURLOPT_RETURNTRANSFER, 1 );

    // cURL proxy support
    $proxy = new \WP_HTTP_Proxy();

    if ( $proxy->is_enabled() && $proxy->send_through_proxy( $url ) ) {

      curl_setopt( $process, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );
      curl_setopt( $process, CURLOPT_PROXY, $proxy->host() );
      curl_setopt( $process, CURLOPT_PROXYPORT, $proxy->port() );

      if ( $proxy->use_authentication() ) {
        curl_setopt( $process, CURLOPT_PROXYAUTH, CURLAUTH_ANY );
        curl_setopt( $process, CURLOPT_PROXYUSERPWD, $proxy->authentication() );
      }
    }

    return curl_exec( $process );
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