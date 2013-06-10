
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'css/sendgrid.css'; ?>" type="text/css">

<div class="wrap"> 
  <div class="stuffbox">
    <?php
    echo '<a href="http://sendgrid.com"><img src="' . plugins_url('/images/logo.png', __FILE__) . '"' .
    'width="100" alt="" /></a><h2 class="title">' . __('SendGrid Options', 'sendgrid_trdom') . '</h2>';
    ?> 
    
    <?php
    if ($status == 'save_error' or $status == 'save_success') 
    {
      echo '<div id="message" class="' . $status . '"><strong>' . $message . '</strong></div>';
    }
    ?>
    <?php echo '<h3>' . __('SendGrid credentials', 'sendgrid_trdom') . "</h3>"; ?>
    <form class="form-table" name="sendgrid_form" method="POST" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
      <table class="form-table">
        <tr class="top">
          <th scope="row"><?php _e("Username: "); ?></th>
          <td>
            <div class="inside">
              <input type="text" required="true" name="sendgrid_user" value="<?php echo $user; ?>" size="20">
            </div>
          </td>
        </tr>
        <tr class="top">
          <th scope="row"><?php _e("Password: "); ?></th>
          <td>
            <div class="inside">
              <input type="password" required="true" name="sendgrid_pwd" value="<?php echo $password; ?>" size="20">
            </div>
          </td>
        </tr>
        <tr class="top">
          <th scope="row"><?php _e("Send Mail with: "); ?></th>
          <td>
            <div class="inside">
              <select name="sendgrid_api">
                <option value="api" id="api" <?php if ($method == 'api') echo 'selected'; ?>>API</option>
                <option value="smtp" id="smtp" <?php if ($method == 'smtp') echo 'selected'; ?>>SMTP</option>
              </select>
            </div>
          </td>
        </tr>
      </table>
      <br />
      <?php echo '<h3>' . __('Mail settings', 'sendgrid_trdom') . "</h3>"; ?>
      <table class="form-table">
        <tr class="top">
          <th scope="row"><?php _e("From name: "); ?></th>
          <td>
            <div class="inside">
              Name the recipients will see in their email clients:
              <br />
              <input type="text" name="sendgrid_name" value="<?php echo $name; ?>" size="20">
            </div>
          </td>
        </tr>
        <tr class="top">
          <th scope="row"><?php _e("From email: "); ?></th>
          <td>
            <div class="inside">
              This address will be used as the sender of the outgoing emails:
              <br />
              <input type="email" name="sendgrid_email" value="<?php echo $email; ?>" size="20">
            </div>
          </td>
        </tr>
        <tr class="top">
          <th scope="row"><?php _e("Reply-to email: "); ?></th>
          <td>
            <div class="inside">
              This address will be used as the recipient where replies from the users will be sent to:
              <br />
              <input type="email" name="sendgrid_reply_to" value="<?php echo $reply_to; ?>" size="20">
              <br />
              <span><small><em>Leave blank to use the FROM Email.</em></small></span>
            </div>
          </td>
        </tr>
      </table>
      <div class="submit-button">
        <p class="submit">
          <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Update Settings', 'sendgrid_trdom') ?>" />
        </p>
      </div>
    </form>  
  </div>
  <br />
  <?php if ($valid_credentials): ?>
    <div class="stuffbox">
      <?php echo '<h2 class="title">' . __('SendGrid Test', 'sendgrid_trdom') . "</h2>"; ?>
      <?php
      if ($status == 'send_failed' or $status == 'send_success') {
        echo '<div id="message" class="' . $status . '"><strong>' . $message . '</strong></div>';
      }
      ?>
      <?php echo '<h3>' . __('Send a test email with these settings', 'sendgrid_trdom') . "</h3>"; ?>
      <form name="sendgrid_test" method="POST" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <table class="form-table">
          <tr class="top">
            <th scope="row"><?php _e("To: "); ?></th>
            <td>
              <div class="inside">
                <input type="email" name="sendgrid_to" required="true" value="<?php echo $success ? '' : $to; ?>" size="20">
              </div>
            </td>
          </tr>
          <tr class="top">
            <th scope="row"><?php _e("Subject: "); ?></th>
            <td>
              <div class="inside">
                <input type="text" name="sendgrid_subj" required="true" value="<?php echo $success ? '' : $subject; ?>" size="20">
              </div>
            </td>
          </tr>
          <tr class="top">
            <th scope="row"><?php _e("Body: "); ?></th>
            <td>
              <div class="inside">
                <textarea name="sendgrid_body" rows="5"><?php echo $success ? '' : $body; ?></textarea>
              </div>
            </td>
          </tr>
          <tr class="top">
            <th scope="row"><?php _e("Headers: "); ?></th>
            <td>
              <div class="inside">
                <textarea name="sendgrid_headers" rows="3"><?php echo $success ? '' : $headers; ?></textarea>
              </div>
            </td>
          </tr>
        </table>
        <input type="hidden" name="email_test" value="true"/>
        <div class="submit-button">
          <p class="submit">
            <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Send', 'sendgrid_trdom') ?>" />
          </p>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>  
