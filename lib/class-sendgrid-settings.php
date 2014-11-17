<?php

class Sendgrid_Settings
{
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
  public static function add_settings_menu()
  {
    add_options_page( __( 'SendGrid' ), __( 'SendGrid' ), 'manage_options', 'sendgrid-settings',
      array( __CLASS__, 'show_settings_page' ));
  }

  /**
   * Display SendGrid settings page content
   */
  public static function show_settings_page()
  { 
    if ( 'POST' == $_SERVER['REQUEST_METHOD'] )
    {
      if ( isset( $_POST['email_test'] ) and $_POST['email_test'] )
      {
        $to      = $_POST['sendgrid_to'];
        $subject = $_POST['sendgrid_subj'];
        $body    = $_POST['sendgrid_body'];
        $headers = $_POST['sendgrid_headers'];
        $sent    = wp_mail($to, $subject, $body, $headers);
        if ( 'api' == get_option('sendgrid_api') )
        {
          $sent = json_decode( $sent );
          if ( "success" == $sent->message )
          {
            $message = 'Email sent.';
            $status  = 'updated';
          } else {
            $errors  = ( $sent->errors[0] ) ? $sent->errors[0] : $sent;
            $message = 'Email not sent. ' . $errors;
            $status  = 'error';
          }

        }
        elseif ( 'smtp' == get_option('sendgrid_api') )
        {
          if ( true === $sent )
          {
            $message = 'Email sent.';
            $status  = 'updated';
          } else {
            $message = 'Email not sent. ' . $sent;
            $status  = 'error';
          }
        }
      } else {
        $message = 'Options saved.';
        $status  = 'updated';
        
        $user = $_POST['sendgrid_user'];
        update_option( 'sendgrid_user', $user );

        $password = $_POST['sendgrid_pwd'];        
        update_option( 'sendgrid_pwd', $password );

        $method = $_POST['sendgrid_api'];
        if ( 'smtp' == $method and ! class_exists('Swift') )
        {
          $message = 'You must have <a href="http://wordpress.org/plugins/swift-mailer/" target="_blank">' .
                      'Swift-mailer plugin</a> installed and activated';
          $status  = 'error';

          update_option( 'sendgrid_api', 'api' );
        } else {
          update_option( 'sendgrid_api', $method );
        }

        $name = $_POST['sendgrid_name'];
        update_option( 'sendgrid_from_name', $name );

        $email = $_POST['sendgrid_email'];
        update_option( 'sendgrid_from_email', $email );

        $reply_to = $_POST['sendgrid_reply_to'];
        update_option( 'sendgrid_reply_to', $reply_to );

        $categories = $_POST['sendgrid_categories'];
        update_option( 'sendgrid_categories', $categories );
      }
    }
    
    $user       = defined( 'SENDGRID_USER' ) ? SENDGRID_USER : get_option('sendgrid_user');
    $password   = defined( 'SENDGRID_PWD' ) ? SENDGRID_PWD : get_option('sendgrid_pwd');
    $method     = get_option('sendgrid_api');
    $name       = get_option('sendgrid_from_name');
    $email      = get_option('sendgrid_from_email');
    $reply_to   = get_option('sendgrid_reply_to');
    $categories = get_option('sendgrid_categories');

    $valid_credentials = false;
    if ( $user and $password )
    {
      if ( in_array( 'curl', get_loaded_extensions() ) )
      {
        $valid_credentials = Sendgrid_Tools::check_username_password( $user, $password );

        if ( ! $valid_credentials )
        {
          $message = 'Invalid username/password';
          $status  = 'error';
        }
      } else {
        $message = 'You must have PHP-curl extension enabled';
        $status  = 'error';
      }
    }
        
    require_once dirname( __FILE__ ) . '/../view/sendgrid_settings.php';
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
  }
}