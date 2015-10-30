<div class="wrap">
  <a href="http://sendgrid.com" target="_blank">
    <img src="<?php echo plugins_url('/images/logo.png', __FILE__) ?>" width="100" alt="" />
  </a>
  <h2><?php echo _e('SendGrid Options') ?></h2>
  <?php if ( isset( $status ) and ( 'updated' == $status or 'error' == $status ) ): ?>
    <div id="message" class="<?php echo $status ?>">
      <p>
        <strong><?php echo $message ?></strong>
      </p>
    </div>
  <?php endif; ?>
  <h3><?php echo _e('SendGrid credentials') ?></h3>
  <form class="form-table" name="sendgrid_form" method="POST" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI'] ); ?>">
    <table class="form-table">
      <tbody>
      <tr valign="top">
          <th scope="row"><?php _e("Authentication method: "); ?></th>
          <td>
            <select name="auth_method" id="auth_method" <?php disabled( defined('SENDGRID_AUTH_METHOD') or ( $is_global_api_key or $are_global_credentials ) ); ?>>
              <option value="apikey" id="apikey" <?php echo ( 'apikey' == $auth_method ) ? 'selected' : '' ?>><?php _e('Api Key') ?></option>
              <option value="username" id="username" <?php echo ( 'username' == $auth_method ) ? 'selected' : '' ?>><?php _e('Username&Password') ?></option>
            </select>
          </td>
        </tr>
        <tr valign="top" class="apikey">
          <th scope="row"><?php _e("API key: "); ?></th>
          <td>
            <input type="password" name="sendgrid_api_key" value="<?php echo ( $is_global_api_key ? "************" : $api_key );  ?>" size="50" <?php disabled( $is_global_api_key ); ?>>
          </td>
        </tr>
        <tr valign="top" class="creds">
          <th scope="row"><?php _e("Username: "); ?></th>
          <td>
            <input type="text" name="sendgrid_user" value="<?php echo $user; ?>" size="20" class="regular-text" <?php disabled( $are_global_credentials ); ?>>
          </td>
        </tr>
        <tr valign="top" class="creds">
          <th scope="row"><?php _e("Password: "); ?></th>
          <td>
            <input type="password" name="sendgrid_pwd" value="<?php echo ( $are_global_credentials ? "******" : $password );  ?>" size="20" class="regular-text" <?php disabled( $are_global_credentials ); ?>>
          </td>
        </tr>
        <?php if ( $are_global_credentials or $is_global_api_key): ?>
        <tr valign="top">
          <td colspan="2">
            <p>
              <?php _e('Your credentials are already configured in the config file. If you want to manage them from the interface, remove them from config.'); ?>
            </p>
            <p>
              <?php _e('If you have both credential types set, by default the Api Key credential is used.'); ?>
            </p>
          </td>
        </tr>
        <?php endif; ?>
        <tr valign="top">
          <th scope="row"><?php _e("Send Mail with: "); ?></th>
          <td>
            <select name="sendgrid_api" id="sendgrid_api" <?php disabled( defined('SENDGRID_SEND_METHOD') ); ?>>
              <option value="api" id="api" <?php echo ( 'api' == $method ) ? 'selected' : '' ?>><?php _e('API') ?></option>
              <option value="smtp" id="smtp" <?php echo ( 'smtp' == $method ) ? 'selected' : '' ?>><?php _e('SMTP') ?></option>
            </select>
          </td>
        </tr>
        <tr valign="top" class="port">
          <th scope="row"><?php _e("PORT: "); ?></th>
          <td>
            <select name="sendgrid_port" id="sendgrid_port" <?php disabled( $has_port ); ?>>
              <option value="<?php echo SGSmtp::TLS ?>" id="tls" <?php echo ( ( SGSmtp::TLS == $port ) or (! $port ) ) ? 'selected' : '' ?>><?php echo SGSmtp::TLS ?></option>
              <option value="<?php echo SGSmtp::TLS_ALTERNATIVE ?>" id="tls_alt" <?php echo ( SGSmtp::TLS_ALTERNATIVE == $port ) ? 'selected' : '' ?>><?php echo SGSmtp::TLS_ALTERNATIVE ?></option>
              <option value="<?php echo SGSmtp::SSL ?>" id="ssl" <?php echo ( SGSmtp::SSL == $port ) ? 'selected' : '' ?>><?php echo SGSmtp::SSL ?></option>
            </select>
          </td>
        </tr>
      </tbody>
    </table>
    <br />
    <h3><?php _e('Mail settings') ?></h3>
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row"><?php _e("Name: "); ?></th>
          <td>
            <input type="text" name="sendgrid_name" value="<?php echo $name; ?>" size="20" class="regular-text" <?php disabled( defined('SENDGRID_FROM_NAME') ); ?>>
            <p class="description"><?php _e('Name as it will appear in recipient clients.') ?></p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e("Sending Address: "); ?></th>
          <td>
            <input type="email" name="sendgrid_email" value="<?php echo $email; ?>" size="20" class="regular-text" <?php disabled( defined('SENDGRID_FROM_EMAIL') ); ?>>
            <p class="description"><?php _e('Email address from which the message will be sent.') ?></p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e("Reply Address: "); ?></th>
          <td>
            <input type="email" name="sendgrid_reply_to" value="<?php echo $reply_to; ?>" size="20" class="regular-text" <?php disabled( defined('SENDGRID_REPLY_TO') ); ?>>
            <span><small><em><?php _e('Leave blank to use Sending Address.') ?></em></small></span>
            <p class="description"><?php _e('Email address where replies will be returned.') ?></p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><?php _e("Categories: "); ?></th>
          <td>
            <input type="text" name="sendgrid_categories" value="<?php echo $categories; ?>" size="20" class="regular-text" <?php disabled( defined('SENDGRID_CATEGORIES') ); ?>>
            <span><small><em><?php _e('Leave blank to send without categories.') ?></em></small></span>
            <p class="description"><?php _e('Associates the category of the email this should be logged as. <br />
            Categories must be separated by commas (Example: category1, category2).') ?></p>
          </td>
        </tr>
        <tr valign="top">
          <td colspan="2">
            <p>
              <?php _e('Disabled fields in this form means that they are already configured in the config file.'); ?>
            </p>
          </td>
        </tr>
      </tbody>
    </table>
    <p class="submit">
      <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Update Settings') ?>" />
    </p>
  </form>  
  <br />
  <?php if ( !isset($status) or 'updated' == $status ): ?>
    <h2><?php _e('SendGrid Test') ?></h2>
    <h3><?php _e('Send a test email with these settings') ?></h3>
    <form name="sendgrid_test" method="POST" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row"><?php _e("To: "); ?></th>
            <td>
              <input type="email" name="sendgrid_to" required="true" value="<?php echo isset($success) ? '' : isset($to) ? $to : '' ; ?>" size="20" class="regular-text">
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e("Subject: "); ?></th>
            <td>
              <input type="text" name="sendgrid_subj" required="true" value="<?php echo isset($success) ? '' : isset($subject) ? $subject : '' ; ?>" size="20" class="regular-text">
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e("Body: "); ?></th>
            <td>
              <textarea name="sendgrid_body" rows="5" class="large-text"><?php echo isset($success) ? '' : isset($body) ? $body : '' ; ?></textarea>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e("Headers: "); ?></th>
            <td>
              <textarea name="sendgrid_headers" rows="3" class="large-text"><?php echo isset($success) ? '' : isset($headers) ? $headers : ''; ?></textarea>
            </td>
          </tr>
        </table>
      </tbody>
      <input type="hidden" name="email_test" value="true"/>
      <p class="submit">
        <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Send') ?>" />
      </p>
    </form>
  <?php endif; ?>
</div>  
