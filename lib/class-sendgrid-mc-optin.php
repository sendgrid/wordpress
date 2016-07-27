<?php

require_once plugin_dir_path( __FILE__ ) . 'class-sendgrid-tools.php';
require_once plugin_dir_path( __FILE__ ) . 'class-sendgrid-nlvx.php';
require_once plugin_dir_path( __FILE__ ) . 'class-sendgrid-virtual-pages.php';

class Sendgrid_OptIn_API_Endpoint{
  /** 
   * Hook WordPress
   *
   * @return void
   */
  public function __construct(){
    add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
    add_action( 'parse_request', array( $this, 'sniff_requests' ), 0 );
  }
    
  /** 
   * Add public query vars
   *
   * @param array $vars List of current public query vars
   * @return array $vars 
   */
  public function add_query_vars( $vars ){
    $vars[] = '__sg_api';
    $vars[] = 'token';

    return $vars;
  }
  
  /** 
   * Sniff Requests
   * This is where we hijack all API requests
   *  
   * @return die if API request
   */
  public function sniff_requests(){
    global $wp;

    if( isset( $wp->query_vars['__sg_api'] ) )
    {
      $this->handle_request();
      exit;
    }
  }
  
  /** 
   * Handle Requests
   * This is where compute the email from the token and subscribe the user_error()
   *
   * @return void 
   */
  protected function handle_request(){
    global $wp;

    $token = $wp->query_vars['token'];
    if ( !$token )
    {
      wp_redirect( 'sg-subscription-missing-token' );

      exit();
    }
    
    $transient = get_transient( $token );

    if ( !$transient || 
      !is_array( $transient ) || 
      !isset( $transient['email'] )  || 
      !isset( $transient['first_name'] ) || 
      !isset( $transient['last_name'] ) )
    {
      wp_redirect( 'sg-subscription-invalid-token' );

      exit();
    }

    $subscribed = Sendgrid_NLVX::create_and_add_recipient_to_list(
                    $transient['email'], 
                    $transient['first_name'], 
                    $transient['last_name'] );

    if ( $subscribed )
    {
      set_transient( $token, null );
      $page = Sendgrid_Tools::get_mc_signup_confirmation_page_url();
      if ( $page == false ) {
        wp_redirect( 'sg-subscription-success' );

        exit();
      } 
      else 
      {
         wp_redirect( $page );

         exit();
      }

      return;
    }
    else
    {
       wp_redirect( 'sg-error' );

       exit();
    }
  }

  /** 
   * Send OptIn email
   *  
   * @param  string $email      Email of subscribed user
   * @param  string $first_name First Name of subscribed user
   * @param  string $last_name  Last Name of subscribed user
   * @return bool
   */
  public static function send_confirmation_email( $email, $first_name = '', $last_name = '', $from_settings = false ) {
    $subject = Sendgrid_Tools::get_mc_signup_email_subject();
    $content = Sendgrid_Tools::get_mc_signup_email_content();

    if ( false == $subject or false == $content ) {
      return false;
    }

    $subject = stripslashes( $subject );
    $content = stripslashes( $content );
    $to = array( $email );

    $token = Sendgrid_OptIn_API_Endpoint::generate_email_token( $email, $first_name, $last_name );

    $transient = get_transient($token);

    if ( $transient and isset( $transient['email'] ) and ! $from_settings ) {
      return false;
    }

    if( false == set_transient( $token, 
      array( 
        'email' => $email, 
        'first_name' => $first_name, 
        'last_name' => $last_name ),
        24 * 60 * 60 ) and ! $from_settings and $transient ) {
      return false;
    }

    $confirmation_link = site_url() . '?__sg_api=1&token=' . $token;
    $headers = new SendGrid\Email();
    $headers->addSubstitution( '%confirmation_link%', array( $confirmation_link ) )
            ->addCategory( 'wp_sendgrid_subscription_widget' );

    add_filter( 'wp_mail_content_type', 'set_html_content_type' );
    $result = wp_mail( $to, $subject, $content, $headers );
    remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

    return $result;
  }

  /**
   * Generates a hash from an email address using sha1
   *
   * @return string hash from email address
   */
  private static function generate_email_token( $email ){
    return hash( "sha1", $email );
  }
}

// Initialize OptIn Endopint
new Sendgrid_OptIn_API_Endpoint();

add_action( 'init', 'sg_create_subscribe_general_error_page' );
add_action( 'init', 'sg_create_subscribe_missing_token_error_page' );
add_action( 'init', 'sg_create_subscribe_invalid_token_error_page' );
add_action( 'init', 'sg_create_subscribe_success_page' );
