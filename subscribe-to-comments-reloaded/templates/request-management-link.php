<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}
global $wp_subscribe_reloaded;

// The the page where the user is coming from
$post_permalink = null;
$current_user_email = null; // Comes from wp_subscribe-to-comments-reloaded\subscribe_reloaded_manage()
$ID = $target_post;

if ( isset($current_user) && $current_user->ID > 0 )
{
    $current_user_email = $current_user->data->user_email;
}

if (array_key_exists('post_permalink', $_GET))
{
    if ( ! empty( $_GET['post_permalink'] ) )
    {
        $post_permalink = $_GET['post_permalink'];
    }
}


ob_start();

if ( ! empty( $email ) ) {
	// Send management link
	$subject        = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_management_subject', 'Manage your subscriptions on [blog_name]' ) ), ENT_QUOTES, 'UTF-8' );
	$page_message   = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_management_content', '' ) ), ENT_QUOTES, 'UTF-8' );
	$email_message  = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_management_email_content', '' ) ), ENT_QUOTES, 'UTF-8' );
	$manager_link   = get_bloginfo( 'url' ) . get_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/' );
	$one_click_unsubscribe_link = $manager_link;
	if ( function_exists( 'qtrans_convertURL' ) ) {
		$manager_link = qtrans_convertURL( $manager_link );
	}

	$clean_email     = $wp_subscribe_reloaded->stcr->utils->clean_email( $email );
	$subscriber_salt = $wp_subscribe_reloaded->stcr->utils->generate_temp_key( $clean_email );

	$manager_link .= ( strpos( $manager_link, '?' ) !== false ) ? '&' : '?';
	$manager_link .= "srek=" . $wp_subscribe_reloaded->stcr->utils->get_subscriber_key($clean_email) . "&srk=$subscriber_salt&amp;srsrc=e&post_permalink=" . $post_permalink;
	$one_click_unsubscribe_link .= ( strpos( $one_click_unsubscribe_link, '?' ) !== false ) ? '&' : '?';
	$one_click_unsubscribe_link .= ( ( strpos( $one_click_unsubscribe_link, '?' ) !== false ) ? '&' : '?' )
								. "srek=" . $this->utils->get_subscriber_key( $clean_email )
								. "&srk=$subscriber_salt" . "&sra=u;srsrc=e" . "&srp=";

	// Replace tags with their actual values
	$subject       = str_replace( '[blog_name]', get_bloginfo( 'name' ), $subject );
	// Setup the fronted page message
	$page_message  = str_replace( '[blog_name]', get_bloginfo( 'name' ), $page_message );
	// Setup the email message
	$email_message = str_replace( '[blog_name]', get_bloginfo( 'name' ), $email_message );
	$email_message = str_replace( '[manager_link]',  $manager_link, $email_message );
	$email_message = str_replace( '[oneclick_link]', $one_click_unsubscribe_link, $email_message );
	$email_message = wpautop( $email_message );

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
	$wp_subscribe_reloaded->stcr->utils->send_email( $email_settings );

	echo wpautop( $page_message );
}
else
{
	$message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_request_mgmt_link' ) ), ENT_QUOTES, 'UTF-8' );
    $email = '';

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

	if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
		$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
	}
	?>
	<p><?php echo wpautop( $message ); ?></p>
	<form action="<?php
		echo esc_url( $_SERVER[ 'REQUEST_URI' ]);?>"
		method="post" name="sub-form">
		<fieldset style="border:0">
			<p><label for="subscribe_reloaded_email"><?php _e( 'Email', 'subscribe-reloaded' ) ?></label>
				<input id='subscribe_reloaded_email' type="text" class="subscribe-form-field" name="sre" value="<?php echo $email; ?>" size="22" onfocus="if(this.value==this.defaultValue)this.value=''" onblur="if(this.value=='')this.value=this.defaultValue" />
				<input name="submit" type="submit" class="subscribe-form-button" value="<?php _e( 'Send', 'subscribe-reloaded' ) ?>" />
			</p>
		</fieldset>
	</form>
<?php
    if ( isset( $post_permalink ) )
    {
        echo '<p id="subscribe-reloaded-update-p"> 
            <a style="margin-right: 10px; text-decoration: none; box-shadow: unset;" href="'. esc_url( $post_permalink ) .'"><i class="fa fa-arrow-circle-left fa-2x" aria-hidden="true" style="vertical-align: middle;"></i>&nbsp; '. __('Return to Post','subscribe-reloaded').'</a>
          </p>';
    }

}
$output = ob_get_contents();
ob_end_clean();

return $output;
?>