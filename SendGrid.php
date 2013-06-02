<?php
/*
Plugin Name: SendGrid
Plugin URI: http://sendgrid.com
Description: Email Delivery. Simplified. SendGrid's cloud-based email infrastructure relieves businesses of the cost and complexity of maintaining custom email systems. SendGrid provides reliable delivery, scalability and real-time analytics along with flexible APIs that make custom integration a breeze.
Version: 1.0
Author: SendGrid
Author URI: http://sendgrid.com
License: A "Slug" license name e.g. GPL2
*/
//namespace sendgridPlugin;
require_once plugin_dir_path( __FILE__ ) . '/lib/SendGridSettings.php';
require_once plugin_dir_path( __FILE__ ) . '/lib/sendgrid-php/SendGrid_loader.php';

$sendgridSettings = new wp_SendGrid_Settings();

if (!function_exists('wp_set_current_user'))
{
  function wp_mail($to, $subject, $message, $headers = '', $attachments = array() )
  {
    $user = get_option('sendgrid_user');
    $password = get_option('sendgrid_pwd');
    $sendgrid = new SendGrid($user, $password);

    $sendgrid = new SendGrid($user, $password);
      $mail = new SendGrid\Mail();
      $mail->addTo($to)
           ->setFrom('me@gmail.com')
           ->setSubject($subject)
           ->setText($message);
      
      //setHtml('<strong>Hello World!</strong>');
    return $sendgrid->web->send($mail);
  }

  // add settings link
  function sendgrid_settings_link($links)
  {
    $settings_link = '<a href="options-general.php?page=sendgrid-settings.php">Settings</a>';
    array_unshift($links, $settings_link);

    return $links;
  }

  $plugin = plugin_basename(__FILE__);
  add_filter("plugin_action_links_$plugin", 'sendgrid_settings_link' );
}

///$mail = wp_mail('laurentiu.craciun@sendgrid.com', 'test plugin', 'testing wordpress plugin');
//var_dump($mail);