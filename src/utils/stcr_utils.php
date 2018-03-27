<?php
/**
 * Class with utility functions. This functions are all over the plugin.
 * @author reedyseth
 * @since 15-Jul-2015
 * @version 160831
 */
namespace stcr;
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}

if( ! class_exists('\\'.__NAMESPACE__.'\\stcr_utils') )
{
	class stcr_utils {
        /**
         * Check a given email to be valid.
         *
         * @since 15-Feb-2018
         * @author Reedyseth
         * @param $email
         * @return mixed
         */
	    public function check_valid_email( $email )
        {
            $email = trim( $email );
            $email = sanitize_email( $email );
            return filter_var( $email, FILTER_VALIDATE_EMAIL );
        }
        /**
         * Check for a valid number.
         *
         * @since 15-Feb-2018
         * @author Reedyseth
         * @param $number String|Integer to be validated
         * @return bool True if number false otherwise
         */
        public function check_valid_number( $number )
        {
            $valid = true;

            if ( ! is_numeric( $number ) )
            {
                $valid = false;
            }

            return $valid;
        }

		/*
		 * This will retrieve an user/email from the prefix_subscribe_reloaded_subscribers table.
		 * @param String $email email to be added.
		 * @return Mix false|unique_key false on failure, key un success
		 * */
		public function get_subscriber_key( $email = null) {
			global $wpdb;
			$subscriber = null;
			// Check if the user is register and the unique key
			$retrieveEmail = "SELECT salt, subscriber_unique_id FROM ".$wpdb->prefix."subscribe_reloaded_subscribers WHERE subscriber_email = %s";
			if( $email != null ) {
				$subscriber = $wpdb->get_row($wpdb->prepare($retrieveEmail,$email), OBJECT);
				if( ! empty( $subscriber ) ) {
					return $subscriber->subscriber_unique_id;
				}
			}
			return false;
		}
		/*
		 * This will add an user/email to the prefix_subscribe_reloaded_subscribers table.
		 * @param String $email email to be added.
		 * @return Boolean true|false true on success, false on failure
		 * */
		public function remove_user_subscriber_table($_email) {
			global $wpdb;

			$OK = $wpdb->query(
				"DELETE FROM ".$wpdb->prefix."subscribe_reloaded_subscribers WHERE subscriber_email = '$_email'"
			);
			return $OK === false || $OK == 0 || empty( $OK ) ? false : $OK;
		}

		/*
		 * This will add an user/email to the prefix_subscribe_reloaded_subscribers table.
		 * @param String $email email to be added.
		 * @return Boolean true|false true on success, false on failure
		 * */
		public function add_user_subscriber_table($_email) {
			global $wpdb;
			$OK = false;
			$checkEmailSql = "SELECT COUNT(subscriber_email) FROM " . $wpdb->prefix . "subscribe_reloaded_subscribers WHERE subscriber_email = %s";
			$numSubscribers = $wpdb->get_var( $wpdb->prepare($checkEmailSql, $_email) );
			// If subscribers not found then add it to the subscribers table.
			if ( (int)$numSubscribers == 0 ) {
				$salt = time();
				// Insert query
				$OK = $wpdb->insert(
					$wpdb->prefix . "subscribe_reloaded_subscribers",
					array(
						"subscriber_email"     => $_email,
						"salt"                 => $salt,
						"subscriber_unique_id" => $this->generate_temp_key( $salt . $_email )
					),
					array(
						"%s",
						"%d",
						"%s"
					)
				);
			}
			return $OK === false || $OK == 0 || empty( $OK ) ? false : $OK;
		}
		/**
		 * @param null|key $key the Unique Key of the email
		 *
		 * @return bool|String false if no key is found or the email if found.
		 */
		public function get_subscriber_email_by_key( $key = null) {
			global $wpdb;

			if( $key != null ) {
				// Sanitize the key just for precaution.
				$key = trim( esc_attr($key) );
				// Check if the user is register and the unique key
				$retrieveEmail = "SELECT subscriber_email FROM "
					.$wpdb->prefix."subscribe_reloaded_subscribers WHERE subscriber_unique_id = %s";

				$subscriber = $wpdb->get_row($wpdb->prepare($retrieveEmail,$key), OBJECT);

				if( ! empty( $subscriber->subscriber_email ) ) {
					return $subscriber->subscriber_email;
				}
			}
			return false;
		}
		/**
		 * Generate a unique key to allow users to manage their subscriptions
		 */
		public function generate_key( $_email = "" ) {
			$salt      = time();
			$dd_salt   = md5( $salt );
			$uniqueKey = md5( $dd_salt . $salt . $_email );

			return $uniqueKey;
		}

		public function generate_temp_key( $_email ) {
			$uniqueKey = get_option( "subscribe_reloaded_unique_key" );
			$key       = md5( $uniqueKey . $_email );

			return $key;
		}
		// end generate_key
        public function stcr_translate_month( $date_str )
        {
            $months_long = array (
                "January" => __("January","subscribe-reloaded"),
                "February" => __("February","subscribe-reloaded"),
                "March" => __("March","subscribe-reloaded"),
                "April" => __("April","subscribe-reloaded"),
                "May" => __("May","subscribe-reloaded"),
                "June" => __("June","subscribe-reloaded"),
                "July" => __("July","subscribe-reloaded"),
                "August" => __("August","subscribe-reloaded"),
                "September" => __("September","subscribe-reloaded"),
                "October" => __("October","subscribe-reloaded"),
                "November" => __("November","subscribe-reloaded"),
                "December" => __("December","subscribe-reloaded")
            );

            $months_short = array (
                "Jan" => __("Jan","subscribe-reloaded"),
                "Feb" => __("Feb","subscribe-reloaded"),
                "Mar" => __("Mar","subscribe-reloaded"),
                "Apr" => __("Apr","subscribe-reloaded"),
                "May" => __("May","subscribe-reloaded"),
                "Jun" => __("Jun","subscribe-reloaded"),
                "Jul" => __("Jul","subscribe-reloaded"),
                "Aug" => __("Aug","subscribe-reloaded"),
                "Sep" => __("Sep","subscribe-reloaded"),
                "Oct" => __("Oct","subscribe-reloaded"),
                "Nov" => __("Nov","subscribe-reloaded"),
                "Dec" => __("Dec","subscribe-reloaded")
            );

            // Replace String
            foreach( $months_long as $key => $value)
            {
                $date_str = str_replace( $key, $value, $date_str);
            }
            // Find String
            foreach( $months_short as $key => $value)
            {
                $date_str = str_replace( $key, $value, $date_str);
            }
            // Return string
            return $date_str;
        }
		/**
		 * Creates the HTML structure to properly handle HTML messages
		 */
		public function wrap_html_message( $_message = '', $_subject = '' ) {
			global $wp_locale;
			$_message = apply_filters( 'stcr_wrap_html_message', $_message );
			// Add HTML paragraph tags to comment
			// See wp-includes/formatting.php for details on the wpautop() function
			$_message = wpautop( $_message );

			if( $wp_locale->text_direction == "rtl")
			{
				$locale = get_locale();
				$html = "<html xmlns='http://www.w3.org/1999/xhtml' dir='rtl' lang='$locale'>";
				$head = "<head><title>$_subject</title></head>";
				$body = "<body>$_message</body>";
				return $html . $head . $body . "</html>";
			}
			else
			{
				$html = "<html>";
				$head = "<head><title>$_subject</title></head>";
				$body = "<body>$_message</body>";
				return $html . $head . $body . "</html>";
			}

		}
		// end _wrap_html_message

		/**
		 * Returns an email address where some possible 'offending' strings have been removed
		 */
		public function clean_email( $_email ) {
			$offending_strings = array(
				"/to\:/i",
				"/from\:/i",
				"/bcc\:/i",
				"/cc\:/i",
				"/content\-transfer\-encoding\:/i",
				"/content\-type\:/i",
				"/mime\-version\:/i"
			);

			return esc_attr( stripslashes( strip_tags( preg_replace( $offending_strings, '', $_email ) ) ) );
		}
		// end clean_email

        public function create_options()
        {
            // Messages related to the management page
            global $wp_rewrite;

            if ( empty( $wp_rewrite->permalink_structure ) ) {
                add_option( 'subscribe_reloaded_manager_page', '/?page_id=99999', '', 'yes' );
            } else {
                add_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/', '', 'yes' );
            }

            add_option( 'subscribe_reloaded_unique_key', $this->generate_key(), '', 'yes' );
            add_option( 'subscribe_reloaded_fresh_install', 'no', '', 'yes' );
            add_option( 'subscribe_reloaded_safely_uninstall', 'yes', '', 'yes' );
            add_option( 'subscribe_reloaded_stcr_position', 'no', '', 'yes' );
            add_option( 'subscribe_reloaded_reply_to', '', '', 'yes' );
            add_option( 'subscribe_reloaded_oneclick_text', "<p>Your are not longer subscribe to the post:</p>\r\n\r\n<h3>[post_title]</h3>\r\n<br>", '', 'yes' );
            add_option( 'subscribe_reloaded_subscriber_table', 'no', '', 'yes' );
            add_option( 'subscribe_reloaded_data_sanitized', 'yes', '', 'yes' );
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
            add_option( 'subscribe_reloaded_notification_content', __( "<h1>There is a new comment on [post_title].</h1>\n\n<hr />\n<strong>Comment link:</strong> <a href=\"[comment_permalink]\">[comment_permalink]</a>\n<strong>Author:</strong> [comment_author]\n\n<strong>Comment:</strong>\n[comment_content]\n<div style=\"font-size: 0.8em;\"><strong>Permalink:</strong> <a href=\"[post_permalink]\">[post_permalink]</a>\n<a href=\"[manager_link]\">Manage your subscriptions</a> | <a href=\"[oneclick_link]\">One click unsubscribe</a></div>", 'subscribe-reloaded' ), '', 'yes' );
            add_option( 'subscribe_reloaded_double_check_subject', __( 'Please confirm your subscription to [post_title]', 'subscribe-reloaded' ), '', 'yes' );
            add_option( 'subscribe_reloaded_double_check_content', __( "You have requested to be notified every time a new comment is added to:\n<a href='[post_permalink]'>[post_permalink]</a>\n\nPlease confirm your request by clicking on this link:\n<a href='[confirm_link]'>[confirm_link]</a>", 'subscribe-reloaded' ), '', 'yes' );
            add_option( 'subscribe_reloaded_management_subject', __( 'Manage your subscriptions on [blog_name]', 'subscribe-reloaded' ) );
            add_option( 'subscribe_reloaded_management_content', __( "You have requested to manage your subscriptions to the articles on [blog_name]. Please check the Subscriptions management link in your email", 'subscribe-reloaded' ) );
            add_option( 'subscribe_reloaded_management_email_content', __( "You have requested to manage your subscriptions to the articles on [blog_name]. Follow this link to access your personal page:\n<a href='[manager_link]'>[manager_link]</a>", 'subscribe-reloaded' ) );

            add_option( 'subscribe_reloaded_purge_days', '30', '', 'yes' );
            add_option( 'subscribe_reloaded_enable_double_check', 'no', '', 'yes' );
            add_option( 'subscribe_reloaded_notify_authors', 'no', '', 'yes' );
            add_option( 'subscribe_reloaded_enable_html_emails', 'yes', '', 'yes' );
            add_option( 'subscribe_reloaded_htmlify_message_links', 'no', '', 'yes' );
            add_option( 'subscribe_reloaded_process_trackbacks', 'no', '', 'yes' );
            add_option( 'subscribe_reloaded_enable_admin_messages', 'no', '', 'yes' );
            add_option( 'subscribe_reloaded_admin_subscribe', 'no', '', 'yes' );
            add_option( 'subscribe_reloaded_admin_bcc', 'no', '', 'yes' );
            add_option( 'subscribe_reloaded_enable_log_data', 'no', '', 'yes' );
            add_option( 'subscribe_reloaded_auto_clean_log_data', 'yes', '', 'yes' );
            add_option( 'subscribe_reloaded_auto_clean_log_frecuency', 'daily', '', 'yes' );
            add_option( 'subscribe_reloaded_enable_font_awesome', 'yes', '', 'yes' );
            add_option( 'subscribe_reloaded_delete_options_subscriptions', 'no', '', 'no' );
            add_option( 'subscribe_reloaded_date_format', 'd M Y', '', 'no' );
        }
        /**
         * @since 08-February-2018
         * @author reedyseth
         * @param $delete_subscriptions String Decide either to delete the subscriptions or not.
         * @return true|false Boolean on success or failure.
         */
        public function delete_all_settings( $delete_subscriptions = no )
        {
            global $wpdb;
            $sql = "SELECT * FROM $wpdb->options WHERE option_name like 'subscribe_reloaded\_%'
 		            ORDER BY option_name";
            $stcr_options  = $wpdb->get_results( $sql , OBJECT );
            if( $stcr_options !== false && is_array( $stcr_options ) )
            {
                // Drop Only the Settings and not the subscriptions.
                // Goodbye options...
                foreach($stcr_options as $option)
                {
                    delete_option( $option->option_name );
                }
                if ( $delete_subscriptions == "yes" )
                {
                    // Delete the subscriptions in both tables.
                    $wpdb->query( "DELETE FROM {$wpdb->prefix}subscribe_reloaded_subscribers" );
                    $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '\_stcr@\_%'" );
                }
            }


            if ( $stcr_options === false )
            {
                return false;
            }
            else
            {
                return true;
            }
        }
		/**
		 * Will send an email by adding the correct headers.
		 *
		 * @since 28-May-2016
		 * @author reedyseth
		 * @param $_emailSettings Array Associative array with settings.
		 * @return true|false Boolean On success or failure
		 */
		public function send_email( $_settings )
		{	// Retrieve the options from the database
			$from_name    = html_entity_decode(
								stripslashes( get_option( 'subscribe_reloaded_from_name', 'admin' ) ), ENT_QUOTES, 'UTF-8' );
			$from_email   = get_option( 'subscribe_reloaded_from_email', get_bloginfo( 'admin_email' ) );
			$reply_to     = get_option( "subscribe_reloaded_reply_to" ) == ''
									? $from_email : get_option( "subscribe_reloaded_reply_to" );
			$content_type = $content_type = (  get_option(  'subscribe_reloaded_enable_html_emails' ) == 'yes' )
									?  'text/html'  :  'text/plain';
			$headers      = "Content-Type: $content_type; charset=" . get_bloginfo( 'charset' ) . "\n";
			$date = date_i18n( 'Y-m-d H:i:s' );

			$_emailSettings = array(
				'fromEmail'    =>  $from_email,
				'fromName'     =>  $from_name,
				'toEmail'      =>  '',
				'subject'      =>  __('StCR Notification' ,'subscribe-reloaded'),
				'message'      =>  '',
				'bcc'          =>  '',
				'reply_to'     =>  $reply_to,
				'XPostId'    =>  '0',
				'XCommentId' =>  '0'
			);

			$_emailSettings = array_merge( $_emailSettings, $_settings );

			if ( $content_type == 'text/html' ) {
				$_emailSettings[ 'message' ] = $this->wrap_html_message( $_emailSettings['message'], $_emailSettings['subject'] );
			}

			$headers .= "From: \"{$_emailSettings['fromName']}\" <{$_emailSettings['fromEmail']}>\n";
			$headers .= "Reply-To: {$_emailSettings['reply_to']}\n";
			$headers .= "X-Post-Id: {$_emailSettings['XPostId']}\n";
			$headers .= "X-Comment-Id: {$_emailSettings['XCommentId']}\n";

			if ( get_option( 'subscribe_reloaded_admin_bcc' ) == 'yes' ) {
				$headers .= "Bcc: $from_name <$from_email>\n"; // The StCR email define or otherwise the blog admin.
			}

			$this->stcr_logger( "*********************************************************************************" );
			$this->stcr_logger( "\n\nDate:			" . $date );
			$this->stcr_logger( "\n\nTo Email:		" . $_emailSettings['toEmail'] );
			$this->stcr_logger( "\n\nFrom Email: 	" . $_emailSettings['fromEmail'] );
			$this->stcr_logger( "\n\nMessage: 		" . $_emailSettings['message'] );
			$this->stcr_logger( "\n\nHeaders:\n\n" 	  . $headers );
			$this->stcr_logger( "*********************************************************************************" );

			$sent_result = ( wp_mail( $_emailSettings['toEmail'], $_emailSettings['subject'], $_emailSettings['message'], $headers ) )
						? true : false;
			if( ! $sent_result )
			{
				$this->stcr_logger( "*********************************************************************************" );
				$this->stcr_logger( "\nError sending email notification.\n" );
				$this->stcr_logger( "*********************************************************************************" );
			}

			return  $sent_result;
		}// End send_email
		/**
		 * Checks if a key is valid for a given email address
		 */
		public function _is_valid_key( $_key, $_email ) {
			if ( $this->generate_temp_key( $_email ) === $_key ) {
				return true;
			} else {
				return false;
			}
		}
		// end _is_valid_key
		/**
		 * Enqueue scripts to load the TinyMCE ritch editor. I could use the hook `after_wp_tiny_mce` but I will
		 * controll the tinyMCE version within my plugin instead of using the WordPress embedded one.
		 * @since 03-Agu-2015
		 * @author reedyseth
		 */
		public function add_ritch_editor_textarea() {
			wp_enqueue_script('stcr-tinyMCE');
			wp_enqueue_script('stcr-tinyMCE-js');
		}
		/**
		 * Register scripts for admin pages. I could use the hook `after_wp_tiny_mce` but I will
		 * controll the tinyMCE version within my plugin instead of using the WordPress embedded one.
		 * @since 03-Agu-2015
		 * @author reedyseth
		 */
		public function register_admin_scripts() {
			// // Tinymce not in use.
			// $tinyMCE_url    = ( is_ssl() ? str_replace( 'http://', 'https://', WP_PLUGIN_URL ) : WP_PLUGIN_URL ) . '/subscribe-to-comments-reloaded/includes/js/tinymce-lite/tinymce.min.js';
			// $tinyMCE_url_js = ( is_ssl() ? str_replace( 'http://', 'https://', WP_PLUGIN_URL ) : WP_PLUGIN_URL ) . '/subscribe-to-comments-reloaded/includes/js/stcr-tinyMCE.js';
			$stcr_admin_js  = ( is_ssl() ? str_replace( 'http://', 'https://', WP_PLUGIN_URL ) : WP_PLUGIN_URL ) . '/subscribe-to-comments-reloaded/includes/js/stcr-admin.js';
			$stcr_admin_css  = ( is_ssl() ? str_replace( 'http://', 'https://', WP_PLUGIN_URL ) : WP_PLUGIN_URL ) . '/subscribe-to-comments-reloaded/includes/css/stcr-admin-style.css';
            $stcr_font_awesome_css  = ( is_ssl() ? str_replace( 'http://', 'https://', WP_PLUGIN_URL ) : WP_PLUGIN_URL ) . '/subscribe-to-comments-reloaded/includes/css/font-awesome.min.css';
            // Javascript
            wp_register_script('stcr-admin-js', $stcr_admin_js, array( 'jquery' ) );
            // Enqueue Scripts
            wp_enqueue_script('stcr-admin-js');
            // // Styles
            wp_register_style( 'stcr-admin-style',  $stcr_admin_css );
            // Enqueue the styles
            wp_enqueue_style('stcr-admin-style');
            // Font Awesome
            if( get_option( 'subscribe_reloaded_enable_font_awesome' ) == "yes" )
            {
                wp_register_style( 'stcr-font-awesome', $stcr_font_awesome_css );
                wp_enqueue_style('stcr-font-awesome');
            }
        }
		/**
		 * Hooking scripts for admin pages.
		 * @since 03-Agu-2015
		 * @author reedyseth
		 */
		public function hook_admin_scripts() {
			// link the hooks
			add_action('admin_enqueue_scripts',array( $this, 'register_admin_scripts') );
		}
        /**
         * Hooking scripts for plugin pages.
         * @since 22-Sep-2015
         * @author reedyseth
         */
        public function hook_plugin_scripts() {
            // link the hooks
            add_action('wp_enqueue_scripts',array( $this, 'register_plugin_scripts') );
        }
		/**
		 * Register scripts for plugin pages.
		 * @since 22-Sep-2015
		 * @author reedyseth
		 */
		public function register_plugin_scripts() {
            $stcr_font_awesome_css = (is_ssl() ? str_replace('http://', 'https://', WP_PLUGIN_URL) : WP_PLUGIN_URL) . '/subscribe-to-comments-reloaded/includes/css/font-awesome.min.css';
            // Font Awesome
            if( get_option( 'subscribe_reloaded_enable_font_awesome' ) == "yes" )
            {
                wp_register_style( 'stcr-font-awesome', $stcr_font_awesome_css );
                wp_enqueue_style('stcr-font-awesome');
            }
		}
		/**
		 * Enqueue `style for plugin pages
		 * @since 22-Sep-2015
		 * @author reedyseth
		 */
		public function add_plugin_js_scripts() {
            // Enqueue Scripts
            //wp_enqueue_script('stcr-plugin-js');
		}
		/**
		 * Create a notice array with its settings and add it to the subscribe_reloaded_deferred_admin_notices
		 * option.
		 *
		 * @since 14-Agu-2015
		 * @author reedyseth
		 *
		 * @param string $_name Name of the notice.
		 * @param string $_status status read/unread. This will determine if the notice is display or not.
		 * @param string $_message Message that you want to show.
		 * @param string $_type What kind of notice you can use updated/error.
		 */
		public function stcr_create_admin_notice( $_name = '', $_status = 'unread', $_message = '', $_type = 'updated' ) {
			$notices   = get_option( 'subscribe_reloaded_deferred_admin_notices', array() );
			$notices[ $_name ] = array(
				"status" => $_status,
				"message" => $_message,
				"type"	=> $_type
			);
			update_option( 'subscribe_reloaded_deferred_admin_notices', $notices );
		}

		/**
		 * Update a given notice with the given arguments.
		 *
		 * @since 14-Agu-2015
		 * @author reedyseth
		 *
		 * @param string $_name Name of the notice.
		 * @param string $_status status read/unread. This will determine if the notice is display or not.
		 * @param string $_message Message that you want to show.
		 * @param string $_type What kind of notice you can use updated/error.
		 */
		public function stcr_update_admin_notice( $_name = '', $_status = 'unread', $_message = '', $_type = 'updated', $_nonce = 'nonce-key' ) {
			$notices = get_option( 'subscribe_reloaded_deferred_admin_notices' );
			foreach ( $notices as $key => $notice ) {
				if ( $key == $_name ) {
					$notices[ $key ] = array(
						"status" => $_status,
						"message" => $_message,
						"type"	=> $_type,
						"nonce" => $_nonce
					);
				}
			}
			update_option( 'subscribe_reloaded_deferred_admin_notices', $notices );
		}
		/**
		 * Update a given notice status.
		 *
		 * @since 18-Agu-2015
		 * @author reedyseth
		 *
		 * @param string $_name Name of the notice.
		 * @param string $_status status read/unread. This will determine if the notice is display or not.
		 */
		public function stcr_update_admin_notice_status( $_name = '', $_status = 'unread', $_nonce = 0 ) {
			$notices = get_option( 'subscribe_reloaded_deferred_admin_notices' );
			foreach ( $notices as $key => $notice ) {
				if ( $key == $_name ) {
					$notices[ $key ] = array(
						"status" => $_status,
						"message" => $notice['message'],
						"type"	=> $notice['type'],
						"nonce" => $_nonce
					);
				}
			}
			update_option( 'subscribe_reloaded_deferred_admin_notices', $notices );
		}
		/**
		 * Delete a given notice with the given arguments.
		 *
		 * @since 14-Agu-2015
		 * @author reedyseth
		 *
		 * @param string $_name Name of the notice to be deleted.
		 */
		public function stcr_remove_admin_notice( $_name = '' ) {
			$notices = get_option( 'subscribe_reloaded_deferred_admin_notices' );
			foreach ( $notices as $key => $notice ) {
				if ( $key == $_name ) {
					unset( $notices[ $key ] );
				}
			}
			update_option( 'subscribe_reloaded_deferred_admin_notices', $notices );
		}
		/**
		 * Bind the notices to the ajax hook.
		 *
		 * @since 14-Agu-2015
		 * @author reedyseth
		 *
		 * @param string $_notices The notifice to be binded.
		 */
		public function stcr_create_ajax_notices() {
			$notices = get_option( 'subscribe_reloaded_deferred_admin_notices' );

			if ( $notices ) {
				foreach ( $notices as $key => $notice ) {
					add_action( 'wp_ajax_' . $key, array( $this, 'stcr_ajax_update_notification') );
				}
			}
			return;
		}
		/**
		 * Update a StCR notification status
		 *
		 * @since 14-Agu-2015
		 * @author reedyseth
		 *
		 * @param string $_notification The notification Name of the notice to be deleted.
		 */
		public function stcr_ajax_update_notification () {
			$_notification = $_POST['action'];
			// Check Nonce
			check_ajax_referer( $_notification, 'security' );
			// Update status
			$this->stcr_update_admin_notice_status(  sanitize_text_field( $_notification ), 'read' ) ;
			// Send success message
			wp_send_json_success( 'Notification status updated for "' . $_notification . '"' );
			die();
		}
		/**
		 * Function to log messages into a given file. The variable $file_path must have writing permissions.
		 *
		 * @param  string $value The message to log
		 * @since 13-Mar-2016
		 * @author reedyseth
		 */
		public function stcr_logger( $value = '' )
		{
			$file_path = plugin_dir_path( __FILE__ );
			$file_name = "log.txt";
			$loggin_info = get_option("subscribe_reloaded_enable_log_data", "no");

			if( is_writable( $file_path ) && $loggin_info === "yes")
			{
				$file = fopen( $file_path . "/" . $file_name, "a" );

				fputs( $file , $value);

				fclose($file);
			}
			// else
			// {
			// 	throw new \Exception("The path $file_path is not writable, please check the folder Permissions.", 1);
			// }
		}
	}
}