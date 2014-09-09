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

    return curl_exec( $process );
  }
}