<?php

require_once plugin_dir_path( __FILE__ ) . 'class-sendgrid-api.php';
require_once plugin_dir_path( __FILE__ ) . 'class-sendgrid-smtp.php';
require_once plugin_dir_path( __FILE__ ) . '../class-sendgrid-tools.php';

class Sendgrid_WP {
  private function __construct() {}

  public static $error;

  public static function get_instance() {
    $send_method = Sendgrid_Tools::get_send_method();

    switch ( $send_method ) {
      case 'api':
        return self::api_instance();
        break;

      case 'smtp':
        return self::smtp_instance();
        break;
    }

    return self::api_instance();
  }

  private static function api_instance() {
    return new Sendgrid_API( 'apikey', Sendgrid_Tools::get_api_key() );
  }

  private static function smtp_instance( ) 
  {
    if ( ! class_exists('Swift') ) {
      self::$error = array(
        "success" => false,
        "message" => "Swift Class not loaded. Please activate Swift plugin or use API."
      );

      return null;
    } 

    $smtp = new Sendgrid_SMTP( "apikey", Sendgrid_Tools::get_api_key() );

    if ( Sendgrid_Tools::get_port() ) {
      if ( in_array( Sendgrid_Tools::get_port(), Sendgrid_Tools::$allowed_ports ) ) {
        $smtp->set_port( Sendgrid_Tools::get_port() );
      } else {
        $smtp->set_port( Sendgrid_SMTP::TLS );
      }
    }

    return $smtp;
  }
}