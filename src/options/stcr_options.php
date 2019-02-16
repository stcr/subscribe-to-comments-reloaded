<?php
// Options

// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
    header( 'Location: /' );
    exit;
}

$options = array(
    "show_subscription_box"        => "yesno",
    "safely_uninstall"             => "yesno",
    "purge_days"                   => "integer",
    "date_format"                  => "text",
    "stcr_position"                => "yesno",
    "enable_double_check"          => "yesno",
    "notify_authors"               => "yesno",
    "enable_html_emails"           => "yesno",
    "process_trackbacks"           => "yesno",
    "enable_admin_messages"        => "yesno",
    "admin_subscribe"              => "yesno",
    "admin_bcc"                    => "yesno",
    "enable_font_awesome"          => "yesno",
    "delete_options_subscriptions" => "yesno"
);

if ( array_key_exists( "generate_key", $_POST ) ) {
    $unique_key = $wp_subscribe_reloaded->stcr->utils->generate_key();
    $wp_subscribe_reloaded->stcr->utils->stcr_update_menu_options( 'unique_key', $unique_key, 'text' );

    echo '<div class="updated"><p>';
    echo __( 'Your settings have been successfully updated.', 'subscribe-reloaded' );
    echo "</p></div>";

}
if ( array_key_exists( "reset_all_options", $_POST ) ) {
    $delete_subscriptions_selection = $_POST['options']['delete_options_subscriptions'];
    $deletion_result = $wp_subscribe_reloaded->stcr->utils->delete_all_settings( $delete_subscriptions_selection );

    if( $deletion_result )
    {
        // Restore settings
        $wp_subscribe_reloaded->stcr->utils->create_options();
    }
}
elseif( isset( $_POST['options'] ) ) { // Update options

    $faulty_fields = array();

    foreach ( $_POST['options'] as $option => $value )
    {
//        echo $option . '<br>';

        if ( ! $wp_subscribe_reloaded->stcr->utils->stcr_update_menu_options( $option, $value, $options[$option] ) )
        {
            array_push( $faulty_fields, $option );
        }
    }

    // Display an alert in the admin interface if something went wrong
    echo '<div class="updated"><p>';
    if ( sizeof( $faulty_fields ) == 0 ) {
        _e( 'Your settings have been successfully updated.', 'subscribe-reloaded' );
    } else {
        _e( 'There was an error updating the options.', 'subscribe-reloaded' );
        // echo ' <strong>' . substr( $faulty_fields, 0, - 2 ) . '</strong>';
    }
    echo "</p></div>";
}
wp_print_scripts( 'quicktags' );

?>
    <link href="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/webui-popover/dist/jquery.webui-popover.min.css" rel="stylesheet"/>

    <div class="container-fluid">
        <div class="mt-3"></div>
        <div class="row">
            <div class="col-sm-9">
                <form action="" method="post">

                    <div class="form-group row" style="margin-bottom: 0;">
                        <label for="show_subscription_box" class="col-sm-3 col-form-label text-right"><?php _e( 'Show StCR checkbox / dropdown', 'subscribe-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[show_subscription_box]"
                                       value="yes" id="show_subscription_box-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'show_subscription_box' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="show_subscription_box-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[show_subscription_box]" value="no" id="show_subscription_box-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'show_subscription_box' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="show_subscription_box-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "This option will disable the StCR checkbox or dropdown in your comment form. You should leave it to Yes always.", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "This option will disable the StCR checkbox or dropdown in your comment form. You should leave it to Yes always.", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="safely_uninstall" class="col-sm-3 col-form-label text-right"><?php _e( 'Safely Uninstall', 'subscribe-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[safely_uninstall]"
                                       value="yes" id="safely_uninstall-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'safely_uninstall' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="safely_uninstall-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[safely_uninstall]" value="no" id="safely_uninstall-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'safely_uninstall' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="safely_uninstall-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "This option will allow you to delete the plugin with WordPress without loosing your subscribers. Any database table and plugin options are wipeout.", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "This option will allow you to delete the plugin with WordPress without loosing your subscribers. Any database table and plugin options are wipeout.", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="purge_days" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Autopurge requests', 'subscribe-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="number" name="options[purge_days]" id="purge_days"
                                   class="form-control form-control-input-3"
                                   value="<?php echo $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'purge_days' ); ?>" size="20">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "Delete pending subscriptions (not confirmed) after X days. Zero disables this feature.", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "Delete pending subscriptions (not confirmed) after X days. Zero disables this feature.", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="date_format" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Date Format', 'subscribe-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[date_format]" id="date_format"
                                   class="form-control form-control-input-3"
                                   value="<?php echo $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'date_format' ); ?>" size="20">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "Date format that will be display on the management page. Use <a href='https://secure.php.net/manual/en/function.date.php#refsect1-function.date-parameters' target='_blank'>PHP Date Format</a>", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "Date format that will be display on the management page. Use <a href='https://secure.php.net/manual/en/function.date.php#refsect1-function.date-parameters' target='_blank'>PHP Date Format</a>", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="stcr_position" class="col-sm-3 col-form-label text-right"><?php _e( 'StCR Position', 'subscribe-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[stcr_position]"
                                       value="yes" id="stcr_position-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'stcr_position' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="stcr_position-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[stcr_position]" value="no" id="stcr_position-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'stcr_position' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="stcr_position-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "If this option is enable the subscription box will be above the submit button in your comment form. Use this when your theme is outdated and using the incorrect WordPress Hooks and the checkbox is not displayed.", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "If this option is enable the subscription box will be above the submit button in your comment form. Use this when your theme is outdated and using the incorrect WordPress Hooks and the checkbox is not displayed.", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="enable_double_check" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Enable double check', 'subscribe-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[enable_double_check]"
                                       value="yes" id="enable_double_check-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_double_check' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="enable_double_check-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[enable_double_check]" value="no" id="enable_double_check-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_double_check' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="enable_double_check-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "Send a notification email to confirm the subscription (to avoid addresses misuse).", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "Send a notification email to confirm the subscription (to avoid addresses misuse).", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="notify_authors" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Subscribe authors', 'subscribe-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[notify_authors]"
                                       value="yes" id="notify_authors-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'notify_authors' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="notify_authors-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[notify_authors]" value="no" id="notify_authors-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'notify_authors' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="notify_authors-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "Automatically subscribe authors to their own articles (not retroactive).", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "Automatically subscribe authors to their own articles (not retroactive).", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="enable_html_emails" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Enable HTML emails', 'subscribe-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[enable_html_emails]"
                                       value="yes" id="enable_html_emails-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_html_emails' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="enable_html_emails-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[enable_html_emails]" value="no" id="enable_html_emails-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_html_emails' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="enable_html_emails-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "If enabled, will send email messages with content-type = text/html instead of text/plain", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "If enabled, will send email messages with content-type = text/html instead of text/plain", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="process_trackbacks" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Process trackbacks', 'subscribe-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[process_trackbacks]"
                                       value="yes" id="process_trackbacks-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'process_trackbacks' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="process_trackbacks-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[process_trackbacks]" value="no" id="process_trackbacks-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'process_trackbacks' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="process_trackbacks-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "Notify users when a new trackback or pingback is added to the discussion.", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "Notify users when a new trackback or pingback is added to the discussion.", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="enable_admin_messages" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Track all subscriptions', 'subscribe-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[enable_admin_messages]"
                                       value="yes" id="enable_admin_messages-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_admin_messages' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="enable_admin_messages-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[enable_admin_messages]" value="no" id="enable_admin_messages-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_admin_messages' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="enable_admin_messages-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "Notify the administrator when users subscribe without commenting.", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "Notify the administrator when users subscribe without commenting.", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="admin_subscribe" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Let Admin Subscribe', 'subscribe-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[admin_subscribe]"
                                       value="yes" id="admin_subscribe-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'admin_subscribe' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="admin_subscribe-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[admin_subscribe]" value="no" id="admin_subscribe-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'admin_subscribe' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="admin_subscribe-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "Let the administrator subscribe to comments when logged in.", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "Let the administrator subscribe to comments when logged in.", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row" style="margin-bottom: 0;">
                        <label for="admin_bcc" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'BCC admin on Notifications', 'subscribe-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[admin_bcc]"
                                       value="yes" id="admin_bcc-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'admin_bcc' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="admin_bcc-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[admin_bcc]" value="no" id="admin_bcc-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'admin_bcc' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="admin_bcc-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "Send a copy of all Notifications to the administrator.", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "Send a copy of all Notifications to the administrator.", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="enable_font_awesome" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Enable Font Awesome', 'subscribe-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[enable_font_awesome]"
                                       value="yes" id="enable_font_awesome-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_font_awesome' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="enable_font_awesome-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[enable_font_awesome]" value="no" id="enable_font_awesome-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_font_awesome' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="enable_font_awesome-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "Let you control the inclusion of the Font Awesome into your site. Disable if your theme already add this into your site.", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "Let you control the inclusion of the Font Awesome into your site. Disable if your theme already add this into your site.", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="unique_key" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'StCR Unique Key', 'subscribe-reloaded' ) ?></label>
                        <div class="col-sm-7">

                            <?php
                            if ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'unique_key' ) == "" ) {

                                echo "<div class=\"alert alert-danger\" role=\"alert\" style='font-size: 0.85rem;'>";
                                echo '<strong>' . __( "This Unique Key is not set, please click the following button to ", 'subscribe-reloaded' ) . '</strong>';
                                echo "<input type='submit' value='" . __( 'Generate' ) ."' class='btn btn-secondary subscribe-form-button' name='generate_key' >";
                                echo "</div>";
                            }
                            else {
                            ?>
                                <input type="text" name="options[unique_key]" id="unique_key"
                                       class="form-control form-control-input-6"
                                       value="<?php echo $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'unique_key' ); ?>" size="20">

                                <input type="submit" value="<?php _e( 'Generate New Key' ) ?>" class="btn btn-secondary subscribe-form-button" name="generate_key" >
                            <?php } ?>


                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "This Unique Key will be use to send the notification to your subscribers with more security.", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "This Unique Key will be use to send the notification to your subscribers with more security.", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Reset All Options', 'subscribe-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">

                            <div class="alert alert-danger" role="alert">
                                <strong>Danger!</strong>
                                <p>
                                    <?php _e( 'This will reset all the options and messages of the plugin. Please proceed with caution.', 'subscribe-reloaded' ); ?>
                                </p>

                                <p>
                                    <?php _e( '<strong>Yes</strong> = Delete Options including subscriptions.', 'subscribe-reloaded' ) ?><br/>
                                    <?php _e( '<strong>No</strong>  = Only delete the StCR Options.', 'subscribe-reloaded' ) ?>
                                </p>


                                <div class="switch">
                                    <input type="radio" class="switch-input" name="options[delete_options_subscriptions]"
                                           value="yes" id="delete_options_subscriptions-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'delete_options_subscriptions' ) == 'yes' ) ? ' checked' : ''; ?> />
                                    <label for="delete_options_subscriptions-yes" class="switch-label switch-label-off">
                                        <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                    </label>
                                    <input type="radio" class="switch-input" name="options[delete_options_subscriptions]" value="no" id="delete_options_subscriptions-no"
                                        <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'delete_options_subscriptions' ) == 'no' ) ? '  checked' : ''; ?> />
                                    <label for="delete_options_subscriptions-no" class="switch-label switch-label-on">
                                        <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                    </label>
                                    <span class="switch-selection"></span>
                                </div>

                                <input type="submit" value="<?php _e( 'Reset All Options' ) ?>" class="btn btn-danger subscribe-form-button reset_all_options" name="reset_all_options" >

                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-1">
                            <button type="submit" class="btn btn-primary subscribe-form-button" name="Submit">
                                <?php _e( 'Save Changes', 'subscribe-reloaded' ) ?>
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            <div class="col-md-3">
                <div class="card card-font-size">
                    <div class="card-body">
                        <div class="text-center">
                            <a href="http://subscribe-reloaded.com/" target="_blank"><img src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/images/stcr-logo-150.png"
                                                                                          alt="Support Subscribe to Comments Reloaded" width="100" height="84">
                            </a>
                        </div>
                        <div class="mt-4">
                            <p>Thank you for Supporting StCR, You can Support the plugin by giving a
                                <a href="http://subscribe-reloaded.com/active-support-donation/"  rel="external" target="_blank">
                                    <i class="fab fa-paypal"></i> Donation</a></p>
                            <p>Please rate it
                                <a href="https://wordpress.org/support/plugin/subscribe-to-comments-reloaded/reviews/#new-post" target="_blank"><img src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/images/rate.png"
                                                                                                                                                     alt="Rate Subscribe to Comments Reloaded" style="vertical-align: sub;" />
                                </a>
                            </p>
                            <p><i class="fas fa-bug"></i> Having issues? Please <a href="https://github.com/stcr/subscribe-to-comments-reloaded/issues/new" target="_blank">create a ticket</a>

                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script type="text/javascript" src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/webui-popover/dist/jquery.webui-popover.min.js"></script>
<?php
//global $wp_subscribe_reloaded;
// Tell WP that we are going to use a resource.
$wp_subscribe_reloaded->stcr->utils->register_script_to_wp( "stcr-subs-options", "subs_options.js", "includes/js/admin");
// Includes the Panel JS resource file as well as the JS text domain translations.
//$wp_subscribe_reloaded->stcr->stcr_i18n->stcr_localize_script( "stcr-subs-options", "stcr_i18n", $wp_subscribe_reloaded->stcr->stcr_i18n->get_js_subs_translation() );
// Enqueue the JS File
$wp_subscribe_reloaded->stcr->utils->enqueue_script_to_wp( "stcr-subs-options" );

?>