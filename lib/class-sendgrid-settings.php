<?php

require_once plugin_dir_path( __FILE__ ) . 'sendgrid/class-sendgrid-smtp.php';
require_once plugin_dir_path( __FILE__ ) . 'class-sendgrid-tools.php';

class Sendgrid_Settings {
  public function __construct( $plugin_directory )
  {
    // Add SendGrid settings page in the menu
    add_action( 'admin_menu', array( __CLASS__, 'add_settings_menu' ) );

    // Add SendGrid settings page in the plugin list
    add_filter( 'plugin_action_links_' . $plugin_directory, array( __CLASS__, 'add_settings_link' ) );

    // Add SendGrid Help contextual menu in the settings page
    add_filter( 'contextual_help', array( __CLASS__, 'show_contextual_help' ), 10, 3 );

    // Add SendGrid javascripts in header
    add_action( 'admin_enqueue_scripts', array( __CLASS__, 'add_headers' ) );
  }

  /**
   * Add SendGrid settings page in the menu
   */
  public static function add_settings_menu() {
    add_options_page( __( 'SendGrid' ), __( 'SendGrid' ), 'manage_options', 'sendgrid-settings',
      array( __CLASS__, 'show_settings_page' ));
  }

  /**
   * Add SendGrid settings page in the plugin list
   *
   * @param  mixed   $links   links
   * @return mixed            links
   */
  public static function add_settings_link( $links )
  {
    $settings_link = '<a href="options-general.php?page=sendgrid-settings.php">Settings</a>';
    array_unshift( $links, $settings_link );

    return $links;
  }

  /**
   * Add SendGrid Help contextual menu in the settings page
   *
   * @param   mixed   $contextual_help    contextual help
   * @param   integer $screen_id          screen id
   * @param   integer $screen             screen
   * @return  string
   */
  public static function show_contextual_help( $contextual_help, $screen_id, $screen )
  {
    if ( SENDGRID_PLUGIN_STATISTICS == $screen_id or SENDGRID_PLUGIN_SETTINGS == $screen_id )
    {
      $contextual_help = file_get_contents( dirname( __FILE__ ) . '/../view/sendgrid_contextual_help.php' );
    }

    return $contextual_help;
  }

  /**
   * Include css & javascripts we need for SendGrid settings page and widget
   *
   * @return void;
   */
  public static function add_headers( $hook )
  {
    if ( SENDGRID_PLUGIN_SETTINGS != $hook ) {
      return;
    }

    wp_enqueue_style( 'sendgrid', plugin_dir_url( __FILE__ ) . '../view/css/sendgrid.css' );

    wp_enqueue_script( 'sendgrid', plugin_dir_url( __FILE__ ) . '../view/js/sendgrid.settings-v1.7.0.js', array('jquery') );
  }

  /**
   * Display SendGrid settings page content
   */
  public static function show_settings_page()
  { 
    $response = null;
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
      $response = self::do_post($_POST);
    }
    
    $user        = Sendgrid_Tools::get_username();
    $password    = Sendgrid_Tools::get_password();
    $api_key     = Sendgrid_Tools::get_api_key();
    $send_method = Sendgrid_Tools::get_send_method();
    $auth_method = Sendgrid_Tools::get_auth_method();
    $name        = stripslashes( Sendgrid_Tools::get_from_name() );
    $email       = Sendgrid_Tools::get_from_email();
    $reply_to    = Sendgrid_Tools::get_reply_to();
    $categories  = stripslashes( Sendgrid_Tools::get_categories() );
    $template    = stripslashes( Sendgrid_Tools::get_template() );
    $port        = Sendgrid_Tools::get_port();

    $allowed_methods = array('API');
    if ( class_exists( 'Swift' ) ) {
      $allowed_methods[] = 'SMTP';
    }

    if ( ! in_array( strtoupper( $send_method ), $allowed_methods ) ) {
      $message = 'Invalid send method, available methods are: "API" or "SMTP".';
      $status = 'error';
    }

    if ( "apikey" == $auth_method and ! Sendgrid_Tools::check_api_key( $api_key, true ) ) {
        $message = 'API Key is invalid.';
        $status  = 'error';
    } else if ( "credentials" == $auth_method and ! Sendgrid_Tools::check_username_password( $user, $password, true ) ) {
        $message = 'Username and password are invalid.';
        $status  = 'error';
    }

    if ( $template and ! Sendgrid_Tools::check_template( $template ) ) {
      $message = 'Template not found.';
      $status  = 'error';
    }

    $is_env_auth_method = defined('SENDGRID_AUTH_METHOD');
    $is_env_send_method = defined('SENDGRID_SEND_METHOD');
    $is_env_username = defined('SENDGRID_USERNAME');
    $is_env_password = defined('SENDGRID_PASSWORD');
    $is_env_api_key = defined('SENDGRID_API_KEY');
    $is_env_port = defined('SENDGRID_PORT');
    
    if ( $response ) {
      $message = $response['message'];
      $status = $response['status'];
      if( array_key_exists('error_type', $response) ) {
        $error_type = $response['error_type'];
      }
    }

    require_once dirname( __FILE__ ) . '/../view/sendgrid_settings.php';
  }

  private static function do_post( $params ) {
    if ( isset($params['email_test'] ) and $params['email_test'] ) {
      return self::send_test_email( $params );
    } 
    
    return self::save_settings( $params );
  }

  private static function save_settings( $params ) {
    if ( ! isset( $params['auth_method'] ) )
      $params['auth_method'] = Sendgrid_Tools::get_auth_method();

    switch ( $params['auth_method'] ) {
      case 'apikey':
        if ( ! isset( $params['sendgrid_apikey'] ) )
          break;

        if ( ! $params['sendgrid_apikey'] ) {
          $response = array(
            "message" => "API Key is empty.",
            "status" => "error"
          );
        } elseif ( ! Sendgrid_Tools::check_api_key( $params['sendgrid_apikey'], true ) ) {
          $response = array(
            "message" => "API Key is invalid or without permissions.",
            "status" => "error"
          );

          break;
        }

        Sendgrid_Tools::set_api_key($params['sendgrid_apikey']);

        break;
      
      case 'credentials':
        if ( ! isset( $params['sendgrid_username'] ) and ! isset( $params['sendgrid_password'] ) )
          break;

        $save_username = true;
        $save_password = true;

        if ( ! isset ( $params['sendgrid_username'] ) ) {
          $save_username = false;
          $params['sendgrid_username'] = Sendgrid_Tools::get_username();
        }

        if ( ! isset ( $params['sendgrid_password'] ) ) {
          $save_password = false;
          $params['sendgrid_password'] = Sendgrid_Tools::get_username();
        }

        if ( ( isset( $params['sendgrid_username'] ) and ! $params['sendgrid_username'] ) or ( isset( $params['sendgrid_password'] ) and ! $params['sendgrid_password'] ) ) {
          $response = array(
            "message" => "Username or password is empty.",
            "status" => "error"
          );
        } elseif ( ! Sendgrid_Tools::check_username_password( $params['sendgrid_username'], $params['sendgrid_password'], true ) ) {
          $response = array(
            "message" => "Username and password are invalid.",
            "status" => "error"
          );

          break;
        }

        if ( $save_username )
          Sendgrid_Tools::set_username($params['sendgrid_username']);
        
        if ( $save_password )
          Sendgrid_Tools::set_password($params['sendgrid_password']);

        break;
    }

    if ( isset( $params['sendgrid_name'] ) ) {
      update_option('sendgrid_from_name', $params['sendgrid_name']);
    }

    if ( isset( $params['sendgrid_email'] ) ) {
      update_option('sendgrid_from_email', $params['sendgrid_email']);
    }

    if ( isset( $params['sendgrid_reply_to'] ) ) {
      update_option('sendgrid_reply_to', $params['sendgrid_reply_to']);
    }

    if ( isset( $params['sendgrid_categories'] ) ) {
      update_option('sendgrid_categories', $params['sendgrid_categories']);
    }

    if ( isset( $params['sendgrid_template'] ) ) {
      if ( ! Sendgrid_Tools::check_template( $params['sendgrid_template'] ) ) {
        $response = array(
          "message" => "Template not found.",
          "status" => "error"
        );
      } else {
        update_option( 'sendgrid_template', $params['sendgrid_template'] );
      }
    }

    if ( isset( $params['send_method'] ) ) {
      update_option('sendgrid_api', $params['send_method']);
    }

    if ( isset( $params['auth_method'] ) ) {
      update_option('sendgrid_auth_method', $params['auth_method']);
    }

    if ( isset( $params['sendgrid_port'] ) ) {
      update_option('sendgrid_port', $params['sendgrid_port']);
    }

    if( isset( $response ) and $response['status'] == 'error')
      return $response;

    return array(
      "message" => "Options are saved.",
      "status" => "updated"
    );
  }

  private static function send_test_email( $params ) {
    $to      = $params['sendgrid_to'];
    $subject = stripslashes( $params['sendgrid_subj'] );
    $body    = stripslashes( $params['sendgrid_body'] );
    $headers = $params['sendgrid_headers'];

    if ( preg_match( "/content-type:\s*text\/html/i", $headers ) ) {
      $body_br = nl2br( $body );
    } else {
      $body_br =  $body;
    }

    $sent = wp_mail($to, $subject, $body_br, $headers);
    if ( true === $sent ) {
      return array(
        "message" => "Email was sent.",
        "status" => "updated"
      );
    }

    return array(
      "message" => "Email wasn't sent.",
      "status" => "updated",
      "error_type" => "sending"
    );
  }
}