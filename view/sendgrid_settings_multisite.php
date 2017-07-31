<?php if ( $active_tab == 'multisite' ): ?>
<?php
    // Pagination setup
    $limit  = 50;
    $offset = 0;

    if ( isset( $_GET['offset'] ) ) {
        $offset = intval( $_GET['offset'] );
    }

    if ( isset( $_GET['limit'] ) ) {
        $limit = intval( $_GET['limit'] );
    }

    $pagination = Sendgrid_Tools::get_multisite_pagination( $offset, $limit );
    $sites = get_sites( array( 'offset' => $offset, 'number' => $limit ) );
?>

<p class="description">
    <?php
        echo translate( 'On this page you can grant each subsite the ability to manage SendGrid settings.' ) . '</br>';
        echo translate( 'If the checkbox is unchecked then that site will not see the SendGrid settings page and will use the settings set on the network.' ) . '</br>';
        echo '<strong>' . translate( 'Warning!' ) . '</strong>';
        echo translate( ' When you activate the management for a subsite, that site will not be able to send emails until the subsite admin updates his SendGrid settings.' );
    ?>
</p>

<p class="sendgrid-multisite-pagination">
    <?php
       echo $pagination['previous_button'] . ' ' . $pagination['next_button'];
    ?>
</p>

<form method="POST" action="<?php echo Sendgrid_Tools::get_form_action(); ?>">
<table class="widefat fixed" id="subsites-table-sg" cellspacing="0">
    <thead>
        <tr valign="top">
            <th scope="col" class="manage-column column-columnname num" colspan="5">
                <?php
                    echo translate( 'Page ' ) . $pagination['current_page'] . translate( ' of ' ) . $pagination['total_pages'];
                ?>
            </th>
        </tr>
        <tr valign="top">
            <th scope="col" class="manage-column column-columnname num"> <?php _e( 'ID' ); ?></th>
            <th scope="col" class="manage-column column-columnname"> <?php _e( 'Name' ); ?></th>
            <th scope="col" class="manage-column column-columnname"> <?php _e( 'Public' ); ?></th>
            <th scope="col" class="manage-column column-columnname"> <?php _e( 'Site URL' ); ?></th>
            <th scope="col" class="manage-column"><input style="margin:0 0 0 0px;" type="checkbox" id="sg-check-all-sites"/> <?php _e( 'Self-Managed?' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ( $sites as $index => $site ): ?>
            <?php if ( ! is_main_site( $site->blog_id ) ): ?>
                <?php $site_info = get_blog_details ($site->blog_id ); ?>
                    <tr <?php echo ( $index % 2 == 1 ) ? 'class="alternate"' : ''?>>
                        <td class="column-columnname num" scope="row"><?php echo $site_info->blog_id; ?></td>
                        <td class="column-columnname" scope="row"><?php echo $site_info->blogname; ?></td>
                        <td class="column-columnname" scope="row"><?php echo $site_info->public ? "true" : "false"; ?></td>
                        <td class="column-columnname" scope="row">
                            <a href="<?php echo $site_info->siteurl; ?>"><?php echo $site_info->siteurl; ?><a>
                        </td>
                        <td class="column-columnname" scope="row" aligh="center">
                            <input type="checkbox" id="check-can-manage-sg" name="checked_sites[<?php echo $site_info->blog_id ?>]"
                                <?php echo ( get_blog_option( $site_info->blog_id, 'sendgrid_can_manage_subsite', 0 ) ? "checked" : "" ) ?> />
                        </td>
                    </tr>
                <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>
<p class="sendgrid-multisite-pagination">
    <?php
        echo $pagination['previous_button'] . ' ' . $pagination['next_button'];
    ?>
</p>
<p class="sendgrid-multisite-submit">
    <input type="submit" id="doaction" class="button button-primary" value="<?php _e( 'Save Settings' ); ?>">
</p>
<input type="hidden" name="subsite_settings" value="true"/>
<input type="hidden" name="sgnonce" value="<?php echo wp_create_nonce('sgnonce'); ?>"/>
</form>
<?php endif; ?>