<?php
/*
Plugin Name: SendGrid
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: 1.0
Author: SendGrid
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/
//namespace sendgridPlugin;
require_once plugin_dir_path( __FILE__ ) . '/lib/SendGridSettings.php';
require_once plugin_dir_path( __FILE__ ) . '/lib/sendgrid-php/SendGrid_loader.php';

$sendgridSettings = new wp_SendGrid_Settings();

//function wp_mail($to, $subject, $message, $headers = '', $attachments = array() )
//{
//  $user = get_option('sendgrid_user');
//  $password = get_option('sendgrid_pwd');
//  $sendgrid = new SendGrid($user, $password);
//
//  $sendgrid = new SendGrid($user, $password);
//    $mail = new SendGrid\Mail();
//    $mail->
//    addTo($to)->
//    setFrom('me@gmail.com')->
//    setSubject($subject)->
//    setText($message);
//    //setHtml('<strong>Hello World!</strong>');
//  return $sendgrid->web->send($mail);
//}
//
//$mail = wp_mail('slcraciun@gmail.com', 'test plugin', 'testing wordpress plugin');
//var_dump($mail);