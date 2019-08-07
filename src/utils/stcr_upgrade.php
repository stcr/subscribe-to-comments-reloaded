<?php
/**
 * Class with utility functions to upgrade the plugin.
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

if( ! class_exists('\\'.__NAMESPACE__.'\\stcr_upgrade') ) {
	class stcr_upgrade extends stcr_utils {

	    private $_stcr_charset = null;
	    private $_stcr_collate = null;
	    private $_db_collate   = null;

	    public function __construct()
        {
            $this->_stcr_charset  = 'utf8';
            $this->_stcr_collate  = 'utf8_unicode_ci';
            $this->_db_collate    = "DEFAULT CHARSET={$this->_stcr_charset} COLLATE={$this->_stcr_collate}";
        }

        public function apply_patches()
        {
            $this->patch_collation();
        }

        public function _create_subscriber_table( $_fresh_install ) {
			global $wpdb;
			$errorMsg        = '';

			// If the update option is set to false
			$stcr_opt_subscriber_table = get_option('subscribe_reloaded_subscriber_table');

			if ( $_fresh_install || ( ! $stcr_opt_subscriber_table ||  $stcr_opt_subscriber_table == 'no' ) ) {
				// Creation of table and subscribers.
				$sqlCreateTable = " CREATE TABLE " . $wpdb->prefix . "subscribe_reloaded_subscribers (
						  stcr_id int(11) NOT NULL AUTO_INCREMENT,
						  subscriber_email varchar(100) NOT NULL,
						  salt int(15) NOT NULL,
						  subscriber_unique_id varchar(50) NULL,
						  add_date timestamp NOT NULL DEFAULT NOW(),
						  PRIMARY KEY  (stcr_id),
						  UNIQUE KEY uk_subscriber_email (subscriber_email))
						ENGINE = InnoDB
						$this->_db_collate";

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				// dbDelta Will create or update the table safety
				// Ref: https://codex.wordpress.org/Creating_Tables_with_Plugins
				$result = dbDelta( $sqlCreateTable );
				$retrieveNumberOfSubscribers = "SELECT COUNT(subscriber_email) FROM " . $wpdb->prefix . "subscribe_reloaded_subscribers";
				$numSubscribers              = $wpdb->get_var( $retrieveNumberOfSubscribers );
				// If subscribers not found then the create routine.
				if ( $numSubscribers == 0 ) {
					// Get list of emails to be imported.
					$retrieveEmails = "SELECT DISTINCT REPLACE(meta_key, '_stcr@_', '') AS email FROM " . $wpdb->postmeta
						. " WHERE meta_key LIKE '\_stcr@\_%'";
					$emails         = $wpdb->get_results( $retrieveEmails, OBJECT );
					// insert the records on the new table.
					foreach ( $emails as $email ) {
						// Insert email
						$OK = $this->add_user_subscriber_table( $email->email );
						if ( ! $OK) {
							$notices   = get_option( 'subscribe_reloaded_deferred_admin_notices', array() );
							$notices[] = '<div class="error"><h3>' . __( 'Important Notice', 'subscribe-to-comments-reloaded' ) . '</h3>' .
								'<p>The creation of of the table <strong>' . $wpdb->prefix . 'subscribe_reloaded_subscribers</strong> failed</p></div>';
							update_option( 'subscribe_reloaded_deferred_admin_notices', $notices );
							break 1;
						}
					}

					if( ! $_fresh_install )
					{
						$this->stcr_create_admin_notice(
							'notify_create_subscriber_table',
							'unread',
							'<p><strong>Subscribe to Comments Reloaded:</strong> The creation of table <code>' . $wpdb->prefix . 'subscribe_reloaded_subscribers</code> was successful.</p>'.
							'<p>This new table will help to add your subscribers email addresses more safely and prevent Google PII violations.'
							 . '<a class="dismiss" href="#">Dismiss.  </a>'
								. '<img class="stcr-loading-animation" src="'. esc_url( admin_url() . '/images/loading.gif'). '" alt="Working...">'
							. '</p>',
							'updated'
						);
					}
					update_option('subscribe_reloaded_subscriber_table', 'yes');
				}
			}
		}

		public function _sanitize_db_information( $_fresh_install ) {
			global $wpdb;

			if ( ( ! get_option( "subscribe_reloaded_data_sanitized" )
				|| get_option( "subscribe_reloaded_data_sanitized" ) == "no" )
				&& ! $_fresh_install  ) {
				$stcr_data            = $wpdb->get_results(
					" SELECT * FROM $wpdb->options WHERE option_name like 'subscribe_reloaded%'
			ORDER BY option_name", OBJECT
				);
				$sctr_data_array_size = sizeof( $stcr_data );
				// Lets make sure that there is not another subscription with the same compose key
				foreach ( $stcr_data as $row ) {
					if ( $row->option_name != 'subscribe_reloaded_deferred_admin_notices' ) {
						$optionValue = $row->option_value;
						$optionValue = html_entity_decode( stripslashes( $optionValue ), ENT_QUOTES, 'UTF-8' );
						$optionValue = esc_attr( $optionValue );
						update_option( $row->option_name, $optionValue );
					}
				}
				$this->stcr_create_admin_notice(
					'notify_update_sanitize_db_options',
					'unread',
					'<p>' . __( '<strong>Subscribe to Comments Reloaded:</strong> The information in your database has been sanitized to prevent the raw html messages. <a class="dismiss" href="#">Dismiss.  </a>'
						  . '<img class="stcr-loading-animation" src="'. esc_url( admin_url() . '/images/loading.gif'). '" alt="Working...">'  , 'subscribe-to-comments-reloaded' )
					. '</p>',
					'updated'
				);
				update_option( "subscribe_reloaded_data_sanitized", "yes" );
			}
		} // end _sanitize_db_information

		/**
		 * Copies the information from the stand-alone table to WP's core table
		 */
		public function _update_db() {
			global $wpdb;
			$stcr_table = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}subscribe_reloaded'" );

			// Perform the import only if the target table does not contain any subscriptions
			$count_postmeta_rows = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%'" );
			if ( ! empty( $stcr_table ) && $count_postmeta_rows == 0 ) {
				$wpdb->query(
					"
			INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value)
				SELECT post_ID, CONCAT('_stcr@_', email), CONCAT(dt, '|Y')
				FROM {$wpdb->prefix}subscribe_reloaded
				WHERE email LIKE '%@%.%' AND status = 'Y'
				GROUP BY email, post_ID"
				);
			}
		}
		// end _update_db

		/**
		 * Imports subscription data created with the Subscribe to Comments plugin
		 */
		public function _import_stc_data() {
            global $wpdb;

			// Import the information collected by Subscribe to Comments, if needed
            $result = $wpdb->get_row( "DESC $wpdb->comments comment_subscribe", ARRAY_A );
            
			// Perform the import only if the target table does not contain any subscriptions
			$count_postmeta_rows = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%'" );

			if ( ! empty( $result ) && is_array( $result ) && $count_postmeta_rows == 0 ) {
				$wpdb->query(
					"
				INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value)
					SELECT comment_post_ID, CONCAT('_stcr@_', comment_author_email), CONCAT(comment_date, '|Y')
					FROM $wpdb->comments
					WHERE comment_author_email LIKE '%@%.%' AND comment_subscribe = 'Y'
					GROUP BY comment_post_ID, comment_author_email"
				);
				$this->stcr_create_admin_notice(
					'notify_import_stc_data',
					'unread',
					'<p>' . __( '<strong>Subscribe to Comments Reloaded:</strong> Comment subscription data from the <strong>Subscribe to Comments</strong> plugin was detected and automatically imported into <strong>Subscribe to Comments Reloaded</strong>.', 'subscribe-to-comments-reloaded' ) . ( is_plugin_active( 'subscribe-to-comments/subscribe-to-comments.php' ) ? __( ' It is recommended that you now <strong>deactivate</strong> Subscribe to Comments to prevent confusion between the two plugins.', 'subscribe-to-comments-reloaded' ) : '' ) . '</p>' .
					'<p>' . __( 'If you have subscription data from Subscribe to Comments Reloaded < v2.0 that you want to import, you\'ll need to import that data manually, as only one import routine will ever run to prevent data loss.', 'subscribe-to-comments-reloaded' ) . '</p>' .
					'<p>' . __( 'Please visit <a href="options-general.php?page=subscribe-to-comments-reloaded/options/index.php">Settings -> Subscribe to Comments</a> to review your configuration.'
						. '<a class="dismiss" href="#">Dismiss.  </a>'
						. '<img class="stcr-loading-animation" src="'. esc_url( admin_url() . '/images/loading.gif'). '" alt="Working...">', 'subscribe-to-comments-reloaded' ) . '</p>',
					'updated'
				);
			}
		}
        // end _import_stc_data
        
        /**
         * Imports subscriptions from Subscribe to Comments by Mark Jaquith
         * 
         * @since 190708
         */
        public function _import_stc_mj_data() {

            global $wpdb;

            // check if we currently have subscriptions
            $current_subscriptions_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%'" );

            // if subscriptions exists, do not proceed
            if ( $current_subscriptions_count > 0 ) return;

            // will hold subscriptions
            $subscriptions = array();

            // data from the other plugin
            $result = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key = '_sg_subscribe-to-comments'", ARRAY_A );

            // no results, go back
            if ( empty( $result ) ) return;

            // add the subscriptions to an array
            foreach ( $result as $subscription ) {
                $subscriptions[] = array(
                    'post_id' => $subscription['post_id'],
                    'email'   => $this->clean_email( $subscription['meta_value'] ),
                );
            }

            // no subscriptions, go back
            if ( empty( $subscriptions ) ) return;

            // add the subscriptions to the DB            
            $dt = date_i18n( 'Y-m-d H:i:s' );
            $status = 'Y';
            foreach ( $subscriptions as $subscription ) {
                $post_id = $subscription['post_id'];
                $meta_key = '_stcr@_' . $subscription['email'];
                $meta_value = $dt . '|' . $status;
                $wpdb->query(
                    $wpdb->prepare(
                        "INSERT IGNORE INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES ( %s, %s, %s )",
                        $post_id, $meta_key, $meta_value
                    )
                );
                $this->add_user_subscriber_table( $subscription['email'] );
            }

            // notify the admin about the import
            $this->stcr_create_admin_notice(
                'notify_import_stc_mj_data',
                'unread',
                '<p>' . __( '<strong>Subscribe to Comments Reloaded:</strong> Comment subscription data from the <strong>Subscribe to Comments</strong> plugin by Mark Jaquith was detected and automatically imported into <strong>Subscribe to Comments Reloaded</strong>.', 'subscribe-to-comments-reloaded' ) . ( is_plugin_active( 'subscribe-to-comments/subscribe-to-comments.php' ) ? __( ' It is recommended that you now <strong>deactivate</strong> Subscribe to Comments to prevent confusion between the two plugins.', 'subscribe-to-comments-reloaded' ) : '' ) . '</p>' .
                '<p>' . __( 'If you have subscription data from Subscribe to Comments Reloaded < v2.0 that you want to import, you\'ll need to import that data manually, as only one import routine will ever run to prevent data loss.', 'subscribe-to-comments-reloaded' ) . '</p>' .
                '<p>' . __( 'Please visit <a href="options-general.php?page=subscribe-to-comments-reloaded/options/index.php">Settings -> Subscribe to Comments</a> to review your configuration.'
                    . '<a class="dismiss" href="#">Dismiss.  </a>'
                    . '<img class="stcr-loading-animation" src="'. esc_url( admin_url() . '/images/loading.gif'). '" alt="Working...">', 'subscribe-to-comments-reloaded' ) . '</p>',
                'updated'
            );

        }

		/**
		 * Imports subscription data created with the Comment Reply Notification plugin. This function is deprecated is not in use anymore.
         *
         * @deprecated
		 * @since 13-May-2014
		 */
		public function _import_crn_data() {
			global $wpdb;
			$crn_data_count          = null;
			$subscriptions_to_import = array();
			$commentMailColumn       = $wpdb->get_var( "SHOW COLUMNS FROM $wpdb->comments LIKE 'comment_mail_notify' " );
			if ( empty( $commentMailColumn ) ) {
				$crn_data_count = 0;
			} else {
				// Import the information collected by Subscribe to Comments, if needed
				$crn_data_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_mail_notify = 1" );
			}

			if ( $crn_data_count > 0 ) { // if $crn_data_count is 0 there is no Comment Reply
				// plugin installed and therefore no comment_mail_notify
				// column.
				// Since we know that there are subscriptions Retrieve all of them from COMMENT_REPLY_NOTIFICATION
				$crn_data             = $wpdb->get_results(
					" SELECT comment_post_ID, comment_author_email"
					. " FROM wp_comments WHERE comment_mail_notify = '1'"
					. " GROUP BY comment_author_email"
					, OBJECT
				);
				$stcr_data            = $wpdb->get_results(
					" SELECT post_id, SUBSTRING(meta_key,8) AS email"
					. " FROM wp_postmeta WHERE meta_key LIKE '_stcr@_%'"
					, ARRAY_N
				);
				$sctr_data_array_size = sizeof( $stcr_data );
				// Lets make sure that there is not another subscription with the same compose key
				foreach ( $crn_data as $row ) {
					// Search the specific compose key in the array
					for ( $i = 0; $i < $sctr_data_array_size; $i ++ ) {
						$post_id_in_stcr = in_array( $row->comment_post_ID, $stcr_data[$i] );
						$email_in_stcr   = in_array( $row->comment_author_email, $stcr_data[$i] );
						// validate with an If
						if ( $post_id_in_stcr && $email_in_stcr ) {
							// If the same compose key is in StCR search for the next value.
							continue 2; // the next subscription.
						}
					}
					// 2) Until this point the compose key is not on StCR so is safe to import.
					$OK = $wpdb->insert(
						$wpdb->postmeta,
						array(
							"post_id"    => $row->comment_post_ID,
							"meta_key"   => "_stcr@_" . $row->comment_author_email,
							"meta_value" => current_time( "mysql" ) . "|R"
						),
						array(
							"%d",
							"%s",
							"%s"
						)
					);

				}
				$this->stcr_create_admin_notice(
					'notify_import_comment_reply',
					'unread',
					'<p>' . __( '<strong>Subscribe to Comments Reloaded:</strong> Comment subscription data from the <strong>Comment Reply Notification</strong> plugin was detected and automatically imported into <strong>Subscribe to Comments Reloaded</strong>.', 'subscribe-to-comments-reloaded' ) . ( is_plugin_active( 'comment-reply-notification/comment-reply-notification.php' ) ? __( ' It is recommended that you now <strong>deactivate</strong> Comment Reply Notification to prevent confusion between the two plugins.', 'subscribe-to-comments-reloaded' ) : '' ) . '</p>' .
					'<p>' . __( 'Please visit <a href="options-general.php?page=subscribe-to-comments-reloaded/options/index.php">Settings -> Subscribe to Comments</a> to review your configuration.'
						. '<a class="dismiss" href="#">Dismiss.  </a>'
						. '<img class="stcr-loading-animation" src="'. esc_url( admin_url() . '/images/loading.gif'). '" alt="Working...">', 'subscribe-to-comments-reloaded' ) . '</p>',
					'updated'
				);
			}
		}
		// end _import_crn_data
		/**
		 * Imports options and subscription data created with the WP Comment Subscriptions plugin
		 */
		public function _import_wpcs_data() {
			global $wpdb;

			// Import the information collected by WP Comment Subscriptions, if needed
			$wpcs_data_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_wpcs@\_%'" );

			// Perform the import only if the target table does not contain any subscriptions
			$count_postmeta_rows = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%'" );

			if ( ! empty( $wpcs_data_count ) && $count_postmeta_rows == 0 ) {
				$wpdb->query(
					"
			INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value)
			SELECT post_id, REPLACE(meta_key, '_wpcs@_', '_stcr@_') meta_key, meta_value
			FROM $wpdb->postmeta
			WHERE meta_key LIKE '%_wpcs@_%'"
				);

				if ( $option = get_option( 'wp_comment_subscriptions_manager_page' ) ) {
					add_option( 'subscribe_reloaded_manager_page', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_show_subscription_box' ) ) {
					add_option( 'subscribe_reloaded_show_subscription_box', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_checked_by_default' ) ) {
					add_option( 'subscribe_reloaded_checked_by_default', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_enable_advanced_subscriptions' ) ) {
					add_option( 'subscribe_reloaded_enable_advanced_subscriptions', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_checkbox_inline_style' ) ) {
					add_option( 'subscribe_reloaded_checkbox_inline_style', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_checkbox_html' ) ) {
					add_option( 'subscribe_reloaded_checkbox_html', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_checkbox_label' ) ) {
					add_option( 'subscribe_reloaded_checkbox_label', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_subscribed_label' ) ) {
					add_option( 'subscribe_reloaded_subscribed_label', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_subscribed_waiting_label' ) ) {
					add_option( 'subscribe_reloaded_subscribed_waiting_label', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_author_label' ) ) {
					add_option( 'subscribe_reloaded_author_label', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_htmlify_message_links' ) ) {
					add_option( 'subscribe_reloaded_htmlify_message_links', $option );
				}

				if ( $option = get_option( 'wp_comment_subscriptions_manager_page_enabled' ) ) {
					add_option( 'subscribe_reloaded_manager_page_enabled', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_manager_page_title' ) ) {
					add_option( 'subscribe_reloaded_manager_page_title', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_custom_header_meta' ) ) {
					add_option( 'subscribe_reloaded_custom_header_meta', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_request_mgmt_link' ) ) {
					add_option( 'subscribe_reloaded_request_mgmt_link', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_request_mgmt_link_thankyou' ) ) {
					add_option( 'subscribe_reloaded_request_mgmt_link_thankyou', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_subscribe_without_commenting' ) ) {
					add_option( 'subscribe_reloaded_subscribe_without_commenting', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_subscription_confirmed' ) ) {
					add_option( 'subscribe_reloaded_subscription_confirmed', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_subscription_confirmed_dci' ) ) {
					add_option( 'subscribe_reloaded_subscription_confirmed_dci', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_author_text' ) ) {
					add_option( 'subscribe_reloaded_author_text', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_user_text' ) ) {
					add_option( 'subscribe_reloaded_user_text', $option );
				}

				if ( $option = get_option( 'wp_comment_subscriptions_from_name' ) ) {
					add_option( 'subscribe_reloaded_from_name', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_from_email' ) ) {
					add_option( 'subscribe_reloaded_from_email', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_notification_subject' ) ) {
					add_option( 'subscribe_reloaded_notification_subject', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_notification_content' ) ) {
					add_option( 'subscribe_reloaded_notification_content', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_double_check_subject' ) ) {
					add_option( 'subscribe_reloaded_double_check_subject', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_double_check_content' ) ) {
					add_option( 'subscribe_reloaded_double_check_content', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_management_subject' ) ) {
					add_option( 'subscribe_reloaded_management_subject', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_management_content' ) ) {
					add_option( 'subscribe_reloaded_management_content', $option );
				}

				if ( $option = get_option( 'wp_comment_subscriptions_purge_days' ) ) {
					add_option( 'subscribe_reloaded_purge_days', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_enable_double_check' ) ) {
					add_option( 'subscribe_reloaded_enable_double_check', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_notify_authors' ) ) {
					add_option( 'subscribe_reloaded_notify_authors', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_enable_html_emails' ) ) {
					add_option( 'subscribe_reloaded_enable_html_emails', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_process_trackbacks' ) ) {
					add_option( 'subscribe_reloaded_process_trackbacks', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_enable_admin_messages' ) ) {
					add_option( 'subscribe_reloaded_enable_admin_messages', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_admin_subscribe' ) ) {
					add_option( 'subscribe_reloaded_admin_subscribe', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_admin_bcc' ) ) {
					add_option( 'subscribe_reloaded_admin_bcc', $option );
				}
				if ( $option = get_option( 'wp_comment_subscriptions_only_for_posts' ) ) {
					add_option( 'subscribe_reloaded_only_for_posts', $option );
				}
				$this->stcr_create_admin_notice(
					'notify_import_wpcs_data',
					'unread',
					'<p>' . __( '<strong>Subscribe to Comments Reloaded:</strong> Plugin options and comment subscription data from the <strong>WP Comment Subscriptions</strong> plugin were detected and automatically imported into <strong>Subscribe to Comments Reloaded</strong>.', 'subscribe-to-comments-reloaded' ) . ( is_plugin_active( 'wp-comment-subscriptions/wp-comment-subscriptions.php' ) ? __( ' It is recommended that you now <strong>deactivate</strong> WP Comment Subscriptions to prevent confusion between the two plugins.', 'subscribe-to-comments-reloaded' ) : '' ) . '</p>' .
					'<p>' . __( 'If you have subscription data from another plugin (such as Subscribe to Comments or Subscribe to Comments Reloaded < v2.0) that you want to import, you\'ll need to import that data manually, as only one import routine will ever run to prevent data loss.', 'subscribe-to-comments-reloaded' ) . '</p>' .
					'<p>' . __( '<strong>Note:</strong> If you were previously using the <code>wp_comment_subscriptions_show()</code> function or the <code>[wpcs-subscribe-url]</code> shortcode, you\'ll need to replace those with <code>subscribe_reloaded_show()</code> and <code>[subscribe-url]</code> respectively.', 'subscribe-to-comments-reloaded' ) . '</p>' .
					'<p>' . __( 'Please visit <a href="options-general.php?page=subscribe-to-comments-reloaded/options/index.php">Settings -> Subscribe to Comments</a> to review your configuration.'
						. '<a class="dismiss" href="#">Dismiss.  </a>'
						. '<img class="stcr-loading-animation" src="'. esc_url( admin_url() . '/images/loading.gif'). '" alt="Working...">', 'subscribe-to-comments-reloaded' ) . '</p>',
					'updated'
				);
			}
		}
		// end _import_wpcs_data
		public function upgrade_notification( $_version, $_db_version, $_fresh_install ) {

            $options_link = sprintf( '<a href="%s"> %s </a>', admin_url( 'admin.php?page=stcr_options' ), __( 'Settings', 'subscribe-to-comments-reloaded' ) );
            $system_link  = sprintf( '<a href="%s"> %s </a>', admin_url( 'admin.php?page=stcr_system' ), __( 'Log Settings', 'subscribe-to-comments-reloaded' ) );

			if( ! $_fresh_install ) {
			
				switch ($_version) {
					case '160106':
						$this->stcr_create_admin_notice(
							'notify_update_' . $_version,
							'unread',
							'<p>' . __('<strong>Subscribe to Comments Reloaded</strong> has been updated to version 160106.', 'subscribe-to-comments-reloaded') . '</p>' .
							'<p>' . __('This version includes many changes and fixes to improve your experience with the plugin, including One Click Unsubscribe, Rich Text Editor to create HTML email templates, Subscription Checkbox position, and more!', 'subscribe-to-comments-reloaded') . '</p>' .
							'<p>' . __('Please visit the <a href="https://wordpress.org/plugins/subscribe-to-comments-reloaded/changelog/" target="_blank">Changelog</a> for a complete list of changes.'
								. '<a class="dismiss" href="#">Dismiss.  </a>'
								. '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Working...">', 'subscribe-to-comments-reloaded') . '</p>',
							'updated'
						);
						// Update the HTML emails option
						update_option( 'subscribe_reloaded_htmlify_message_links', 'no' );
						update_option( 'subscribe_reloaded_enable_html_emails', 'yes' );
						break;
					case '160115':
						$this->stcr_create_admin_notice(
							'notify_update_' . $_version,
							'unread',
							'<p>' . __('<strong>Subscribe to Comments Reloaded</strong> has been updated to version 160115.', 'subscribe-to-comments-reloaded') . '</p>' .
							'<p>' . __('This version includes fixes to broken links while managing your subscriptions', 'subscribe-to-comments-reloaded') . '</p>' .
							'<p>' . __('Please visit the <a href="https://wordpress.org/plugins/subscribe-to-comments-reloaded/changelog/" target="_blank">Changelog</a> for a complete list of changes.'
								. '<a class="dismiss" href="#">Dismiss.  </a>'
								. '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Working...">', 'subscribe-to-comments-reloaded') . '</p>',
							'updated'
						);
						// Update the HTML emails option
						update_option( 'subscribe_reloaded_htmlify_message_links', 'no' );
						update_option( 'subscribe_reloaded_enable_html_emails', 'yes' );
						break;
					case '160831':
						$this->stcr_create_admin_notice(
							'notify_update_' . $_version,
							'unread',
							'<p>' . __('<strong>Subscribe to Comments Reloaded</strong> has been updated to version 160831', 'subscribe-to-comments-reloaded') . '</p>' .
							'<p>' . __('This version includes fixes to many bugs and also new features, ', 'subscribe-to-comments-reloaded') . '</p>' .
							'<ul>' .
								'<li>' . __("<strong>New Feature</strong> Add new option to set the Reply To email address. This will help the subscribers to use the Reply option in their email agents.", 'subscribe-to-comments-reloaded') . '</li>'.
								'<li>' . __("<strong>New Feature</strong> Improve the Admin Menu for StCR. Replace the StCR menu on the Settings Menu for a new Menu with sub menus for the pages.", 'subscribe-to-comments-reloaded') . '</li>'.
								'<li>' . __("<strong>New Feature</strong> Safely Uninstall option to Delete the plugin without loosing your subscriptions. You can use this option also for reset all the settings, see the FAQ.", 'subscribe-to-comments-reloaded') . '</li>'.
								'<li>' . __("<strong>New Feature</strong> Now the WordPress Authors can use the <strong>Subscribe authors</strong> option to autor subscribe to a Custom Post Type.", 'subscribe-to-comments-reloaded') . '</li>'.
								'<li>' . __("<strong>New Feature</strong> A new field was added under the notification options to and the management link only by email and not to display it on the request link page.", 'subscribe-to-comments-reloaded') . '</li>'.
							'</ul>' .
							'<p>' . __('Please visit the <a href="https://wordpress.org/plugins/subscribe-to-comments-reloaded/changelog/" target="_blank">Changelog</a> for a complete list of changes.'
								. '<a class="dismiss" href="#">Dismiss.  </a>'
								. '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Working...">', 'subscribe-to-comments-reloaded') . '</p>',
							'updated'
						);
						// Update the HTML emails option
						update_option( 'subscribe_reloaded_htmlify_message_links', 'no' );
						update_option( 'subscribe_reloaded_enable_html_emails', 'yes' );
						break;
					case '160902':
						$this->stcr_create_admin_notice(
							'notify_update_' . $_version,
							'unread',
							'<p>' . __('<strong>Subscribe to Comments Reloaded</strong> has been updated to version 160902', 'subscribe-to-comments-reloaded') . '</p>' .
							'<p>' . __('This version includes fixes to many bugs and also new features, ', 'subscribe-to-comments-reloaded') . '</p>' .
							'<ul>' .
								'<li>' . __("<strong>Fix update</strong> this version Fixes some issue trigger by the previous 160831 version.", 'subscribe-to-comments-reloaded') . '</li>'.
								'<li>' . __("<strong>New Feature</strong> Add new option to set the Reply To email address. This will help the subscribers to use the Reply option in their email agents.", 'subscribe-to-comments-reloaded') . '</li>'.
								'<li>' . __("<strong>New Feature</strong> Improve the Admin Menu for StCR. Replace the StCR menu on the Settings Menu for a new Menu with sub menus for the pages.", 'subscribe-to-comments-reloaded') . '</li>'.
								'<li>' . __("<strong>New Feature</strong> Safely Uninstall option to Delete the plugin without loosing your subscriptions. You can use this option also for reset all the settings, see the FAQ.", 'subscribe-to-comments-reloaded') . '</li>'.
								'<li>' . __("<strong>New Feature</strong> Now the WordPress Authors can use the <strong>Subscribe authors</strong> option to autor subscribe to a Custom Post Type.", 'subscribe-to-comments-reloaded') . '</li>'.
								'<li>' . __("<strong>New Feature</strong> A new field was added under the notification options to and the management link only by email and not to display it on the request link page.", 'subscribe-to-comments-reloaded') . '</li>'.
							'</ul>' .
							'<p>' . __('Please visit the <a href="https://wordpress.org/plugins/subscribe-to-comments-reloaded/changelog/" target="_blank">Changelog</a> for a complete list of changes.'
								. '<a class="dismiss" href="#">Dismiss.  </a>'
								. '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Working...">', 'subscribe-to-comments-reloaded') . '</p>',
							'updated'
						);
						// Update the HTML emails option
						update_option( 'subscribe_reloaded_htmlify_message_links', 'no' );
						update_option( 'subscribe_reloaded_enable_html_emails', 'yes' );
						break;
					case '160915':
						$options_link = sprintf( '<a href="%s"> %s </a>', admin_url( 'admin.php?page=stcr_options' ), __( 'Settings', 'subscribe-to-comments-reloaded' ) );
						$this->stcr_create_admin_notice(
							'notify_update_' . $_version,
							'unread',
							'<p>' . __('<strong>Subscribe to Comments Reloaded</strong> has been updated to version 160915', 'subscribe-to-comments-reloaded') . '</p>' .
							'<p>' . __('This version includes fixes and improvements, ', 'subscribe-to-comments-reloaded') . '</p>' .
							'<ul>' .
								'<li>' . __("<strong>Fix</strong> StCR checkbox position issues with some WordPress themes, Go to the {$options_link} to activate it.", 'subscribe-to-comments-reloaded') . '</li>'.
								'<li>' . __("<strong>Change</strong> the radio buttons in the management page for a dropdown.", 'subscribe-to-comments-reloaded') . '</li>'.
								'<li>' . __("<strong>Improve</strong> Email validation for empty values and using a regex.", 'subscribe-to-comments-reloaded') . '</li>'.
							'</ul>' .
							'<p>' . __('Please visit the <a href="https://wordpress.org/plugins/subscribe-to-comments-reloaded/changelog/" target="_blank">Changelog</a> for a complete list of changes.'
								. '<a class="dismiss" href="#">Dismiss.  </a>'
								. '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Working...">', 'subscribe-to-comments-reloaded') . '</p>',
							'updated'
						);
						// Update the HTML emails option
						update_option( 'subscribe_reloaded_htmlify_message_links', 'no' );
						update_option( 'subscribe_reloaded_enable_html_emails', 'yes' );
						break;
                    case '170428':
                        $this->stcr_create_admin_notice(
                            'notify_update_' . $_version,
                            'unread',
                            '<p>' . __('<strong>Subscribe to Comments Reloaded</strong> has been updated to version ' . $_version, 'subscribe-to-comments-reloaded') . '</p>' .
                            '<p>' . __('This version includes fixes and improvements, ', 'subscribe-to-comments-reloaded') . '</p>' .

                            '<ul>' .
                                '<li>' . __("<strong>Fix</strong> Wrong confirmation link when the double check option is enable.", 'subscribe-to-comments-reloaded') . '</li>'.
                                '<li>' . __("<strong>Improve</strong> Manage subscription page. Take a look ;).", 'subscribe-to-comments-reloaded') . '</li>'.
                                '<li>' . __("<strong>Improve</strong> Log file manipulation. Now you can control how the log behaves, take a look at the {$system_link}.", 'subscribe-to-comments-reloaded') . '</li>'.
                            '</ul>' .

                            '<p>' . __('If you find a bug or an issue you can report it <a href="https://github.com/stcr/subscribe-to-comments-reloaded/issues" target="_blank">here</a>.', 'subscribe-to-comments-reloaded') . '</p>' .
                            '<p>' . __('Please visit the <a href="https://wordpress.org/plugins/subscribe-to-comments-reloaded/changelog/" target="_blank">Changelog</a> for a complete list of changes.'
                                . '<a class="dismiss" href="#">Dismiss.  </a>'
                                . '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Dismissing Message">', 'subscribe-to-comments-reloaded') . '</p>',
                            'updated'
                        );
                        // Update the HTML emails option
                        update_option( 'subscribe_reloaded_htmlify_message_links', 'no' );
                        update_option( 'subscribe_reloaded_enable_html_emails', 'yes' );
                        break;
                    case '170607':
                        $this->stcr_create_admin_notice(
                            'notify_update_' . $_version,
                            'unread',
                            '<p>' . __('<strong>Subscribe to Comments Reloaded</strong> has been updated to version ' . $_version, 'subscribe-to-comments-reloaded') . '</p>' .
                            '<p>' . __('This version includes fixes and improvements, ', 'subscribe-to-comments-reloaded') . '</p>' .

                            '<ul>' .
                            '<li>' . __("<strong>Fix Critical Bug</strong> This version fix a critical bug on fresh installation regarding a database table creation.", 'subscribe-to-comments-reloaded') . '</li>'.
                            '<li>' . __("<strong>Add</strong> Option to control the inclusion of the style Font Awesome.", 'subscribe-to-comments-reloaded') . '</li>'.
                            '</ul>' .

                            '<p>' . __('If you find a bug or an issue you can report it <a href="https://github.com/stcr/subscribe-to-comments-reloaded/issues" target="_blank">here</a>.', 'subscribe-to-comments-reloaded') . '</p>' .
                            '<p>' . __('Please visit the <a href="https://wordpress.org/plugins/subscribe-to-comments-reloaded/changelog/" target="_blank">Changelog</a> for a complete list of changes.'
                                . '<a class="dismiss" href="#">Dismiss.  </a>'
                                . '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Dismissing Message">', 'subscribe-to-comments-reloaded') . '</p>',
                            'updated'
                        );
                        // Update the HTML emails option
                        update_option( 'subscribe_reloaded_htmlify_message_links', 'no' );
                        update_option( 'subscribe_reloaded_enable_html_emails', 'yes' );
                        break;
                    case '180212':
                        $this->stcr_create_admin_notice(
                            'notify_update_' . $_version,
                            'unread',
                            '<p>' . __('<strong>Subscribe to Comments Reloaded</strong> has been updated to version ' . $_version, 'subscribe-to-comments-reloaded') . '</p>' .
                            '<p>' . __('This version includes fixes and improvements, ', 'subscribe-to-comments-reloaded') . '</p>' .

                            '<ul>' .
                                '<li>' . __("<strong>Security Patch</strong> This version add a patch for some security issues.", 'subscribe-to-comments-reloaded') . '</li>'.
                                '<li>' . __("<strong>Add</strong> Option to reset all the plugin options", 'subscribe-to-comments-reloaded') . '</li>'.
                                '<li>' . __("<strong>Fix</strong> issue regarding database collations", 'subscribe-to-comments-reloaded') . '</li>'.
                            '</ul>' .

                            '<p>' . __('If you find a bug or an issue you can report it <a href="https://github.com/stcr/subscribe-to-comments-reloaded/issues" target="_blank">here</a>.', 'subscribe-to-comments-reloaded') . '</p>' .
                            '<p>' . __('Please visit the <a href="https://wordpress.org/plugins/subscribe-to-comments-reloaded/changelog/" target="_blank">Changelog</a> for a complete list of changes.'
                                . '<a class="dismiss" href="#">Dismiss.  </a>'
                                . '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Dismissing Message">', 'subscribe-to-comments-reloaded') . '</p>',
                            'updated'
                        );
                        // Update the HTML emails option
                        update_option( 'subscribe_reloaded_htmlify_message_links', 'no' );
                        update_option( 'subscribe_reloaded_enable_html_emails', 'yes' );
                        break;
                    case '180225':
                        $this->stcr_create_admin_notice(
                            'notify_update_' . $_version,
                            'unread',
                            '<p>' . __('<strong>Subscribe to Comments Reloaded</strong> has been updated to version ' . $_version, 'subscribe-to-comments-reloaded') . '</p>' .
                            '<p>' . __('This version includes fixes and improvements, ', 'subscribe-to-comments-reloaded') . '</p>' .
                            '<p>' . __('If you find a bug or an issue you can report it <a href="https://github.com/stcr/subscribe-to-comments-reloaded/issues" target="_blank">here</a>.', 'subscribe-to-comments-reloaded') . '</p>' .
                            '<p>' . __('Please visit the <a href="https://wordpress.org/plugins/subscribe-to-comments-reloaded/changelog/" target="_blank">Changelog</a> for a complete list of changes.'
                                . '<a class="dismiss" href="#">Dismiss.  </a>'
                                . '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Dismissing Message">', 'subscribe-to-comments-reloaded') . '</p>',
                            'updated'
                        );
                        // Update the HTML emails option
                        update_option( 'subscribe_reloaded_htmlify_message_links', 'no' );
                        update_option( 'subscribe_reloaded_enable_html_emails', 'yes' );
                        break;
                    case '190117':
                        $this->stcr_create_admin_notice(
                            'notify_update_' . $_version,
                            'unread',
                            '<p>' . __('<strong>Subscribe to Comments Reloaded</strong> has been updated to version ' . $_version, 'subscribe-to-comments-reloaded') . '</p>' .
                            '<p>' . __('This version includes fixes and improvements, ', 'subscribe-to-comments-reloaded') . '</p>' .
                            '<p>' . __('If you find a bug or an issue you can report it <a href="https://github.com/stcr/subscribe-to-comments-reloaded/issues" target="_blank">here</a>.', 'subscribe-to-comments-reloaded') . '</p>' .
                            '<p>' . __('You might need to clear you cache !!', 'subscribe-to-comments-reloaded') . '</p>' .
                            '<p>' . __('Please visit the <a href="http://subscribe-reloaded.com/update/stcr-release-version-'.$_version.'/" target="_blank">Release Post</a> for a complete list of changes and guide about the new version.'
                                . '<a class="dismiss" href="#">Dismiss.  </a>'
                                . '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Dismissing Message">', 'subscribe-to-comments-reloaded') . '</p>',
                            'updated'
                        );
						break;
					case '190214':
						$this->stcr_create_admin_notice(
							'notify_update_' . $_version,
							'unread',
							'<p>' . __('<strong>Subscribe to Comments Reloaded</strong> has been updated to version ' . $_version, 'subscribe-to-comments-reloaded') . '</p>' .
							'<p>' . __('This version includes fixes., ', 'subscribe-to-comments-reloaded') . '</p>' .
							'<p>' . __('If you find a bug or an issue you can report it <a href="https://github.com/stcr/subscribe-to-comments-reloaded/issues" target="_blank">here</a>.', 'subscribe-to-comments-reloaded') . '</p>' .
							'<p>' . __('You might need to clear you cache !!', 'subscribe-to-comments-reloaded') . '</p>' .
							'<p>' . __('Please visit the <a href="http://subscribe-reloaded.com/update/stcr-release-version-'.$_version.'/" target="_blank">Release Post</a> for a complete list of changes and guide about the new version.'
								. '<a class="dismiss" href="#">Dismiss.  </a>'
								. '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Dismissing Message">', 'subscribe-to-comments-reloaded') . '</p>',
							'updated'
						);
						break;
				}
			}
		}

		private function patch_collation()
        {
            global $wpdb;
            $wp_postmeta_table_data     = $wpdb->get_results( 'SHOW TABLE STATUS LIKE \''. $wpdb->prefix .'postmeta\'' );
            $wp_stcr_subs_table_data    = $wpdb->get_results( 'SHOW TABLE STATUS LIKE \''. $wpdb->prefix .'subscribe_reloaded_subscribers\'' );
            $wp_postmeta_collation_data = $wpdb->get_results( 'SHOW COLLATION' );

            $wp_postmeta_collation  = $wp_postmeta_table_data[0]->Collation;
            $wp_stcr_subs_collation = $wp_stcr_subs_table_data[0]->Collation;
            // Check Collation
            if( $wp_postmeta_collation !== $wp_stcr_subs_collation )
            {
                // Get database collations
                $database_collations = $wpdb->get_results( 'SHOW COLLATION' );
                $collations = array();

                if( ! empty($database_collations))
                {
                    foreach ( $database_collations as $collation)
                    {
                        $collations[$collation->Collation] = $collation->Charset;
                    }
                    //$collations = array_unique($collations, SORT_STRING);
                }
                // Update subscribe_reloaded_subscribers table collation
                $new_charset = $collations[$wp_postmeta_collation];
                $new_collation = $wp_postmeta_collation;
                $sql = 'ALTER TABLE '. $wpdb->prefix .'subscribe_reloaded_subscribers CONVERT TO CHARACTER SET '. $new_charset .' COLLATE '. $new_collation;
                $result = $wpdb->query( $sql );

                if( $result !== false ) // Query executed without any error.
                {
                    // Update subscribe_reloaded_subscribers columns collation
                    $sql = 'ALTER TABLE '. $wpdb->prefix .'subscribe_reloaded_subscribers CHANGE subscriber_email subscriber_email VARCHAR(100) CHARACTER SET '. $new_charset .' COLLATE '. $new_collation .' NOT NULL';
                    $result = $wpdb->query( $sql );

                    if( $result !== false ) // Query executed without any error.
                    {
                        $sql = 'ALTER TABLE '. $wpdb->prefix .'subscribe_reloaded_subscribers CHANGE subscriber_unique_id subscriber_unique_id VARCHAR(50) CHARACTER SET '. $new_charset .' COLLATE '. $new_collation .' NOT NULL';
                        $result = $wpdb->query( $sql );

                        if( $result === false ) // Query executed without any error.
                        {
                            // Log query execution.
                            $this->stcr_logger("Error while updating the collation for the subscribe_reloaded_subscribers COLUMN subscriber_unique_id.");
                            $this->stcr_logger("\nDatabase Error: \" $wpdb->last_error \"\n");
                        }
                    }
                    else
                    {
                        // Log query execution.
                        $this->stcr_logger("Error while updating the collation for the subscribe_reloaded_subscribers table COLUMN subscriber_email");
                        $this->stcr_logger("\nDatabase Error: \" $wpdb->last_error \"\n");
                    }
                }
                else
                {
                    // Log query execution.
                    $this->stcr_logger("Error while updating the collation for the subscribe_reloaded_subscribers table");
                    $this->stcr_logger("\nDatabase Error: \" $wpdb->last_error \"\n");
                }
            }
        }
	}
}