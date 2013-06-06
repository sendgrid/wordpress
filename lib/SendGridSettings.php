<?php
class wp_SendGrid_Settings
{
  public function __construct()
  {
    add_action('admin_menu', array(__CLASS__, 'sendgridPluginMenu'));
  }

  public function sendgridPluginMenu()
  {
    add_options_page(__('SendGrid Settings'), __('SendGrid Settings'), 'manage_options', 'sendgrid-settings.php',
      array(__CLASS__, 'show_settings_page'));
  }

  public function show_settings_page()
  {
    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
      $user = $_POST['sendgrid_user'];
      update_option('sendgrid_user', $user);

      $password = $_POST['sendgrid_pwd'];
      update_option('sendgrid_pwd', $password);

      $method = $_POST['sendgrid_api'];
      update_option('sendgrid_api', $method);

      $secure = ($_POST['sendgrid_secure'] == 'on') ? true : false;
      update_option('sendgrid_secure', $secure);
      echo '<div class="updated"><p><strong>Options saved.</strong></p></div>';
    }
    else
    {
      $user = get_option('sendgrid_user');
      $password = get_option('sendgrid_pwd');
      $method = get_option('sendgrid_api');
      $secure = get_option('sendgrid_secure');
    }

    require_once '../wp-content/plugins/SendGrid/view/sendgrid_settings.php';
  }
}