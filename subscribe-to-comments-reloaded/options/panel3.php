<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}
// Update options
if ( isset( $_POST['options'] ) ) {
	$faulty_fields = '';
	if ( isset( $_POST['options']['manager_page_enabled'] )
		&& ! subscribe_reloaded_update_option( 'manager_page_enabled', $_POST['options']['manager_page_enabled'], 'yesno' )
	) {
		$faulty_fields = __( 'Virtual Management Page', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['manager_page_title'] )
		&& ! subscribe_reloaded_update_option( 'manager_page_title', $_POST['options']['manager_page_title'], 'text' )
	) {
		$faulty_fields = __( 'Page title', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['manager_page'] )
		&& ! subscribe_reloaded_update_option( 'manager_page', $_POST['options']['manager_page'], 'text-no-encode' )
	) {
		$faulty_fields = __( 'Management URL', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['custom_header_meta'] )
		&& ! subscribe_reloaded_update_option( 'custom_header_meta', $_POST['options']['custom_header_meta'], 'text-no-encode' )
	) {
		$faulty_fields = __( 'Custom HEAD meta', 'subscribe-reloaded' ) . ', ';
	}

	if ( isset( $_POST['options']['request_mgmt_link'] )
		&& ! subscribe_reloaded_update_option( 'request_mgmt_link', $_POST['options']['request_mgmt_link'], 'text' )
	) {
		$faulty_fields = __( 'Request link', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['request_mgmt_link_thankyou'] )
		&& ! subscribe_reloaded_update_option( 'request_mgmt_link_thankyou', $_POST['options']['request_mgmt_link_thankyou'], 'text' )
	) {
		$faulty_fields = __( 'Request submitted', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['subscribe_without_commenting'] )
		&& ! subscribe_reloaded_update_option( 'subscribe_without_commenting', $_POST['options']['subscribe_without_commenting'], 'text' )
	) {
		$faulty_fields = __( 'Subscribe without commenting', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['subscription_confirmed'] )
		&& ! subscribe_reloaded_update_option( 'subscription_confirmed', $_POST['options']['subscription_confirmed'], 'text' )
	) {
		$faulty_fields = __( 'Subscription processed', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['subscription_confirmed_dci'] )
		&& ! subscribe_reloaded_update_option( 'subscription_confirmed_dci', $_POST['options']['subscription_confirmed_dci'], 'text' )
	) {
		$faulty_fields = __( 'Subscription processed (DCI)', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['author_text'] )
		&& ! subscribe_reloaded_update_option( 'author_text', $_POST['options']['author_text'], 'text' )
	) {
		$faulty_fields = __( 'Authors', 'subscribe-reloaded' ) . ', ';
	}
	if ( isset( $_POST['options']['user_text'] )
		&& ! subscribe_reloaded_update_option( 'user_text', $_POST['options']['user_text'], 'text' )
	) {
		$faulty_fields = __( 'Users', 'subscribe-reloaded' ) . ', ';
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
$is_html_enabled = ( get_option( 'subscribe_reloaded_enable_html_emails', 'no' ) == 'yes' );
?>

<form action="" method="post">
	<h3><?php _e( 'Options', 'subscribe-reloaded' ) ?></h3>
	<table class="form-table <?php echo $wp_locale->text_direction ?>">
		<tbody>
		<tr>
			<th scope="row">
				<label for="manager_page_enabled"><?php _e( 'Virtual Management Page', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<input type="radio" name="options[manager_page_enabled]" id="manager_page_enabled" value="yes"<?php echo ( subscribe_reloaded_get_option( 'manager_page_enabled', 'no' ) == 'yes' ) ? ' checked="checked"' : ''; ?>> <?php _e( 'Enabled', 'subscribe-reloaded' ) ?> &nbsp; &nbsp; &nbsp;
				<input type="radio" name="options[manager_page_enabled]" value="no" <?php echo ( subscribe_reloaded_get_option( 'manager_page_enabled', 'no' ) == 'no' ) ? '  checked="checked"' : ''; ?>> <?php _e( 'Disabled', 'subscribe-reloaded' ) ?>
				<div class="description"><?php _e( 'Disable the virtual management page if you need to create a <a href="https://github.com/stcr/subscribe-to-comments-reloaded/wiki/KB#create-a-real-management-page">real page</a> to make your theme happy.', 'subscribe-reloaded' ) ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="manager_page_title"><?php _e( 'Page title', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<input type="text" name="options[manager_page_title]" id="manager_page_title" value="<?php echo subscribe_reloaded_get_option( 'manager_page_title' ); ?>" size="70">

				<div class="description"><?php _e( 'Title of the page your visitors will use to manage their subscriptions.', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="manager_page"><?php _e( 'Management URL', 'subscribe-reloaded' ) ?></label></th>
			<td><?php echo get_bloginfo( 'url' ) ?>
				<input type="text" name="options[manager_page]" id="manager_page" value="<?php echo subscribe_reloaded_get_option( 'manager_page' ); ?>" size="30">

				<div class="description"><?php _e( 'The permalink for your management page (something like <code>/manage-subscriptions</code> or <code>/?page_id=345</code>). This page <b>does not</b> actually exist in the system, but its link must follow your permalink structure.', 'subscribe-reloaded' );
					if ( ( get_option( 'permalink_structure', '' ) == '' ) && ( strpos( subscribe_reloaded_get_option( 'manager_page' ), '?page_id=' ) === false ) ) {
						echo '<br/><strong>' . __( "Warning: it looks like the value you are using may be incompatible with your permalink structure", 'subscribe-reloaded' ) . '</strong>';
					} ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="custom_header_meta"><?php _e( 'Custom HEAD meta', 'subscribe-reloaded' ) ?></label></th>
			<td>
				<input type="text" name="options[custom_header_meta]" id="custom_header_meta" value="<?php echo subscribe_reloaded_get_option( 'custom_header_meta' ); ?>" size="70">

				<div class="description"><?php _e( 'Specify your custom HTML code to be added to the HEAD section of the page. Use <strong>single</strong> quotes for values.', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		</tbody>
	</table>

	<h3><?php _e( 'Messages', 'subscribe-reloaded' ) ?></h3>
	<table class="form-table <?php echo $wp_locale->text_direction ?>">
		<tbody>
		<tr>
			<th scope="row"><label for="request_mgmt_link"><?php _e( 'Request link', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<?php
					$id_request_mgmt_link = "request_mgmt_link";
					$args_notificationContent = array(
						"media_buttons" => false,
						"textarea_rows" => 3,
						"teeny"         => true,
						"textarea_name" => "options[{$id_request_mgmt_link}]"
					);
					wp_editor( subscribe_reloaded_get_option( $id_request_mgmt_link ), $id_request_mgmt_link, $args_notificationContent );
				?>
				<div class="description"><?php _e( 'Text shown to those who request to manage their subscriptions.', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="request_mgmt_link_thankyou"><?php _e( 'Request submitted', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<?php
					$id_request_mgmt_link_thankyou = "request_mgmt_link_thankyou";
					$args_notificationContent = array(
						"media_buttons" => false,
						"textarea_rows" => 3,
						"teeny"         => true,
						"textarea_name" => "options[{$id_request_mgmt_link_thankyou}]"
					);
					wp_editor( subscribe_reloaded_get_option( $id_request_mgmt_link_thankyou ), $id_request_mgmt_link_thankyou, $args_notificationContent );
				?>
				<div class="description"><?php _e( 'Thank you note shown after the request here above has been processed. Allowed tags: [post_title], [post_permalink]', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="subscribe_without_commenting"><?php _e( 'Subscribe without commenting', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<?php
					$id_subscribe_without_commenting = "subscribe_without_commenting";
					$args_notificationContent = array(
						"media_buttons" => false,
						"textarea_rows" => 3,
						"teeny"         => true,
						"textarea_name" => "options[{$id_subscribe_without_commenting}]"
					);
					wp_editor( subscribe_reloaded_get_option( $id_subscribe_without_commenting ), $id_subscribe_without_commenting, $args_notificationContent );
				?>
				<div class="description"><?php _e( 'Text shown to those who want to subscribe without commenting. Allowed tags: [post_title], [post_permalink]', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="subscription_confirmed"><?php _e( 'Subscription processed', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<?php
					$id_subscription_confirmed = "subscription_confirmed";
					$args_notificationContent = array(
						"media_buttons" => false,
						"textarea_rows" => 3,
						"teeny"         => true,
						"textarea_name" => "options[{$id_subscription_confirmed}]"
					);
					wp_editor( subscribe_reloaded_get_option( $id_subscription_confirmed ), $id_subscription_confirmed, $args_notificationContent );
				?>
				<div class="description"><?php _e( 'Thank you note shown after the subscription request has been processed (double check-in disabled). Allowed tags: [post_title], [post_permalink]', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="subscription_confirmed_dci"><?php _e( 'Subscription processed (DCI)', 'subscribe-reloaded' ) ?></label>
			</th>
			<td>
				<?php
					$id_subscription_confirmed_dci = "subscription_confirmed_dci";
					$args_notificationContent = array(
						"media_buttons" => false,
						"textarea_rows" => 3,
						"teeny"         => true,
						"textarea_name" => "options[{$id_subscription_confirmed_dci}]"
					);
					wp_editor( subscribe_reloaded_get_option( $id_subscription_confirmed_dci ), $id_subscription_confirmed_dci, $args_notificationContent );
				?>
				<div class="description"><?php _e( 'Thank you note shown after the subscription request has been processed (double check-in enabled). Allowed tags: [post_title], [post_permalink]', 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="author_text"><?php _e( 'Authors', 'subscribe-reloaded' ) ?></label></th>
			<td>
				<?php
					$id_author_text = "author_text";
					$args_notificationContent = array(
						"media_buttons" => false,
						"textarea_rows" => 3,
						"teeny"         => true,
						"textarea_name" => "options[{$id_author_text}]"
					);
					wp_editor( subscribe_reloaded_get_option( $id_author_text ), $id_author_text, $args_notificationContent );
				?>
				<div class="description"><?php _e( "Introductory text for the authors' management page.", 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="user_text"><?php _e( 'Users', 'subscribe-reloaded' ) ?></label></th>
			<td>
				<?php
					$id_user_text = "user_text";
					$args_notificationContent = array(
						"media_buttons" => false,
						"textarea_rows" => 3,
						"teeny"         => true,
						"textarea_name" => "options[{$id_user_text}]"
					);
					wp_editor( subscribe_reloaded_get_option( $id_user_text ), $id_user_text, $args_notificationContent );
				?>
				<div class="description"><?php _e( "Introductory text for the users' management page.", 'subscribe-reloaded' ); ?></div>
			</td>
		</tr>
		</tbody>
	</table>
	<p class="submit"><input type="submit" value="<?php _e( 'Save Changes' ) ?>" class="button-primary" name="Submit">
	</p>
</form>
