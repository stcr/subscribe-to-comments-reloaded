<?php
/**
 * Class with management functions for Subscribe to Comments Reloaded
 * @author reedyseth
 * @since 16-Jul-2015
 * @version 160115
 */
namespace stcr {
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

			public $current_version = '160320';
			public $utils = null;
			public $upgrade = null;
			public $db_version = null;

			public function __construct() {
				$this->upgrade = new stcr_upgrade();
				$this->utils = new stcr_utils();
				$this->db_version = get_option( 'subscribe_reloaded_version' );
			}

			/**
			 * Search for a new version of code for a possible update
			 */
			public function admin_init() {

				if ( empty ( $this->db_version ) || (int) $this->db_version < (int) $this->current_version ) {
					// Do whatever upgrades needed here.
					$this->_activate();
					// Send the current version to display the appropiate message
					// The notification will only be visible once there is an update not a activation.
					$this->upgrade->upgrade_notification( $this->current_version, $this->db_version );
				} else {
					return;
				}
			}

			public function admin_notices() {
				$notices = get_option( 'subscribe_reloaded_deferred_admin_notices' );
				$nonce = null;

				if ( $notices ) {
					// Add JS script
					wp_enqueue_script( 'stcr-admin-js' );
					wp_enqueue_style( 'stcr-admin-style' );

					foreach ( $notices as $key => $notice ) {
						if( $notice['status'] == 'unread' ) {
							$nonce = wp_create_nonce( $key );
							// Set the a fresh nonce
							$this->utils->stcr_update_admin_notice_status( $key, 'unread', $nonce );
							echo "<div class='notice is-dismissible stcr-dismiss-notice  {$notice['type']}' data-nonce='{$nonce}|{$key}'>";
								echo $notice['message'];
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
				if (function_exists('pll_default_language')) {
					$currentLanguage = pll_current_language();  //get current active language
					$defaultLanguage = pll_default_language();  // get default language
					$currID = url_to_postid(get_option("subscribe_reloaded_manager_page"));  // get post id of subscription manager page
					$languageParameter = '';

					if(($currentLanguage != $defaultLanguage)) { // Generating user_link
						$translationIds = pll_get_post($currID, $currentLanguage);  // get post id of translated page
						$post = get_post($translationIds);
						$slug = $post->post_name;
						$languageParameter = '/' . $currentLanguage . '/' . $slug;
						$manager_link = get_bloginfo('url') . $languageParameter;
					} else {
						$manager_link = get_bloginfo('url') . $languageParameter . get_option( 'subscribe_reloaded_manager_page', '' );
					}
				}

				$clean_email     = $this->utils->clean_email( $_email );
				$subscriber_salt = $this->utils->generate_temp_key( $clean_email );

				$this->utils->add_user_subscriber_table( $clean_email );

				$manager_link .= ( ( strpos( $manager_link, '?' ) !== false ) ? '&' : '?' ) . "sre=" . $this->utils->get_subscriber_key( $clean_email ) . "&srk=$subscriber_salt";
				$confirm_link = "$manager_link&srp=$_post_ID&sra=c";

				$headers      = "From: $from_name <$from_email>\n";
				$content_type = ( get_option( 'subscribe_reloaded_enable_html_emails', 'no' ) == 'yes' ) ? 'text/html' : 'text/plain';
				$headers .= "Content-Type: $content_type; charset=" . get_bloginfo( 'charset' ) . "\n";

				$post           = get_post( $_post_ID );
				$post_permalink = get_permalink( $_post_ID );

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
				if ( $content_type == 'text/html' ) {
					if ( get_option( 'subscribe_reloaded_htmlify_message_links' ) == 'yes' ) {
						$message = $this->htmlify_message_links( $message );
					}
					$message = $this->utils->wrap_html_message( $message, $subject );
				}

				wp_mail( $clean_email, $subject, $message, $headers );
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
				load_plugin_textdomain( 'subscribe-reloaded', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

				// Import data from the WP Comment Subscriptions plugin, if needed
				$this->upgrade->_import_wpcs_data();

				// Import data from Subscribe to Comments & Co., if needed
				$this->upgrade->_import_stc_data();

				// Import data from Comment Reply Notification, if needed
				$this->upgrade->_import_crn_data();

				// Starting from version 2.0 StCR uses Wordpress' tables to store the information about subscriptions
				$this->upgrade->_update_db();

				// Since there are some users with the database corrupted due to encoding stuff we need to sanitize
				// their information
				$this->upgrade->_sanitize_db_information();

				// Messages related to the management page
				global $wp_rewrite;

				if ( empty( $wp_rewrite->permalink_structure ) ) {
					add_option( 'subscribe_reloaded_manager_page', '/?page_id=99999', '', 'yes' );
				} else {
					add_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/', '', 'yes' );
				}

				// Let us make sure that the Unique Key is created
				delete_option('subscribe_reloaded_unique_key');
				add_option( 'subscribe_reloaded_unique_key', $this->utils->generate_key(), '', 'yes' );
				add_option( 'subscribe_reloaded_commentbox_place', 'no', '', 'yes' );
				add_option( 'subscribe_reloaded_reply_to', '', '', 'yes' );
				add_option( 'subscribe_reloaded_oneclick_text', "<p>Your are not longer subscribe to the post:</p>\r\n\r\n<h3>[post_title]</h3>\r\n<br>", '', 'yes' );
				add_option( 'subscribe_reloaded_subscriber_table', 'no', '', 'yes' );
				add_option( 'subscribe_reloaded_data_sanitized', 'no', '', 'yes' );
				add_option( 'subscribe_reloaded_show_subscription_box', 'yes', '', 'yes' );
				add_option( 'subscribe_reloaded_checked_by_default', 'no', '', 'yes' );
				add_option( 'subscribe_reloaded_enable_advanced_subscriptions', 'no', '', 'yes' );
				add_option( 'subscribe_reloaded_default_subscription_type', '2', '', 'yes' );
				add_option( 'subscribe_reloaded_checked_by_default_value', '0', '', 'yes' );
				add_option( 'subscribe_reloaded_checkbox_inline_style', 'width:30px', '', 'yes' );
				add_option( 'subscribe_reloaded_checkbox_html', "<p class='comment-form-subscriptions'><label for='subscribe-reloaded'>[checkbox_field] [checkbox_label]</label></p>", '', 'yes' );
				add_option( 'subscribe_reloaded_checkbox_label', __( "Notify me of followup comments via e-mail. You can also <a href='[subscribe_link]'>subscribe</a> without commenting.", 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_subscribed_label', __( "You are subscribed to this post. <a href='[manager_link]'>Manage</a> your subscriptions.", 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_subscribed_waiting_label', __( "Your subscription to this post needs to be confirmed. <a href='[manager_link]'>Manage your subscriptions</a>.", 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_author_label', __( "You can <a href='[manager_link]'>manage the subscriptions</a> of this post.", 'subscribe-reloaded' ), '', 'yes' );

				add_option( 'subscribe_reloaded_manager_page_enabled', 'yes', '', 'yes' );
				add_option( 'subscribe_reloaded_virtual_manager_page_enabled', 'yes', '', 'yes' );
				add_option( 'subscribe_reloaded_manager_page_title', __( 'Manage subscriptions', 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_custom_header_meta', "<meta name='robots' content='noindex,nofollow'>", '', 'yes' );
				add_option( 'subscribe_reloaded_request_mgmt_link', __( 'To manage your subscriptions, please enter your email address here below. We will send you a message containing the link to access your personal management page.', 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_request_mgmt_link_thankyou', __( 'Thank you for using our subscription service. Your request has been completed, and you should receive an email with the management link in a few minutes.', 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_subscribe_without_commenting', __( "You can follow the discussion on <strong>[post_title]</strong> without having to leave a comment. Cool, huh? Just enter your email address in the form here below and you're all set.", 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_subscription_confirmed', __( "Thank you for using our subscription service. Your request has been completed. You will receive a notification email every time a new comment to this article is approved and posted by the administrator.", 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_subscription_confirmed_dci', __( "Thank you for using our subscription service. In order to confirm your request, please check your email for the verification message and follow the instructions.", 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_author_text', __( "In order to cancel or suspend one or more notifications, select the corresponding checkbox(es) and click on the button at the end of the list.", 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_user_text', __( "In order to cancel or suspend one or more notifications, select the corresponding checkbox(es) and click on the button at the end of the list. You are currently subscribed to:", 'subscribe-reloaded' ), '', 'yes' );

				add_option( 'subscribe_reloaded_from_name', get_bloginfo( 'name' ), '', 'yes' );
				add_option( 'subscribe_reloaded_from_email', get_bloginfo( 'admin_email' ), '', 'yes' );
				add_option( 'subscribe_reloaded_notification_subject', __( 'There is a new comment to [post_title]', 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_notification_content', __( "There is a new comment to [post_title].\nComment Link: [comment_permalink]\nAuthor: [comment_author]\nComment:\n[comment_content]\nPermalink: [post_permalink]\nManage your subscriptions: [manager_link]", 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_double_check_subject', __( 'Please confirm your subscription to [post_title]', 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_double_check_content', __( "You have requested to be notified every time a new comment is added to:\n[post_permalink]\n\nPlease confirm your request by clicking on this link:\n[confirm_link]", 'subscribe-reloaded' ), '', 'yes' );
				add_option( 'subscribe_reloaded_management_subject', __( 'Manage your subscriptions on [blog_name]', 'subscribe-reloaded' ) );
				add_option( 'subscribe_reloaded_management_content', __( "You have requested to manage your subscriptions to the articles on [blog_name]. Follow this link to access your personal page:\n[manager_link]", 'subscribe-reloaded' ) );

				add_option( 'subscribe_reloaded_purge_days', '30', '', 'yes' );
				add_option( 'subscribe_reloaded_enable_double_check', 'no', '', 'yes' );
				add_option( 'subscribe_reloaded_notify_authors', 'no', '', 'yes' );
				add_option( 'subscribe_reloaded_enable_html_emails', 'no', '', 'yes' );
				add_option( 'subscribe_reloaded_htmlify_message_links', 'no', '', 'yes' );
				add_option( 'subscribe_reloaded_process_trackbacks', 'no', '', 'yes' );
				add_option( 'subscribe_reloaded_enable_admin_messages', 'no', '', 'yes' );
				add_option( 'subscribe_reloaded_admin_subscribe', 'no', '', 'yes' );
				add_option( 'subscribe_reloaded_admin_bcc', 'no', '', 'yes' );

				// Create a new table if not exists to manage the subscribers safer
				$this->upgrade->_create_subscriber_table();

				update_option( 'subscribe_reloaded_version', $this->current_version );

				// Schedule the autopurge hook
				if ( ! wp_next_scheduled( '_cron_subscribe_reloaded_purge' ) ) {
					wp_clear_scheduled_hook( '_cron_subscribe_reloaded_purge' );
					// Let us bind the schedule event with our desire action.
					wp_schedule_event( time() + 15, 'daily', '_cron_subscribe_reloaded_purge' );
				}
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
					}
					restore_current_blog();
				} else {
					wp_clear_scheduled_hook( '_cron_subscribe_reloaded_purge' );
				}
			}

			// end deactivate

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
					$parent_slug= "stcr_options";
					add_menu_page( $page_title, $menu_title, $capability, $parent_slug, $function, $icon_url, $position );

					add_submenu_page( $parent_slug ,
									__( 'Manage subscriptions', 'subscribe-reloaded' ),
									__( 'Manage subscriptions', 'subscribe-reloaded' ),
									 $capability,
									 "stcr_manage_subscriptions",
									 array( $this, "stcr_option_manage_subscriptions") );
					add_submenu_page( $parent_slug ,
									__( 'Comment Form', 'subscribe-reloaded' ),
									__( 'Comment Form', 'subscribe-reloaded' ),
									 $capability,
									 "stcr_comment_form",
									 array( $this, "stcr_option_comment_form" ) );
					add_submenu_page( $parent_slug ,
									__( 'Management Page', 'subscribe-reloaded' ),
									__( 'Management Page', 'subscribe-reloaded' ),
									 $capability,
									 "stcr_management_page",
									 array( $this, "stcr_option_management_page" ) );
					add_submenu_page( $parent_slug ,
									__( 'Notifications', 'subscribe-reloaded' ),
									__( 'Notifications', 'subscribe-reloaded' ),
									 $capability,
									 "stcr_notifications",
									 array( $this, "stcr_option_notifications" ) );
					add_submenu_page( $parent_slug ,
									__( 'Options', 'subscribe-reloaded' ),
									__( 'Options', 'subscribe-reloaded' ),
									 $capability,
									 "stcr_options",
									 array( $this, "stcr_option_options" ) );
					// @since 160316
					// Using this page requires to list a cool table, on this case will be the WP_List_Table,
					// since this requires much effort this is pointe to go to the pro version. NO ETA avaiable.
					//
					// add_submenu_page( $parent_slug ,
					// 				__( 'Subscribers Emails', 'subscribe-reloaded' ),
					// 				__( 'Subscribers Emails', 'subscribe-reloaded' ),
					// 				 $capability,
					// 				 "stcr_subscribers_emails",
					// 				 array( $this, "stcr_option_subscribers_emails" ) );
					add_submenu_page( $parent_slug ,
									__( 'You can help', 'subscribe-reloaded' ),
									__( 'You can help', 'subscribe-reloaded' ),
									 $capability,
									 "stcr_you_can_help",
									 array( $this, "stcr_option_you_can_help" ) );
					add_submenu_page( $parent_slug ,
									__( 'Support', 'subscribe-reloaded' ),
									__( 'Support', 'subscribe-reloaded' ),
									 $capability,
									 "stcr_support",
									 array( $this, "stcr_option_support" ) );
					add_submenu_page( $parent_slug ,
									__( 'Donate', 'subscribe-reloaded' ),
									__( 'Donate', 'subscribe-reloaded' ),
									 $capability,
									 "stcr_donate",
									 array( $this, "stcr_option_donate" ) );
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
					if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel1.php" ) )
					{
						require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel1.php";
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
					if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel2.php" ) )
					{
						require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel2.php";
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
					if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel3.php" ) )
					{
						require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel3.php";
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
					if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel4.php" ) )
					{
						require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel4.php";
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
					if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel5.php" ) )
					{
						require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel5.php";
					}
				}
			}
			/**
			 * Dispaly the stcr_option_subscribers_emails template
			 * @since 160316
			 * @author reedyseth
			 */
			public function stcr_option_subscribers_emails()
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
					if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel6.php" ) )
					{
						require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel6.php";
					}
				}
			}
			/**
			 * Dispaly the stcr_option_you_can_help template
			 * @since 160316
			 * @author reedyseth
			 */
			public function stcr_option_you_can_help()
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
					if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel7.php" ) )
					{
						require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel7.php";
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
					if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel8.php" ) )
					{
						require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel8.php";
					}
				}
			}
			/**
			 * Dispaly the stcr_option_donate template
			 * @since 160316
			 * @author reedyseth
			 */
			public function stcr_option_donate()
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
					if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel9.php" ) )
					{
						require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel9.php";
					}
				}
			}

			/**
			 * Adds a custom stylesheet file to the admin interface
			 */
			public function add_options_stylesheet() {
				// It looks like WP_PLUGIN_URL doesn't honor the HTTPS setting in wp-config.php
				$stylesheet_url = ( is_ssl() ? str_replace( 'http://', 'https://', WP_PLUGIN_URL ) : WP_PLUGIN_URL ) . '/subscribe-to-comments-reloaded/style.css';
				wp_register_style( 'subscribe-to-comments', $stylesheet_url );
				wp_enqueue_style( 'subscribe-to-comments' );
			}

			public function add_post_comments_stylesheet() {
				// It looks like WP_PLUGIN_URL doesn't honor the HTTPS setting in wp-config.php
				$stylesheet_url = ( is_ssl() ? str_replace( 'http://', 'https://', WP_PLUGIN_URL ) : WP_PLUGIN_URL ) . '/subscribe-to-comments-reloaded/post-and-comments.css';
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
				$image_url                      = ( is_ssl() ? str_replace( 'http://', 'https://', WP_PLUGIN_URL ) : WP_PLUGIN_URL ) . '/subscribe-to-comments-reloaded/images';
				$image_tooltip                  = __( 'Subscriptions', 'subscribe-reloaded' );
				$_columns['subscribe-reloaded'] = "<img src='$image_url/subscribe-to-comments-small.png' width='17' height='12' alt='" . $image_tooltip . "' title='" . $image_tooltip . "' />";

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
					echo '<a href="options-general.php?page=subscribe-to-comments-reloaded/options/index.php&subscribepanel=1&amp;sra=add-subscription&amp;srp=' . $comment->comment_post_ID . '&amp;sre=' . urlencode( $comment->comment_author_email ) . '">' . __( 'No', 'subscribe-reloaded' ) . '</a>';
				} else {
					echo '<a href="options-general.php?page=subscribe-to-comments-reloaded/options/index.php&subscribepanel=1&amp;srf=email&amp;srt=equals&amp;srv=' . urlencode( $comment->comment_author_email ) . '">' . $subscription[0]->status . '</a>';
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
				load_plugin_textdomain( 'subscribe-reloaded', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
				echo '<a href="options-general.php?page=subscribe-to-comments-reloaded/options/index.php&subscribepanel=1&amp;srf=post_id&amp;srt=equals&amp;srv=' . $post->ID . '">' . count( $this->get_subscriptions( 'post_id', 'equals', $post->ID ) ) . '</a>';
			}
			// end add_column

			/**
			 * Contextual help (link to the support forum)
			 */
			public function contextual_help( $contextual_help, $screen_id, $screen ) {
				if ( $screen_id == 'subscribe-to-comments-reloaded/options/index' ) {
					load_plugin_textdomain( 'subscribe-reloaded', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
					$contextual_help = __( 'Need help on how to use Subscribe to Comments Reloaded? Visit the official', 'subscribe-reloaded' ) . ' <a href="http://wordpress.org/tags/subscribe-to-comments-reloaded?forum_id=10" target="_blank">' . __( 'support forum', 'subscribe-reloaded' ) . '</a>. ';
					$contextual_help .= __( 'Feeling generous?', 'subscribe-reloaded' ) . ' <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=XF86X93FDCGYA&lc=US&item_name=Datasoft%20Engineering&item_number=DI%2dSTCR&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted" target="_blank">' . __( 'Donate a few bucks!', 'subscribe-reloaded' ) . '</a>';
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



		}
	}
}
