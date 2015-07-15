<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}

global $wp_subscribe_reloaded;
$post = get_post( $post_ID );
ob_start();

if ( is_object( $post ) ) {
	$rows_affected = $wp_subscribe_reloaded->delete_subscriptions( $post_ID, $email );

	$message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_oneclick_text' ) ), ENT_COMPAT, 'UTF-8' );
	$message = str_replace('[post_name]', $post->post_name, $message );

	if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
		$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
	}
	echo "<p>$message</p>";
} else {
	echo '<p>' . __( 'No subscriptions match your search criteria.', 'subscribe-reloaded' ) . '</p>';
}
$output = ob_get_contents();
ob_end_clean();

return $output;
?>
