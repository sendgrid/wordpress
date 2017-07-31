<?php if ( $active_tab == 'general' ): ?>
  <?php if (
              // No status is set
              ! isset( $status ) or
              // There is no error status
              ( 'error' != $status and ! empty( $api_key ) ) or
              // There is an error, but the error was from sending with the test form
              ( 'error' == $status and isset( $error_type ) and 'sending' == $error_type )
           ) :
  ?>
    <form name="sendgrid_test" method="POST" action="<?php echo Sendgrid_Tools::get_form_action(); ?>">
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <td colspan="2">
              <h3><?php echo _e('SendGrid Test - Send a test email with these settings') ?></h3>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e("To: "); ?></th>
            <td>
              <input type="text" name="sendgrid_to" required="true" value="<?php echo isset( $success ) ? '' : isset( $to ) ? $to : '' ; ?>" size="20" class="regular-text">
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e("Subject: "); ?></th>
            <td>
              <input type="text" name="sendgrid_subj" required="true" value="<?php echo isset( $success ) ? '' : isset( $subject ) ? $subject : '' ; ?>" size="20" class="regular-text">
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e("Body: "); ?></th>
            <td>
              <textarea name="sendgrid_body" rows="5" class="large-text"><?php echo isset( $success ) ? '' : isset( $body ) ? $body : '' ; ?></textarea>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e("Headers: "); ?></th>
            <td>
              <textarea name="sendgrid_headers" rows="3" class="large-text"><?php echo isset( $success ) ? '' : isset( $headers ) ? $headers : ''; ?></textarea>
            </td>
          </tr>
        </table>
      </tbody>
      <input type="hidden" name="email_test" value="true"/>
      <input type="hidden" name="sgnonce" value="<?php echo wp_create_nonce('sgnonce'); ?>"/>
      <p class="submit">
        <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Send') ?>" />
      </p>
    </form>
  <?php endif; ?>
<?php endif; ?>