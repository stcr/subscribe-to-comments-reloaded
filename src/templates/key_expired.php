<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}

$error_message   = esc_html__( "Woohaa the link to manage your subscriptions has expired, don't worry, just enter your email below and a new link will be send.", 'subscribe-to-comments-reloaded');

global $wp_subscribe_reloaded;
ob_start();

if ( isset( $_POST[ 'sre' ] ) && trim( $_POST[ 'sre' ] ) !== "" ) {
	$email         = sanitize_text_field( wp_unslash( $_POST['sre'] ) );
	$subject       = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_management_subject', 'Manage your subscriptions on [blog_name]' ) ), ENT_QUOTES, 'UTF-8' );
	$page_message  = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_management_content', '' ) ), ENT_QUOTES, 'UTF-8' );
	$email_message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_management_email_content', '' ) ), ENT_QUOTES, 'UTF-8' );
	$manager_link  = get_bloginfo( 'url' ) . get_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/' );
	$one_click_unsubscribe_link = $manager_link;

	if ( function_exists( 'qtrans_convertURL' ) ) {
		$manager_link = qtrans_convertURL( $manager_link );
	}

	$clean_email     = $wp_subscribe_reloaded->stcr->utils->clean_email( $email );
	$subscriber_salt = $wp_subscribe_reloaded->stcr->utils->generate_temp_key( $clean_email );

	$manager_link .= ( strpos( $manager_link, '?' ) !== false ) ? '&' : '?';
	$manager_link .= "srek=" . $wp_subscribe_reloaded->stcr->utils->get_subscriber_key($clean_email) . "&srk=$subscriber_salt&amp;srsrc=e";
	$one_click_unsubscribe_link .= ( strpos( $one_click_unsubscribe_link, '?' ) !== false ) ? '&' : '?';
	$one_click_unsubscribe_link .= ( ( strpos( $one_click_unsubscribe_link, '?' ) !== false ) ? '&' : '?' ) . "srek=" . $this->utils->get_subscriber_key( $clean_email ) . "&srk=$subscriber_salt" . "&sra=u" . "&srp=";

	// Replace tags with their actual values
	$subject       = str_replace( '[blog_name]', get_bloginfo( 'name' ), $subject );
	// Setup the fronted page message
	$page_message  = str_replace( '[blog_name]', get_bloginfo( 'name' ), $page_message );
	// Setup the email message
	$email_message = str_replace( '[blog_name]', get_bloginfo( 'name' ), $email_message );
	$email_message = str_replace( '[manager_link]',  $manager_link, $email_message );
    $email_message = str_replace( '[oneclick_link]', $one_click_unsubscribe_link, $email_message );

    if ( get_option( 'subscribe_reloaded_enable_html_emails', 'yes' ) == 'yes' ) {
        $email_message = wpautop( $email_message );
    }

	// QTranslate support
	if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
		$subject       = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $subject );
		$page_message  = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $page_message );
		$email_message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $email_message );
	}
	// Prepare email settings
	$email_settings = array(
		'subject'      => $subject,
		'message'      => $email_message,
		'toEmail'      => $clean_email
	);
	$has_blacklist_email = $this->utils->blacklisted_emails( $clean_email );
	// Send the confirmation email only if the email
	// address is not in blacklist email list.
	if ( $has_blacklist_email ) {
		$wp_subscribe_reloaded->stcr->utils->send_email( $email_settings );
	}

	echo wpautop( wp_kses( $page_message, wp_kses_allowed_html( 'post' ) ) );
}
else
{
	$message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_request_mgmt_link' ) ), ENT_QUOTES, 'UTF-8' );
	if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
		$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
	}
	?>
	<?php echo wpautop( esc_html( $error_message ) ); ?>
	<form action="<?php
	$url = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$url = preg_replace('/sre=\w+&|&key\_expired=\d+/', '', $url );
	echo esc_url( $url . "&key_expired=1" );
	?>" name="sub-form" method="post">
		<fieldset style="border:0">
			<p><label for="subscribe_reloaded_email"><?php esc_html_e( 'Email', 'subscribe-to-comments-reloaded' ) ?></label>
			<?php
			$comment_author_email = isset( $_COOKIE[ 'comment_author_email_' . COOKIEHASH ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ 'comment_author_email_' . COOKIEHASH ] ) ) : '';
			?>
				<input id='subscribe_reloaded_email' type="text" class="subscribe-form-field" name="sre" value="<?php echo esc_attr( $comment_author_email ); ?>" size="22" onfocus="if(this.value==this.defaultValue)this.value=''" onblur="if(this.value=='')this.value=this.defaultValue" />
				<input name="submit" type="submit" class="subscribe-form-button" value="<?php esc_attr_e( 'Send', 'subscribe-to-comments-reloaded' ) ?>" />
			</p>
		</fieldset>
	</form>
	<?php
}
$output = ob_get_contents();
ob_end_clean();
return $output;
?>
