<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
    header( 'Location: /' );
    exit;
}

ob_start();
echo '<p>' . __( 'You have request to manage another email address and this is forbidden.', 'subscribe-to-comments-reloaded' ) . '</p>';
$output = ob_get_contents();
ob_end_clean();

return $output;
?>
