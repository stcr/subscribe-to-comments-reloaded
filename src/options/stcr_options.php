<?php
// Options

// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
    header( 'Location: /' );
    exit;
}

$options = array(
	'show_subscription_box'        => 'yesno',
	'safely_uninstall'             => 'yesno',
	'purge_days'                   => 'integer',
	'date_format'                  => 'text',
	'stcr_position'                => 'yesno',
	'enable_double_check'          => 'yesno',
	'notify_authors'               => 'yesno',
	'enable_html_emails'           => 'yesno',
	'process_trackbacks'           => 'yesno',
	'enable_admin_messages'        => 'yesno',
	'admin_subscribe'              => 'yesno',
	'admin_bcc'                    => 'yesno',
	'enable_font_awesome'          => 'yesno',
	'delete_options_subscriptions' => 'yesno',
	'only_for_logged_in'           => 'yesno',
	'use_cookies'                  => 'yesno',
	'use_challenge_question'       => 'yesno',
	'challenge_question'           => 'text',
	'challenge_answer'             => 'text',
	'use_captcha'                  => 'yesno',
	'captcha_site_key'             => 'text',
	'captcha_secret_key'           => 'text',
	'unique_key'                   => '',
	'recaptcha_version'            => 'select',
	'blacklisted_emails'           => 'textarea',
	'post_type_supports'           => 'multicheck',
);

if ( array_key_exists( "generate_key", $_POST ) ) {

    if ( empty( $_POST['stcr_save_options_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['stcr_save_options_nonce'], 'stcr_save_options_nonce' ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $unique_key = $wp_subscribe_reloaded->stcr->utils->generate_key();
    $wp_subscribe_reloaded->stcr->utils->stcr_update_menu_options( 'unique_key', $unique_key, 'text' );

    echo '<div class="updated"><p>';
    esc_html_e( 'Your settings have been successfully updated.', 'subscribe-to-comments-reloaded' );
    echo '</p></div>';

} elseif ( array_key_exists( "reset_all_options", $_POST ) ) {

    if ( empty( $_POST['stcr_save_options_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['stcr_save_options_nonce'], 'stcr_save_options_nonce' ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $delete_subscriptions_selection = isset( $_POST['options']['delete_options_subscriptions'] ) ? sanitize_text_field( wp_unslash( $_POST['options']['delete_options_subscriptions'] ) ) : '';
    $deletion_result = $wp_subscribe_reloaded->stcr->utils->delete_all_settings( $delete_subscriptions_selection );

    if( $deletion_result )
    {
        // Restore settings
        $wp_subscribe_reloaded->stcr->utils->create_options();
    }
} elseif( isset( $_POST['options'] ) ) { // Update options

    if ( empty( $_POST['stcr_save_options_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['stcr_save_options_nonce'], 'stcr_save_options_nonce' ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $faulty_fields     = array();
    $subscribe_options = wp_unslash( $_POST['options'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    $subscribe_options = array_map(
        array(
            'stcr\stcr_utils',
            'sanitize_options'
        ),
        $subscribe_options
    );
    foreach ( $subscribe_options as $option => $value )
    {
        if ( ! $wp_subscribe_reloaded->stcr->utils->stcr_update_menu_options( $option, $value, $options[$option] ) )
        {
            array_push( $faulty_fields, $option );
        }
    }

    // Display an alert in the admin interface if something went wrong
    echo '<div class="updated"><p>';
    if ( sizeof( $faulty_fields ) == 0 ) {
        esc_html_e( 'Your settings have been successfully updated.', 'subscribe-to-comments-reloaded' );
    } else {
        esc_html_e( 'There was an error updating the options.', 'subscribe-to-comments-reloaded' );
        // echo ' <strong>' . substr( $faulty_fields, 0, - 2 ) . '</strong>';
    }
    echo '</p></div>';
}
wp_print_scripts( 'quicktags' );

?>
    <div class="container-fluid">
        <div class="mt-3"></div>
        <div class="row">
            <div class="col-sm-9">
                <form action="" method="post">

                    <div class="form-group row" style="margin-bottom: 0;">
                        <label for="show_subscription_box" class="col-sm-3 col-form-label text-right"><?php esc_html_e( 'Show StCR checkbox / dropdown', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[show_subscription_box]"
                                       value="yes" id="show_subscription_box-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'show_subscription_box' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="show_subscription_box-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[show_subscription_box]" value="no" id="show_subscription_box-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'show_subscription_box' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="show_subscription_box-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "This option will disable the StCR checkbox or dropdown in your comment form. You should leave it to Yes always.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "This option will disable the StCR checkbox or dropdown in your comment form. You should leave it to Yes always.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="safely_uninstall" class="col-sm-3 col-form-label text-right"><?php esc_html_e( 'Safely Uninstall', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[safely_uninstall]"
                                       value="yes" id="safely_uninstall-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'safely_uninstall' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="safely_uninstall-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[safely_uninstall]" value="no" id="safely_uninstall-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'safely_uninstall' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="safely_uninstall-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "This option will allow you to delete the plugin with WordPress without loosing your subscribers. Any database table and plugin options are wipeout.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "This option will allow you to delete the plugin with WordPress without loosing your subscribers. Any database table and plugin options are wipeout.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="purge_days" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Autopurge requests', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="number" name="options[purge_days]" id="purge_days"
                                   class="form-control form-control-input-3"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'purge_days' ) ); ?>" size="20">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Delete pending subscriptions (not confirmed) after X days. Zero disables this feature.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Delete pending subscriptions (not confirmed) after X days. Zero disables this feature.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="date_format" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Date Format', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[date_format]" id="date_format"
                                   class="form-control form-control-input-3"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'date_format' ) ); ?>" size="20">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php echo wp_kses( __( "Date format that will be display on the management page. Use <a href='https://secure.php.net/manual/en/function.date.php#refsect1-function.date-parameters' target='_blank'>PHP Date Format</a>", 'subscribe-to-comments-reloaded' ), wp_kses_allowed_html( 'post' ) ); ?>"
                                 data-placement="right"
                                 aria-label="<?php echo wp_kses( __( "Date format that will be display on the management page. Use <a href='https://secure.php.net/manual/en/function.date.php#refsect1-function.date-parameters' target='_blank'>PHP Date Format</a>", 'subscribe-to-comments-reloaded' ), wp_kses_allowed_html( 'post' ) ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="stcr_position" class="col-sm-3 col-form-label text-right"><?php esc_html_e( 'StCR Position', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[stcr_position]"
                                       value="yes" id="stcr_position-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'stcr_position' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="stcr_position-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[stcr_position]" value="no" id="stcr_position-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'stcr_position' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="stcr_position-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "If this option is enable the subscription box will be above the submit button in your comment form. Use this when your theme is outdated and using the incorrect WordPress Hooks and the checkbox is not displayed.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "If this option is enable the subscription box will be above the submit button in your comment form. Use this when your theme is outdated and using the incorrect WordPress Hooks and the checkbox is not displayed.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="enable_double_check" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Enable double check', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[enable_double_check]"
                                       value="yes" id="enable_double_check-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_double_check' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="enable_double_check-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[enable_double_check]" value="no" id="enable_double_check-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_double_check' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="enable_double_check-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Send a notification email to confirm the subscription (to avoid addresses misuse).", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Send a notification email to confirm the subscription (to avoid addresses misuse).", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="notify_authors" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Subscribe authors', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[notify_authors]"
                                       value="yes" id="notify_authors-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'notify_authors' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="notify_authors-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[notify_authors]" value="no" id="notify_authors-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'notify_authors' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="notify_authors-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Automatically subscribe authors to their own articles (not retroactive).", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Automatically subscribe authors to their own articles (not retroactive).", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="enable_html_emails" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Enable HTML emails', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[enable_html_emails]"
                                       value="yes" id="enable_html_emails-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_html_emails' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="enable_html_emails-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[enable_html_emails]" value="no" id="enable_html_emails-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_html_emails' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="enable_html_emails-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "If enabled, will send email messages with content-type = text/html instead of text/plain", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "If enabled, will send email messages with content-type = text/html instead of text/plain", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="process_trackbacks" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Process trackbacks', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[process_trackbacks]"
                                       value="yes" id="process_trackbacks-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'process_trackbacks' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="process_trackbacks-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[process_trackbacks]" value="no" id="process_trackbacks-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'process_trackbacks' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="process_trackbacks-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Notify users when a new trackback or pingback is added to the discussion.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Notify users when a new trackback or pingback is added to the discussion.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="enable_admin_messages" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Track all subscriptions', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[enable_admin_messages]"
                                       value="yes" id="enable_admin_messages-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_admin_messages' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="enable_admin_messages-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[enable_admin_messages]" value="no" id="enable_admin_messages-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_admin_messages' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="enable_admin_messages-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Notify the administrator when users subscribe without commenting.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Notify the administrator when users subscribe without commenting.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="admin_subscribe" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Let Admin Subscribe', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[admin_subscribe]"
                                       value="yes" id="admin_subscribe-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'admin_subscribe' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="admin_subscribe-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[admin_subscribe]" value="no" id="admin_subscribe-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'admin_subscribe' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="admin_subscribe-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Let the administrator subscribe to comments when logged in.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Let the administrator subscribe to comments when logged in.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row" style="margin-bottom: 0;">
                        <label for="admin_bcc" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'BCC admin on Notifications', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[admin_bcc]"
                                       value="yes" id="admin_bcc-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'admin_bcc' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="admin_bcc-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[admin_bcc]" value="no" id="admin_bcc-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'admin_bcc' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="admin_bcc-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Send a copy of all Notifications to the administrator.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Send a copy of all Notifications to the administrator.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="enable_font_awesome" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Enable Font Awesome', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[enable_font_awesome]"
                                       value="yes" id="enable_font_awesome-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_font_awesome' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="enable_font_awesome-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[enable_font_awesome]" value="no" id="enable_font_awesome-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_font_awesome' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="enable_font_awesome-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Let you control the inclusion of the Font Awesome into your site. Disable if your theme already add this into your site.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Let you control the inclusion of the Font Awesome into your site. Disable if your theme already add this into your site.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="post_type_supports" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Enable on post types', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="multicheck">
                                <?php
                                $args = array(
                                    '_builtin' => false,
                                    'public'   => true,
                                );
                                $post_types         = get_post_types( $args );
                                $default_post_types = array(
                                    'post',
                                    'page',
                                );
                                $post_types         = array_merge( $default_post_types, $post_types );
                                $post_types_enabled = $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'post_type_supports', '' );

                                foreach ( $post_types as $post_type ) {
                                    $checked = '';
                                    foreach ( (array) $post_types_enabled as $post_type_enabled ) {
                                        if ( $post_type_enabled === $post_type ) {
                                            $checked = checked( $post_type_enabled, $post_type, false );
                                        }
                                    }
                                    ?>
                                    <div class="form-check pl-0">
                                        <input type="checkbox" id="<?php echo esc_attr( $post_type ); ?>" name="options[post_type_supports][]" value="<?php echo esc_attr( $post_type ) ?>" <?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />

                                        <label for="<?php echo esc_attr( $post_type ); ?>">
                                            <?php echo esc_html( get_post_type_object( $post_type )->label ); ?>
                                        </label>
                                    </div>
                                <?php } ?>

                                <input type="hidden" value="stcr_none" name="options[post_type_supports][]" />
                            </div>

                            <div class="helpDescription subsOptDescriptions ml-0"
                                 data-content="<?php esc_attr_e( "Enable for these specific post types only.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Enable for these specific post types only.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="only_for_logged_in" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Enable only for logged in users', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[only_for_logged_in]"
                                       value="yes" id="only_for_logged_in-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'only_for_logged_in', 'no' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="only_for_logged_in-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[only_for_logged_in]" value="no" id="only_for_logged_in-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'only_for_logged_in', 'no' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="only_for_logged_in-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Enable subscription only for logged in users.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Enable subscription only for logged in users.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="use_cookies" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Enable cookies', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[use_cookies]"
                                       value="yes" id="use_cookies-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'use_cookies', 'yes' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="use_cookies-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[use_cookies]" value="no" id="use_cookies-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'use_cookies', 'no' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="use_cookies-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Remembers the email address to prepopulate StCR forms.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Remembers the email address to prepopulate StCR forms.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="use_challenge_question" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Enable challenge question', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[use_challenge_question]"
                                       value="yes" id="use_challenge_question-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'use_challenge_question', 'no' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="use_challenge_question-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[use_challenge_question]" value="no" id="use_challenge_question-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'use_challenge_question', 'no' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="use_challenge_question-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Enables input for challenge question/answer on the subscription form.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Enables input for challenge question/answer on the subscription form.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="challenge_question" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Challenge question', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[challenge_question]" id="challenge_question"
                                   class="form-control form-control-input-3"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'challenge_question', 'What is 1 + 2?' ) ); ?>">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "The question shown to visitor when subscribing without commenting or when requesting a subscription management link.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "The question shown to visitor when subscribing without commenting or when requesting a subscription management link", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="challenge_answer" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Challenge answer', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[challenge_answer]" id="challenge_answer"
                                   class="form-control form-control-input-3"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'challenge_answer', 3 ) ); ?>">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "The visitor needs to provide this answer to proceed with subscription.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "The visitor needs to provide this answer to proceed with subscription.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <?php /* captcha options start */ ?>

                    <div class="form-group row">
                        <label for="use_captcha" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Enable Google reCAPTCHA', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[use_captcha]"
                                       value="yes" id="use_captcha-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'use_captcha', 'no' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="use_captcha-yes" class="switch-label switch-label-off">
                                    <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[use_captcha]" value="no" id="use_captcha-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'use_captcha', 'no' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="use_captcha-no" class="switch-label switch-label-on">
                                    <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Shown to visitor when subscribing without commenting or when requesting a subscription management link.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Shown to visitor when subscribing without commenting or when requesting a subscription management link.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="recaptcha_version" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'reCAPTCHA Version', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">
                            <select class="form-control form-control-input-3" id="recaptcha_version" name="options[recaptcha_version]">
                                <option value="v2" <?php selected( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'recaptcha_version', 'v2' ), 'v2', true ) ?>><?php esc_html_e( 'V2', 'subscribe-to-comments-reloaded' ); ?></option>
                                <option value="v3" <?php selected( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'recaptcha_version', 'v2' ), 'v3', true ) ?>><?php esc_html_e( 'V3', 'subscribe-to-comments-reloaded' ); ?></option>
                            </select>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "reCAPTCHA version to be used.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "reCAPTCHA version to be used.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="captcha_site_key" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'reCAPTCHA site key', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[captcha_site_key]" id="captcha_site_key"
                                   class="form-control form-control-input-3"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'captcha_site_key', '' ) ); ?>">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "The site key for Google reCAPTCHA.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "The site key for Google reCAPTCHA.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="captcha_secret_key" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'reCAPTCHA secret key', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[captcha_secret_key]" id="captcha_secret_key"
                                   class="form-control form-control-input-3"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'captcha_secret_key', '' ) ); ?>">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "The secret key for Google reCAPTCHA.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "The secret key for Google reCAPTCHA.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="blacklisted_emails" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Blacklisted Emails', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <textarea name="options[blacklisted_emails]" id="blacklisted_emails"
                                   class="form-control form-control-input-9" cols="10" rows="6"
                                   ><?php echo esc_textarea( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'blacklisted_emails', '' ) ); ?></textarea>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Add a comma separated list of emails to blacklist them from subscribing to comments. Example: example@example.com, mail@mail.com", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Add a comma separated list of emails to blacklist them from subscribing to comments. Example: example@example.com, mail@mail.com", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <?php /* captcha options end */ ?>

                    <div class="form-group row">
                        <label for="unique_key" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'StCR Unique Key', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">

                            <?php
                            if ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'unique_key' ) == "" ) {

                                echo "<div class=\"alert alert-danger\" role=\"alert\" style='font-size: 0.85rem;'>";
                                echo '<strong>' . esc_html__( "This Unique Key is not set, please click the following button to ", 'subscribe-to-comments-reloaded' ) . '</strong>';
                                echo "<input type='submit' value='" . esc_attr__( 'Generate', 'subscribe-to-comments-reloaded' ) ."' class='btn btn-secondary subscribe-form-button' name='generate_key' >";
                                echo "</div>";
                            }
                            else {
                            ?>
                                <input type="text" name="options[unique_key]" id="unique_key"
                                       class="form-control form-control-input-6"
                                       value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'unique_key' ) ); ?>" size="20">

                                <input type="submit" value="<?php esc_attr_e( 'Generate New Key' ) ?>" class="btn btn-secondary subscribe-form-button" name="generate_key" >
                            <?php } ?>


                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "This Unique Key will be use to send the notification to your subscribers with more security.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "This Unique Key will be use to send the notification to your subscribers with more security.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="" class="col-sm-3 col-form-label text-right">
                            <?php esc_html_e( 'Reset All Options', 'subscribe-to-comments-reloaded' ) ?>
                        </label>
                        <div class="col-sm-7">

                            <div class="alert alert-danger" role="alert">
                                <strong>Danger!</strong>
                                <p>
                                    <?php esc_html_e( 'This will reset all the options and messages of the plugin. Please proceed with caution.', 'subscribe-to-comments-reloaded' ); ?>
                                </p>

                                <p>
                                    <?php echo wp_kses( __( '<strong>Yes</strong> = Delete Options including subscriptions.', 'subscribe-to-comments-reloaded' ), wp_kses_allowed_html( 'post' ) ); ?><br/>
                                    <?php echo wp_kses( __( '<strong>No</strong>  = Only delete the StCR Options.', 'subscribe-to-comments-reloaded' ), wp_kses_allowed_html( 'post' ) ); ?>
                                </p>


                                <div class="switch">
                                    <input type="radio" class="switch-input" name="options[delete_options_subscriptions]"
                                           value="yes" id="delete_options_subscriptions-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'delete_options_subscriptions' ) == 'yes' ) ? ' checked' : ''; ?> />
                                    <label for="delete_options_subscriptions-yes" class="switch-label switch-label-off">
                                        <?php esc_html_e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                    </label>
                                    <input type="radio" class="switch-input" name="options[delete_options_subscriptions]" value="no" id="delete_options_subscriptions-no"
                                        <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'delete_options_subscriptions' ) == 'no' ) ? '  checked' : ''; ?> />
                                    <label for="delete_options_subscriptions-no" class="switch-label switch-label-on">
                                        <?php esc_html_e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                    </label>
                                    <span class="switch-selection"></span>
                                </div>

                                <input type="submit" value="<?php esc_attr_e( 'Reset All Options' ) ?>" class="btn btn-danger subscribe-form-button reset_all_options" name="reset_all_options" >

                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-1">
                            <button type="submit" class="btn btn-primary subscribe-form-button" name="Submit">
                                <?php esc_html_e( 'Save Changes', 'subscribe-to-comments-reloaded' ) ?>
                            </button>
                        </div>
                    </div>

                    <?php wp_nonce_field( 'stcr_save_options_nonce', 'stcr_save_options_nonce' ); ?>

                </form>
            </div>

            <div class="col-md-3">
                <div class="card card-font-size">
                    <div class="card-body">
                        <p>
                            Thank you for using Subscribe to Comments Reloaded. You can Support the plugin by rating it
                            <a href="https://wordpress.org/support/plugin/subscribe-to-comments-reloaded/reviews/#new-post" target="_blank"><img src="<?php echo esc_url( plugins_url( '/images/rate.png', STCR_PLUGIN_FILE ) ); ?>" alt="Rate Subscribe to Comments Reloaded" style="vertical-align: sub;" /></a>
                        </p>
                        <p>
                            <i class="fas fa-bug"></i> Having issues? Please <a href="https://github.com/stcr/subscribe-to-comments-reloaded/issues/" target="_blank">create a ticket</a>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
<?php
//global $wp_subscribe_reloaded;
// Tell WP that we are going to use a resource.
$wp_subscribe_reloaded->stcr->utils->register_script_to_wp( "stcr-subs-options", "subs_options.js", "includes/js/admin");
// Includes the Panel JS resource file as well as the JS text domain translations.
//$wp_subscribe_reloaded->stcr->stcr_i18n->stcr_localize_script( "stcr-subs-options", "stcr_i18n", $wp_subscribe_reloaded->stcr->stcr_i18n->get_js_subs_translation() );
// Enqueue the JS File
$wp_subscribe_reloaded->stcr->utils->enqueue_script_to_wp( "stcr-subs-options" );

?>
