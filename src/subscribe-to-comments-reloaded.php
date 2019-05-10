<?php
/**
 * Plugin Name:       Subscribe to Comments Reloaded
 * Description:       Subscribe to Comments Reloaded is a robust plugin that enables commenters to sign up for e-mail notifications. It includes a full-featured subscription manager that your commenters can use to unsubscribe to certain posts or suspend all notifications.
 * Version:           190510
 * Author:            WPKube
 * Author URI:        http://wpkube.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       subscribe-to-comments-reloaded
 * Domain Path:       /langs
 */

namespace stcr;
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}
require_once dirname(__FILE__).'/wp_subscribe_reloaded.php';
if( ! class_exists('\\'.__NAMESPACE__.'\\stcr_subscribe_reloaded'))
{
	class stcr_subscribe_reloaded {
		// http://www.garfieldtech.com/blog/class-constants-php54
		const CLASSNAME = __CLASS__;

		public $stcr = null;

		function __construct() {
			$this->stcr = new wp_subscribe_reloaded();
			$this->stcr->setUserCoookie();
		}

		/**
		 * This will trigger the activate function located on utils/stcr_manage.php
		 * @since 150720
		 */
		static function activate() {
			require_once dirname(__FILE__).'/utils/stcr_manage.php';
			$_stcra = new stcr_manage();
			$_stcra->activate();
		}
		/**
		 * This will trigger the activate function located on utils/stcr_manage.php
		 * @since 150720
		 */
		static function deactivate() {
			require_once dirname(__FILE__).'/utils/stcr_manage.php';
			$_stcrd = new stcr_manage();
			$_stcrd->deactivate();
		}
	}
	// Initialization routines that should be executed on activation/deactivation
	// Due to Wordpress restrinctions these hooks have to be on the main file.
	register_activation_hook( __FILE__, array( \stcr\stcr_subscribe_reloaded::CLASSNAME , 'activate' ) );
	register_deactivation_hook( __FILE__, array( \stcr\stcr_subscribe_reloaded::CLASSNAME , 'deactivate' ) );

	if (  ! isset( $GLOBALS['wp_subscribe_reloaded'] ) )
    {
        $GLOBALS['wp_subscribe_reloaded'] = new stcr_subscribe_reloaded(); // Initialize the cool stuff
    }
}
