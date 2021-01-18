<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}

$error_message = __( 'You are not allowed to access this page.', 'subscribe-to-comments-reloaded' );

global $wp_subscribe_reloaded;

ob_start();

	?><p><?php echo wpautop( $error_message ); ?></p><?php

$output = ob_get_contents();
ob_end_clean();
return $output;

?>