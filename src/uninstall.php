<?php
namespace stcr;
// Avoid misusage
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$safeUnistall = get_option("subscribe_reloaded_safely_uninstall");

$stcr_options = stcr_get_settings($wpdb);

if ($safeUnistall === "yes")
{
	// Drop Only the Settings and not the subscriptions.
	// Goodbye options...
	foreach($stcr_options as $option)
	{
		delete_option( $option->option_name );
	}
}
else if ($safeUnistall === "no")
{
	// Goodbye data...
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}subscribe_reloaded" ); // Compatibility with versions prior to 1.7
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}subscribe_reloaded_subscribers" ); // Compatibility with versions prior to 1.7
	$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%'" );
	// Goodbye options...
	foreach($stcr_options as $option)
	{
		delete_option( $option->option_name );
	}
}

// Remove scheduled autopurge events
wp_clear_scheduled_hook( '_cron_subscribe_reloaded_purge' );


function stcr_get_settings($_wpdb)
{
	$stcr_options  = $_wpdb->get_results(
		" SELECT * FROM $_wpdb->options WHERE option_name like 'subscribe_reloaded\_%'
 		  ORDER BY option_name", OBJECT
	);

	return $stcr_options;
}

?>