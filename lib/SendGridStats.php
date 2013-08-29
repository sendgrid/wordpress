<?php
/*
 * Display statistics on dashboard
 */
function sendgrid_dashboard_statistics() 
{
  require plugin_dir_path( __FILE__ ) . '../view/sendgrid_stats.php';
}

function sendgrid_dashboard_statistics_deliveries() 
{
  require plugin_dir_path( __FILE__ ) . '../view/sendgrid_stats_deliveries.php';
}

function sendgrid_dashboard_statistics_compliance() 
{
  require plugin_dir_path( __FILE__ ) . '../view/sendgrid_stats_compliance.php';
}

function sendgrid_dashboard_statistics_engagement() 
{
  require plugin_dir_path( __FILE__ ) . '../view/sendgrid_stats_engagement.php';
}

function my_custom_dashboard_widgets() 
{ 
  $sendgridSettings = new wpSendGridSettings();
  if (!$sendgridSettings->checkUsernamePassword(get_option('sendgrid_user'),get_option('sendgrid_pwd')))
    return;
  
  add_meta_box('sendgrid_statistics_widget', 'SendGrid Statistics', 'sendgrid_dashboard_statistics', 'dashboard', 'side', 'high');
  add_meta_box('sendgrid_statistics_deliveries_widget', 'SendGrid Deliveries', 'sendgrid_dashboard_statistics_deliveries', 'dashboard', 'side', 'high');
  add_meta_box('sendgrid_statistics_compliance_widget', 'SendGrid Compliance', 'sendgrid_dashboard_statistics_compliance', 'dashboard', 'side', 'high');
  add_meta_box('sendgrid_statistics_engagement_widget', 'SendGrid Engagement', 'sendgrid_dashboard_statistics_engagement', 'dashboard', 'side', 'high');
}
add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');

/*
 * Add javascripts we need
 */
function sendgrid_load_script($hook) 
{
  if ($hook != "index.php")
    return;
  
  wp_enqueue_script('sendgrid-stats', plugin_dir_url(__FILE__) . '../view/js/sendgrid-stats.js', array('jquery'));
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

/*
 * Get stats from api
 */
function sendgrid_process_stats() 
{
  if (!isset($_POST['sendgrid_nonce']) || !wp_verify_nonce($_POST['sendgrid_nonce'], 'sendgrid-nonce'))
    die('Permissions check failed');
  
  $parameters = array();
  $parameters['api_user'] = get_option('sendgrid_user');
  $parameters['api_key'] = get_option('sendgrid_pwd');
  $parameters['data_type'] = 'global';
  $parameters['metric'] = 'all';
  
  if (array_key_exists('days', $_POST)) 
  {
    $parameters['days'] = $_POST['days'];
  } else {
    $parameters['start_date'] = $_POST['start_date'];
    $parameters['end_date'] = $_POST['end_date'];
  }

  echo _processRequest('api/stats.get.json', $parameters);
  
  die();
}
add_action('wp_ajax_sendgrid_get_stats', 'sendgrid_process_stats');

function _processRequest($api = 'api/stats.get.json', $parameters = array()) 
{
  $data = urldecode(http_build_query($parameters));
  $process = curl_init();
  curl_setopt($process, CURLOPT_URL, 'http://sendgrid.com/' . $api . '?' . $data);
  curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($process, CURLOPT_POST, 1);
  curl_setopt($process, CURLOPT_POSTFIELDS, http_build_query($data));
  
  return curl_exec($process);
}