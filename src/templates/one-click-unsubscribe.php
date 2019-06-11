<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}

global $wp_subscribe_reloaded;
$post = get_post( $post_ID );
$manager_link = get_bloginfo( 'url' ) . get_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/' );
$manager_link .= ( strpos( $manager_link, '?' ) !== false ) ? '&' : '?';
$srk = ! empty( $_POST['srek'] ) ? $_POST['srek']  : ( ! empty( $_GET['srek'] ) ?  $_GET['srek']  : '' );
$manager_link .= "srek=" . $srk . "&srk=" . $_GET['srk']."&amp;srsrc=e";
ob_start();

if ( is_object( $post ) ) {

	$message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_oneclick_text' ) ), ENT_QUOTES, 'UTF-8' );
	$message = str_replace( '[post_title]',   get_the_title($post), $message );
	$message = str_replace( '[blog_name]' , get_bloginfo('name'), $message );

	$rows_affected = $wp_subscribe_reloaded->stcr->delete_subscriptions( $post_ID, $email );

	if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
		$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
	}
	$message = wpautop( $message ); // Let us add the <p> tag if need it.
	echo "$message"; // TODO: Add management link with number of subscriptions.
} else {
	echo '<p>' . __( 'No subscriptions match your search criteria.', 'subscribe-to-comments-reloaded' ) . '</p>';
}
$output = ob_get_contents();
ob_end_clean();

return $output;
?>
