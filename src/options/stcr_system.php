<?php
/**
 * Displays WP-admin -> StCR -> StCR System 
 */

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

$stcr_options_str        = "";
$stcr_options_array      = array();
$stcr_system_information = array();

foreach ($stcr_options as $option) {
    $stcr_options_str .= "{$option->option_name}: {$option->option_value}\n";
    $stcr_options_array[$option->option_name] = $option->option_value;
}

$stcr_options_array["custom_post_types"] = implode( "| ", get_post_types( '', 'names' )  );
$stcr_options_array["permalink_structure"] = get_option('permalink_structure');

// Setup the auto purge for the download report.
// // Schedule the auto purge for the log file.
if ( ! wp_next_scheduled( '_cron_subscribe_reloaded_system_report_file_purge' ) ) {
    wp_clear_scheduled_hook( '_cron_subscribe_reloaded_system_report_file_purge' );
    // Let us bind the schedule event with our desire action.
    wp_schedule_event( time() + 15, "daily", '_cron_subscribe_reloaded_system_report_file_purge' );
}

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
            $message = __( 'The log file has been successfully deleted.', 'subscribe-to-comments-reloaded' );
            $message_type = "notice-success";
        }
        else
        {
            $message = __( 'Can\'t delete the log file, check the file permissions.', 'subscribe-to-comments-reloaded' );
            $message_type = "notice-warning";
        }
    }
    else
    {
        $message     = __( 'The log file does not exists.', 'subscribe-to-comments-reloaded' );
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
            _e( 'Your settings have been successfully updated.', 'subscribe-to-comments-reloaded' );
        } else {
            _e( 'There was an error updating the options.', 'subscribe-to-comments-reloaded' );
            // echo ' <strong>' . substr( $faulty_fields, 0, - 2 ) . '</strong>';
        }
        echo "</p></div>";
    }
}
?>
    <link href="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/webui-popover/dist/jquery.webui-popover.min.css" rel="stylesheet"/>
    <style type="text/css">
        .system-error {
            color: #dc3545;
        }

        .system-success{
            color: #7cc575;
        }
    </style>


    <div class="container-fluid">
        <div class="mt-3"></div>
        <div class="row">
            <div class="col-sm-9">
                <form action="" method="post">

                    <div class="form-group row" style="margin-bottom: 0;">
                        <label for="enable_log_data" class="col-sm-3 col-form-label text-right"><?php _e( 'Enable Log Information', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[enable_log_data]"
                                       value="yes" id="enable_log_data-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_log_data' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="enable_log_data-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[enable_log_data]" value="no" id="enable_log_data-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_log_data' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="enable_log_data-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "If enabled, will log information of the plugin. Helpful for debugging purposes.<p>The file is stored under the path <code>Plugins Dir>subscribe-to-comments-reloaded>utils>log.txt</code></code></p>", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "If enabled, will log information of the plugin. Helpful for debugging purposes.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row" style="margin-bottom: 0;">
                        <label for="auto_clean_log_data" class="col-sm-3 col-form-label text-right"><?php _e( 'Enable Auto clean log data', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[auto_clean_log_data]"
                                       value="yes" id="auto_clean_log_data-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'auto_clean_log_data' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="auto_clean_log_data-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[auto_clean_log_data]" value="no" id="auto_clean_log_data-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'auto_clean_log_data' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="auto_clean_log_data-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>

                            <select class="auto_clean_log_frecuency form-control form-control-select" name="options[auto_clean_log_frecuency]">
                                <option value="hourly" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'auto_clean_log_frecuency' ) === 'hourly' ) ? "selected='selected'" : ''; ?>><?php _e( 'Hourly', 'subscribe-to-comments-reloaded' ); ?></option>
                                <option value="twicedaily" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'auto_clean_log_frecuency' ) === 'twicedaily' ) ? "selected='selected'" : ''; ?>><?php _e( 'Twice Daily', 'subscribe-to-comments-reloaded' ); ?></option>
                                <option value="daily" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'auto_clean_log_frecuency' ) === 'daily' ) ? "selected='selected'" : ''; ?>><?php _e( 'Daily', 'subscribe-to-comments-reloaded' ); ?></option>
                            </select>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "If enabled, StCR will auto clean your information according to the frequency that you defined on the dropdown.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "If enabled, StCR will auto clean your information according to the frequency that you defined on the dropdown.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="purge_log" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Clean Up Log Archive', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">

                            <span style="font-size: 0.9rem;"><?php _e(
                                    "If you want to clean up the log archive please click the following button",
                                    'subscribe-to-comments-reloaded'
                                ); ?>
                            </span>

                            <input type='submit' id="purge_log" value='<?php esc_attr_e( 'Clean' ); ?>' class='btn btn-secondary subscribe-form-button' name='purge_log' >
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="generate_system_info" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Download System Info File', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <a class="download_report btn btn-download subscribe-form-button" href="#">
                                <?php _e( 'Download', 'subscribe-to-comments-reloaded' ); ?>
                            </a>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-3">
                            <button type="submit" class="btn btn-primary subscribe-form-button" name="Submit">
                                <?php _e( 'Save Changes', 'subscribe-to-comments-reloaded' ) ?>
                            </button>
                        </div>
                    </div>

                    <h4><?php _e( 'System Information', 'subscribe-to-comments-reloaded' ) ?></h4><br>

                    <!-- Plugin Info -->
                    <?php
                        // get the total number of subscribers and subscriptions
                        $total_subscribers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}subscribe_reloaded_subscribers");
                        $total_subscriptions = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}postmeta where meta_key LIKE '_stcr@_%'");
                    ?>
                    <table class="table table-sm table-hover table-striped system-info-table" style="font-size: 0.8em">
                        <thead style="background-color: #4688d2; color: #ffffff;">
                            <th style="textalilfe" class="text-left" colspan="2"><?php _e( 'Plugin Info', 'subscribe-to-comments-reloaded' ) ?></th>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-left"><?php _e( 'Subscribers', 'subscribe-toc-omments-reloaded' ); ?></td>
                                <td class="text-left"><?php echo $total_subscribers; ?></td>
                            </tr>
                            <tr>
                                <td class="text-left"><?php _e( 'Subscriptions', 'subscribe-toc-omments-reloaded' ); ?></td>
                                <td class="text-left"><?php echo $total_subscriptions; ?></td>
                            </tr>
                        </tbody>
                    </table>
                                
                    <table class="table table-sm table-hover table-striped system-info-table" style="font-size: 0.8em">
                        <thead style="background-color: #4688d2; color: #ffffff;">
                        <th style="textalilfe" class="text-left" colspan="2"><?php _e( 'WordPress Environment', 'subscribe-to-comments-reloaded' ) ?></th>
                        </thead>
                        <?php

                        $memory = $wp_subscribe_reloaded->stcr->utils->to_num_ini_notation( WP_MEMORY_LIMIT );
                        $memoryValue = '';
                        $wpDebug     = 'No';
                        $wpCron      = 'No';
                        $wpHome      = get_option( 'home' );
                        $wpsiteurl   = get_option( 'siteurl' );
                        $wpVersion   = get_bloginfo( 'version' );
                        $wpMultisite = is_multisite();
                        $wpLanguage  = get_locale();
                        $wpPermalink = esc_html( get_option( 'permalink_structure' ) );
                        $wpTablePrefix = esc_html( $wpdb->prefix );
                        $wpTablePrefixLength = strlen( $wpdb->prefix );
                        $wpTablePrefixStatus = $wpTablePrefixLength > 16 ? esc_html( 'Error: Too long', 'subscribe-to-comments-reloaded' ) : esc_html( 'Acceptable', 'subscribe-to-comments-reloaded' );
                        $wpRegisteredPostStatuses  = esc_html( implode( ', ', get_post_stati() ) );

                        $stcr_system_information['Wordpress Environment']["Home URL"] = $wpHome;
                        $stcr_system_information['Wordpress Environment']["Site URL"] = $wpsiteurl;
                        $stcr_system_information['Wordpress Environment']["WordPress Version"] = $wpVersion;
                        $stcr_system_information['Wordpress Environment']["Multisite"] = $wpMultisite;
                        $stcr_system_information['Wordpress Environment']["Language"] = $wpLanguage;
                        $stcr_system_information['Wordpress Environment']["Permalink Structure"] = $wpPermalink;
                        $stcr_system_information['Wordpress Environment']["Table Prefix"] = $wpTablePrefix;
                        $stcr_system_information['Wordpress Environment']["Table Prefix Length"] = $wpTablePrefixLength;
                        $stcr_system_information['Wordpress Environment']["Table Prefix Status"] = $wpTablePrefixStatus;
                        $stcr_system_information['Wordpress Environment']["Registered Post Statuses"] = $wpRegisteredPostStatuses;



                        if ( function_exists( 'memory_get_usage' ) ) {
                            $system_memory = $wp_subscribe_reloaded->stcr->utils->to_num_ini_notation( @ini_get( 'memory_limit' ) );
                            $memory        = max( $memory, $system_memory );
                        }

                        if ( $memory < 67108864 ) {
                            $memoryValue = '<div class="system-error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s - We recommend setting memory to at least 64 MB. See: %s', 'subscribe-to-comments-reloaded' ), size_format( $memory ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . __( 'Increasing memory allocated to PHP', 'subscribe-to-comments-reloaded' ) . '</a>' ) . '</div>';
                            $stcr_system_information['Wordpress Environment']["Memory Limit"] = "Memory under 64MB";
                        }
                        else {
                            $memoryValue = '<div class="system-success">' . size_format( $memory ) . '</div>';
                            $stcr_system_information['Wordpress Environment']["Memory Limit"] = size_format( $memory );
                        }
                        // Check if Debug is Enable
                        if ( defined( 'WP_DEBUG' ) && WP_DEBUG )
                        {
                            $wpDebug = '<div class="system-success"><span class="dashicons dashicons-yes"></span></div>';
                            $stcr_system_information['Wordpress Environment']["WP Debug Mode"] = true;
                        }
                        else
                        {
                            $wpDebug = '<div>'. $wpDebug .'</div>';
                            $stcr_system_information['Wordpress Environment']["WP Debug Mode"] = false;
                        }
                        // Check if WP Cron is Enable
                        if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON )
                        {
                            $wpCron = '<div>'. $wpCron .'</div>';
                            $stcr_system_information['Wordpress Environment']["WP Cron"] = true;
                        }
                        else
                        {
                            $wpCron = '<div class="system-success"><span class="dashicons dashicons-yes"></span></div>';
                            $stcr_system_information['Wordpress Environment']["WP Cron"] = false;
                        }

                        $wordpressEnvironment = array(
                            1 => array(
                                __( "Home URL", 'subscribe-to-comments-reloaded' ),
                                $wpHome
                            ),
                            2 => array(
                                __( "Site URL", 'subscribe-to-comments-reloaded' ),
                                $wpsiteurl
                            ),
                            3 => array(
                                __( "WordPress Version", 'subscribe-to-comments-reloaded' ),
                                $wpVersion
                            ),
                            4 => array(
                                "Multisite",
                                $wpMultisite ? '<span class="dashicons dashicons-yes"></span>' :  'No'
                            ),
                            5 => array(
                                __( "Memory Limit", 'subscribe-to-comments-reloaded' ),
                                $memoryValue
                            ),
                            6 => array(
                                __( "WP Debug Mode", 'subscribe-to-comments-reloaded' ),
                                $wpDebug
                            ),
                            7 => array(
                                __( "WP Cron", 'subscribe-to-comments-reloaded' ),
                                $wpCron
                            ),
                            8 => array(
                                __( "Language", 'subscribe-to-comments-reloaded' ),
                                $wpLanguage
                            ),
                            9 => array(
                                __( "Permalink Structure", 'subscribe-to-comments-reloaded' ),
                                $wpPermalink
                            ),
                            10 => array(
                                __( "Table Prefix", 'subscribe-to-comments-reloaded' ),
                                $wpTablePrefix
                            ),
                            11 => array(
                                __( "Table Prefix Length", 'subscribe-to-comments-reloaded' ),
                                $wpTablePrefixLength
                            ),
                            12 => array(
                                __( "Table Prefix Status", 'subscribe-to-comments-reloaded' ),
                                $wpTablePrefixStatus
                            ),
                            13 => array(
                                __( "Registered Post Statuses", 'subscribe-to-comments-reloaded' ),
                                $wpRegisteredPostStatuses
                            )
                        );
                        ?>
                        <tbody>
                        <?php
                        foreach ( $wordpressEnvironment as $key => $opt )
                        {
                            echo "<tr>";
                            echo "<td class='text-left' style='min-width: 50px;'>{$opt[0]}</td>";
                            echo "<td class='text-left'>{$opt[1]}</td>";
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>

                    <!-- Server Environment -->
                    <table class="table table-sm table-hover table-striped system-info-table" style="font-size: 0.8em">
                        <thead style="background-color: #4688d2; color: #ffffff;">
                        <th style="textalilfe" class="text-left" colspan="2"><?php _e( 'Server Environment', 'subscribe-to-comments-reloaded' ) ?></th>
                        </thead>
                        <?php

                        $tlsCheck      = false;
                        $tlsCheckValue = __( 'Cannot Evaluate', 'subscribe-to-comments-reloaded' );
                        $tlsRating     = __( 'Not Available', 'subscribe-to-comments-reloaded' );
                        $phpVersion    = __( 'Not Available', 'subscribe-to-comments-reloaded' );
                        $cURLVersion   = __( 'Not Available', 'subscribe-to-comments-reloaded' );
                        $MySQLSVersion = __( 'Not Available', 'subscribe-to-comments-reloaded' );
                        $defaultTimezone = __( 'Not Available', 'subscribe-to-comments-reloaded' );
                        $serverInfo    = esc_html( $_SERVER['SERVER_SOFTWARE'] );
                        $maxPostSize    = size_format( $wp_subscribe_reloaded->stcr->utils->to_num_ini_notation( ini_get( 'post_max_size' ) ) );

                        // Get the SSL status.
                        if ( ini_get( 'allow_url_fopen' ) ) {
                            $tlsCheck = file_get_contents( 'https://www.howsmyssl.com/a/check' );
                        }

                        if ( false !== $tlsCheck )
                        {
                            $tlsCheck = json_decode( $tlsCheck );
                            /* translators: %s: SSL connection response */
                            $tlsCheckValue = sprintf( __( 'Connection uses %s', 'subscribe-to-comments-reloaded' ), esc_html( $tlsCheck->tls_version ) );
                        }
                        $stcr_system_information['Server Environment']["TLS Connection"] = $tlsCheckValue;
                        // Check TSL Rating
                        if ( false !== $tlsCheck )
                        {
                            $tlsRating = property_exists( $tlsCheck, 'rating' ) ? $tlsCheck->rating : $tlsCheck->tls_version;
                        }
                        $stcr_system_information['Server Environment']["TLS Rating"] = $tlsRating;
                        $stcr_system_information['Server Environment']["Server Info"] = $serverInfo;
                        // Check the PHP Version
                        if ( function_exists( 'phpversion' ) )
                        {
                            $phpVersion = phpversion();

                            if ( version_compare( $phpVersion, '5.6', '<' ) )
                            {
                                $phpVersion = '<div class="system-error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s - We recommend a minimum PHP version of 5.6. See: %s', 'subscribe-to-comments-reloaded' ), esc_html( $phpVersion ), '<a href="http://subscribe-reloaded.com/about/" target="_blank">' . __( 'PHP Requirements in StCR', 'subscribe-to-comments-reloaded' ) . '</a>' ) . '</div>';
                                $stcr_system_information['Server Environment']["PHP Version"] = sprintf( '%s - We recommend a minimum PHP version of 5.6. See: %s', esc_html( $phpVersion ), '<a href="http://subscribe-reloaded.com/about/" target="_blank">PHP Requirements in StCR</a>' );
                            }
                            else
                            {
                                $phpVersion = '<div class="system-success">' . esc_html( $phpVersion ) . '</div>';
                                $stcr_system_information['Server Environment']["PHP Version"] = phpversion();
                            }
                        }
                        else
                        {
                            $phpVersion = __( "Couldn't determine PHP version because the function phpversion() doesn't exist.", 'subscribe-to-comments-reloaded' );
                            $stcr_system_information['Server Environment']["PHP Version"] = "Couldn't determine PHP version because the function phpversion() doesn't exist.";
                        }

                        $stcr_system_information['Server Environment']["PHP Post Max Size"] = $maxPostSize;
                        $stcr_system_information['Server Environment']["PHP Max Execution Time"] = ini_get( 'max_execution_time' );
                        $stcr_system_information['Server Environment']["PHP Max Input Vars"] = ini_get( 'max_input_vars' );
                        $stcr_system_information['Server Environment']["PHP Max Upload Size"] = size_format( wp_max_upload_size() );

                        // Check the cURL Version
                        if ( function_exists( 'curl_version' ) )
                        {
                            $cURLVersion = curl_version();
                            $cURLVersionNumber = $cURLVersion['version'];
                            $cURLSSLVersion = $cURLVersion['ssl_version'];

                            if ( version_compare( $cURLVersionNumber, '7.40', '<' ) )
                            {
                                $cURLVersion = '<div class="system-error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s - We recommend a minimum cURL version of 7.40.', 'subscribe-to-comments-reloaded' ), esc_html( $cURLVersionNumber . ', ' . $cURLSSLVersion ) ) . '</div>';
                                $stcr_system_information['Server Environment']["cURL Version"] = sprintf('%s - We recommend a minimum cURL version of 7.40.', esc_html( $cURLVersionNumber . ', ' . $cURLSSLVersion ) );
                            }
                            else
                            {
                                $cURLVersion = '<div class="system-success">' . esc_html( $cURLVersionNumber ) . '</div>';
                                $stcr_system_information['Server Environment']["cURL Version"] = $cURLVersion;
                            }
                        }
                        else
                        {
                            $cURLVersion = '&ndash;';
                            $stcr_system_information['Server Environment']["cURL Version"] = 'cURL is not available';
                        }

                        // check MySQL version
                        global $wp_version, $required_mysql_version;
                        if ( version_compare( $wpdb->db_version(), $required_mysql_version, '<' ) ) {
                            $MySQLSVersion = '<div class="system-error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '<strong>ERROR</strong>: WordPress %1$s requires MySQL %2$s or higher' ), $wp_version, $required_mysql_version ) . '</div>';
                            $stcr_system_information['Server Environment']["MySQL Version"] = $wpdb->db_version();
                        } else {
                            $MySQLSVersion = '<div class="system-success">' . $wpdb->db_version() . '</div>';
                            $stcr_system_information['Server Environment']["MySQL Version"] = $wpdb->db_version();
                        }

                        // Get the Timezone
                        $defaultTimezone = date_default_timezone_get();

                        if ( 'UTC' !== $defaultTimezone )
                        {
                            $defaultTimezone = '<div class="system-error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Default timezone is %s - it should be UTC', 'subscribe-to-comments-reloaded' ), $defaultTimezone ) . '</div>';
                            $stcr_system_information['Server Environment']["Default Timezone is UTC"] = sprintf('Default timezone is %s - it should be UTC', $defaultTimezone );
                        }
                        else
                        {
                            $defaultTimezone = '<div class="system-success"><span class="dashicons dashicons-yes"></span></div>';
                            $stcr_system_information['Server Environment']["Default Timezone is UTC"] = "Yes";
                        }
                        // DOMDocument
                        $DOMDocument = __( 'Not Available', 'subscribe-to-comments-reloaded' );
                        if ( class_exists( 'DOMDocument' ) )
                        {
                            $DOMDocument = '<div class="system-success"><span class="dashicons dashicons-yes"></span></div>';
                            $stcr_system_information['Server Environment']["DOMDocument"] = "Yes";
                        }
                        else {
                            $DOMDocument = sprintf( __( 'Your server does not have the %s class enabled - HTML/Multipart emails, and also some extensions, will not work without DOMDocument.', 'subscribe-to-comments-reloaded' ), '<a href="https://php.net/manual/en/class.domdocument.php">DOMDocument</a>' );
                            $stcr_system_information['Server Environment']["DOMDocument"] = sprintf( 'Your server does not have the %s class enabled - HTML/Multipart emails, and also some extensions, will not work without DOMDocument.', '<a href="https://php.net/manual/en/class.domdocument.php">DOMDocument</a>' );
                        }
                        // Check gzip
                        $gzip = __( 'Not Available', 'subscribe-to-comments-reloaded' );
                        if ( is_callable( 'gzopen' ) )
                        {
                            $gzip = '<div class="system-success"><span class="dashicons dashicons-yes"></span></div>';
                            $stcr_system_information['Server Environment']["gzip"] = "Yes";
                        }
                        else {
                            $gzip = sprintf( __( 'Your server does not support the %s function - this is used for file compression and decompression.', 'subscribe-to-comments-reloaded' ), '<a href="https://php.net/manual/en/zlib.installation.php">gzopen</a>' );
                            $stcr_system_information['Server Environment']["gzip"] = sprintf( 'Your server does not support the %s function - this is used for file compression and decompression.', '<a href="https://php.net/manual/en/zlib.installation.php">gzopen</a>' );
                        }// Check GD
                        $gd = __( 'Not Available', 'subscribe-to-comments-reloaded' );
                        if ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) )
                        {
                            $gd = '<div class="system-success"><span class="dashicons dashicons-yes"></span></div>';
                            $stcr_system_information['Server Environment']["GD Graphics Library"] = "Yes";
                        }
                        else {
                            $gd = '<div class="system-error"><span class="dashicons dashicons-no"></span></div>';
                            $stcr_system_information['Server Environment']["GD Graphics Library"] = "No";
                        }

                        // Define array of values
                        $serverEnvironment = array(
                            1 => array(
                                __( "TLS Connection", 'subscribe-to-comments-reloaded' ),
                                $tlsCheckValue
                            ),
                            2 => array(
                                __( "TLS Rating", 'subscribe-to-comments-reloaded' ),
                                $tlsRating
                            ),
                            3 => array(
                                __( "Server Info", 'subscribe-to-comments-reloaded' ),
                                $serverInfo
                            ),
                            4 => array(
                                __( "PHP Version", 'subscribe-to-comments-reloaded' ),
                                $phpVersion
                            ),
                            5 => array(
                                __( "PHP Post Max Size", 'subscribe-to-comments-reloaded' ),
                                $maxPostSize
                            ),
                            6 => array(
                                __( "PHP Max Execution Time", 'subscribe-to-comments-reloaded' ),
                                ini_get( 'max_execution_time' )
                            ),
                            7 => array(
                                __( "PHP Max Input Vars", 'subscribe-to-comments-reloaded' ),
                                ini_get( 'max_input_vars' )
                            ),
                            8 => array(
                                __( "PHP Max Upload Size", 'subscribe-to-comments-reloaded' ),
                                size_format( wp_max_upload_size() )
                            ),
                            9 => array(
                                __( "cURL Version", 'subscribe-to-comments-reloaded' ),
                                $cURLVersion
                            ),
                            10 => array(
                                __( "MySQL Version", 'subscribe-to-comments-reloaded' ),
                                $MySQLSVersion
                            ),
                            11 => array(
                                __( "Default Timezone is UTC", 'subscribe-to-comments-reloaded' ),
                                $defaultTimezone
                            ),
                            12 => array(
                                __( "DOMDocument", 'subscribe-to-comments-reloaded' ),
                                $DOMDocument
                            ),
                            13 => array(
                                __( "gzip", 'subscribe-to-comments-reloaded' ),
                                $gzip
                            ),
                            14 => array(
                                __( "GD Graphics Library", 'subscribe-to-comments-reloaded' ),
                                $gd
                            )
                        );
                        ?>
                        <tbody>
                        <?php
                        foreach ( $serverEnvironment as $key => $opt )
                        {
                            echo "<tr>";
                                echo "<td class='text-left' style='min-width: 50px;'>{$opt[0]}</td>";
                                echo "<td class='text-left'>{$opt[1]}</td>";
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                    <?php
                    $plugins = $wp_subscribe_reloaded->stcr->utils->stcr_get_plugins();
                    $installed_plugins = array();
                    ?>
                    <!-- Active Plugins -->
                    <table class="table table-sm table-hover table-striped system-info-table" style="font-size: 0.8em">
                        <thead style="background-color: #4688d2; color: #ffffff;">
                        <th style="textalilfe" class="text-left" colspan="2"><?php _e( 'Active Plugins', 'subscribe-to-comments-reloaded' ) ?></th>
                        </thead>

                        <tbody>
                        <?php

                        foreach ( $plugins as $plugin_data )
                        {
                            // Filter only the Active plugins
                            if ('active' !== $plugin_data['Status'] )
                            {
                                continue;
                            }

                            $plugin_name = $plugin_data['Name'];
                            $author_name = $plugin_data['Author'];

                            $installed_plugins[] = array(
                                    "plugin-name" => $plugin_name,
                                    "plugin-author" => $author_name
                            );

                            // Link the plugin name to the plugin URL if available.
                            if ( ! empty( $plugin_data['PluginURI'] ) ) {
                                $plugin_name = sprintf(
                                    '<a href="%s" title="%s">%s</a>',
                                    esc_url( $plugin_data['PluginURI'] ),
                                    esc_attr__( 'Visit plugin homepage', 'subscribe-to-comments-reloaded' ),
                                    $plugin_name
                                );
                            }
                            // Link the author name to the author URL if available.
                            if ( ! empty( $plugin_data['AuthorURI'] ) ) {
                                $author_name = sprintf(
                                    '<a href="%s" title="%s">%s</a>',
                                    esc_url( $plugin_data['AuthorURI'] ),
                                    esc_attr__( 'Visit author homepage', 'subscribe-to-comments-reloaded' ),
                                    $author_name
                                );

                                $author_name = sprintf( _x( 'by %s', 'by author', 'subscribe-to-comments-reloaded' ),
                                                    wp_kses( $author_name, wp_kses_allowed_html( 'post' ) ) ) . ' &ndash; '
                                                    . esc_html( $plugin_data['Version'] );
                            }
                            echo "<tr>";
                            echo "<td class='text-left' style='min-width: 50px;'>{$plugin_name}</td>";
                            echo "<td class='text-left'>{$author_name}</td>";
                            echo "</tr>";
                            $stcr_system_information['WordPress Active Plugins'][$plugin_data['Name']] = $plugin_data;
                        }

                        ?>
                        </tbody>
                    </table>
                    <!-- Inactive Plugins -->
                    <table class="table table-sm table-hover table-striped system-info-table" style="font-size: 0.8em">
                        <thead style="background-color: #4688d2; color: #ffffff;">
                        <th style="textalilfe" class="text-left" colspan="2"><?php _e( 'Inactive Plugins', 'subscribe-to-comments-reloaded' ) ?></th>
                        </thead>

                        <tbody>
                        <?php

                        foreach ( $plugins as $plugin_data )
                        {
                            // Filter only the Inactive plugins
                            if ('inactive' !== $plugin_data['Status'] )
                            {
                                continue;
                            }

                            $plugin_name = $plugin_data['Name'];
                            $author_name = $plugin_data['Author'];

                            $installed_plugins[] = array(
                                "plugin-name" => $plugin_name,
                                "plugin-author" => $author_name
                            );

                            // Link the plugin name to the plugin URL if available.
                            if ( ! empty( $plugin_data['PluginURI'] ) ) {
                                $plugin_name = sprintf(
                                    '<a href="%s" title="%s">%s</a>',
                                    esc_url( $plugin_data['PluginURI'] ),
                                    esc_attr__( 'Visit plugin homepage', 'subscribe-to-comments-reloaded' ),
                                    $plugin_name
                                );
                            }
                            // Link the author name to the author URL if available.
                            if ( ! empty( $plugin_data['AuthorURI'] ) ) {
                                $author_name = sprintf(
                                    '<a href="%s" title="%s">%s</a>',
                                    esc_url( $plugin_data['AuthorURI'] ),
                                    esc_attr__( 'Visit author homepage', 'subscribe-to-comments-reloaded' ),
                                    $author_name
                                );

                                $author_name = sprintf( _x( 'by %s', 'by author', 'subscribe-to-comments-reloaded' ),
                                        wp_kses( $author_name, wp_kses_allowed_html( 'post' ) ) ) . ' &ndash; '
                                    . esc_html( $plugin_data['Version'] );
                            }
                            echo "<tr>";
                            echo "<td class='text-left' style='min-width: 50px;'>{$plugin_name}</td>";
                            echo "<td class='text-left'>{$author_name}</td>";
                            echo "</tr>";
                            $stcr_system_information['WordPress Inactive Plugins'][$plugin_data['Name']] = $plugin_data;
                        }
                        ?>
                        </tbody>
                    </table>
                    
                </form>

                <form name="stcr_sysinfo_form" class="stcr-hidden" action="<?php echo esc_url( admin_url( 'admin.php?page=stcr_system' ) ); ?>" method="post">
                    <input type="hidden" name="stcr_sysinfo_action" value="download_sysinfo" />
                    <textarea name="stcr_sysinfo" readonly><?php echo serialize( $stcr_system_information ); ?></textarea>
                </form>

            </div>

            <div class="col-md-3">
                <div class="card card-font-size">
                    <div class="card-body">
                        <p>
                            Thank you for using Subscribe to Comments Reloaded. You can Support the plugin by rating it
                            <a href="https://wordpress.org/support/plugin/subscribe-to-comments-reloaded/reviews/#new-post" target="_blank"><img src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/images/rate.png" alt="Rate Subscribe to Comments Reloaded" style="vertical-align: sub;" /></a>
                        </p>
                        <p>
                            <i class="fas fa-bug"></i> Having issues? Please <a href="https://github.com/stcr/subscribe-to-comments-reloaded/issues/" target="_blank">create a ticket</a>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script type="text/javascript" src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/webui-popover/dist/jquery.webui-popover.min.js"></script>
<?php
//global $wp_subscribe_reloaded;
// Tell WP that we are going to use a resource.
$wp_subscribe_reloaded->stcr->utils->register_script_to_wp( "stcr-system-info", "stcr_system.js", "includes/js/admin");
$wp_subscribe_reloaded->stcr->utils->register_script_to_wp( "stcr-subs-options", "subs_options.js", "includes/js/admin");
// Includes the Panel JS resource file as well as the JS text domain translations.
//$wp_subscribe_reloaded->stcr->stcr_i18n->stcr_localize_script( "stcr-system-info", "stcr_i18n", $wp_subscribe_reloaded->stcr->stcr_i18n->get_js_subs_translation() );
// Enqueue the JS File
$wp_subscribe_reloaded->stcr->utils->enqueue_script_to_wp( "stcr-system-info" );
$wp_subscribe_reloaded->stcr->utils->enqueue_script_to_wp( "stcr-subs-options" );

?>