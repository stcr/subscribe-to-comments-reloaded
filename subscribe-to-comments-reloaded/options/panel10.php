<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}

global $wpdb;
global $wp_subscribe_reloaded;
$unique_key = $wp_subscribe_reloaded->stcr->utils->generate_key();
$faulty_fields = '';

$stcr_options = $wpdb->get_results('SELECT * FROM '. $wpdb->prefix . 'options WHERE option_name LIKE "subscribe_reloaded%"');

$stcr_options_str = "";

foreach ($stcr_options as $option) {
	$stcr_options_str .= "{$option->option_name}: {$option->option_value}\n";
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
	echo "<div class='notice $message_type fade'><p>";
	echo 	$message;
	// echo 	"<br><pre>$file_path$file_name</pre>";
	echo "</p></div>\n";
} else {
	// echo "<pre>Option selected ";
	// 		 print_r($_POST['options']);
	// 		 echo "</pre>";
	// Update options
	if ( isset( $_POST['options'] ) ) {
		if ( isset( $_POST['options']['enable_log_data'] ) && ! subscribe_reloaded_update_option( 'enable_log_data', $_POST['options']['enable_log_data'], 'yesno' ) ) {
			$faulty_fields = __( 'Enable Log Information', 'subscribe-reloaded' ) . ', ';
		}

		if ( isset( $_POST['options']['auto_clean_log_data'] )
				&& ! (subscribe_reloaded_update_option( 'auto_clean_log_data', $_POST['options']['auto_clean_log_data'], 'yesno' )
					&& subscribe_reloaded_update_option( 'auto_clean_log_frecuency', $_POST['options']['auto_clean_log_frecuency']) ) ) {
			$faulty_fields = __( 'Enable Auto clean log data', 'subscribe-reloaded' ) . ', ';
		}
		else if ( isset( $_POST['options']['auto_clean_log_data'] ) &&  $_POST['options']['auto_clean_log_data'] === "yes" )
		{
			// // Schedule the auto purge for the log file.
			if ( ! wp_next_scheduled( '_cron_log_file_purge' ) ) {
				$log_purger_recurrence = get_option( "subscribe_reloaded_auto_clean_log_frecuency" );
				wp_clear_scheduled_hook( '_cron_log_file_purge' );
				// Let us bind the schedule event with our desire action.
				wp_schedule_event( time() + 15, $log_purger_recurrence, '_cron_log_file_purge' );
			}
		}
		else if ( isset( $_POST['options']['auto_clean_log_data'] ) &&  $_POST['options']['auto_clean_log_data'] === "no" )
		{
			// Delete a Schedule event
			wp_clear_scheduled_hook( '_cron_log_file_purge' );
		}

		// Display an alert in the admin interface if something went wrong
		echo '<div class="notice notice-success is-dismissible "><p>';
		if ( empty( $faulty_fields ) ) {
			_e( 'Your settings have been successfully updated.', 'subscribe-reloaded' );
		} else {
			_e( 'There was an error updating the following fields:', 'subscribe-reloaded' );
			echo ' <strong>' . substr( $faulty_fields, 0, - 2 ) . '</strong>';
		}
		echo "</p></div>\n";
	}
}
?>

<div class="donate-panel">

<h3>StCR Tools</h3>
<form action="" method="post">
	<table class="form-table <?php echo $wp_locale->text_direction ?>">
		<tr>
			<th scope="row">
				<label for="enable_log_data"><?php _e( 'Enable Log Information', 'subscribe-reloaded' ) ?></label></th>
			<td>
				<input type="radio" name="options[enable_log_data]" id="enable_log_data" value="yes"<?php echo ( subscribe_reloaded_get_option( 'enable_log_data' ) == 'yes' ) ? ' checked="checked"' : ''; ?>> <?php _e( 'Yes', 'subscribe-reloaded' ) ?> &nbsp; &nbsp; &nbsp;
				<input type="radio" name="options[enable_log_data]" value="no" <?php echo ( subscribe_reloaded_get_option( 'enable_log_data' ) == 'no' ) ? '  checked="checked"' : ''; ?>> <?php _e( 'No', 'subscribe-reloaded' ) ?>
				<div class="description"><?php _e( 'If enabled, will log information of the plugin. Helpful for debugging purposes.', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="auto_clean_log_data"><?php _e( 'Enable Auto clean log data', 'subscribe-reloaded' ) ?></label></th>
			<td>
				<input class="auto_clean_log_data" type="radio" name="options[auto_clean_log_data]" id="auto_clean_log_data" value="yes"<?php echo ( subscribe_reloaded_get_option( 'auto_clean_log_data' ) == 'yes' ) ? ' checked="checked"' : ''; ?>> <?php _e( 'Yes', 'subscribe-reloaded' ) ?> &nbsp; &nbsp; &nbsp;
				<input class="auto_clean_log_data" type="radio" name="options[auto_clean_log_data]" value="no" <?php echo ( subscribe_reloaded_get_option( 'auto_clean_log_data' ) == 'no' ) ? '  checked="checked"' : ''; ?>> <?php _e( 'No', 'subscribe-reloaded' ) ?>

				<select class="auto_clean_log_frecuency" name="options[auto_clean_log_frecuency]">
					<option value="hourly" <?php echo ( subscribe_reloaded_get_option( 'auto_clean_log_frecuency' ) === 'hourly' ) ? "selected='selected'" : ''; ?>><?php _e( 'Hourly', 'subscribe-reloaded' ); ?></option>
					<option value="twicedaily" <?php echo ( subscribe_reloaded_get_option( 'auto_clean_log_frecuency' ) === 'twicedaily' ) ? "selected='selected'" : ''; ?>><?php _e( 'Twice Daily', 'subscribe-reloaded' ); ?></option>
					<option value="daily" <?php echo ( subscribe_reloaded_get_option( 'auto_clean_log_frecuency' ) === 'daily' ) ? "selected='selected'" : ''; ?>><?php _e( 'Daily', 'subscribe-reloaded' ); ?></option>
				</select>
				<div class="description"><?php _e( 'If enabled, StCR will auto clean your information every day.', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label><?php _e( 'Clean Up Log Archive', 'subscribe-reloaded' ) ?></label></th>
			<td>
				<div class="description">
					<?php _e(
						"If you want to clean up the log archive please click the following button",
						'subscribe-reloaded'
					); ?> <input type="submit" value="<?php _e( 'Clean' ) ?>" class="button-primary" size="6" name="purge_log" style="background-color: #D54E21;border-color: #B34B28;text-shadow: none; margin-left: 10px;">
				</div>
			</td>
		</tr>
	</table>


<h3><?php _e( 'System Information', 'subscribe-reloaded' ) ?></h3>


<p style="font-size: 14px;"><?php _e( "System Information", 'subscribe-reloaded' ) ?></p>

<textarea style="width:90%; min-height:300px;">
<?php echo $stcr_options_str; ?>
.
.
Custom_Post_Type: <?php $postTypes = get_post_types( '', 'names' ); echo implode("| ", $postTypes ); ?>

Permalink_Structure: <?php echo get_option('permalink_structure'); ?>
</textarea>

<hr>

<hr>
		<p class="submit"><input type="submit" value="<?php _e( 'Save Changes' ) ?>" class="button-primary" name="Submit">
			</p>
	</form>
</div>