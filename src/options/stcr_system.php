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

                    <table class="table table-sm table-hover table-striped subscribers-table" style="font-size: 0.8em">
                        <thead style="background-color: #4688d2; color: #ffffff;">
                        <th style="textalilfe" class="text-left" colspan="2"><?php _e( 'WordPress Environment', 'subscribe-reloaded' ) ?></th>
                        </thead>
                        <?php

                        $memory = $wp_subscribe_reloaded->stcr->utils->to_num_ini_notation( WP_MEMORY_LIMIT );
                        $memoryValue = '';
                        $wpDebug     = 'No';
                        $wpCron      = 'No';

                        if ( function_exists( 'memory_get_usage' ) ) {
                            $system_memory = $wp_subscribe_reloaded->stcr->utils->to_num_ini_notation( @ini_get( 'memory_limit' ) );
                            $memory        = max( $memory, $system_memory );
                        }

                        if ( $memory < 67108864 ) {
                            $memoryValue = '<div class="system-error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s - We recommend setting memory to at least 64 MB. See: %s', 'subscribe-reloaded' ), size_format( $memory ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . __( 'Increasing memory allocated to PHP', 'subscribe-reloaded' ) . '</a>' ) . '</div>';
                        }
                        else {
                            $memoryValue = '<div class="system-success">' . size_format( $memory ) . '</div>';
                        }
                        // Check if Debug is Enable
                        if ( defined( 'WP_DEBUG' ) && WP_DEBUG )
                        {
                            $wpDebug = '<div class="system-success"><span class="dashicons dashicons-yes"></span></div>';
                        }
                        else
                        {
                            $wpDebug = '<div>'. $wpDebug .'</div>';
                        }
                        // Check if WP Cron is Enable
                        if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON )
                        {
                            $wpCron = '<div>'. $wpCron .'</div>';
                        }
                        else
                        {
                            $wpCron = '<div class="system-success"><span class="dashicons dashicons-yes"></span></div>';
                        }

                        $wordpressEnvironment = array(
                            1 => array(
                                __( "Home URL", "subscribe-reloaded" ),
                                get_option( 'home' )
                            ),
                            2 => array(
                                __( "Site URL", "subscribe-reloaded" ),
                                get_option( 'siteurl' )
                            ),
                            3 => array(
                                __( "WordPress Version", "subscribe-reloaded" ),
                                get_bloginfo( 'version' )
                            ),
                            4 => array(
                                "Multisite",
                                is_multisite() ? '<span class="dashicons dashicons-yes"></span>' :  'No'
                            ),
                            5 => array(
                                __( "Memory Limit", "subscribe-reloaded" ),
                                $memoryValue
                            ),
                            6 => array(
                                __( "WP Debug Mode", "subscribe-reloaded" ),
                                $wpDebug
                            ),
                            7 => array(
                                __( "WP Cron", "subscribe-reloaded" ),
                                $wpDebug
                            ),
                            8 => array(
                                __( "Language", "subscribe-reloaded" ),
                                get_locale()
                            ),
                            9 => array(
                                __( "Permalink Structure", "subscribe-reloaded" ),
                                esc_html( get_option( 'permalink_structure' ) )
                            ),
                            10 => array(
                                __( "Table Prefix", "subscribe-reloaded" ),
                                esc_html( $wpdb->prefix )
                            ),
                            11 => array(
                                __( "Table Prefix Length", "subscribe-reloaded" ),
                                esc_html( strlen( $wpdb->prefix ) )
                            ),
                            12 => array(
                                __( "Table Prefix Status", "subscribe-reloaded" ),
                                strlen( $wpdb->prefix ) > 16 ? esc_html( 'Error: Too long', 'subscribe-reloaded' ) : esc_html( 'Acceptable', 'subscribe-reloaded' )
                            ),
                            13 => array(
                                __( "Registered Post Statuses", "subscribe-reloaded" ),
                                esc_html( implode( ', ', get_post_stati() ) )
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

                    <table class="table table-sm table-hover table-striped subscribers-table" style="font-size: 0.8em">
                        <thead>
                        <th style="textalilfe" class="text-left" colspan="2"><?php _e( 'Server Environment', 'subscribe-reloaded' ) ?></th>
                        </thead>
                        <?php

                        $tlsCheck      = false;
                        $tlsCheckValue = __( 'Cannot Evaluate', 'subscribe-reloaded' );
                        $tlsRating     = __( 'Not Available', 'subscribe-reloaded' );
                        $phpVersion    = __( 'Not Available', 'subscribe-reloaded' );
                        $cURLVersion   = __( 'Not Available', 'subscribe-reloaded' );
                        $MySQLSVersion = __( 'Not Available', 'subscribe-reloaded' );
                        $defaultTimezone = __( 'Not Available', 'subscribe-reloaded' );

                        // Get the SSL status.
                        if ( ini_get( 'allow_url_fopen' ) ) {
                            $tlsCheck = file_get_contents( 'https://www.howsmyssl.com/a/check' );
                        }

                        if ( false !== $tlsCheck )
                        {
                            $tlsCheck = json_decode( $tlsCheck );
                            /* translators: %s: SSL connection response */
                            $tlsCheckValue = sprintf( __( 'Connection uses %s', 'subscribe-reloaded' ), esc_html( $tlsCheck->tls_version ) );
                        }
                        // Check TSL Rating
                        if ( false !== $tlsCheck )
                        {
                            $tlsRating = property_exists( $tlsCheck, 'rating' ) ? $tlsCheck->rating : $tlsCheck->tls_version;
                        }
                        // Check the PHP Version
                        if ( function_exists( 'phpversion' ) )
                        {
                            $phpVersion = phpversion();

                            if ( version_compare( $phpVersion, '5.6', '<' ) )
                            {
                                $phpVersion = '<div class="system-error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s - We recommend a minimum PHP version of 5.6. See: %s', 'subscribe-reloaded' ), esc_html( $phpVersion ), '<a href="http://docs.givewp.com/settings-system-info" target="_blank">' . __( 'PHP Requirements in Give', 'subscribe-reloaded' ) . '</a>' ) . '</div>';
                            }
                            else
                            {
                                $phpVersion = '<div class="system-success">' . esc_html( $phpVersion ) . '</div>';
                            }
                        }
                        else
                        {
                            $phpVersion = __( "Couldn't determine PHP version because the function phpversion() doesn't exist.", 'subscribe-reloaded' );
                        }
                        // Check the cURL Version
                        if ( function_exists( 'curl_version' ) )
                        {
                            $cURLVersion = curl_version();

                            if ( version_compare( $cURLVersion['version'], '7.40', '<' ) )
                            {
                                $cURLVersion = '<div class="system-error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s - We recommend a minimum cURL version of 7.40.', 'subscribe-reloaded' ), esc_html( $cURLVersion['version'] . ', ' . $cURLVersion['ssl_version'] ) ) . '</div>';
                            }
                            else
                            {
                                $cURLVersion = '<div class="system-success">' . esc_html( $cURLVersion ) . '</div>';
                            }
                        }
                        else
                        {
                            $cURLVersion = '&ndash;';
                        }
                        // Check MySQL Version
                        if ( $wpdb->use_mysqli )
                        {
                            $ver = mysqli_get_server_info( $wpdb->dbh );
                        }
                        else
                        {
                            if( function_exists( 'mysql_get_server_info' ) )
                            {
                                $ver = mysql_get_server_info();
                            }
                        }

                        if ( ! empty( $wpdb->is_mysql ) && ! stristr( $ver, 'MariaDB' ) )
                        {
                            $MySQLSVersion = $wpdb->db_version();

                            if ( version_compare( $MySQLSVersion, '5.6', '<' ) )
                            {
                                $MySQLSVersion = '<div class="system-error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( '%s - We recommend a minimum MySQL version of 5.6. See: %s', 'subscribe-reloaded' ), esc_html( $MySQLSVersion ), '<a href="https://wordpress.org/about/requirements/" target="_blank">' . __( 'WordPress Requirements', 'subscribe-reloaded' ) . '</a>' ) . '</div>';
                            }
                            else
                            {
                                $MySQLSVersion = '<div class="system-success">' . esc_html( $MySQLSVersion ) . '</div>';
                            }
                        }
                        // Get the Timezone
                        $defaultTimezone = date_default_timezone_get();

                        if ( 'UTC' !== $defaultTimezone )
                        {
                            $defaultTimezone = '<div class="system-error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'Default timezone is %s - it should be UTC', 'subscribe-reloaded' ), $defaultTimezone ) . '</div>';
                        }
                        else
                        {
                            $defaultTimezone = '<div class="system-success"><span class="dashicons dashicons-yes"></span></div>';
                        }
                        // DOMDocument
                        $DOMDocument = __( 'Not Available', 'subscribe-reloaded' );
                        if ( class_exists( 'DOMDocument' ) )
                        {
                            $DOMDocument = '<div class="system-success"><span class="dashicons dashicons-yes"></span></div>';
                        }
                        else {
                            $DOMDocument = sprintf( __( 'Your server does not have the %s class enabled - HTML/Multipart emails, and also some extensions, will not work without DOMDocument.', 'subscribe-reloaded' ), '<a href="https://php.net/manual/en/class.domdocument.php">DOMDocument</a>' );
                        }
                        // Check gzip
                        $gzip = __( 'Not Available', 'subscribe-reloaded' );
                        if ( is_callable( 'gzopen' ) )
                        {
                            $gzip = '<div class="system-success"><span class="dashicons dashicons-yes"></span></div>';
                        }
                        else {
                            $gzip = sprintf( __( 'Your server does not support the %s function - this is used for file compression and decompression.', 'subscribe-reloaded' ), '<a href="https://php.net/manual/en/zlib.installation.php">gzopen</a>' );
                        }// Check GD
                        $gd = __( 'Not Available', 'subscribe-reloaded' );
                        if ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) )
                        {
                            $gd = '<div class="system-success"><span class="dashicons dashicons-yes"></span></div>';
                        }
                        else {
                            $gd = '<div class="system-error"><span class="dashicons dashicons-no"></span></div>';;
                        }

                        // Define array of values
                        $serverEnvironment = array(
                            1 => array(
                                __( "TLS Connection", "subscribe-reloaded" ),
                                $tlsCheckValue
                            ),
                            2 => array(
                                __( "TLS Rating", "subscribe-reloaded" ),
                                $tlsRating
                            ),
                            3 => array(
                                __( "Server Info", "subscribe-reloaded" ),
                                esc_html( $_SERVER['SERVER_SOFTWARE'] )
                            ),
                            4 => array(
                                __( "PHP Version", "subscribe-reloaded" ),
                                $phpVersion
                            ),
                            5 => array(
                                __( "PHP Post Max Size", "subscribe-reloaded" ),
                                size_format( $wp_subscribe_reloaded->stcr->utils->to_num_ini_notation( ini_get( 'post_max_size' ) ) )
                            ),
                            6 => array(
                                __( "PHP Max Execution Time", "subscribe-reloaded" ),
                                ini_get( 'max_execution_time' )
                            ),
                            7 => array(
                                __( "PHP Max Input Vars", "subscribe-reloaded" ),
                                ini_get( 'max_input_vars' )
                            ),
                            8 => array(
                                __( "PHP Max Upload Size", "subscribe-reloaded" ),
                                size_format( wp_max_upload_size() )
                            ),
                            9 => array(
                                __( "cURL Version", "subscribe-reloaded" ),
                                $cURLVersion
                            ),
                            10 => array(
                                __( "MySQL Version", "subscribe-reloaded" ),
                                $MySQLSVersion
                            ),
                            11 => array(
                                __( "Default Timezone is UTC", "subscribe-reloaded" ),
                                $defaultTimezone
                            ),
                            12 => array(
                                __( "DOMDocument", "subscribe-reloaded" ),
                                $DOMDocument
                            ),
                            13 => array(
                                __( "gzip", "subscribe-reloaded" ),
                                $gzip
                            ),
                            14 => array(
                                __( "GD Graphics Library", "subscribe-reloaded" ),
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

                    <!-- Other Active Plugins -->

                    <table class="table table-sm table-hover table-striped subscribers-table" style="font-size: 0.8em">
                        <thead>
                        <th style="textalilfe" class="text-left" colspan="2"><?php _e( 'Other Active Plugins', 'subscribe-reloaded' ) ?></th>
                        </thead>
                        <?php

                        $tlsCheck      = false;
                        $tlsCheckValue = __( 'Cannot Evaluate', 'subscribe-reloaded' );
                        $tlsRating     = __( 'Not Available', 'subscribe-reloaded' );
                        $phpVersion    = __( 'Not Available', 'subscribe-reloaded' );
                        $cURLVersion   = __( 'Not Available', 'subscribe-reloaded' );
                        $MySQLSVersion = __( 'Not Available', 'subscribe-reloaded' );
                        $defaultTimezone = __( 'Not Available', 'subscribe-reloaded' );

                        // Define array of values
                        $activePlugins = array(
                            1 => array(
                                __( "TLS Connection", "subscribe-reloaded" ),
                                $tlsCheckValue
                            )
                        );

                        // Get the SSL status.
                        if ( ini_get( 'allow_url_fopen' ) ) {
                            $tlsCheckValue = file_get_contents( 'https://www.howsmyssl.com/a/check' );
                        }
                        ?>
                        <tbody>
                        <?php
                        foreach ( $activePlugins as $key => $opt )
                        {
                            echo "<tr>";
                            echo "<td class='text-left' style='min-width: 50px;'>{$opt[0]}</td>";
                            echo "<td class='text-left'>{$opt[1]}</td>";
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>
<!--                    <textarea style="width:90%; min-height:300px;" readonly>--><?php //echo serialize( $stcr_options_array ); ?><!--</textarea>-->

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