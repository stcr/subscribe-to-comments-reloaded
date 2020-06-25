<?php
/**
 * Class with management functions for Subscribe to Comments Reloaded
 * @author reedyseth
 * @since 16-Jul-2015
 * @version 160831
 */
namespace stcr;
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}

require_once dirname(__FILE__).'/stcr_utils.php';
require_once dirname(__FILE__).'/stcr_upgrade.php';

if( ! class_exists('\\'.__NAMESPACE__.'\\stcr_manage') )
{
	class stcr_manage {

		public $current_version = VERSION;
		public $utils = null;
		public $upgrade = null;
		public $db_version = null;
		public $fresh_install = false;

		public function __construct() {
			$this->upgrade = new stcr_upgrade();
			$this->utils = new stcr_utils();
			$this->db_version = get_option( 'subscribe_reloaded_version' );
			if ( ! get_option( 'subscribe_reloaded_fresh_install' )
					|| get_option( 'subscribe_reloaded_fresh_install' ) == 'yes')
			{
				$this->fresh_install = true;
			}
			else
			{
				$this->fresh_install = false;
				update_option('subscribe_reloaded_fresh_install', 'no');
			}
			// Schedule the autopurge hook
			if ( ! wp_next_scheduled( '_cron_subscribe_reloaded_purge' ) ) {
				wp_clear_scheduled_hook( '_cron_subscribe_reloaded_purge' );
				// Let us bind the schedule event with our desire action.
				wp_schedule_event( time() + 15, 'daily', '_cron_subscribe_reloaded_purge' );
			}

		}

		/**
		 * Search for a new version of code for a possible update
		 */
		public function stcr_admin_init() {

			// Add Authors to custom posts types
			if ( get_option( 'subscribe_reloaded_notify_authors' ) === 'yes' ) {
				// Retrieve custom post types
				$gpt_args = array('_builtin' => false);
				$post_types = get_post_types( $gpt_args );
				foreach ($post_types as $post_type_name) {
					// Add author subscription in every post type
					add_action( 'publish_' . $post_type_name , array( $this, 'subscribe_post_author' ) );
				}
			}
		}

		public function admin_notices() {
			$notices = get_option( 'subscribe_reloaded_deferred_admin_notices' );
			$nonce = null;

			if ( $notices ) {
				// Add JS script
				wp_enqueue_script( 'stcr-admin-js' );
				wp_enqueue_style( 'stcr-admin-style' );

				foreach ( $notices as $name => $noticeData ) {
					if( $noticeData['status'] == 'unread' ) {
						$nonce = wp_create_nonce( $name );
						// Set the a fresh nonce
						$this->utils->stcr_update_admin_notice_status( $name, 'unread', $nonce );
						echo "<div class='notice is-dismissible stcr-dismiss-notice  {$noticeData['type']}' data-nonce='{$nonce}|{$name}'>";
							echo $noticeData['message'];
						echo "</div>";
					}
				}
				update_option( 'subscribe_reloaded_deferred_admin_notices', $notices );
			}
		}

		/**
		 * Support for WP MU network activations (experimental)
		 */
		public function new_blog( $_blog_id ) {
			switch_to_blog( $_blog_id );
			$this->_activate();
			restore_current_blog();
		}
		// end new_blog

		public function sendConfirmationEMail( $info ) {
			// Retrieve the information about the new comment
			$this->confirmation_email( $info->comment_post_ID, $info->comment_author_email );
		}

		/**
		 * Sends a message to confirm a subscription
		 */
		public function confirmation_email( $_post_ID = 0, $_email = '' ) {
			// Retrieve the options from the database
			$from_name    = stripslashes( get_option( 'subscribe_reloaded_from_name', 'admin' ) );
			$from_email   = get_option( 'subscribe_reloaded_from_email', get_bloginfo( 'admin_email' ) );
			$subject      = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_double_check_subject', 'Please confirm your subscribtion to [post_title]' ) ), ENT_COMPAT, 'UTF-8' );
			$message      = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_double_check_content', '' ) ), ENT_COMPAT, 'UTF-8' );
			$manager_link = get_bloginfo( 'url' ) . get_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/' );
			if ( function_exists( 'qtrans_convertURL' ) ) {
				$manager_link = qtrans_convertURL( $manager_link );
			}

			$clean_email     = $this->utils->clean_email( $_email );
			$subscriber_salt = $this->utils->generate_temp_key( $clean_email );

			$this->utils->add_user_subscriber_table( $clean_email );

			$post           = get_post( $_post_ID );
            $post_permalink = get_permalink( $_post_ID );

            $manager_link .= ( ( strpos( $manager_link, '?' ) !== false ) ? '&' : '?' ) . "srek=" . $this->utils->get_subscriber_key( $clean_email ) . "&srk=" . $subscriber_salt;
            $confirm_link = "$manager_link&srp=$_post_ID&sra=c&srsrc=e&confirmation_email=y&post_permalink=" . esc_url( $post_permalink );


            // Replace tags with their actual values
			$subject = str_replace( '[post_title]', $post->post_title, $subject );

			$message = str_replace( '[post_permalink]', $post_permalink, $message );
			$message = str_replace( '[confirm_link]', $confirm_link, $message );
			$message = str_replace( '[manager_link]', $manager_link, $message );

			// QTranslate support
			if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
				$subject = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $subject );
				$message = str_replace( '[post_title]', qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $post->post_title ), $message );
				$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
			} else {
				$message = str_replace( '[post_title]', $post->post_title, $message );
			}
			$message = apply_filters( 'stcr_confirmation_email_message', $message, $_post_ID, $clean_email );

			// Prepare email settings
			$email_settings = array(
				'subject'      => $subject,
				'message'      => $message,
				'toEmail'      => $clean_email,
				'XPostId'    => $_post_ID
			);
			$this->utils->send_email( $email_settings );
		}
		// end confirmation_email

		/**
		 * Support for WP MU network activations (experimental)
		 */
		public function activate() {
			global $wpdb;

			if ( function_exists( 'is_multisite' ) && is_multisite() && isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1 ) ) {
				$blogids = $wpdb->get_col(
					$wpdb->prepare(
						"
			SELECT blog_id
			FROM $wpdb->blogs
			WHERE site_id = %d
			AND deleted = 0
			AND spam = 0", $wpdb->siteid
					)
				);

				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					$this->_activate();
				}
				restore_current_blog();
			} else {
				$this->_activate();
			}
		}
		// end activate
		/**
		 * Adds the options to the database and imports the data from other plugins
		 */
		public function _activate() {
			// Load localization files
//			load_plugin_textdomain( 'subscribe-to-comments-reloaded', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
			// Upgrade rountine
			$this->upgrade();

            delete_option('subscribe_reloaded_unique_key');

            $this->utils->create_options();
		}
		/**
		 * Performs some clean-up maintenance (disable cron job).
		 */
		public function deactivate() {
			global $wpdb;
			if ( function_exists( 'is_multisite' ) && is_multisite() && isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1 ) ) {
				$blogids = $wpdb->get_col(
					$wpdb->prepare(
						"
			SELECT blog_id
			FROM $wpdb->blogs
			WHERE site_id = %d
			AND deleted = 0
			AND spam = 0", $wpdb->siteid
					)
				);

				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					wp_clear_scheduled_hook( '_cron_subscribe_reloaded_purge' );
					wp_clear_scheduled_hook( '_cron_log_file_purge' );
					wp_clear_scheduled_hook( '_cron_subscribe_reloaded_system_report_file_purge' );

				}
				restore_current_blog();
			} else {
				wp_clear_scheduled_hook( '_cron_subscribe_reloaded_purge' );
				wp_clear_scheduled_hook( '_cron_log_file_purge' );
                wp_clear_scheduled_hook( '_cron_subscribe_reloaded_system_report_file_purge' );
			}
		}

		// end deactivate

		public function maybe_update()
		{
			$int_db_version   = str_replace('.','', $this->db_version) ;
			$int_curr_version = str_replace('.','', $this->current_version) ;

			if ( empty ( $int_db_version ) || (int) $int_db_version < (int) $int_curr_version ) {
				// // Do whatever upgrades needed here.
				// $this->_activate();
				// Send the current version to display the appropiate message
				// The notification will only be visible once there is an update not a activation.
				$this->upgrade->upgrade_notification( $this->current_version, $this->db_version, $this->fresh_install );
				$this->upgrade();
				update_option( 'subscribe_reloaded_version', $this->current_version );
			}
		}

		private function upgrade()
		{
			// Import data from the WP Comment Subscriptions plugin, if needed
			$this->upgrade->_import_wpcs_data();

			// Import data from Subscribe to Comments & Co., if needed
            $this->upgrade->_import_stc_data();
            
            // Import data from Subscribe to Comments by Mark Jaquith
			$this->upgrade->_import_stc_mj_data();

			// Import data from Comment Reply Notification, if needed
            // Function deprecated and not in use anymore.
			// $this->upgrade->_import_crn_data();

			// Starting from version 2.0 StCR uses Wordpress' tables to store the information about subscriptions
			$this->upgrade->_update_db();

			// Since there are some users with the database corrupted due to encoding stuff we need to sanitize
			// their information
			$this->upgrade->_sanitize_db_information( $this->fresh_install );
			// Create a new table if not exists to manage the subscribers safer
			// First Check if the subscribers table is created.
			if ( ! get_option( 'subscribe_reloaded_subscriber_table' ) || get_option( 'subscribe_reloaded_subscriber_table' ) == 'no') {
				$this->upgrade->_create_subscriber_table( $this->fresh_install );
			}
			// Apply Patches
            $this->upgrade->apply_patches();
		}

		/**
		 * Remove the log file created by StCR
		 * @since 05-Apr-2017
		 */
		public function log_file_purge()
		{
			$plugin_dir   = plugin_dir_path( __DIR__ );
			$file_name    = "log.txt";
			$file_path    = $plugin_dir . "utils/" . $file_name;

			if( file_exists( $file_path )  && is_writable( $plugin_dir ) )
			{
				// unlink the file
				unlink($file_path);
			}
		}
		/**
		 * Removes old entries from the database
		 */
		public function subscribe_reloaded_purge() {
			global $wpdb;

			if ( ( $autopurge_interval = intval( get_option( 'subscribe_reloaded_purge_days', 0 ) ) ) <= 0 ) {
				return true;
			}

			// First retrieve the emails to be deleted
			$emailsToPurge = "SELECT DISTINCT REPLACE(meta_key, '_stcr@_', '') AS email FROM $wpdb->postmeta
					  WHERE meta_key LIKE '\_stcr@\_%'
					  AND STR_TO_DATE(meta_value, '%Y-%m-%d %H:%i:%s') <= DATE_SUB(NOW(),
					  INTERVAL $autopurge_interval DAY) AND meta_value LIKE '%C'";

			$emails = $wpdb->get_results( $emailsToPurge, OBJECT );
			// Now if there are emails go ahead and delete them
			if ( ! empty( $emails ) && is_array( $emails ) ) {
				foreach( $emails as $row ) {
					$this->utils->remove_user_subscriber_table($row->email);
				}
			}
			// Delete old entries on the post_meta table
			$wpdb->query(
				"
		DELETE FROM $wpdb->postmeta
		WHERE meta_key LIKE '\_stcr@\_%'
			AND STR_TO_DATE(meta_value, '%Y-%m-%d %H:%i:%s') <= DATE_SUB(NOW(), INTERVAL $autopurge_interval DAY) AND meta_value LIKE '%C'"
			);
		}
		// end subscribe_reloaded_purge
		/**
		 * Finds all links in text and wraps them with an HTML anchor tag
		 *
		 * @param unknown $text
		 *
		 * @return string Text with all links wrapped in HTML anchor tags
		 *
		 */
		public function htmlify_message_links( $text ) {
			return preg_replace( '!(((f|ht)tp(s)?://)[-a-zA-Z?-??-?()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1">$1</a>', $text );
		}

		/**
		 * Adds a new entry in the admin menu, to manage this plugin's options
		 */
		public function add_config_menu( $_s ) {

			if ( current_user_can( 'manage_options' ) ) {

				$page_title = "Subscribe to Comments Reloaded";
				$menu_title = "StCR";
				$capability = "manage_options";
				$function   = "";
				$icon_url   = "dashicons-email";
				$position   = 26;
				$parent_slug= "stcr_manage_subscriptions";
				add_menu_page( $page_title, $menu_title, $capability, $parent_slug, $function, $icon_url, $position );

				add_submenu_page( $parent_slug ,
								__( 'Manage subscriptions', 'subscribe-to-comments-reloaded' ),
								__( 'Manage subscriptions', 'subscribe-to-comments-reloaded' ),
								 $capability,
								 "stcr_manage_subscriptions",
								 array( $this, "stcr_option_manage_subscriptions") );
				add_submenu_page( $parent_slug ,
								__( 'Comment Form', 'subscribe-to-comments-reloaded' ),
								__( 'Comment Form', 'subscribe-to-comments-reloaded' ),
								 $capability,
								 "stcr_comment_form",
								 array( $this, "stcr_option_comment_form" ) );
				add_submenu_page( $parent_slug ,
								__( 'Management Page', 'subscribe-to-comments-reloaded' ),
								__( 'Management Page', 'subscribe-to-comments-reloaded' ),
								 $capability,
								 "stcr_management_page",
								 array( $this, "stcr_option_management_page" ) );
				add_submenu_page( $parent_slug ,
								__( 'Notifications', 'subscribe-to-comments-reloaded' ),
								__( 'Notifications', 'subscribe-to-comments-reloaded' ),
								 $capability,
								 "stcr_notifications",
								 array( $this, "stcr_option_notifications" ) );
				add_submenu_page( $parent_slug ,
								__( 'Options', 'subscribe-to-comments-reloaded' ),
								__( 'Options', 'subscribe-to-comments-reloaded' ),
								 $capability,
								 "stcr_options",
								 array( $this, "stcr_option_options" ) );
				// @since 160316
				// Using this page requires to list a cool table, on this case will be the WP_List_Table,
				// since this requires much effort this is pointe to go to the pro version. NO ETA avaiable.
				//
				// add_submenu_page( $parent_slug ,
				// 				__( 'Subscribers Emails', 'subscribe-to-comments-reloaded' ),
				// 				__( 'Subscribers Emails', 'subscribe-to-comments-reloaded' ),
				// 				 $capability,
				// 				 "stcr_subscribers_emails",
				// 				 array( $this, "stcr_option_subscribers_emails" ) );

				add_submenu_page( $parent_slug ,
								__( 'Support', 'subscribe-to-comments-reloaded' ),
								__( 'Support', 'subscribe-to-comments-reloaded' ),
								 $capability,
								 "stcr_support",
								 array( $this, "stcr_option_support" ) );
//				add_submenu_page( $parent_slug ,
//								__( 'Donate', 'subscribe-to-comments-reloaded' ),
//								__( 'Donate', 'subscribe-to-comments-reloaded' ),
//								 $capability,
//								 "stcr_donate",
//								 array( $this, "stcr_option_donate" ) );
				add_submenu_page( $parent_slug ,
								__( 'StCR System', 'subscribe-to-comments-reloaded' ),
								__( 'StCR System', 'subscribe-to-comments-reloaded' ),
								 $capability,
								 "stcr_system",
								 array( $this, "stcr_option_system" ) );
			}

			return $_s;
		}
		// end add_config_menu

		/**
		 * Dispaly the stcr_option_manage_subscriptions template
		 * @since 160316
		 * @author reedyseth
		 */
		public function stcr_option_manage_subscriptions()
		{
			//must check that the user has the required capability
		    if (!current_user_can('manage_options'))
		    {
		    	wp_die( __('You do not have sufficient permissions to access this page.') );
		    }

		    $this->add_options_stylesheet();
		    global $wp_subscribe_reloaded;

			// echo 'New Page Settings';
			if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php" ) )
			{
				// What panel to display
				$current_panel = 2;
				require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php";
				if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_manage_subscriptions.php" ) )
				{
					require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_manage_subscriptions.php";
				}
			}
		}
		/**
		 * Dispaly the stcr_option_comment_form template
		 * @since 160316
		 * @author reedyseth
		 */
		public function stcr_option_comment_form()
		{
			//must check that the user has the required capability
		    if (!current_user_can('manage_options'))
		    {
		    	wp_die( __('You do not have sufficient permissions to access this page.') );
		    }

		    $this->add_options_stylesheet();

			// echo 'New Page Settings';
			if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php" ) )
			{
				// What panel to display
				$current_panel = 2;
				require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php";
				if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_comment_form.php" ) )
				{
					require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_comment_form.php";
				}
			}
		}
		/**
		 * Dispaly the stcr_option_management_page template
		 * @since 160316
		 * @author reedyseth
		 */
		public function stcr_option_management_page()
		{
			//must check that the user has the required capability
		    if (!current_user_can('manage_options'))
		    {
		    	wp_die( __('You do not have sufficient permissions to access this page.') );
		    }

		    $this->add_options_stylesheet();

			// echo 'New Page Settings';
			if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php" ) )
			{
				// What panel to display
				$current_panel = 2;
				require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php";
				if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_management_page.php" ) )
				{
					require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_management_page.php";
				}
			}
		}
		/**
		 * Dispaly the stcr_option_notifications template
		 * @since 160316
		 * @author reedyseth
		 */
		public function stcr_option_notifications()
		{
			//must check that the user has the required capability
		    if (!current_user_can('manage_options'))
		    {
		    	wp_die( __('You do not have sufficient permissions to access this page.') );
		    }

		    $this->add_options_stylesheet();

			// echo 'New Page Settings';
			if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php" ) )
			{
				// What panel to display
				$current_panel = 2;
				require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php";
				if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_notifications.php" ) )
				{
					require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_notifications.php";
				}
			}
		}
		/**
		 * Dispaly the stcr_option_options template
		 * @since 160316
		 * @author reedyseth
		 */
		public function stcr_option_options()
		{
			//must check that the user has the required capability
		    if (!current_user_can('manage_options'))
		    {
		    	wp_die( __('You do not have sufficient permissions to access this page.') );
		    }

		    $this->add_options_stylesheet();

			// echo 'New Page Settings';
			if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php" ) )
			{
				// What panel to display
				$current_panel = 2;
				require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php";
				if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_options.php" ) )
				{
					require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_options.php";
				}
			}
		}
		/**
		 * Dispaly the stcr_option_support template
		 * @since 160316
		 * @author reedyseth
		 */
		public function stcr_option_support()
		{
			//must check that the user has the required capability
		    if (!current_user_can('manage_options'))
		    {
		    	wp_die( __('You do not have sufficient permissions to access this page.') );
		    }

		    $this->add_options_stylesheet();

			// echo 'New Page Settings';
			if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php" ) )
			{
				// What panel to display
				$current_panel = 2;
				require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php";
				if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_support.php" ) )
				{
					require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_support.php";
				}
			}
		}


		/**
		 * Dispaly the stcr_option_donate template
		 * @since 160524
		 * @author reedyseth
		 */
		public function stcr_option_system()
		{
			//must check that the user has the required capability
		    if (!current_user_can('manage_options'))
		    {
		    	wp_die( __('You do not have sufficient permissions to access this page.') );
		    }

		    $this->add_options_stylesheet();

			// echo 'New Page Settings';
			if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php" ) )
			{
				// What panel to display
				$current_panel = 2;
				require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/index.php";
				if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_system.php" ) )
				{
					require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/stcr_system.php";
				}
			}
		}

		/**
		 * Download system information file
		 *
		 * @since       190325
		 * @return      void
		 */
		function sysinfo_download() {

			if ( ! isset( $_POST['stcr_sysinfo_action'] ) ) {
				return;
			}

			if ( $_POST['stcr_sysinfo_action'] != 'download_sysinfo' ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			nocache_headers();

			header( 'Content-Type: text/plain' );
			header( 'Content-Disposition: attachment; filename="stcr-sysinfo.txt"' );

			echo stripslashes( $_POST['stcr_sysinfo'] );
			
			exit;

		}

		/**
		 * Adds a custom stylesheet file to the admin interface
		 */
		public function add_options_stylesheet() {			
			$stylesheet_url = plugins_url( 'subscribe-to-comments-reloaded/style.css' );
			wp_register_style( 'subscribe-to-comments', $stylesheet_url );
			wp_enqueue_style( 'subscribe-to-comments' );
		}

		public function add_post_comments_stylesheet() {
			$stylesheet_url = plugins_url( 'subscribe-to-comments-reloaded/post-and-comments.css' );
			wp_register_style( 'subscribe-to-comments', $stylesheet_url );
			wp_enqueue_style( 'subscribe-to-comments' );
		}
		// end add_stylesheet

		/**
		 * Adds custom HTML code to the HEAD section of the management page
		 */
		public function add_custom_header_meta() {
			$a = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_custom_header_meta', '' ) ), ENT_QUOTES, 'UTF-8' );
			echo $a;
		}
		// end add_custom_header_meta

		/**
		 * Adds a new column header to the Edit Comments panel
		 */
		public function add_column_header( $_columns ) {
			$image_url                      = plugins_url( 'subscribe-to-comments-reloaded/images' );
			$image_tooltip                  = __( 'Subscriptions', 'subscribe-to-comments-reloaded' );
			$_columns['subscribe-reloaded'] = "<span class='hidden'>" . $image_tooltip . "</span><img src='$image_url/subscribe-to-comments-small.png' width='17' height='12' alt='" . $image_tooltip . "' title='" . $image_tooltip . "' />";

			return $_columns;
		}
		// end add_comment_column_header

		/**
		 * Adds a new column to the Edit Comments panel
		 */
		public function add_comment_column( $_column_name ) {
			if ( 'subscribe-reloaded' != $_column_name ) {
				return;
			}

			global $comment;
			$subscription = $this->get_subscriptions(
				array(
					'post_id',
					'email'
				), array(
				'equals',
				'equals'
			), array(
				$comment->comment_post_ID,
				$comment->comment_author_email
			), 'dt', 'DESC', 0, 1
			);
			if ( count( $subscription ) == 0 ) {
				echo '<a href="admin.php?page=stcr_manage_subscriptions&subscribepanel=1&amp;sra=add-subscription&amp;srp=' . $comment->comment_post_ID . '&amp;sre=' . urlencode( $comment->comment_author_email ) . '">' . __( 'No', 'subscribe-to-comments-reloaded' ) . '</a>';
			} else {
				echo '<a href="admin.php?page=stcr_manage_subscriptions&subscribepanel=1&amp;srf=email&amp;srt=equals&amp;srv=' . urlencode( $comment->comment_author_email ) . '">' . $subscription[0]->status . '</a>';
			}
		}
		// end add_column

		/**
		 * Adds a new column to the Posts management panel
		 */
		public function add_post_column( $_column_name ) {
			if ( 'subscribe-reloaded' != $_column_name ) {
				return;
			}

			global $post;
			echo '<a href="admin.php?page=stcr_manage_subscriptions&subscribepanel=1&amp;srf=post_id&amp;srt=equals&amp;srv=' . $post->ID . '">' . count( $this->get_subscriptions( 'post_id', 'equals', $post->ID ) ) . '</a>';
		}
		// end add_column

		/**
		 * Contextual help (link to the support forum)
         * @deprecated
		 */
		public function contextual_help( $contextual_help, $screen_id, $screen ) {
			if ( $screen_id == 'subscribe-to-comments-reloaded/options/index' ) {
				load_plugin_textdomain( 'subscribe-to-comments-reloaded', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
				$contextual_help = __( 'Need help on how to use Subscribe to Comments Reloaded? Visit the official', 'subscribe-to-comments-reloaded' ) . ' <a href="http://wordpress.org/tags/subscribe-to-comments-reloaded?forum_id=10" target="_blank">' . __( 'support forum', 'subscribe-to-comments-reloaded' ) . '</a>. ';
			}

			return $contextual_help;
		}
		// end contextual_help

		/**
		 * Returns the URL of the management page as a shortcode
		 */
		public function subscribe_url_shortcode() {
			global $post;
			$user_link = get_bloginfo( 'url' ) . get_option( 'subscribe_reloaded_manager_page', '' );
			if ( function_exists( 'qtrans_convertURL' ) ) {
				$user_link = qtrans_convertURL( $user_link );
			}
			if ( strpos( $user_link, '?' ) !== false ) {
				return "$user_link&amp;srp=$post->ID&amp;sra=s";
			} else {
				return "$user_link?srp=$post->ID&amp;sra=s";
			}
		}
		// end subscribe_url_shortcode

		/**
		 * Exclude subscriptions on post duplication
		 * 
		 * @since 200625
		 */
		public function duplicate_post_exclude_subs( $exclude ) {

			return array_merge( $exclude, array( '_stcr' ) ); 

		}

	}
}