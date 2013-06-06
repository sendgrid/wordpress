<div class="wrap">  
  <?php    echo "<h2>" . __( 'Sendgrid Options', 'sendgrid_trdom' ) . "</h2>"; ?>  

  <form name="sendgrid_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
    <?php    echo "<h4>" . __( 'Sendgrid credentials', 'sendgrid_trdom' ) . "</h4>"; ?>  
    <p><?php _e("User: " ); ?><input type="text" name="sendgrid_user" value="<?php echo $user; ?>" size="20"></p>  
    <p><?php _e("Password: " ); ?><input type="password" name="sendgrid_pwd" value="<?php echo $password; ?>" size="20"></p>  
    <p><?php _e("Send Mail with: " ); ?>
      <select name="sendgrid_api">
        <option value="api" id="api" <?php if($method == 'api') echo 'selected'; ?>>API</option>
        <option value="smtp" id="smtp" <?php if($method == 'smtp') echo 'selected'; ?>>SMTP</option>
      </select>
    </p> 
    <p><?php _e("Secure: " ); ?><input type="checkbox" name="sendgrid_secure" <?php if($secure) echo 'checked'; ?>></p>
    <hr />  
    <p class="submit">  
    <input type="submit" name="Submit" value="<?php _e('Update Settings', 'sendgrid_trdom' ) ?>" />  
    </p>  
  </form>  
</div>  
