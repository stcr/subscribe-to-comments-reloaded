<?php

namespace stcr;

// Avoid misusage
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// vars
global $wpdb;
$safeUnistall = get_option('subscribe_reloaded_safely_uninstall');
$stcr_options = stcr_get_settings($wpdb);

// only delete settings, keep the subscriptions
if ( $safeUnistall === 'yes' ) {
	
	// delete settings
	foreach( $stcr_options as $option ) {
		delete_option( $option->option_name );
	}

// delete settings and subscriptions
} else if ( $safeUnistall === 'no' ) {
	
	// delete subscriptions
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}subscribe_reloaded" ); // Compatibility with versions prior to 1.7
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}subscribe_reloaded_subscribers" ); // Compatibility with versions prior to 1.7
	$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%'" );
	
	// delete settings
	foreach($stcr_options as $option) {
		delete_option( $option->option_name );
	}

}

// remove scheduled autopurge events
wp_clear_scheduled_hook( '_cron_subscribe_reloaded_purge' );
wp_clear_scheduled_hook( '_cron_subscribe_reloaded_system_report_file_purge' );

/**
 * Function to get all the settings info
 * 
 * @since 190705 cleanup
 */
function stcr_get_settings($_wpdb) {

	// get the options
	$stcr_options = $_wpdb->get_results(
		"SELECT * FROM $_wpdb->options 
		 WHERE option_name 
		 LIKE 'subscribe_reloaded\_%'
		 ORDER BY option_name", OBJECT
	);

	// pass back the data
	return $stcr_options;

}