<?php
/**
 * This is just a template/example file, not used
 */

// Options

// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
    header( 'Location: /' );
    exit;
}

$options = array(
	'show_subscription_box'           => 'yesno',
	'safely_uninstall'                => 'yesno',
	'purge_days'                      => 'integer',
	'date_format'                     => 'text',
	'stcr_position'                   => 'yesno',
	'enable_double_check'             => 'yesno',
	'notify_authors'                  => 'yesno',
	'enable_html_emails'              => 'yesno',
	'process_trackbacks'              => 'yesno',
	'enable_admin_messages'           => 'yesno',
	'admin_subscribe'                 => 'yesno',
	'admin_bcc'                       => 'yesno',
	'enable_font_awesome'             => 'yesno',
	'delete_options_subscriptions'    => 'yesno',
	'only_for_logged_in'              => 'yesno',
	'use_cookies'                     => 'yesno',
	'use_challenge_question'          => 'yesno',
	'challenge_question'              => 'text',
	'challenge_answer'                => 'text',
	'use_captcha'                     => 'yesno',
	'captcha_site_key'                => 'text',
	'captcha_secret_key'              => 'text',
	'allow_subscribe_without_comment' => 'yesno',
	'allow_request_management_link'   => 'yesno',
	'recaptcha_version'               => 'select',
	'blacklisted_emails'              => 'textarea',
	'post_type_supports'              => 'multicheck',
);

// Update options
if ( isset( $_POST['options'] ) ) {

    if ( empty( $_POST['stcr_action_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['stcr_action_nonce'], 'stcr_action_nonce' ) ) {
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



                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-1">
                            <button type="submit" class="btn btn-primary subscribe-form-button" name="Submit">
                                <?php esc_html_e( 'Save Changes', 'subscribe-to-comments-reloaded' ) ?>
                            </button>
                        </div>
                    </div>

                    <?php wp_nonce_field( 'stcr_action_nonce', 'stcr_action_nonce' ); ?>

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
