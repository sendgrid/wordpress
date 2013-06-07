
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'css/sendgrid.css'; ?>" type="text/css">

<div class="wrap"> 
  <div class="box">
    <?php
    echo '<a href="http://sendgrid.com"><img src="' . plugins_url('/images/logo.png', __FILE__) . '"' .
    'width="100" alt="" /></a><h2 class="title">' . __('SendGrid Options', 'sendgrid_trdom') . '</h2>';
    ?> 
    <?php
    if ($status == 'updated') 
    {
      echo '<div id="message" class="' . $status . '"><p><strong>' . $message . '</strong></p></div>';
    }
    ?>
    <?php echo '<h4>' . __('SendGrid credentials', 'sendgrid_trdom') . "</h4>"; ?>  
    <form class="form-table" name="sendgrid_form" method="POST" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
      <p>
        <label><?php _e("Username: "); ?></label>
        <input class="pull-right" type="text" required="true" name="sendgrid_user" value="<?php echo $user; ?>" size="20">
      </p>  
      <p>
        <label><?php _e("Password: "); ?></label>
        <input class="pull-right" type="password" required="true" name="sendgrid_pwd" value="<?php echo $password; ?>" size="20">
      </p>  
      <p>
        <label><?php _e("Send Mail with: "); ?></label>
        <select name="sendgrid_api" class="pull-right">
          <option value="api" id="api" <?php if ($method == 'api') echo 'selected'; ?>>API</option>
          <option value="smtp" id="smtp" <?php if ($method == 'smtp') echo 'selected'; ?>>SMTP</option>
        </select>
      </p> 
      <p>
        <label><?php _e("Secure (use SSL): "); ?></label>
        <input type="checkbox" class="pull-right" name="sendgrid_secure" <?php if ($secure) echo 'checked'; ?>>
      </p>
      <?php echo '<h4>' . __('Mail settings', 'sendgrid_trdom') . "</h4>"; ?>  
      <p>
        <label><?php _e("From name:"); ?></label>
        <input class="pull-right" type="text" name="sendgrid_name" value="<?php echo $name; ?>" size="20">
      </p>  
      <p>
        <label><?php _e("From email:"); ?></label>
        <input class="pull-right" type="email" name="sendgrid_email" value="<?php echo $email; ?>" size="20">
      </p>  
      <p>
        <label><?php _e("Reply to email:"); ?></label>
        <input class="pull-right" type="email" name="sendgrid_reply_to" value="<?php echo $reply_to; ?>" size="20">
      </p>
      <p class="submit">  
        <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Update Settings', 'sendgrid_trdom') ?>" />  
      </p>  
    </form>  
  </div>
  <div class="box">
    <?php echo '<h2 class="title">' . __('SendGrid Test', 'sendgrid_trdom') . "</h2>"; ?>   
    <?php
    if ($status == 'send_failed' or $status == 'send_success') {
      echo '<div id="message" class="' . $status . '"><strong>' . $message . '</strong></div>';
    }
    ?>
    <?php echo '<h4>' . __('Send a test email with these settings', 'sendgrid_trdom') . "</h4>"; ?> 
    <form name="sendgrid_test" method="POST" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
      <p>
        <?php _e("To: "); ?>
        <input class="pull-right" type="email" name="sendgrid_to" required="true" value="<?php echo $success ? '' : $to; ?>" size="20">
      </p>  
      <p>
        <?php _e("Subject: "); ?>
        <input class="pull-right" type="text" name="sendgrid_subj" required="true" value="<?php echo $success ? '' : $subject; ?>" size="20">
      </p> 
      <p class="clearfix">
        <?php _e("Body: "); ?>
        <textarea class="pull-right" name="sendgrid_body" size="200"><?php echo $success ? '' : $body; ?></textarea>
      </p>
      <p class="clearfix">
        <?php _e("Headers: "); ?>
        <textarea class="pull-right" name="sendgrid_headers" size="200"><?php echo $success ? '' : $headers; ?></textarea>
      </p>
      <input type="hidden" name="email_test" value="true"/>
      <p class="submit">  
        <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Send', 'sendgrid_trdom') ?>" />
      </p> 
    </form>  
  </div>
</div>  
