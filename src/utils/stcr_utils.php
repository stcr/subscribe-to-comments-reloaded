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

	    protected $menu_opts_cache = array();

	    public function __construct()
        {

        }

        public function __destruct()
        {
            // house keeping
            unset($this->menu_opts_cache);
        }

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
					return sanitize_key( $subscriber->subscriber_unique_id );
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

			$OK = $wpdb->query( $wpdb->prepare(
                "DELETE FROM " . $wpdb->prefix . "subscribe_reloaded_subscribers WHERE subscriber_email = %s",
                $_email
            ));
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
				$key = trim( sanitize_key($key) );
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
			$uniqueKey = md5( $dd_salt . $salt . sanitize_email( $_email ) );

			return $uniqueKey;
		}

		public function generate_temp_key( $_email ) {
			$uniqueKey = sanitize_key( get_option( "subscribe_reloaded_unique_key" ) );
			$key       = md5( $uniqueKey . $_email );

			return $key;
		}
		// end generate_key
        public function stcr_translate_month( $date_str )
        {
            $months_long = array (
                "January" => esc_html__("January",'subscribe-to-comments-reloaded'),
                "February" => esc_html__("February",'subscribe-to-comments-reloaded'),
                "March" => esc_html__("March",'subscribe-to-comments-reloaded'),
                "April" => esc_html__("April",'subscribe-to-comments-reloaded'),
                "May" => esc_html__("May",'subscribe-to-comments-reloaded'),
                "June" => esc_html__("June",'subscribe-to-comments-reloaded'),
                "July" => esc_html__("July",'subscribe-to-comments-reloaded'),
                "August" => esc_html__("August",'subscribe-to-comments-reloaded'),
                "September" => esc_html__("September",'subscribe-to-comments-reloaded'),
                "October" => esc_html__("October",'subscribe-to-comments-reloaded'),
                "November" => esc_html__("November",'subscribe-to-comments-reloaded'),
                "December" => esc_html__("December",'subscribe-to-comments-reloaded')
            );

            $months_short = array (
                "Jan" => esc_html__("Jan",'subscribe-to-comments-reloaded'),
                "Feb" => esc_html__("Feb",'subscribe-to-comments-reloaded'),
                "Mar" => esc_html__("Mar",'subscribe-to-comments-reloaded'),
                "Apr" => esc_html__("Apr",'subscribe-to-comments-reloaded'),
                "May" => esc_html__("May",'subscribe-to-comments-reloaded'),
                "Jun" => esc_html__("Jun",'subscribe-to-comments-reloaded'),
                "Jul" => esc_html__("Jul",'subscribe-to-comments-reloaded'),
                "Aug" => esc_html__("Aug",'subscribe-to-comments-reloaded'),
                "Sep" => esc_html__("Sep",'subscribe-to-comments-reloaded'),
                "Oct" => esc_html__("Oct",'subscribe-to-comments-reloaded'),
                "Nov" => esc_html__("Nov",'subscribe-to-comments-reloaded'),
                "Dec" => esc_html__("Dec",'subscribe-to-comments-reloaded')
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

        public function to_num_ini_notation( $size )
        {
            $l   = substr( $size, - 1 );
            $ret = substr( $size, 0, - 1 );
            switch ( strtoupper( $l ) ) {
                case 'P':
                    $ret *= 1024;
                case 'T':
                    $ret *= 1024;
                case 'G':
                    $ret *= 1024;
                case 'M':
                    $ret *= 1024;
                case 'K':
                    $ret *= 1024;
            }

            return $ret;
        }

        /**
         * Get plugin info including status
         *
         * This is an enhanced version of get_plugins() that returns the status
         * (`active` or `inactive`) of all plugins. Does not include MU plugins.
         *
         * @version 1.0.0
         * @since 28-Nov-2018
         *
         * @return array Plugin info plus status
         */
        function stcr_get_plugins() {
            $plugins             = get_plugins();
            $active_plugin_paths = (array) get_option( 'active_plugins', array() );

            if ( is_multisite() ) {
                $network_activated_plugin_paths = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
                $active_plugin_paths            = array_merge( $active_plugin_paths, $network_activated_plugin_paths );
            }

            foreach ( $plugins as $plugin_path => $plugin_data ) {
                // Is plugin active?
                if ( in_array( $plugin_path, $active_plugin_paths ) ) {
                    $plugins[ $plugin_path ]['Status'] = 'active';
                } else {
                    $plugins[ $plugin_path ]['Status'] = 'inactive';
                }
            }

            return $plugins;
        }

		/**
		 * Creates the HTML structure to properly handle HTML messages
		 */
		public function wrap_html_message( $_message = '', $_subject = '' ) {

            global $wp_locale;

            // HTML emails
            if ( get_option( 'subscribe_reloaded_enable_html_emails', 'yes' ) == 'yes' ) {

                $_message = apply_filters( 'stcr_wrap_html_message', $_message );
                $_message = wpautop( $_message );

                if( $wp_locale->text_direction == "rtl") {
                    $locale = get_locale();
                    $html = "<html xmlns='http://www.w3.org/1999/xhtml' dir='rtl' lang='$locale'>";
                    $head = "<head><title>$_subject</title></head>";
                    $body = "<body>$_message</body>";
                    return $html . $head . $body . "</html>";
                } else {
                    $html = "<html>";
                    $head = "<head><title>$_subject</title></head>";
                    $body = "<body>$_message</body>";
                    return $html . $head . $body . "</html>";
                }

            // Plain text emails
            } else {

                return $_message;

            }

		}

		/**
		 * Returns an email address where some possible 'offending' strings have been removed
		 */
		public function clean_email( $_email ) {

            if ( is_array( $_email) || is_object( $_email ) ) {
                return;
            }

			$offending_strings = array(
				"/to\:/i",
				"/from\:/i",
				"/bcc\:/i",
				"/cc\:/i",
				"/content\-transfer\-encoding\:/i",
				"/content\-type\:/i",
				"/mime\-version\:/i"
			);

            return sanitize_email( stripslashes( strip_tags( preg_replace( $offending_strings, '', strtolower( $_email ) ) ) ) );

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
            add_option( 'subscribe_reloaded_checkbox_label', wp_kses( __( "Notify me of followup comments via e-mail. You can also <a href='[subscribe_link]'>subscribe</a> without commenting.", 'subscribe-to-comments-reloaded' ), wp_kses_allowed_html( 'post' ) ), '', 'yes' );
            add_option( 'subscribe_reloaded_subscribed_label', wp_kses( __( "You are subscribed to this post. <a href='[manager_link]'>Manage</a> your subscriptions.", 'subscribe-to-comments-reloaded' ), wp_kses_allowed_html( 'post' ) ), '', 'yes' );
            add_option( 'subscribe_reloaded_subscribed_waiting_label', wp_kses( __( "Your subscription to this post needs to be confirmed. <a href='[manager_link]'>Manage your subscriptions</a>.", 'subscribe-to-comments-reloaded' ), wp_kses_allowed_html( 'post' ) ), '', 'yes' );
            add_option( 'subscribe_reloaded_author_label', wp_kses( __( "You can <a href='[manager_link]'>manage the subscriptions</a> of this post.", 'subscribe-to-comments-reloaded' ), wp_kses_allowed_html( 'post' ) ), '', 'yes' );

            add_option( 'subscribe_reloaded_manager_page_enabled', 'yes', '', 'yes' );
            add_option( 'subscribe_reloaded_virtual_manager_page_enabled', 'yes', '', 'yes' );
            add_option( 'subscribe_reloaded_manager_page_title', esc_html__( 'Manage subscriptions', 'subscribe-to-comments-reloaded' ), '', 'yes' );
            add_option( 'subscribe_reloaded_custom_header_meta', "<meta name='robots' content='noindex,nofollow'>", '', 'yes' );
            add_option( 'subscribe_reloaded_request_mgmt_link', esc_html__( 'To manage your subscriptions, please enter your email address here below. We will send you a message containing the link to access your personal management page.', 'subscribe-to-comments-reloaded' ), '', 'yes' );
            add_option( 'subscribe_reloaded_request_mgmt_link_thankyou', esc_html__( 'Thank you for using our subscription service. Your request has been completed, and you should receive an email with the management link in a few minutes.', 'subscribe-to-comments-reloaded' ), '', 'yes' );
            add_option( 'subscribe_reloaded_subscribe_without_commenting', wp_kses( __( "You can follow the discussion on <strong>[post_title]</strong> without having to leave a comment. Cool, huh? Just enter your email address in the form here below and you're all set.", 'subscribe-to-comments-reloaded' ), wp_kses_allowed_html( 'post' ) ), '', 'yes' );
            add_option( 'subscribe_reloaded_subscription_confirmed', esc_html__( "Thank you for using our subscription service. Your request has been completed. You will receive a notification email every time a new comment to this article is approved and posted by the administrator.", 'subscribe-to-comments-reloaded' ), '', 'yes' );
            add_option( 'subscribe_reloaded_subscription_confirmed_dci', esc_html__( "Thank you for using our subscription service. In order to confirm your request, please check your email for the verification message and follow the instructions.", 'subscribe-to-comments-reloaded' ), '', 'yes' );
            add_option( 'subscribe_reloaded_author_text', esc_html__( "In order to cancel or suspend one or more notifications, select the corresponding checkbox(es) and click on the button at the end of the list.", 'subscribe-to-comments-reloaded' ), '', 'yes' );
            add_option( 'subscribe_reloaded_user_text', esc_html__( "In order to cancel or suspend one or more notifications, select the corresponding checkbox(es) and click on the button at the end of the list. You are currently subscribed to:", 'subscribe-to-comments-reloaded' ), '', 'yes' );

            add_option( 'subscribe_reloaded_from_name', get_bloginfo( 'name' ), '', 'yes' );
            add_option( 'subscribe_reloaded_from_email', get_bloginfo( 'admin_email' ), '', 'yes' );
            add_option( 'subscribe_reloaded_notification_subject', esc_html__( 'There is a new comment to [post_title]', 'subscribe-to-comments-reloaded' ), '', 'yes' );
            add_option( 'subscribe_reloaded_notification_content', wp_kses( __( "<h1>There is a new comment on [post_title].</h1>\n\n<hr />\n<strong>Comment link:</strong> <a href=\"[comment_permalink]\">[comment_permalink]</a>\n<strong>Author:</strong> [comment_author]\n\n<strong>Comment:</strong>\n[comment_content]\n<div style=\"font-size: 0.8em;\"><strong>Permalink:</strong> <a href=\"[post_permalink]\">[post_permalink]</a>\n<a href=\"[manager_link]\">Manage your subscriptions</a> | <a href=\"[oneclick_link]\">One click unsubscribe</a></div>", 'subscribe-to-comments-reloaded' ), wp_kses_allowed_html( 'post' ) ), '', 'yes' );
            add_option( 'subscribe_reloaded_double_check_subject', esc_html__( 'Please confirm your subscription to [post_title]', 'subscribe-to-comments-reloaded' ), '', 'yes' );
            add_option( 'subscribe_reloaded_double_check_content', wp_kses( __( "You have requested to be notified every time a new comment is added to:\n<a href='[post_permalink]'>[post_permalink]</a>\n\nPlease confirm your request by clicking on this link:\n<a href='[confirm_link]'>[confirm_link]</a>", 'subscribe-to-comments-reloaded' ), wp_kses_allowed_html( 'post' ) ), '', 'yes' );
            add_option( 'subscribe_reloaded_management_subject', esc_html__( 'Manage your subscriptions on [blog_name]', 'subscribe-to-comments-reloaded' ) );
            add_option( 'subscribe_reloaded_management_content', esc_html__( "You have requested to manage your subscriptions to the articles on [blog_name]. Please check the Subscriptions management link in your email", 'subscribe-to-comments-reloaded' ) );
            add_option( 'subscribe_reloaded_management_email_content', wp_kses( __( "You have requested to manage your subscriptions to the articles on [blog_name]. Follow this link to access your personal page:\n<a href='[manager_link]'>[manager_link]</a>", 'subscribe-to-comments-reloaded' ), wp_kses_allowed_html( 'post' ) ) );

            add_option( 'subscribe_reloaded_purge_days', '30', '', 'yes' );
            add_option( 'subscribe_reloaded_enable_double_check', 'yes', '', 'yes' );
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

            // For blacklist email.
            add_option( 'subscribe_reloaded_blacklisted_emails', '', '', 'yes' );

            // For recaptcha version.
            add_option( 'subscribe_reloaded_recaptcha_version', 'v2', '', 'yes' );

            // For post type support.
            $post_type_supports = array();
            $args = array(
                '_builtin' => false,
                'public'   => true,
            );
            $post_types         = get_post_types( $args );
            $default_post_types =  array(
                'post',
                'page',
            );
            $post_types         = array_merge( $default_post_types, $post_types );
            foreach ( $post_types as $post_type ) {
                $post_type_supports[] = $post_type;
            }
            add_option( 'subscribe_reloaded_post_type_supports', $post_type_supports, '', 'yes' );
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
         * Enqueue a script that was previous registered,
         *
         * @since 28-Mar-2018
         * @author reedyseth
         * @param string $handle Script handle that will be enqueue
         */
        public function enqueue_script_to_wp( $handle )
        {
            wp_enqueue_script( $handle );
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
			$content_type = (  get_option(  'subscribe_reloaded_enable_html_emails' ) == 'yes' )
									?  'text/html'  :  'text/plain';
			$headers      = "Content-Type: $content_type; charset=" . get_bloginfo( 'charset' ) . "\n";
			$date = date_i18n( 'Y-m-d H:i:s' );

			$_emailSettings = array(
				'fromEmail'    =>  $from_email,
				'fromName'     =>  $from_name,
				'toEmail'      =>  '',
				'subject'      =>  esc_html__('StCR Notification' ,'subscribe-to-comments-reloaded'),
				'message'      =>  '',
				'bcc'          =>  '',
				'reply_to'     =>  $reply_to,
				'XPostId'    =>  '0',
				'XCommentId' =>  '0'
			);

			$_emailSettings = array_merge( $_emailSettings, $_settings );
            $_emailSettings[ 'message' ] = $this->wrap_html_message( $_emailSettings['message'], $_emailSettings['subject'] );

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
			wp_enqueue_script('stcr-tinyMCE'); // TODO: Only enqueue it the first time.
			wp_enqueue_script('stcr-tinyMCE-js');
		}
		/**
		 * Register scripts for admin pages. I could use the hook `after_wp_tiny_mce` but I will
		 * controll the tinyMCE version within my plugin instead of using the WordPress embedded one.
		 * @since 03-Agu-2015
		 * @author reedyseth
		 */
		public function register_admin_scripts( $hook ) {

            // paths
            $stcr_admin_js = plugins_url( '/includes/js/stcr-admin.js', STCR_PLUGIN_FILE );
            $stcr_admin_css = plugins_url( '/includes/css/stcr-admin-style.css', STCR_PLUGIN_FILE );

            // register scripts
            wp_register_script( 'stcr-admin-js', $stcr_admin_js, array( 'jquery' ) );
            wp_register_script( 'bootstrap', plugins_url( '/vendor/bootstrap/dist/js/bootstrap.bundle.min.js', STCR_PLUGIN_FILE ), array( 'jquery' ), false, true );
            wp_register_script( 'webui-popover', plugins_url( '/vendor/webui-popover/dist/jquery.webui-popover.min.js', STCR_PLUGIN_FILE ), array( 'jquery' ), false, true );
            wp_register_script( 'dataTables', plugins_url( '/vendor/datatables/media/js/jquery.dataTables.min.js', STCR_PLUGIN_FILE ), array( 'jquery' ), false, true );
            wp_register_script( 'dataTables-bootstrap4', plugins_url( '/vendor/datatables/media/js/dataTables.bootstrap4.min.js', STCR_PLUGIN_FILE ), array( 'jquery' ), false, true );
            wp_register_script( 'dataTables-responsive', plugins_url( '/vendor/datatables.net-responsive/js/dataTables.responsive.min.js', STCR_PLUGIN_FILE ), array( 'jquery' ), false, true );
            wp_register_script( 'responsive-bootstrap4', plugins_url( '/vendor/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js', STCR_PLUGIN_FILE ), array( 'jquery' ), false, true );

            // rergister styles
            wp_register_style( 'stcr-admin-style',  $stcr_admin_css );
            wp_register_style( 'fontawesome', plugins_url( '/vendor/Font-Awesome/web-fonts-with-css/css/fontawesome-all.min.css', STCR_PLUGIN_FILE ) );
            wp_register_style( 'bootstrap', plugins_url( '/vendor/bootstrap/dist/css/bootstrap.min.css', STCR_PLUGIN_FILE ) );
            wp_register_style( 'webui-popover', plugins_url( '/vendor/webui-popover/dist/jquery.webui-popover.min.css', STCR_PLUGIN_FILE ) );
            wp_register_style( 'datatables', plugins_url( '/vendor/datatables/media/css/jquery.dataTables.min.css', STCR_PLUGIN_FILE ) );
            wp_register_style( 'datatables-bootstrap4', plugins_url( '/vendor/datatables/media/css/dataTables.bootstrap4.min.css', STCR_PLUGIN_FILE ) );
            wp_register_style( 'datatables-net-responsive-bs4', plugins_url( '/vendor/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css', STCR_PLUGIN_FILE ) );

            // check if we're on our pages
            if ( strpos( $hook, 'stcr' ) !== false ) {

                // enqueue scripts
                wp_enqueue_script( 'stcr-admin-js' );
                wp_enqueue_script( 'bootstrap' );
                wp_enqueue_script( 'webui-popover' );
                wp_enqueue_script( 'dataTables' );
                wp_enqueue_script( 'dataTables-bootstrap4' );
                wp_enqueue_script( 'dataTables-responsive' );
                wp_enqueue_script( 'responsive-bootstrap4' );

                // enqueue styles
                wp_enqueue_style( 'stcr-admin-style' );
                wp_enqueue_style( 'fontawesome' );
                wp_enqueue_style( 'bootstrap' );
                wp_enqueue_style( 'webui-popover' );
                wp_enqueue_style( 'datatables' );
                wp_enqueue_style( 'datatables-bootstrap4' );
                wp_enqueue_style( 'datatables-net-responsive-bs4' );

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
         * Registers a Javacsript file to the `wp_register_script` hook.
         *
         * @since 28-Mar-2018
         * @author reedyseth
         * @param string $handle Script handle the data will be attached to.
         * @param string $script_name JS File name.
         * @param string $path_add Sometimes the path is not in the root, therefore you can use this to complete the path.
         */
		public function register_script_to_wp( $handle, $script_name, $path_add = "" )
        {
            $js_resource  = plugins_url( "/$path_add/$script_name", STCR_PLUGIN_FILE );
            wp_register_script( $handle, $js_resource );
        }
        /**includes/js/admin
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

            $stcr_font_awesome_css = plugins_url( '/includes/css/font-awesome.min.css', STCR_PLUGIN_FILE );
            // Font Awesome
            if ( get_option( 'subscribe_reloaded_enable_font_awesome' ) == "yes" ) {
                wp_register_style( 'stcr-font-awesome', $stcr_font_awesome_css );
                wp_enqueue_style('stcr-font-awesome');
            }

            $stcr_style_css = plugins_url( '/includes/css/stcr-style.css', STCR_PLUGIN_FILE );
            wp_register_style( 'stcr-style', $stcr_style_css );
            wp_enqueue_style( 'stcr-style' );

            // google recaptcha
            if ( get_option( 'subscribe_reloaded_use_captcha', 'no' ) == 'yes' ) {

                // management page permalink
                $manager_page_permalink = get_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/' );
                if ( function_exists( 'qtrans_convertURL' ) ) {
                    $manager_page_permalink = qtrans_convertURL( $manager_page_permalink );
				}

				// management page permalink fallback
                if ( empty( $manager_page_permalink ) ) {
                    $manager_page_permalink = '/comment-subscriptions/';
                }

                // remove the ending slash so both variations (with and without slash) work in the strpos check below
                $manager_page_permalink = rtrim( $manager_page_permalink, '/' );

				// if we are on the management page, add the script
                if ( strpos( $_SERVER["REQUEST_URI"], $manager_page_permalink ) !== false ) {
                    wp_enqueue_script( 'stcr-google-recaptcha', 'https://www.google.com/recaptcha/api.js' );
                }
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
         * Method to avoid using all the StCR option variable name. Return the given option value.
         *
         * @since 08-Apr-2018
         * @author Israel Barragan (Reedyseth)
         *
         * @param string $_option Option Name
         * @param string $_default Default value in case is not defined.
         * @return string The option value store in WP.
         */
        public function stcr_get_menu_options($_option = '', $_default = '' )
        {
            $value = null;

            if ( isset( $this->menu_opts_cache[$_option] ) )
            {
                return $this->menu_opts_cache[$_option];
            }
            else
            {
                $value = get_option( 'subscribe_reloaded_' . $_option, $_default );
                $value = ( ! is_array( $value ) ) ? html_entity_decode( stripslashes( $value ), ENT_QUOTES, 'UTF-8' ) : wp_unslash( $value );
                $value = ( ! is_array( $value ) ) ? stripslashes( $value ) : wp_unslash( $value );
                // Set the cache value
                $this->menu_opts_cache[$_option] = $value;
            }

            return $value;
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
                    if ( $_status == 'read' ) {
                        unset( $notices[$key] );
                    } else {
                        $notices[ $key ] = array(
                            "status" => $_status,
                            "message" => $notice['message'],
                            "type"	=> $notice['type'],
                            "nonce" => $_nonce
                        );
                    }
				}
			}
			update_option( 'subscribe_reloaded_deferred_admin_notices', $notices );
		}


        /**
         * Method to avoid using all the StCR option variable name. It also verify the type of value to be store.
         *
         * @since 07-Apr-2018
         * @author Israel Barragan (Reedyseth)
         *
         * @param string $_option Option Name.
         * @param string $_value
         * @param string $_type type to use for the correct sanitation yesno|integer|text|text-html|email|url
         * @return bool false in case that the value is not defined.
         */
        public function stcr_update_menu_options($_option = '', $_value = '', $_type = '' )
        {
            if ( ! isset( $_value ) ) {
                return false;
            }

            // Prevent XSS/CSRF attacks
            $_value = ( 'multicheck' !== $_type ) ? trim( stripslashes( $_value ) ) : wp_unslash( $_value );

            switch ( $_type ) {
                case 'yesno':
                    if ( $_value == 'yes' || $_value == 'no' ) {
                        update_option( 'subscribe_reloaded_' . $_option, esc_attr( $_value ) );
                    }
                    break;
                case 'integer':
                    update_option( 'subscribe_reloaded_' . $_option, abs( intval( esc_attr( $_value ) ) ) );

                    break;
                case 'text':
                    update_option( 'subscribe_reloaded_' . $_option, sanitize_text_field( $_value ) );

                    break;
                case 'text-html':
                    update_option( 'subscribe_reloaded_' . $_option, esc_html( $_value ) );

                    break;
                case 'email':
                    update_option( 'subscribe_reloaded_' . $_option, sanitize_email( esc_attr( $_value ) ) );

                    break;
                case 'url':
                    update_option( 'subscribe_reloaded_' . $_option, esc_url( $_value ) );

                    break;
                case 'textarea':
                    update_option( 'subscribe_reloaded_' . $_option, wp_kses_post( $_value ) );

                    break;
                case 'multicheck':
                    $final_value = array();
                    foreach ( $_value as $value ) {
                        $final_value[] = sanitize_text_field( $value );
                    }
                    update_option( 'subscribe_reloaded_' . $_option, $final_value );

                    break;
                case 'select':
                    update_option( 'subscribe_reloaded_' . $_option, sanitize_key( $_value ) );

                    break;
                default:
                    update_option( 'subscribe_reloaded_' . $_option, esc_attr( $_value ) );

                    break;
            }

            return true;
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
         * Create a new Ajax Hook.
         *
         * @since 07-Dic-2018
         * @author reedyseth
         *
         * @param array $_actions An array with the ajax hooks to bind. Each element of the array should be the name and the function to bind.
         */
        public function stcr_create_ajax_hook( array $_actions )
        {
            foreach ($_actions as $hookName => $functionToBind )
            {
                add_action( 'wp_ajax_' . $hookName, array( $this, $functionToBind ) );
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
			$_notification = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
			// Check Nonce
			check_ajax_referer( $_notification, 'security' );
			// Update status
			$this->stcr_update_admin_notice_status(  sanitize_text_field( $_notification ), 'read' ) ;
			// Send success message
			wp_send_json_success( 'Notification status updated for "' . $_notification . '"' );
			die();
		}
        /**
         * Check if the current user is a WordPress Admin
         *
         * @since 10-Dic-2018
         * @author reedyseth
         *
         */
        public function stcr_is_admin()
        {
            return is_admin();
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

		}

		/**
		 * Function to check if the commenter email address is blacklisted or not.
		 *
		 * @param string $email_to_check The commentor's email address.
		 *
		 * @return bool
		 */
		public function blacklisted_emails( $email_to_check = '' ) {

			// If the emails is blacklisted then, do not proceed to send the subscription confirmation email.
			$blacklisted_emails = get_option( 'subscribe_reloaded_blacklisted_emails', '' );
			$email_blacklist    = ! empty( $blacklisted_emails ) ? explode( ',', $blacklisted_emails ) : false;

			if ( is_array( $email_blacklist ) ) {
				foreach ( $email_blacklist as $blacklist_item ) {
					$blacklisted_items = trim( $blacklist_item );

					if ( ! empty( trim( $blacklisted_items ) ) ) {
						if ( is_email( $email_to_check ) === is_email( $blacklisted_items ) ) {
							return false;
						}
					}
				}
			}

			return true;

		}

		/**
		 * Sanitize the user input on plugin options save.
		 *
		 * @param string|array|mixed $values The plugin setting options.
		 *
		 * @return string|array|mixed The sanitized user data.
		 */
		public static function sanitize_options( $values ) {

			// If the values is set to array, sanitize each of the values.
			if ( is_array( $values ) ) {
				$final_value = array();
				foreach ( $values as $value ) {
					$final_value[] = sanitize_text_field( $value );
				}

				return $final_value;
			}

			// If user have set the meta tag, then, sanitize that via wp_kses.
			$matches = array();
			preg_match( '/<meta/i', $values, $matches );
			if ( $matches ) {
				$final_value = wp_kses(
					$values,
					array(
						'meta' => array(
							'charset'    => array(),
							'content'    => array(),
							'http-equiv' => array(),
							'name'       => array(),
						),
					)
				);

				return $final_value;
			}

			// Sanitize everything else with the HTML attributes available for posts.
			return wp_kses_post( $values );

		}
	}
}
