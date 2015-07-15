<?php
/**
 * Class with utility functions. This functions are all over the plugin.
 * @author reedyseth
 * @since 15-Jul-2015
 * @version 150715
 */
namespace stcr {
	// Avoid direct access to this piece of code
	if ( ! function_exists( 'add_action' ) ) {
		header( 'Location: /' );
		exit;
	}

	if( ! class_exists('\\'.__NAMESPACE__.'\\stcr_utils') )
	{
		class stcr_utils {

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

			/**
			 * Creates the HTML structure to properly handle HTML messages
			 */
			public function wrap_html_message( $_message = '', $_subject = '' ) {
				$_message = apply_filters( 'stcr_wrap_html_message', $_message );

				return "<html><head><title>$_subject</title></head><body>$_message</body></html>";
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
			/**
			 * Checks if a key is valid for a given email address
			 */
			private function _is_valid_key( $_key, $_email ) {
				if ( $this->generate_temp_key( $_email ) === $_key ) {
					return true;
				} else {
					return false;
				}
			}
			// end _is_valid_key
		}
	}
}
