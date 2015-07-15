<?php
/*
Plugin Name: Subscribe to Comments Reloaded

Version: 150611
Stable tag: 150611
Requires at least: 2.9.2
Tested up to: 4.2.2

Plugin URI: http://wordpress.org/extend/plugins/subscribe-to-comments-reloaded/
Description: Subscribe to Comments Reloaded is a robust plugin that enables commenters to sign up for e-mail notifications. It includes a full-featured subscription manager that your commenters can use to unsubscribe to certain posts or suspend all notifications.
Contributors: camu, reedyseth, andreasbo, raamdev
Author: camu, reedyseth, Raam Dev
*/

namespace stcr {
	// Avoid direct access to this piece of code
	if ( ! function_exists( 'add_action' ) ) {
		header( 'Location: /' );
		exit;
	}
	require_once dirname(__FILE__).'\\wp_subscribe_reloaded.php';
	if(class_exists('\\'.__NAMESPACE__.'\\wp_subscribe_reloaded'))
	{
		// Initialize the cool stuff
		$wp_subscribe_reloaded = new wp_subscribe_reloaded();
		$wp_subscribe_reloaded->setUserCoookie();
	}
}
