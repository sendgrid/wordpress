<?php
class wpSendGridSettings
{
  public function __construct()
  {
    add_action('admin_menu', array(__CLASS__, 'sendgridPluginMenu'));
  }

  /**
   * Add settings page
   */
  public function sendgridPluginMenu()
  {
    add_options_page(__('SendGrid'), __('SendGrid'), 'manage_options', 'sendgrid-settings.php',
      array(__CLASS__, 'show_settings_page'));
  }

  /**
   * Check username/password
   *
   * @param   string  $username   sendgrid username
   * @param   string  $password   sendgrid password
   * @return  bool  
   */
  public static function checkUsernamePassword($username, $password)
  {
    $url = "https://sendgrid.com/api/profile.get.json?";
    $url .= "api_user=". $username . "&api_key=" . $password;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $data = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($data, true);

    if (isset($response['error']))
    {
      return false;
    }

    return true;
  }

  /**
   * Display settings page
   */
  public function show_settings_page()
  { 
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
      if (isset($_POST['email_test']) and $_POST['email_test'])
      {
        $to = $_POST['sendgrid_to'];
        $subject = $_POST['sendgrid_subj'];
        $body = $_POST['sendgrid_body'];
        $headers = $_POST['sendgrid_headers'];
        $sent = wp_mail($to, $subject, $body, $headers);
        if (get_option('sendgrid_api') == 'api')
        {
          $sent = json_decode($sent);
          if ($sent->message == "success")
          {
            $message = 'Email sent.';
            $status = 'updated';
          }
          else 
          {
            $errors = ($sent->errors[0]) ? $sent->errors[0] : $sent;
            $message = 'Email not sent. ' . $errors;
            $status = 'error';
          }

        }
        elseif (get_option('sendgrid_api') == 'smtp')
        {
          if ($sent === true)
          {
            $message = 'Email sent.';
            $status = 'updated';
          }
          else 
          {
            $message = 'Email not sent. ' . $sent;
            $status = 'error';
          }
        }
      }
      else
      {
        $message = 'Options saved.';
        $status = 'updated';
        
        $user = $_POST['sendgrid_user'];
        update_option('sendgrid_user', $user);

        $password = $_POST['sendgrid_pwd'];        
        update_option('sendgrid_pwd', $password);

        $method = $_POST['sendgrid_api'];
        if ($method == 'smtp' and !class_exists('Swift'))
        {
          $message = 'You must have <a href="http://wordpress.org/plugins/swift-mailer/" target="_blank">' .
                      'Swift-mailer plugin</a> installed and activated';
          $status = 'error';
          update_option('sendgrid_api', 'api');
        }
        else
        {
          update_option('sendgrid_api', $method);
        }

        $name = $_POST['sendgrid_name'];
        update_option('sendgrid_from_name', $name);

        $email = $_POST['sendgrid_email'];
        update_option('sendgrid_from_email', $email);

        $reply_to = $_POST['sendgrid_reply_to'];
        update_option('sendgrid_reply_to', $reply_to);        
      }
    }
    
    $user = get_option('sendgrid_user');
    $password = get_option('sendgrid_pwd');
    $method = get_option('sendgrid_api');
    $name = get_option('sendgrid_from_name');
    $email = get_option('sendgrid_from_email');
    $reply_to = get_option('sendgrid_reply_to');

    if ($user and $password)
    {
      if (in_array('curl', get_loaded_extensions()))
      {
        $valid_credentials = self::checkUsernamePassword($user, $password);

        if (!$valid_credentials)
        {
          $message = 'Invalid username/password';
          $status = 'error';
        }
      }
      else
      {
        $message = 'You must have PHP-curl extension enabled';
        $status = 'error';
      }
    }
        
    require_once dirname(__FILE__) . '/../view/sendgrid_settings.php';
  }
}