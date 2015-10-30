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
        $subject = stripslashes( $_POST['sendgrid_subj'] );
        $body    = stripslashes( $_POST['sendgrid_body'] );
        $headers = $_POST['sendgrid_headers'];
        if ( preg_match( "/content-type:\s*text\/html/i", $headers ) ) {
          $body_br = nl2br( $body );
        } else {
          $body_br =  $body;
        }

        $sent    = wp_mail($to, $subject, $body_br, $headers);
        if ( 'api' == Sendgrid_Tools::get_send_method() )
        {
          $sent = json_decode( $sent['body'] );
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
        elseif ( 'smtp' == Sendgrid_Tools::get_send_method() )
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
        if ( isset ($_POST['auth_method'] ) ) {
          if ( $_POST['auth_method'] == 'apikey' ) {
            if ( ! $_POST['sendgrid_api_key'] ){
              $message = 'Api key is required';
              $status  = 'error';
            } else {
              if ( ! Sendgrid_Tools::check_api_key( $_POST['sendgrid_api_key'] ) ) {
                $message = 'Invalid api key';
                $status  = 'error';
              }
            }
          } else {
            if ( ! $_POST['sendgrid_user'] or ! $_POST['sendgrid_pwd'] ) {
              $message = 'Username/Password are required';
              $status  = 'error';
            } else {
              if ( ! Sendgrid_Tools::check_username_password( $_POST['sendgrid_user'], $_POST['sendgrid_pwd'] ) ) {
                $message = 'Invalid username/password';
                $status  = 'error';
              }
            }
          }
        }

        if (isset($_POST['sendgrid_name']))
        {
          $name = $_POST['sendgrid_name'];
          update_option('sendgrid_from_name', $name);
        }

        if (isset($_POST['sendgrid_email']))
        {
          $email = $_POST['sendgrid_email'];
          update_option('sendgrid_from_email', $email);
        }

        if (isset($_POST['sendgrid_reply_to']))
        {
          $reply_to = $_POST['sendgrid_reply_to'];
          update_option('sendgrid_reply_to', $reply_to);
        }

        if (isset($_POST['sendgrid_categories']))
        {
          $categories = $_POST['sendgrid_categories'];
          update_option('sendgrid_categories', $categories);
        }

        if (isset($_POST['sendgrid_api']))
        {
          $method = $_POST['sendgrid_api'];
          update_option('sendgrid_api', $method);
        }

        if (isset($_POST['auth_method']))
        {
          $auth_method = $_POST['auth_method'];
          update_option('sendgrid_auth_method', $auth_method);
        }

        if ( isset( $_POST['sendgrid_port'] ) ) {
          $port = $_POST['sendgrid_port'];
          update_option('sendgrid_port', $port);
        }

        if ( ! isset( $status ) or ( isset( $status ) and ( $status != 'error' ) ) ) {
          $message = 'Options saved.';
          $status  = 'updated';

          if (isset($_POST['sendgrid_api_key']))
          {
            $user = $_POST['sendgrid_api_key'];
            update_option('sendgrid_api_key', $user);
          }

          if (isset($_POST['sendgrid_user']))
          {
            $user = $_POST['sendgrid_user'];
            update_option('sendgrid_user', $user);
          }

          if (isset($_POST['sendgrid_pwd']))
          {
            $password = $_POST['sendgrid_pwd'];
            update_option('sendgrid_pwd', $password);
          }
        }
      }
    }
    
    $user        = Sendgrid_Tools::get_username();
    $password    = Sendgrid_Tools::get_password();
    $api_key     = Sendgrid_Tools::get_api_key();
    $method      = Sendgrid_Tools::get_send_method();
    $auth_method = Sendgrid_Tools::get_auth_method();
    $name        = stripslashes( Sendgrid_Tools::get_from_name() );
    $email       = Sendgrid_Tools::get_from_email();
    $reply_to    = Sendgrid_Tools::get_reply_to();
    $categories  = stripslashes( Sendgrid_Tools::get_categories() );
    $port        = Sendgrid_Tools::get_port();

    $allowed_methods = array('smtp', 'api');
    if (!in_array($method, $allowed_methods))
    {
      $message = 'Invalid send method, available methods are: "api" or "smtp".';
      $status = 'error';
    }

    if ('smtp' == $method and !class_exists('Swift'))
    {
      $message = 'You must have <a href="http://wordpress.org/plugins/swift-mailer/" target="_blank">' .
        'Swift-mailer plugin</a> installed and activated';
      $status = 'error';
    }

    if ( $api_key ) {
      if ( ! Sendgrid_Tools::check_api_key( $api_key ) ) {
          $message = 'Invalid api key';
          $status  = 'error';
        }
    } else {
      if ( $user and $password ) {
        if ( ! Sendgrid_Tools::check_username_password( $user, $password ) ) {
            $message = 'Invalid username/password';
            $status  = 'error';
        }
      }
    }

    $are_global_credentials = ( defined('SENDGRID_USERNAME') and defined('SENDGRID_PASSWORD') );
    $is_global_api_key = defined('SENDGRID_API_KEY');
    $has_port = defined('SENDGRID_PORT');
        
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

    wp_enqueue_script( 'sendgrid', plugin_dir_url( __FILE__ ) . '../view/js/sendgrid.settings.js', array('jquery') );
  }
}