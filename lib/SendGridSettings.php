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
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
      if ($_POST['email_test'])
      {
        $to = $_POST['sendgrid_to'];
        $subject = $_POST['sendgrid_subj'];
        $body = $_POST['sendgrid_body'];
        $headers = $_POST['sendgrid_headers'];
        $attachments = null;
        $success = json_decode(wp_mail($to, $subject, $body, $headers, $attachments));

        if ($success->message == "success" or $success === true)
        {
          $message = 'Email sent.';
          $status = 'send_success';
        }
        else 
        {
          $message = 'Email not sent. ' . $success->errors[0];
          $status = 'send_failed';
        }
      }
      else
      {
        $user = $_POST['sendgrid_user'];
        update_option('sendgrid_user', $user);

        $password = $_POST['sendgrid_pwd'];
        update_option('sendgrid_pwd', $password);

        $method = $_POST['sendgrid_api'];
        update_option('sendgrid_api', $method);

        $secure = ($_POST['sendgrid_secure'] == 'on') ? true : false;
        update_option('sendgrid_secure', $secure);

        $name = $_POST['sendgrid_name'];
        update_option('sendgrid_from_name', $name);

        $email = $_POST['sendgrid_email'];
        update_option('sendgrid_from_email', $email);

        $reply_to = $_POST['sendgrid_reply_to'];
        update_option('sendgrid_reply_to', $reply_to);

        $message = 'Options saved.';
        $status = 'updated';
      }
    }
    $user = get_option('sendgrid_user');
    $password = get_option('sendgrid_pwd');
    $method = get_option('sendgrid_api');
    $secure = get_option('sendgrid_secure');
    $name = get_option('sendgrid_from_name');
    $email = get_option('sendgrid_from_email');
    $reply_to = get_option('sendgrid_reply_to');
    
    require_once dirname(__FILE__) . '/../view/sendgrid_settings.php';
  }
}