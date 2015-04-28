<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}

$faulty_fields = '';

if ( array_key_exists( "generate_key", $_POST ) ) {
	global $wp_subscribe_reloaded;
	$unique_key = $wp_subscribe_reloaded->generate_key();
	subscribe_reloaded_update_option( 'unique_key', $unique_key, 'text' );

	// Display an alert in the admin interface if something went wrong
	echo '<div class="updated fade"><p>';
	if ( empty( $faulty_fields ) ) {
		_e( 'Your settings have been successfully updated.', 'subscribe-reloaded' );
	} else {
		_e( 'There was an error updating the following fields:', 'subscribe-reloaded' );
		echo ' <strong>' . substr( $faulty_fields, 0, - 2 ) . '</strong>';
	}
	echo "</p></div>\n";
} else {
	// Update options
	if ( isset( $_POST['options'] ) ) {
		if ( isset( $_POST['options']['purge_days'] ) && ! subscribe_reloaded_update_option( 'purge_days', $_POST['options']['purge_days'], 'integer' ) ) {
			$faulty_fields = __( 'Autopurge requests', 'subscribe-reloaded' ) . ', ';
		}
		if ( isset( $_POST['options']['enable_double_check'] ) && ! subscribe_reloaded_update_option( 'enable_double_check', $_POST['options']['enable_double_check'], 'yesno' ) ) {
			$faulty_fields = __( 'Enable double check', 'subscribe-reloaded' ) . ', ';
		}
		if ( isset( $_POST['options']['notify_authors'] ) && ! subscribe_reloaded_update_option( 'notify_authors', $_POST['options']['notify_authors'], 'yesno' ) ) {
			$faulty_fields = __( 'Subscribe authors', 'subscribe-reloaded' ) . ', ';
		}
		if ( isset( $_POST['options']['enable_html_emails'] ) && ! subscribe_reloaded_update_option( 'enable_html_emails', $_POST['options']['enable_html_emails'], 'yesno' ) ) {
			$faulty_fields = __( 'Enable HTML emails', 'subscribe-reloaded' ) . ', ';
		}
		if ( isset( $_POST['options']['htmlify_message_links'] ) && ! subscribe_reloaded_update_option( 'htmlify_message_links', $_POST['options']['htmlify_message_links'], 'yesno' ) ) {
			$faulty_fields = __( 'HTMLify Links in HTML emails', 'subscribe-reloaded' ) . ', ';
		}
		if ( isset( $_POST['options']['process_trackbacks'] ) && ! subscribe_reloaded_update_option( 'process_trackbacks', $_POST['options']['process_trackbacks'], 'yesno' ) ) {
			$faulty_fields = __( 'Send trackbacks', 'subscribe-reloaded' ) . ', ';
		}
		if ( isset( $_POST['options']['enable_admin_messages'] ) && ! subscribe_reloaded_update_option( 'enable_admin_messages', $_POST['options']['enable_admin_messages'], 'yesno' ) ) {
			$faulty_fields = __( 'Notify admin', 'subscribe-reloaded' ) . ', ';
		}
		if ( isset( $_POST['options']['admin_subscribe'] ) && ! subscribe_reloaded_update_option( 'admin_subscribe', $_POST['options']['admin_subscribe'], 'yesno' ) ) {
			$faulty_fields = __( 'Let admin subscribe', 'subscribe-reloaded' ) . ', ';
		}
		if ( isset( $_POST['options']['admin_bcc'] ) && ! subscribe_reloaded_update_option( 'admin_bcc', $_POST['options']['admin_bcc'], 'yesno' ) ) {
			$faulty_fields = __( 'BCC admin on Notifications', 'subscribe-reloaded' ) . ', ';
		}
		// Display an alert in the admin interface if something went wrong
		echo '<div class="updated fade"><p>';
		if ( empty( $faulty_fields ) ) {
			_e( 'Your settings have been successfully updated.', 'subscribe-reloaded' );
		} else {
			_e( 'There was an error updating the following fields:', 'subscribe-reloaded' );
			echo ' <strong>' . substr( $faulty_fields, 0, - 2 ) . '</strong>';
		}
		echo "</p></div>\n";
	}
}


wp_print_scripts( 'quicktags' );
?>
<form action="admin.php?page=subscribe-to-comments-reloaded/options/index.php&subscribepanel=<?php echo $current_panel ?>" method="post">
	<table class="form-table <?php echo $wp_locale->text_direction ?>">
		<tr>
			<th scope="row"><label for="purge_days"><?php _e( 'Autopurge requests', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<input type="text" name="options[purge_days]" id="purge_days" value="<?php echo subscribe_reloaded_get_option( 'purge_days' ); ?>" size="10"> <?php _e( 'days', 'subscribe-reloaded' ) ?>
				<div class="description"><?php _e( "Delete pending subscriptions (not confirmed) after X days. Zero disables this feature.", 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="enable_double_check"><?php _e( 'Enable double check', 'subscribe-reloaded' ) ?></label></th>
			<td>
				<input type="radio" name="options[enable_double_check]" id="enable_double_check" value="yes"<?php echo ( subscribe_reloaded_get_option( 'enable_double_check' ) == 'yes' ) ? ' checked="checked"' : ''; ?>> <?php _e( 'Yes', 'subscribe-reloaded' ) ?> &nbsp; &nbsp; &nbsp;
				<input type="radio" name="options[enable_double_check]" value="no" <?php echo ( subscribe_reloaded_get_option( 'enable_double_check' ) == 'no' ) ? '  checked="checked"' : ''; ?>> <?php _e( 'No', 'subscribe-reloaded' ) ?>
				<div class="description"><?php _e( 'Send a notification email to confirm the subscription (to avoid addresses misuse).', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="notify_authors"><?php _e( 'Subscribe authors', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<input type="radio" name="options[notify_authors]" id="notify_authors" value="yes"<?php echo ( subscribe_reloaded_get_option( 'notify_authors' ) == 'yes' ) ? ' checked="checked"' : ''; ?>> <?php _e( 'Yes', 'subscribe-reloaded' ) ?> &nbsp; &nbsp; &nbsp;
				<input type="radio" name="options[notify_authors]" value="no" <?php echo ( subscribe_reloaded_get_option( 'notify_authors' ) == 'no' ) ? '  checked="checked"' : ''; ?>> <?php _e( 'No', 'subscribe-reloaded' ) ?>
				<div class="description"><?php _e( 'Automatically subscribe authors to their own articles (not retroactive).', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="enable_html_emails"><?php _e( 'Enable HTML emails', 'subscribe-reloaded' ) ?></label></th>
			<td>
				<input type="radio" name="options[enable_html_emails]" id="enable_html_emails" value="yes"<?php echo ( subscribe_reloaded_get_option( 'enable_html_emails' ) == 'yes' ) ? ' checked="checked"' : ''; ?>> <?php _e( 'Yes', 'subscribe-reloaded' ) ?> &nbsp; &nbsp; &nbsp;
				<input type="radio" name="options[enable_html_emails]" value="no" <?php echo ( subscribe_reloaded_get_option( 'enable_html_emails' ) == 'no' ) ? '  checked="checked"' : ''; ?>> <?php _e( 'No', 'subscribe-reloaded' ) ?>
				<div class="description"><?php _e( 'If enabled, will send email messages with content-type = text/html instead of text/plain', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="htmlify_message_links"><?php _e( 'HTMLify links in emails', 'wp-comment-subscriptions' ) ?></label>
			</th>
			<td>
				<input type="radio" name="options[htmlify_message_links]" id="htmlify_message_links" value="yes"<?php echo ( subscribe_reloaded_get_option( 'htmlify_message_links' ) == 'yes' ) ? ' checked="checked"' : ''; ?>> <?php _e( 'Yes', 'subscribe-reloaded' ) ?> &nbsp; &nbsp; &nbsp;
				<input type="radio" name="options[htmlify_message_links]" value="no" <?php echo ( subscribe_reloaded_get_option( 'htmlify_message_links' ) == 'no' ) ? '  checked="checked"' : ''; ?>> <?php _e( 'No', 'subscribe-reloaded' ) ?>
				<div class="description"><?php _e( 'If enabled, will wrap all links in messages with <code>&lt;a href=""&gt;&lt;/a&gt;</code> (only when HTML emails enabled).', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="process_trackbacks"><?php _e( 'Process trackbacks', 'subscribe-reloaded' ) ?></label></th>
			<td>
				<input type="radio" name="options[process_trackbacks]" id="process_trackbacks" value="yes"<?php echo ( subscribe_reloaded_get_option( 'process_trackbacks' ) == 'yes' ) ? ' checked="checked"' : ''; ?>> <?php _e( 'Yes', 'subscribe-reloaded' ) ?> &nbsp; &nbsp; &nbsp;
				<input type="radio" name="options[process_trackbacks]" value="no" <?php echo ( subscribe_reloaded_get_option( 'process_trackbacks' ) == 'no' ) ? '  checked="checked"' : ''; ?>> <?php _e( 'No', 'subscribe-reloaded' ) ?>
				<div class="description"><?php _e( 'Notify users when a new trackback or pingback is added to the discussion.', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="enable_admin_messages"><?php _e( 'Track all subscriptions', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<input type="radio" name="options[enable_admin_messages]" id="enable_admin_messages" value="yes"<?php echo ( subscribe_reloaded_get_option( 'enable_admin_messages' ) == 'yes' ) ? ' checked="checked"' : ''; ?>> <?php _e( 'Yes', 'subscribe-reloaded' ) ?> &nbsp; &nbsp; &nbsp;
				<input type="radio" name="options[enable_admin_messages]" value="no" <?php echo ( subscribe_reloaded_get_option( 'enable_admin_messages' ) == 'no' ) ? '  checked="checked"' : ''; ?>> <?php _e( 'No', 'subscribe-reloaded' ) ?>
				<div class="description"><?php _e( 'Notify the administrator when users subscribe without commenting.', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="admin_subscribe"><?php _e( 'Let admin subscribe', 'subscribe-reloaded' ) ?></label></th>
			<td>
				<input type="radio" name="options[admin_subscribe]" id="admin_subscribe" value="yes"<?php echo ( subscribe_reloaded_get_option( 'admin_subscribe' ) == 'yes' ) ? ' checked="checked"' : ''; ?>> <?php _e( 'Yes', 'subscribe-reloaded' ) ?> &nbsp; &nbsp; &nbsp;
				<input type="radio" name="options[admin_subscribe]" value="no" <?php echo ( subscribe_reloaded_get_option( 'admin_subscribe' ) == 'no' ) ? '  checked="checked"' : ''; ?>> <?php _e( 'No', 'subscribe-reloaded' ) ?>
				<div class="description"><?php _e( 'Let the administrator subscribe to comments when logged in.', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="admin_bcc"><?php _e( 'BCC admin on Notifications', 'subscribe-reloaded' ) ?></label></th>
			<td>
				<input type="radio" name="options[admin_bcc]" id="admin_bcc" value="yes"<?php echo ( subscribe_reloaded_get_option( 'admin_bcc' ) == 'yes' ) ? ' checked="checked"' : ''; ?>> <?php _e( 'Yes', 'subscribe-reloaded' ) ?> &nbsp; &nbsp; &nbsp;
				<input type="radio" name="options[admin_bcc]" value="no" <?php echo ( subscribe_reloaded_get_option( 'admin_bcc' ) == 'no' ) ? '  checked="checked"' : ''; ?>> <?php _e( 'No', 'subscribe-reloaded' ) ?>
				<div class="description"><?php _e( 'Send a copy of all Notifications to the administrator.', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="admin_bcc"><?php _e( 'StCR Unique Key', 'subscribe-reloaded' ) ?></label></th>
			<td>
				<?php
				if ( subscribe_reloaded_get_option( 'unique_key' ) == "" ) :
					_e(
						"This Unique Key is not set, please click the following button to ",
						'subscribe-reloaded'
					);
					?>
					<input type="submit" value="<?php _e( 'Generate' ) ?>" class="button-primary" size="6" name="generate_key">
				<?php
				else :
					?>
					<input type="text" name="options[uk_key]" id="uk_key"
						   value="<?php echo subscribe_reloaded_get_option( 'unique_key' ); ?>" size="35" disabled>
					<div class="description">
						<?php _e(
							"This Unique Key will be use to send the notification to your subscribers with more
																security.",
							'subscribe-reloaded'
						); ?></div>
					<input type="submit" value="<?php _e( 'Generate' ) ?>" class="button-primary" size="6" name="generate_key" style="background-color: #D54E21;border-color: #B34B28;">
				<?php
				endif;
				?>
			</td>
		</tr>
		</tbody>
	</table>
	<p class="submit"><input type="submit" value="<?php _e( 'Save Changes' ) ?>" class="button-primary" name="Submit">
	</p>
</form>
