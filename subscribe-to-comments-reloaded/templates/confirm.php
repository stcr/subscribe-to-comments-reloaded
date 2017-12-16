<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}

global $wp_subscribe_reloaded;
$post_permalink = null;

if (array_key_exists('post_permalink', $_GET))
{
    if ( ! empty( $_GET['post_permalink'] ) )
    {
        $post_permalink = $_GET['post_permalink'];
    }
}

$wp_subscribe_reloaded->stcr->update_subscription_status( $post_ID, $email, '-C' );
$message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_subscription_confirmed' ) ), ENT_COMPAT, 'UTF-8' );
if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
	$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
}

if ( isset( $post_permalink ) )
{
    $message .= '<p id="subscribe-reloaded-update-p"> 
            <a style="margin-right: 10px; text-decoration: none; box-shadow: unset;" href="'. esc_url( $post_permalink ) .'"><i class="fa fa-arrow-circle-left fa-2x" aria-hidden="true" style="vertical-align: middle;"></i>&nbsp; '. __('Return to Post','subscribe-reloaded').'</a>
          </p>';
}

return "<p>$message</p>";
