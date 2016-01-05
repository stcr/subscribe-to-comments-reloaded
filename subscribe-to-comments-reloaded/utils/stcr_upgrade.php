<?php
/**
 * Class with utility functions to upgrade the plugin.
 * @author reedyseth
 * @since 15-Jul-2015
 * @version 160106
 */
namespace stcr {
	// Avoid direct access to this piece of code
	if ( ! function_exists( 'add_action' ) ) {
		header( 'Location: /' );
		exit;
	}

	if( ! class_exists('\\'.__NAMESPACE__.'\\stcr_upgrade') ) {
		class stcr_upgrade extends stcr_utils {

			public function _create_subscriber_table() {
				global $wpdb;
				$charset_collate = $wpdb->get_charset_collate();
				$errorMsg        = '';
				// If the update option is set to false
				if ( ! get_option('subscribe_reloaded_subscriber_table') ||  get_option('subscribe_reloaded_subscriber_table') == 'no' ) {
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
							$charset_collate";
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
								$notices[] = '<div class="error"><h3>' . __( 'Important Notice', 'subscribe-reloaded' ) . '</h3>' .
									'<p>The creation of of the table <strong>' . $wpdb->prefix . 'subscribe_reloaded_subscribers</strong> failed</p></div>';
								update_option( 'subscribe_reloaded_deferred_admin_notices', $notices );
								break 1;
							}
						}

						$this->stcr_create_admin_notice(
							'notify_create_subscriber_table',
							'unread',
							'<h3>' . __( 'Subscribe to Comments Reloaded Important Notice', 'subscribe-reloaded' ) . '</h3>' .
							'<p>The creation of table <strong>' . $wpdb->prefix . 'subscribe_reloaded_subscribers</strong> was successful.</p>'.
							'<p>This new table will help to add your subscribers email address safer and prevent the Google PII violation.'
							 . '<a class="dismiss" href="#">Got it.  </a>'
								. '<img class="stcr-loading-animation" src="'. esc_url( admin_url() . '/images/loading.gif'). '" alt="Working...">'
							. '</p>',
							'updated'
						);
						update_option('subscribe_reloaded_subscriber_table', 'yes');
					}
				}
			}

			public function _sanitize_db_information() {
				global $wpdb;

				if ( ! get_option( "subscribe_reloaded_data_sanitized" ) || get_option( "subscribe_reloaded_data_sanitized" ) == "no" ) {
					$stcr_data            = $wpdb->get_results(
						" SELECT * FROM wp_options WHERE option_name like 'subscribe_reloaded%'
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
						'<h3>' . __( 'Subscribe to Comments Reloaded Important Notice', 'subscribe-reloaded' ) . '</h3>' .
						'<p>' . __( 'The information in your database has been sanitize to prevent the raw html messages. <a class="dismiss" href="#">Got it.  </a>'
							  . '<img class="stcr-loading-animation" src="'. esc_url( admin_url() . '/images/loading.gif'). '" alt="Working...">'  , 'subscribe-reloaded' )
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
						'<h3>' . __( 'Important Notice', 'subscribe-reloaded' ) . '</h3>' .
						'<p>' . __( 'Comment subscription data from the <strong>Subscribe to Comments</strong> plugin was detected and automatically imported into <strong>Subscribe to Comments Reloaded</strong>.', 'subscribe-reloaded' ) . ( is_plugin_active( 'subscribe-to-comments/subscribe-to-comments.php' ) ? __( ' It is recommended that you now <strong>deactivate</strong> Subscribe to Comments to prevent confusion between the two plugins.', 'subscribe-reloaded' ) : '' ) . '</p>' .
						'<p>' . __( 'If you have subscription data from Subscribe to Comments Reloaded < v2.0 that you want to import, you\'ll need to import that data manually, as only one import routine will ever run to prevent data loss.', 'subscribe-reloaded' ) . '</p>' .
						'<p>' . __( 'Please visit <a href="options-general.php?page=subscribe-to-comments-reloaded/options/index.php">Settings -> Subscribe to Comments</a> to review your configuration.'
							. '<a class="dismiss" href="#">Got it.  </a>'
							. '<img class="stcr-loading-animation" src="'. esc_url( admin_url() . '/images/loading.gif'). '" alt="Working...">', 'subscribe-reloaded' ) . '</p>',
						'updated'
					);
				}
			}
			// end _import_stc_data
			/**
			 * Imports subscription data created with the Comment Reply Notification plugin
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
						. "FROM wp_postmeta WHERE meta_key LIKE '_stcr@_%'"
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
						'<h3>' . __( 'Important Notice', 'subscribe-reloaded' ) . '</h3>' .
						'<p>' . __( 'Comment subscription data from the <strong>Comment Reply Notification</strong> plugin was detected and automatically imported into <strong>Subscribe to Comments Reloaded</strong>.', 'subscribe-reloaded' ) . ( is_plugin_active( 'comment-reply-notification/comment-reply-notification.php' ) ? __( ' It is recommended that you now <strong>deactivate</strong> Comment Reply Notification to prevent confusion between the two plugins.', 'subscribe-reloaded' ) : '' ) . '</p>' .
						'<p>' . __( 'Please visit <a href="options-general.php?page=subscribe-to-comments-reloaded/options/index.php">Settings -> Subscribe to Comments</a> to review your configuration.'
							. '<a class="dismiss" href="#">Got it.  </a>'
							. '<img class="stcr-loading-animation" src="'. esc_url( admin_url() . '/images/loading.gif'). '" alt="Working...">', 'subscribe-reloaded' ) . '</p>',
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
					$this->stcr_create_admin_notice(
						'notify_import_wpcs_data',
						'unread',
						'<h3>' . __( 'Important Notice', 'subscribe-reloaded' ) . '</h3>' .
						'<p>' . __( 'Plugin options and comment subscription data from the <strong>WP Comment Subscriptions</strong> plugin were detected and automatically imported into <strong>Subscribe to Comments Reloaded</strong>.', 'subscribe-reloaded' ) . ( is_plugin_active( 'wp-comment-subscriptions/wp-comment-subscriptions.php' ) ? __( ' It is recommended that you now <strong>deactivate</strong> WP Comment Subscriptions to prevent confusion between the two plugins.', 'subscribe-reloaded' ) : '' ) . '</p>' .
						'<p>' . __( 'If you have subscription data from another plugin (such as Subscribe to Comments or Subscribe to Comments Reloaded < v2.0) that you want to import, you\'ll need to import that data manually, as only one import routine will ever run to prevent data loss.', 'subscribe-reloaded' ) . '</p>' .
						'<p>' . __( '<strong>Note:</strong> If you were previously using the <code>wp_comment_subscriptions_show()</code> function or the <code>[wpcs-subscribe-url]</code> shortcode, you\'ll need to replace those with <code>subscribe_reloaded_show()</code> and <code>[subscribe-url]</code> respectively.', 'subscribe-reloaded' ) . '</p>' .
						'<p>' . __( 'Please visit <a href="options-general.php?page=subscribe-to-comments-reloaded/options/index.php">Settings -> Subscribe to Comments</a> to review your configuration.'
							. '<a class="dismiss" href="#">Got it.  </a>'
							. '<img class="stcr-loading-animation" src="'. esc_url( admin_url() . '/images/loading.gif'). '" alt="Working...">', 'subscribe-reloaded' ) . '</p>',
						'updated'
					);
				}
			}
			// end _import_wpcs_data
			public function upgrade_notification( $_version, $_db_version ) {
				if( empty( $_db_version ) ) {
					$this->stcr_create_admin_notice(
						'notify_update_new_install',
						'unread',
						'<h3>' . __('Important Notice', 'subscribe-reloaded') . '</h3>' .
						'<p>' . __('Thank you for installing <strong>Subscribe to Comments Reloaded</strong>', 'subscribe-reloaded') . '</p>' .
						'<p>' . __('If you find a bug or issue you can report it <a href="https://github.com/stcr/subscribe-to-comments-reloaded/issues" target="_blank">here</a>', 'subscribe-reloaded') . '</p>' .
						'<p>' . __('If you want to donate you can do it by <a href="
https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=XF86X93FDCGYA&lc=US&item_name=Datasoft%20Engineering&item_number=DI%2dSTCR&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted" target="_blank">Paypal</a>', 'subscribe-reloaded') . '</p>' .
						'<p>' . __('Please visit the <a href="https://wordpress.org/plugins/subscribe-to-comments-reloaded/changelog/" target="_blank">Changelog</a> to review a detail information on the plugin changes.'
							. '<a class="dismiss" href="#">Got it.  </a>'
							. '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Working...">', 'subscribe-reloaded') . '</p>',
						'updated'
					);
				} else {
					switch ($_version) {
						case '160106':
							$this->stcr_create_admin_notice(
								'notify_update_' . $_version,
								'unread',
								'<h3>' . __('Important Notice', 'subscribe-reloaded') . '</h3>' .
								'<p>' . __('<strong>Subscribe to Comments Reloaded</strong> has been updated to the version 160106', 'subscribe-reloaded') . '</p>' .
								'<p>' . __('On this version you will find many changes and fixes that they will improve your experience with the plugin, just to mention a few changes:', 'subscribe-reloaded') . '</p>' .
								'<p>' . __('<ul><li>Important change on the Plugin core codebase</li><li>One Click Unsubscribe</li>' .
									'<li>Subscription Checkbox position, now you can move the subscription box above the submit button in your comment form.</li>' .
									'<li>Improve notification System on the Admin areas.</li>' .
									'<li>Updates on translation files</li>' .
									'<li>A new rich editor to create HTML email messages.</li>' .
									'<li>And more...</li>' .
									'</ul>', 'subscribe-reloaded') . '</p>' .
								'<p>' . __('Please visit the <a href="https://wordpress.org/plugins/subscribe-to-comments-reloaded/changelog/" target="_blank">Changelog</a> to review a detail information on the update.'
									. '<a class="dismiss" href="#">Got it.  </a>'
									. '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Working...">', 'subscribe-reloaded') . '</p>',
								'updated'
							);
							break;
					}
				}

				// Show the Comment Mail announcement to new installs and upgrades
				$this->stcr_create_admin_notice(
				'comment_mail_announcement',
				'unread',
				'<h3>' . __('Announcement: Introducing Comment Mail', 'subscribe-reloaded') . '</h3>' .
				'<p>' . __('<strong><a href="http://comment-mail.com/r/stcr-to-cm/" target="_blank">Comment Mail</strong></a> is a new free plugin based on the original <strong>Subscribe to Comments Reloaded</strong> and includes all of the core StCR features.', 'subscribe-reloaded') . '</p>' .
				'<p>' . __('A powerful StCR importer lets you import your StCR settings and subscriptions into Comment Mail, allowing for a seamless transition.', 'subscribe-reloaded') . '</p>' .
				'<p>' . __('Need more powerful features to manage your subscriptions but can\'t wait for StCR? <a href="http://comment-mail.com/r/stcr-to-cm/" target="_blank">Take a look at Comment Mail!</a>', 'subscribe-reloaded') . '</p>' .
				'<p>' . __('<em>Note: Subscribe to Comments Reloaded remains in active development.</em>'
					. '<a class="dismiss" href="#">Got it.  </a>'
					. '<img class="stcr-loading-animation" src="' . esc_url(admin_url() . '/images/loading.gif') . '" alt="Working...">', 'subscribe-reloaded') . '</p>',
				'updated'
				);
			}
		}
	}
}
