<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}

global $wp_subscribe_reloaded;

$current_user_email = null; // Comes from wp_subscribe-to-comments-reloaded\subscribe_reloaded_manage()

if ( isset($current_user) && $current_user->ID > 0 )
{
    $current_user_email = $current_user->data->user_email;
}

ob_start();
$post_permalink = get_permalink( $post_ID );
if ( ! empty( $email ) ) {
	// Use Akismet, if available, to check this user is legit
	if ( function_exists( 'akismet_http_post' ) ) {
		global $akismet_api_host, $akismet_api_port;

		$akismet_query_string  = "user_ip={$_SERVER['REMOTE_ADDR']}";
		$akismet_query_string .= "&user_agent=" . urlencode( stripslashes( $_SERVER['HTTP_USER_AGENT'] ) );
		$akismet_query_string .= "&blog=" . urlencode( get_option( 'home' ) );
		$akismet_query_string .= "&blog_lang=" . get_locale();
		$akismet_query_string .= "&blog_charset=" . get_option( 'blog_charset' );
		$akismet_query_string .= "&permalink=$post_permalink";
		$akismet_query_string .= "&comment_author_email=" . urlencode( stripslashes( $email ) );

		$akismet_response = akismet_http_post( $akismet_query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port );

		// If this is considered SPAM, we stop here
		if ( $akismet_response[1] == 'true' ) {
			ob_end_clean();

			return '';
		}
	}

	$clean_email = $wp_subscribe_reloaded->stcr->utils->clean_email( $email );

	// If the case, send a message to the administrator
	if ( get_option( 'subscribe_reloaded_enable_admin_messages' ) == 'yes' )
	{
		$from_name  = stripslashes( get_option( 'subscribe_reloaded_from_name', 'admin' ) );
		$from_email = get_option( 'subscribe_reloaded_from_email', get_bloginfo( 'admin_email' ) );

		$subject = __( 'New subscription to', 'subscribe-reloaded' ) . " $target_post->post_title";
		$message = __( 'New subscription to', 'subscribe-reloaded' ) . " $target_post->post_title\n" . __( 'User:', 'subscribe-reloaded' ) . " $clean_email";
		// Prepare email settings
		$email_settings = array(
			'subject'      => $subject,
			'message'      => $message,
			'toEmail'      => get_bloginfo( 'admin_email' )
		);
		$wp_subscribe_reloaded->stcr->utils->send_email( $email_settings );
	}
	if ( get_option( 'subscribe_reloaded_enable_double_check' ) == 'yes'
			&& ! $wp_subscribe_reloaded->stcr->is_user_subscribed( $post_ID, $clean_email, 'C' ) )
	{
		$wp_subscribe_reloaded->stcr->add_subscription( $post_ID, $clean_email, 'YC' );
		$wp_subscribe_reloaded->stcr->confirmation_email( $post_ID, $clean_email );
		$message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_subscription_confirmed_dci' ) ), ENT_QUOTES, 'UTF-8' );
	}
	else {
		$this->add_subscription( $post_ID, $clean_email, 'Y' );
		$message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_subscription_confirmed' ) ), ENT_QUOTES, 'UTF-8' );
	}

	$message = str_replace( '[post_permalink]', $post_permalink, $message );
	if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
		$message = str_replace( '[post_title]', qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $target_post->post_title ), $message );
		$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
	} else {
		$message = str_replace( '[post_title]', $target_post->post_title, $message );
	}
	echo wpautop($message);
}
else {
    if ( isset($current_user_email) )
    {
        $email = $current_user_email;
    }
    else if ( isset( $_COOKIE['comment_author_email_' . COOKIEHASH] ))
    {
        $email = $_COOKIE['comment_author_email_' . COOKIEHASH];
    }
    else
    {
        $email = 'email';
    }

?>
	<p><?php
	$message = str_replace( '[post_permalink]', $post_permalink, __(html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_subscribe_without_commenting' ) ), ENT_QUOTES, 'UTF-8' ), 'subscribe-reloaded' ) );
	if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
		$message = str_replace( '[post_title]', qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $target_post->post_title ), $message );
		$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
	} else {
		$message = str_replace( '[post_title]', $target_post->post_title, $message );
	}
	echo $message;
	?></p>
	<form action="<?php
		echo esc_url( $_SERVER[ 'REQUEST_URI' ]);?>"
		method="post" name="sub-form">
		<fieldset style="border:0">
			<p><label for="sre"><?php _e( 'Email', 'subscribe-reloaded' ) ?></label>
				<input id='sre' type="text" class="subscribe-form-field" name="sre" value="<?php echo $email ?>" size="22" onfocus="if(this.value==this.defaultValue)this.value=''" onblur="if(this.value=='')this.value=this.defaultValue" />
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
