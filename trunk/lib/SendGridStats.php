<?php
/*
 * Display statistics on dashboard
 */

/**
 * Verify if SendGrid username and password provided are correct and
 * initialize function for add widget in dashboard
 * 
 * @return void
 */
function my_custom_dashboard_widgets() 
{ 
  $sendgridSettings = new wpSendGridSettings();
  if (!$sendgridSettings->checkUsernamePassword(get_option('sendgrid_user'), get_option('sendgrid_pwd')))
  {
    return;
  }
  
  add_meta_box('sendgrid_statistics_widget', 'SendGrid Wordpress Statistics', 'sendgrid_dashboard_statistics', 'dashboard', 'normal', 'high');
}
add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');

/**
 * Add widget content to wordpress admin dashboard
 * 
 * @return void
 */
function sendgrid_dashboard_statistics() 
{
  require plugin_dir_path( __FILE__ ) . '../view/partials/sendgrid_stats_widget.php';
}

/**
 * Add new SendGrid statistics page in wordpress admin menu
 * 
 * @return void
 */
function add_dashboard_menu()
{
  $sendgridSettings = new wpSendGridSettings();
  if (!$sendgridSettings->checkUsernamePassword(get_option('sendgrid_user'), get_option('sendgrid_pwd')))
  {
    return;
  }
  
  add_dashboard_page( "SendGrid Statistics", "SendGrid Statistics", "manage_options", "sendgrid-statistics", "sendgrid_statistics_page"); 
}
add_action('admin_menu', 'add_dashboard_menu');

/**
 * Set content for SendGrid statistics page
 * 
 * @return void
 */
function sendgrid_statistics_page()
{
  require plugin_dir_path( __FILE__ ) . '../view/sendgrid_stats.php';
}

/**
 * Include javascripts we need for SendGrid statistics page and widget
 * 
 * @return void;
 */
function sendgrid_load_script($hook) 
{
  if ($hook != "index.php" && $hook != "dashboard_page_sendgrid-statistics")
  {
    return;
  }
  
  wp_enqueue_script('sendgrid-stats', plugin_dir_url(__FILE__) . '../view/js/sendgrid.stats.js', array('jquery'));
  wp_enqueue_script('jquery-flot', plugin_dir_url(__FILE__) . '../view/js/jquery.flot.js', array('jquery'));
  wp_enqueue_script('jquery-flot-time', plugin_dir_url(__FILE__) . '../view/js/jquery.flot.time.js', array('jquery'));
  wp_enqueue_script('jquery-flot-tofflelegend', plugin_dir_url(__FILE__) . '../view/js/jquery.flot.togglelegend.js', array('jquery'));
  wp_enqueue_script('jquery-flot-symbol', plugin_dir_url(__FILE__) . '../view/js/jquery.flot.symbol.js', array('jquery'));
  wp_enqueue_script('jquery-ui-datepicker', plugin_dir_url(__FILE__) . '../view/js/jquery.ui.datepicker.js', array('jquery', 'jquery-ui-core'));
  wp_enqueue_style('jquery-ui-datepicker', plugin_dir_url(__FILE__) . '../view/css/smoothness/jquery-ui-1.10.3.custom.css');
  wp_enqueue_style('sendgrid', plugin_dir_url(__FILE__) . '../view/css/sendgrid.css');
  wp_localize_script('sendgrid-stats', 'sendgrid_vars', array(
      'sendgrid_nonce' => wp_create_nonce('sendgrid-nonce')
  ));
}
add_action('admin_enqueue_scripts', 'sendgrid_load_script');

/**
 * Get SendGrid stats from API and return JSON response,
 * this function work like a page and is used for ajax request by javascript functions
 * 
 * @return void;
 */
function sendgrid_process_stats() 
{
  if (!isset($_POST['sendgrid_nonce']) || !wp_verify_nonce($_POST['sendgrid_nonce'], 'sendgrid-nonce'))
  {
    die('Permissions check failed');
  }
  
  $parameters = array();
  $parameters['api_user'] = get_option('sendgrid_user');
  $parameters['api_key'] = get_option('sendgrid_pwd');
  $parameters['data_type'] = 'global';
  $parameters['metric'] = 'all';
  
  if (array_key_exists('days', $_POST)) 
  {
    $parameters['days'] = $_POST['days'];
  } 
  else 
  {
    $parameters['start_date'] = $_POST['start_date'];
    $parameters['end_date'] = $_POST['end_date'];
  }
  
  if ($_POST['type'] and $_POST['type'] == 'wordpress')
  {
    $parameters['category'] = 'wp_sendgrid_plugin';
  }

  echo _processRequest('api/stats.get.json', $parameters);
  
  die();
}
add_action('wp_ajax_sendgrid_get_stats', 'sendgrid_process_stats');

/**
 * Make cURL request to SendGrid API for required statistics
 * 
 * @param type $api
 * @param type $parameters
 * @return json
 */
function _processRequest($api = 'api/stats.get.json', $parameters = array()) 
{
  $data = urldecode(http_build_query($parameters));
  $process = curl_init();
  curl_setopt($process, CURLOPT_URL, 'http://sendgrid.com/' . $api . '?' . $data);
  curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
  
  return curl_exec($process);
}