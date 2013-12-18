=== SendGrid ===
Contributors: team-rs
Donate link: http://sendgrid.com/
Tags: email, email reliability, email templates, sendgrid, smtp, transactional email, wp_mail,email infrastructure, email marketing, marketing email, deliverability, email deliverability, email delivery, email server, mail server, email integration, cloud email
Requires at least: 3.3
Tested up to: 3.8
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Email Delivery. Simplified.

== Description ==

SendGrid's cloud-based email infrastructure relieves businesses of the cost and complexity of maintaining custom email systems. SendGrid provides reliable delivery, scalability and real-time analytics along with flexible APIs that make custom integration a breeze.

The SendGrid plugin uses SMTP or API integration to send outgoing emails from your WordPress installation. It replaces the wp_mail function included with WordPress. 

First, you need to have PHP-curl extension enabled. To send emails through SMTP you need to install also the 'Swift Mailer' plugin. After installing 'Swift Mailer' plugin, you must have PHP-short_open_tag setting enabled in your php.ini file.

To have the SendGrid plugin running after you have activated it, go to the plugin's settings page and set the SendGrid credentials, and how your email will be sent - either through SMTP or API.

You can also set default values for the "Name", "Sending Address" and the "Reply Address", so that you don't need to set these headers every time you want to send an email from your application.

Emails are tracked and automatically tagged for statistics within the SendGrid Dashboard. You can also add general tags to every email sent, as well as particular tags based on selected emails defined by your requirements. 

There are a couple levels of integration between your WordPress installation and the SendGrid plugin:

* The simplest option is to Install it, Configure it, and the SendGrid plugin for WordPress will start sending your emails through SendGrid.
* We amended wp_mail() function so all email sends from wordpress should go through SendGrid. The wp_mail function is sending text emails as default, but you have an option of sending an email with HTML content.

How to use `wp_mail()` function:

We amended `wp_mail()` function so all email sends from wordpress should go through SendGrid.

You can send emails using the following function: `wp_mail($to, $subject, $message, $headers = '', $attachments = array())`

Where:

* `$to` - Array or comma-separated list of email addresses to send message.
* `$subject` - Email subject
* `$message` - Message contents
* `$headers` - Array or "\n" separated  list of additional headers. Optional.
* `$attachments` - Array or "\n"/"," separated list of files to attach. Optional.

The wp_mail function is sending text emails as default. If you want to send an email with HTML content you have to set the content type to 'text/html' running `add_filter('wp_mail_content_type', 'set_html_content_type');` function before to `wp_mail()` one.

After wp_mail function you need to run the `remove_filter('wp_mail_content_type', 'set_html_content_type');` to remove the 'text/html' filter to avoid conflicts --http://core.trac.wordpress.org/ticket/23578

Example about how to send an HTML email using different headers:

`$subject = 'test plugin';
$message = 'testing wordpress plugin';
$to = 'address1@sendgrid.com, Address2 <address2@sendgrid.com@>, address3@sendgrid.com';
or
$to = array('address1@sendgrid.com', 'Address2 <address2@sendgrid.com>', 'address3@sendgrid.com');
 
$headers = array();
$headers[] = 'From: Me Myself <me@example.net>';
$headers[] = 'Cc: address4@sendgrid.com';
$headers[] = 'Bcc: address5@sendgrid.com';
 
$attachments = array('/tmp/img1.jpg', '/tmp/img2.jpg');
 
add_filter('wp_mail_content_type', 'set_html_content_type');
$mail = wp_mail($to, $subject, $message, $headers, $attachments);
 
remove_filter('wp_mail_content_type', 'set_html_content_type');`

== Installation ==

Note: requires PHP version >= 5.3.0

To upload the SendGrid Plugin .ZIP file:

1. Upload the WordPress SendGrid Plugin to the /wp-contents/plugins/ folder.
2. Activate the plugin from the "Plugins" menu in WordPress.
3. Create a SendGrid account at <a href="http://sendgrid.com/partner/wordpress" target="_blank">http://sendgrid.com/partner/wordpress</a>  
4. Navigate to "Settings" -> "SendGrid Settings" and enter your SendGrid credentials

To auto install the SendGrid Plugin from the WordPress admin:

1. Navigate to "Plugins" -> "Add New"
2. Search for "SendGrid Plugin" and click "Install Now" for the "SendGrid Plugin" listing
3. Activate the plugin from the "Plugins" menu in WordPress, or from the plugin installation screen.
4. Create a SendGrid account at <a href="http://sendgrid.com/partner/wordpress" target="_blank">http://sendgrid.com/partner/wordpress</a>
5. Navigate to "Settings" -> "SendGrid Settings" and enter your SendGrid credentials

== Frequently asked questions ==

= What credentials do I need to add on settings page =

Create a SendGrid account at <a href="http://sendgrid.com/partner/wordpress" target="_blank">http://sendgrid.com/partner/wordpress</a> and use these credentials.

== Screenshots ==

1. Go to Admin Panel, section Plugins and activate the SendGrid plugin. If you want to send emails through SMTP you need to install also the 'Swift Mailer' plugin. 
2. After activation "Settings" link will appear. 
3. Go to settings page and provide your SendGrid credentials. On this page you can set also the default "Name", "Sending Address" and "Reply Address". 
4. If you provide valid credentials, a form which can be used to send test emails will appear. Here you can test the plugin sending some emails. 
5. Header provided in the send test email form. 
6. If you click in the right corner from the top of the page on the "Help" button, a popup window with more information will appear. 
7. Select the time interval for which you want to see SendGrid statistics and charts.

== Changelog ==

= 1.0 =
* Fixed issue: Add error message when PHP-curl extension is not enabled.
= 1.1 =
* Added SendGrid Statistics 
= 1.1.1 =
* Added default category on sending
= 1.1.2 =
* Fix display for october charts
= 1.1.3 =
* Fix missing argument warning message
= 1.2 =
* Added statistics for emails sent through wordpress plugin
= 1.2.1 =
* Fix errors: set_html_content_type error, WP_DEBUG enabled notice, Reply-To header is overwritten by default option
= 1.3 =
* Added support for Wordpress 3.8, fixed visual issues for Wordpress 3.7
= 1.3.1 = 
* Fixed reply-to to accept: "name <email@example.com>"

== Upgrade notice ==

= 1.1 =
* SendGrid Statistics can be used by selecting the time interval for which you want to see your statistics.
= 1.2 =
* Now you can switch between Sendgrid general statistics and Sendgrid wordpress statistics.
= 1.3 =
* Added support for Wordpress 3.8, fixed visual issues for Wordpress 3.7