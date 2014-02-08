<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}

require_once WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/classes/helper.class.php';

$helper = new subscribeToCommentsHelper();
ob_start();
if ( ! empty( $email ) ) {
	global $wp_subscribe_reloaded;

	// Send management link
	$from_name    = stripslashes( get_option( 'subscribe_reloaded_from_name', 'admin' ) );
	$from_email   = get_option( 'subscribe_reloaded_from_email', get_bloginfo( 'admin_email' ) );
	$subject      = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_management_subject', 'Manage your subscriptions on [blog_name]' ) ), ENT_COMPAT, 'UTF-8' );
	$message      = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_management_content', '' ) ), ENT_COMPAT, 'UTF-8' );
	$manager_link = get_bloginfo( 'url' ) . get_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/' );
	if ( function_exists( 'qtrans_convertURL' ) ) {
		$manager_link = qtrans_convertURL( $manager_link );
	}

	$clean_email     = $wp_subscribe_reloaded->clean_email( $email );
	$subscriber_salt = $wp_subscribe_reloaded->generate_key( $clean_email );
	$post_permalink  = get_permalink( $post_ID );

	$headers = "MIME-Version: 1.0\n";
	$headers .= "From: $from_name <$from_email>\n";
	$content_type = ( get_option( 'subscribe_reloaded_enable_html_emails', 'no' ) == 'yes' ) ? 'text/html' : 'text/plain';
	$headers .= "Content-Type: $content_type; charset=" . get_bloginfo( 'charset' );

	$manager_link .= ( strpos( $manager_link, '?' ) !== false ) ? '&' : '?';
	$manager_link .= "sre=" . urlencode( $clean_email ) . "&srk=$subscriber_salt";

	// Replace tags with their actual values
	$subject = str_replace( '[blog_name]', get_bloginfo( 'name' ), $subject );
	$message = str_replace( '[blog_name]', get_bloginfo( 'name' ), $message );
	$message = str_replace( '[manager_link]', $manager_link, $message );

	// QTranslate support
	if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
		$subject = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $subject );
		$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
	}
	if ( $content_type == 'text/html' ) {
		$message = $wp_subscribe_reloaded->wrap_html_message( $message, $subject );
	}

	wp_mail( $clean_email, $subject, $message, $headers );

	$message = str_replace( '[post_permalink]', $post_permalink, html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_request_mgmt_link_thankyou' ) ), ENT_COMPAT, 'UTF-8' ) );
	if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
		$message = str_replace( '[post_title]', qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $post->post_title ), $message );
		$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
	} else {
		$message = str_replace( '[post_title]', $post->post_title, $message );
	}

	echo $message;
} else {
	$message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_request_mgmt_link' ) ), ENT_COMPAT, 'UTF-8' );
	if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
		$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
	}
?>
	<p><?php echo $message ?></p>
	<form action="<?php if ( $helper->verifyXSS( $_SERVER['REQUEST_URI'] ) ) {
		echo "#";
	} else {
		echo $_SERVER['REQUEST_URI'];
	} ?>" method="post" onsubmit="if(this.subscribe_reloaded_email.value=='' || this.subscribe_reloaded_email.value.indexOf('@')==0) return false">
		<fieldset style="border:0">
			<p><label for="subscribe_reloaded_email"><?php _e( 'Email', 'subscribe-reloaded' ) ?></label>
				<input type="text" class="subscribe-form-field" name="sre" value="<?php echo isset( $_COOKIE['comment_author_email_' . COOKIEHASH] ) ? $_COOKIE['comment_author_email_' . COOKIEHASH] : 'email'; ?>" size="22" onfocus="if(this.value==this.defaultValue)this.value=''" onblur="if(this.value=='')this.value=this.defaultValue" />
				<input name="submit" type="submit" class="subscribe-form-button" value="<?php _e( 'Send', 'subscribe-reloaded' ) ?>" />
			</p>
		</fieldset>
	</form>
<?php
}
$output = ob_get_contents();
ob_end_clean();
return $output;
?>
