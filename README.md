=== SendGrid Wordpress ===
Contributors: no other constributors
Donate link: http://example.com/
Tags: email, email reliability, email templates, sendgrid, smtp, transactional email, wp_mail,email infrastructure, email marketing, marketing email, deliverability, email deliverability, email delivery, email server, mail server, email integration, cloud email
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.htm

Email Delivery. Simplified

== Description ==

SendGrid's cloud-based email infrastructure relieves businesses of the cost and complexity of maintaining custom email systems. SendGrid provides reliable delivery, scalability and real-time analytics along with flexible APIs that make custom integration a breeze.
The SendGrid plugin uses SMTP or API integration to send outgoing emails from your WordPress installation. It replaces the wp_mail function included with WordPress. 

To have the SendGrid plugin running after you have activated it, go to the plugin’s settings page and set the SendGrid credentials, and the way your email will be sent through SMTP or API.

You can also set default values for the “Name”, “Sending Address” and the “Reply Address”, so that you don’t need to set these headers every time you want to send and email from your application.

Emails are tracked and automatically tagged for statistics within the SendGrid Dashboard. You can also add general tags to every email sent, as well as particular tags based on selected emails defined by your requirements. 

There are a couple levels of integration between your WordPress installation and the SendGrid plugin:

* The simplest option is to Install it, configure it, and the SendGrid plugin for WordPress will start sending your emails through SendGrid.
* We amended wp_mail() function so all email sends from wordpress should go through SendGrid. The wp_mail function is sending text emails as default, but you have an option of sending an email with HTML content.


== Installation ==

To upload the SendGrid Plugin .ZIP file:
1. Upload the WordPress SendGrid Plugin to the /wp-contents/plugins/ folder.
2. Activate the plugin from the "Plugins" menu in WordPress.
3. Navigate to "Settings" → "SendGrid Settings" and enter your SendGrid credentials
To auto install the SendGrid Plugin from the WordPress admin:
1. Navigate to "Plugins" → "Add New"
2. Search for "SendGrid Plugin" and click "Install Now" for the "SendGrid Plugin” listing
3. Activate the plugin from the "Plugins" menu in WordPress, or from the plugin installation screen.
4. Navigate to "Settings" → "SendGrid Settings" and enter your SendGrid credentials


== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.

== Screenshots ==

1. Go to Admin Panel, section Plugins and activate the SendGrid plugin. If you want to send emails through SMTP you need to install also the 'Swift Mailer' plugin. screenshot-1.png.
2. After activation "Settings" link will appear: screenshot-2.png.
3. Go to settings page and provide your SendGrid credentials. On this page you can set also the default "Name", "Sending Address" and "Reply Address" : screenshot-3.png.
4. If you provide valid credentials, a form which can be used to send test emails will appear. Here you can test the plugin sending some emails. screenshot-4.png.
5. Header provided in the send test email form should look like this: screenshot-5.png
6. If you click in the right corner from the top of the page on the "Help" button, a popup window. screenshot-6.png

== Changelog ==

= 1.0 =
* This is the first version.

== Upgrade Notice ==

= 1.0 =
This is the first version.
