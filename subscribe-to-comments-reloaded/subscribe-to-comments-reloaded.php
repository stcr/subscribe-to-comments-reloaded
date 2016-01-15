<?php
/*
Plugin Name: Subscribe to Comments Reloaded

Version: 160115
Stable tag: 160115
Requires at least: 4.0
Tested up to: 4.4.1

Plugin URI: http://wordpress.org/extend/plugins/subscribe-to-comments-reloaded/
Description: Subscribe to Comments Reloaded is a robust plugin that enables commenters to sign up for e-mail notifications. It includes a full-featured subscription manager that your commenters can use to unsubscribe to certain posts or suspend all notifications.
Contributors: reedyseth, camu, andreasbo, raamdev
Author: reedyseth, Raam Dev, camu

Text Domain: subscribe-reloaded
Domain Path: /langs
*/

namespace stcr {
	// Avoid direct access to this piece of code
	if ( ! function_exists( 'add_action' ) ) {
		header( 'Location: /' );
		exit;
	}
	require_once dirname(__FILE__).'/wp_subscribe_reloaded.php';
	if( ! class_exists('\\'.__NAMESPACE__.'\\stcr_subscribe_reloaded'))
	{
		class stcr_subscribe_reloaded {

			public $stcr = null;

			function __construct() {
				$this->stcr = new wp_subscribe_reloaded();
				$this->stcr->setUserCoookie();
				// Run the activation/deactivation routing only for admins.
				if(is_admin()){
					// Initialization routines that should be executed on activation/deactivation
					// Due to Wordpress restrinctions these hooks have to be on the main file.
					register_activation_hook( __FILE__, array( $this, 'activate' ) );
					register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
				}
			}

			/**
			 * This will trigger the activate function located on utils/stcr_manage.php
			 * @since 150720
			 */
			function activate() {
				$this->stcr->activate();
			}
			/**
			 * This will trigger the activate function located on utils/stcr_manage.php
			 * @since 150720
			 */
			function deactivate() {
				$this->stcr->deactivate();
			}
		}
		$wp_subscribe_reloaded = new stcr_subscribe_reloaded(); // Initialize the cool stuff
	}
}
