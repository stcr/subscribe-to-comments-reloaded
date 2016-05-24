<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}

global $wpdb;

$stcr_options = $wpdb->get_results('SELECT * FROM '. $wpdb->prefix . 'options WHERE option_name LIKE "subscribe_reloaded%"');

$stcr_options_str = "";

foreach ($stcr_options as $option) {
	$stcr_options_str .= "{$option->option_name}: {$option->option_value}\n";
}

?>

<div class="donate-panel">

<h3><?php _e( 'System Information', 'subscribe-reloaded' ) ?></h3>
<p style="font-size: 14px;"><?php _e( "System Information", 'subscribe-reloaded' ) ?></p>

<textarea style="width:90%; min-height:900px;"><?php echo $stcr_options_str; ?></textarea>
<hr>

<hr>

</div>