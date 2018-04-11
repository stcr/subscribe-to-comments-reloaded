<?php
// Options

// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
    header( 'Location: /' );
    exit;
}

global $wpdb;

$options = array(
    "enable_log_data"          => "yesno",
    "auto_clean_log_data"      => "yesno",
    "auto_clean_log_frecuency" => "text"
);

$stcr_options = $wpdb->get_results('SELECT * FROM '. $wpdb->prefix . 'options WHERE option_name LIKE "subscribe_reloaded%"');

$stcr_options_str   = "";
$stcr_options_array = array();

foreach ($stcr_options as $option) {
    $stcr_options_str .= "{$option->option_name}: {$option->option_value}\n";
    $stcr_options_array[$option->option_name] = $option->option_value;
}

$stcr_options_array["custom_post_types"] = implode( "| ", get_post_types( '', 'names' )  );
$stcr_options_array["permalink_structure"] = get_option('permalink_structure');

// Updating options
if ( array_key_exists( "purge_log", $_POST ) ) {
    // Check that the log file exits
    $plugin_dir   = plugin_dir_path( __DIR__ );
    $file_name    = "log.txt";
    $message_type = "";
    $message      = "";
    $file_path    = $plugin_dir . "utils/" . $file_name;

    if( file_exists( $file_path )  && is_writable( $plugin_dir ) )
    {
        // unlink the file
        if( unlink($file_path) )
        {
            // show success message.
            $message = __( 'The log file has been successfully deleted.', 'subscribe-reloaded' );
            $message_type = "notice-success";
        }
        else
        {
            $message = __( 'Can\'t delete the log file, check the file permissions.', 'subscribe-reloaded' );
            $message_type = "notice-warning";
        }
    }
    else
    {
        $message     = __( 'The log file does not exists.', 'subscribe-reloaded' );
        $message_type = "notice-warning";
    }
    echo "<div class='notice $message_type'><p>";
    echo 	$message;
    // echo 	"<br><pre>$file_path$file_name</pre>";
    echo "</p></div>\n";
}
else {
    // echo "<pre>Option selected ";
    // 		 print_r($_POST['options']);
    // 		 echo "</pre>";
    // Update options
    if( isset( $_POST['options'] ) ) { // Update options

        $faulty_fields = array();

        foreach ( $_POST['options'] as $option => $value )
        {
//        echo $option . '<br>';

            if ( ! $wp_subscribe_reloaded->stcr->utils->stcr_update_menu_options( $option, $value, $options[$option] ) )
            {
                array_push( $faulty_fields, $option );
            }

            if ( $option === "auto_clean_log_data" && $value === "yes" )
            {
                // // Schedule the auto purge for the log file.
                if ( ! wp_next_scheduled( '_cron_log_file_purge' ) ) {
                    $log_purger_recurrence = get_option( "subscribe_reloaded_auto_clean_log_frecuency" );
                    wp_clear_scheduled_hook( '_cron_log_file_purge' );
                    // Let us bind the schedule event with our desire action.
                    wp_schedule_event( time() + 15, $log_purger_recurrence, '_cron_log_file_purge' );
                }
            }
            elseif ( $option === "auto_clean_log_data" && $value === "no" )
            {
                // Delete a Schedule event
                wp_clear_scheduled_hook( '_cron_log_file_purge' );
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
}


?>
    <link href="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/webui-popover/dist/jquery.webui-popover.min.css" rel="stylesheet"/>

    <div class="container-fluid">
        <div class="mt-3"></div>
        <div class="row">
            <div class="col-sm-9">
                <form action="" method="post">

                    <div class="form-group row" style="margin-bottom: 0;">
                        <label for="enable_log_data" class="col-sm-3 col-form-label text-right"><?php _e( 'Enable Log Information', 'subscribe-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[enable_log_data]"
                                       value="yes" id="enable_log_data-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_log_data' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="enable_log_data-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[enable_log_data]" value="no" id="enable_log_data-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_log_data' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="enable_log_data-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "If enabled, will log information of the plugin. Helpful for debugging purposes.<p>The file is stored under the path <code>Plugins Dir>subscribe-to-comments-reloaded>utils>log.txt</code></code></p>", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "If enabled, will log information of the plugin. Helpful for debugging purposes.", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row" style="margin-bottom: 0;">
                        <label for="auto_clean_log_data" class="col-sm-3 col-form-label text-right"><?php _e( 'Enable Auto clean log data', 'subscribe-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[auto_clean_log_data]"
                                       value="yes" id="auto_clean_log_data-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'auto_clean_log_data' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="auto_clean_log_data-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[auto_clean_log_data]" value="no" id="auto_clean_log_data-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'auto_clean_log_data' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="auto_clean_log_data-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>

                            <select class="auto_clean_log_frecuency form-control form-control-select" name="options[auto_clean_log_frecuency]">
                                <option value="hourly" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'auto_clean_log_frecuency' ) === 'hourly' ) ? "selected='selected'" : ''; ?>><?php _e( 'Hourly', 'subscribe-reloaded' ); ?></option>
                                <option value="twicedaily" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'auto_clean_log_frecuency' ) === 'twicedaily' ) ? "selected='selected'" : ''; ?>><?php _e( 'Twice Daily', 'subscribe-reloaded' ); ?></option>
                                <option value="daily" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'auto_clean_log_frecuency' ) === 'daily' ) ? "selected='selected'" : ''; ?>><?php _e( 'Daily', 'subscribe-reloaded' ); ?></option>
                            </select>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "If enabled, StCR will auto clean your information according to the frequency that you defined on the dropdown.", 'subscribe-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "If enabled, StCR will auto clean your information according to the frequency that you defined on the dropdown.", 'subscribe-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="unique_key" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Clean Up Log Archive', 'subscribe-reloaded' ) ?></label>
                        <div class="col-sm-7">

                            <span style="font-size: 0.9rem;"><?php _e(
                                    "If you want to clean up the log archive please click the following button",
                                    'subscribe-reloaded'
                                ); ?>
                            </span>

                            <input type='submit' value='<?php _e( 'Clean' ); ?>' class='btn btn-secondary subscribe-form-button' name='purge_log' >


                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-3">
                            <button type="submit" class="btn btn-primary subscribe-form-button" name="Submit">
                                <?php _e( 'Save Changes', 'subscribe-reloaded' ) ?>
                            </button>
                        </div>
                    </div>

                    <h3><?php _e( 'System Information', 'subscribe-reloaded' ) ?></h3>

                    <textarea style="width:90%; min-height:300px;"><?php echo serialize( $stcr_options_array ); ?></textarea>

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