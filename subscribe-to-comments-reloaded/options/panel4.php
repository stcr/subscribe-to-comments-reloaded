<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}

$is_html_enabled = subscribe_reloaded_get_option( 'enable_html_emails', 'no' ) == 'yes' ? true : false;
// Update options
if ( isset( $_POST['options'] ) ) {
	$faulty_fields = '';
	if ( isset( $_POST['options']['from_name'] ) &&
		! subscribe_reloaded_update_option( 'from_name', $_POST['options']['from_name'], 'text' )
	) {
		$faulty_fields = __( 'Sender name', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['from_email'] ) &&
		! subscribe_reloaded_update_option( 'from_email', $_POST['options']['from_email'], 'text' )
	) {
		$faulty_fields = __( 'Sender email address', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['reply_to'] ) &&
		! subscribe_reloaded_update_option( 'reply_to', $_POST['options']['reply_to'], 'text' )
	) {
		$faulty_fields = __( 'Sender email address', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['notification_subject'] ) &&
		! subscribe_reloaded_update_option( 'notification_subject', $_POST['options']['notification_subject'], 'text' )
	) {
		$faulty_fields = __( 'Notification subject', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['notification_content'] ) &&
		trim( $_POST['options']['notification_content'] ) == false &&
		! subscribe_reloaded_update_option( 'notification_content', "<h1>There is a new comment on [post_title].</h1><hr><p><strong>Comment link:</strong>&nbsp;<a href=\"[comment_permalink]\" data-mce-href=\"[comment_permalink]\">[comment_permalink]</a>&nbsp;<br><strong>Author:</strong>&nbsp;[comment_author]</p><p><strong>Comment:</strong><br>[comment_content]</p><div style=\"font-size: 0.8em\" data-mce-style=\"font-size: 0.8em;\"><strong>Permalink:</strong>&nbsp;<a href=\"[post_permalink]\" data-mce-href=\"[post_permalink]\">[post_permalink]</a><br><a href=\"[manager_link]\" data-mce-href=\"[manager_link]\">Manage your subscriptions</a>&nbsp;|&nbsp;<a href=\"[oneclick_link]\" data-mce-href=\"[oneclick_link]\">One click unsubscribe</a></div>", 'text-no-encode' )
	) {
		$faulty_fields = __( 'Notification message', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['notification_content'] ) &&
		trim( $_POST['options']['notification_content'] )  &&
		! subscribe_reloaded_update_option( 'notification_content', $_POST['options']['notification_content'], 'text-no-encode' )
	) {
		$faulty_fields = __( 'Notification message', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['double_check_subject'] ) &&
		! subscribe_reloaded_update_option( 'double_check_subject', $_POST['options']['double_check_subject'], 'text' )
	) {
		$faulty_fields = __( 'Double check subject', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['double_check_content'] ) &&
		! subscribe_reloaded_update_option( 'double_check_content', $_POST['options']['double_check_content'], 'text' )
	) {
		$faulty_fields = __( 'Double check message', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['management_subject'] ) &&
		! subscribe_reloaded_update_option( 'management_subject', $_POST['options']['management_subject'], 'text' )
	) {
		$faulty_fields = __( 'Management subject', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['management_content'] ) &&
		! subscribe_reloaded_update_option( 'management_content', $_POST['options']['management_content'], 'text' )
	) {
		$faulty_fields = __( 'Management message', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['oneclick_text'] ) &&
		! subscribe_reloaded_update_option( 'oneclick_text', $_POST['options']['oneclick_text'], 'text' )
	) {
		$faulty_fields = __( 'Management message', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['management_email_content'] ) &&
		! subscribe_reloaded_update_option( 'management_email_content', $_POST['options']['management_email_content'], 'text' )
	) {
		$faulty_fields = __( 'Management Email message', 'subscribe-reloaded' ) . ', ';
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
wp_print_scripts( 'quicktags' );

?>
<form action="" method="post">
	<h3><?php _e( 'Options', 'subscribe-reloaded' ) ?></h3>
	<table class="form-table <?php echo $wp_locale->text_direction ?>">
		<tbody>
		<tr>
			<th scope="row">
				<label for="from_name"><?php _e( 'Sender name', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<input type="text" name="options[from_name]" id="from_name"
					   value="<?php echo subscribe_reloaded_get_option( 'from_name' ); ?>" size="50">

				<div class="description">
					<?php _e( 'Name to use for the "from" field when sending a new notification to the user.', 'subscribe-reloaded' ); ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="from_email"><?php _e( 'Sender email address', 'subscribe-reloaded' ) ?>
				</label>
			</th>
			<td>
				<input type="text" name="options[from_email]" id="from_email"
					   value="<?php echo subscribe_reloaded_get_option( 'from_email' ); ?>" size="50">

				<div class="description">
					<?php _e( 'Email address to use for the "from" field when sending a new notification to the user.', 'subscribe-reloaded' ); ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="reply_to"><?php _e( 'Reply To', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<input type="text" name="options[reply_to]" id="reply_to"
					   value="<?php echo subscribe_reloaded_get_option( 'reply_to' ); ?>" size="50">

				<div class="description">
					<?php _e( 'This will be use when the user click reply on their email agent. If not set will be the same as the Sender email address.', 'subscribe-reloaded' ); ?>
				</div>
			</td>
		</tr>
		</tbody>
	</table>

	<h3><?php _e( 'Messages', 'subscribe-reloaded' ) ?></h3>
	<table class="form-table <?php echo $wp_locale->text_direction ?>">
		<tbody>
		<tr>
			<th scope="row">
				<label for="notification_subject"><?php _e( 'Notification subject', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<input type="text" name="options[notification_subject]" id="notification_subject"
					   value="<?php echo subscribe_reloaded_get_option( 'notification_subject' ); ?>" size="70">

				<div class="description">
					<?php _e( 'Subject of the notification email. Allowed tag: [post_title]', 'subscribe-reloaded' ); ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="notificationContent"><?php _e( 'Notification message', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<?php
					$args_notificationContent = array(
						"media_buttons" => false,
						"textarea_rows" => 15,
						"teeny"         => true,
						"textarea_name" => "options[notification_content]"
						// "tinymce"		=> array(
						// 						"theme_advance_buttons1" => "bold, italic, ul, min_size, max_size"
						// 					)
					);

					wp_editor( subscribe_reloaded_get_option( 'notification_content' ), "notificationContent", $args_notificationContent );
				?>
				<div class="description" style="padding-top:0">
					<?php _e( 'Content of the notification email. Allowed tags: [post_title], [comment_permalink], [comment_author], [comment_content], [post_permalink], [manager_link], [comment_gravatar]', 'subscribe-reloaded' ); ?>
					<?php _e( '<p><strong>Note: To get a default template clear all the content and save the options.</strong></p>', 'subscribe-reloaded' ); ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="double_check_subject"><?php _e( 'Double check subject', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<input type="text" name="options[double_check_subject]" id="double_check_subject"
					   value="<?php echo subscribe_reloaded_get_option( 'double_check_subject' ); ?>" size="70">

				<div class="description" style="padding-top:0">
					<?php _e( 'Subject of the confirmation email. Allowed tag: [post_title]', 'subscribe-reloaded' ); ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="double_check_content"><?php _e( 'Double check message', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<?php
					$id_double_check_content = "double_check_content";
					$args_notificationContent = array(
						"media_buttons" => false,
						"textarea_rows" => 7,
						"teeny"         => true,
						"textarea_name" => "options[{$id_double_check_content}]"
					);
					wp_editor( subscribe_reloaded_get_option( $id_double_check_content ), $id_double_check_content, $args_notificationContent );
				?>
				<div class="description" style="padding-top:0">
					<?php _e( 'Content of the confirmation email. Allowed tags: [post_permalink], [confirm_link], [post_title], [manager_link]', 'subscribe-reloaded' ); ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="management_subject"><?php _e( 'Management subject', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<input type="text" name="options[management_subject]" id="management_subject"
					   value="<?php echo subscribe_reloaded_get_option( 'management_subject' ); ?>" size="70">

				<div class="description" style="padding-top:0">
					<?php _e( 'Subject of the mail sent to those who request to access their management page. Allowed tag: [blog_name]', 'subscribe-reloaded' ); ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="management_content"><?php _e( 'Management Page message', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<?php
					$id_management_content = "management_content";
					$args_notificationContent = array(
						"media_buttons" => false,
						"textarea_rows" => 5,
						"teeny"         => true,
						"textarea_name" => "options[{$id_management_content}]"
					);
					wp_editor( subscribe_reloaded_get_option( $id_management_content ), $id_management_content, $args_notificationContent );
				?>
				<div class="description" style="padding-top:0">
					<?php _e( 'Content of the management Page message. Allowed tags: [blog_name].', 'subscribe-reloaded' ); ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="management_email_content"><?php _e( 'Management Email message', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<?php
				$id_management_email_content = "management_email_content";
				$args_notificationContent = array(
					"media_buttons" => false,
					"textarea_rows" => 5,
					"teeny"         => true,
					"textarea_name" => "options[{$id_management_email_content}]"
				);
				wp_editor( subscribe_reloaded_get_option( $id_management_email_content ), $id_management_email_content, $args_notificationContent );
				?>
				<div class="description" style="padding-top:0">
					<?php _e( 'Content of the management email message. Allowed tags: [blog_name], [manager_link].', 'subscribe-reloaded' ); ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="oneclick_text"><?php _e( 'One Click Unsubscribe', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<?php
					$id_oneclick_text = "oneclick_text";
					$args_notificationContent = array(
						"media_buttons" => false,
						"textarea_rows" => 5,
						"teeny"         => true,
						"textarea_name" => "options[{$id_oneclick_text}]"
					);
					wp_editor( subscribe_reloaded_get_option( $id_oneclick_text ), $id_oneclick_text, $args_notificationContent );
				?>
				<div class="description" style="padding-top:0">
					<?php _e( 'Content of the One Click confirmation. Allowed tags: [post_title], [blog_name]', 'subscribe-reloaded' ); ?>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
	<p class="submit"><input type="submit" value="<?php _e( 'Save Changes' ) ?>" class="button-primary" name="Submit">
	</p>
</form>
