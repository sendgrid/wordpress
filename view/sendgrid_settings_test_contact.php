<?php if ( $active_tab == 'marketing' ): ?>
  <?php if (
            // Both api keys are set and the contact list id is set
            ( $is_mc_api_key_valid and $is_api_key_valid and $contact_list_id_is_valid ) or
            // There was an error sending the subscription email for contact upload
            ( 'error' == $status and isset( $error_type ) and 'upload' == $error_type )
           ) :
  ?>
    <form class="form-table" name="sendgrid_form" method="POST" action="<?php echo Sendgrid_Tools::get_form_action(); ?>">
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <td colspan="2">
              <h2><?php _e('SendGrid Test - Subscription') ?></h2>
            </td>
          </tr>
          <tr valign="top" class="mc_test_email">
            <th scope="row"><?php _e("Email: "); ?></th>
            <td>
              <input type="text" id="mc_test_email" name="sendgrid_test_email" value="" size="50">
              <p class="description"><?php _e('An email will be send to this address to confirm the subscription as it does for users that subscribe using the widget.') ?></p>
            </td>
          </tr>
          <input type="hidden" name="contact_upload_test" value="true"/>
          <input type="hidden" name="sgnonce" value="<?php echo wp_create_nonce('sgnonce'); ?>"/>
          <tr valign="top" class="mc_test_email">
            <th scope="row" colspan="2">
              <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Test') ?>" />
            </th>
          </tr>
        </tbody>
      </table>
    </form>
  <?php endif; ?>
<?php endif; ?>