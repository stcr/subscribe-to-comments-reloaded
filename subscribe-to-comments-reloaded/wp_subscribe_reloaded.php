<?php
namespace stcr;
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}

require_once dirname(__FILE__).'/utils/stcr_manage.php';

if(!class_exists('\\'.__NAMESPACE__.'\\wp_subscribe_reloaded'))	{

	class wp_subscribe_reloaded extends stcr_manage {
		/**
		 * Constructor -- Sets things up.
		 */
		public function __construct() {

			parent::__construct(); // Run parent constructor.

			$this->salt = defined( 'NONCE_KEY' ) ? NONCE_KEY : 'please create a unique key in your wp-config.php';

			// Show the checkbox - You can manually override this by adding the corresponding function in your template
			if ( get_option( 'subscribe_reloaded_show_subscription_box' ) === 'yes' )
			{
                if( get_option('subscribe_reloaded_stcr_position') == 'yes' )
                {
                    add_action( 'comment_form', array($this, 'subscribe_reloaded_show'), 5, 0 );
                }
                else
                {
                    add_filter( 'comment_form_submit_field', array($this, 'subscribe_reloaded_show'), 5, 1 );
                }

			}

			$this->maybe_update();

			// What to do when a new comment is posted
			add_action( 'comment_post', array( $this, 'new_comment_posted' ), 12, 2 );
			// Add hook for the subscribe_reloaded_purge, define on the constructure so that the hook is read on time.
			add_action('_cron_subscribe_reloaded_purge', array($this, 'subscribe_reloaded_purge'), 10 );
			add_action('_cron_log_file_purge', array($this, 'log_file_purge'), 10 );

			// Load Text Domain
			add_action( 'plugins_loaded', array( $this, 'subscribe_reloaded_load_plugin_textdomain' ) );

			// Provide content for the management page using WP filters
			if ( ! is_admin() ) {
				$manager_page_permalink = get_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/' );
				if ( function_exists( 'qtrans_convertURL' ) ) {
					$manager_page_permalink = qtrans_convertURL( $manager_page_permalink );
				}
				if ( empty( $manager_page_permalink ) ) {
					$manager_page_permalink = get_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/' );
				}
				if ( ( strpos( $_SERVER["REQUEST_URI"], $manager_page_permalink ) !== false ) ) {
					add_filter( 'the_posts', array( $this, 'subscribe_reloaded_manage' ), 10, 2 );
				}
				// Enqueue plugin scripts
				$this->utils->hook_plugin_scripts();
			} else {
				// Hook for WPMU - New blog created
				add_action( 'wpmu_new_blog', array( $this, 'new_blog' ), 10, 1 );

				// Remove subscriptions attached to a post that is being deleted
				add_action( 'delete_post', array( $this, 'delete_subscriptions' ), 10, 2 );

				// Monitor actions on existing comments
				add_action( 'deleted_comment', array( $this, 'comment_deleted' ) );
				add_action( 'wp_set_comment_status', array( $this, 'comment_status_changed' ) );
				// Add a new column to the Edit Comments panel
				add_filter( 'manage_edit-comments_columns', array( $this, 'add_column_header' ) );
				add_filter( 'manage_posts_columns', array( $this, 'add_column_header' ) );
				add_action( 'manage_comments_custom_column', array( $this, 'add_comment_column' ) );
				add_action( 'manage_posts_custom_column', array( $this, 'add_post_column' ) );

				// Add appropriate entries in the admin menu
				add_action( 'admin_menu', array( $this, 'add_config_menu' ) );
				// TODO: Remove admin_print_styles and add the style with the correct hook.
				add_action( 'admin_print_styles-edit-comments.php', array( $this, 'add_post_comments_stylesheet' ) );
				add_action( 'admin_print_styles-edit.php', array( $this, 'add_post_comments_stylesheet' ) );

				// Admin notices
				add_action( 'admin_init', array( $this, 'stcr_admin_init' ) );
				add_action( 'admin_notices', array( $this, 'admin_notices' ) );

				// Contextual help
				add_action( 'contextual_help', array( $this, 'contextual_help' ), 10, 3 );

				// Shortcodes to use the management URL sitewide
				add_shortcode( 'subscribe-url', array( $this, 'subscribe_url_shortcode' ) );

				// Settings link for plugin on plugins page
				add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 10, 2 );
				// Subscribe post authors, if the case
				if ( get_option( 'subscribe_reloaded_notify_authors' ) === 'yes' ) {
					add_action( 'publish_post', array( $this, 'subscribe_post_author' ) );
				}
				// Enqueue admin scripts
				$this->utils->hook_admin_scripts();

				// Add the AJAX Action
				$this->utils->stcr_create_ajax_notices();
			}


		}
		// end __construct

		/**
		 * Load localization files
		 */
		function subscribe_reloaded_load_plugin_textdomain() {
			load_plugin_textdomain( 'subscribe-reloaded', FALSE, basename( dirname( __FILE__ ) ) . '/langs/' );
		}

		/*
		 * Add Settings link to plugin on plugins page
		 */
		public function plugin_settings_link( $links, $file ) {
			if ( $file == 'subscribe-to-comments-reloaded/subscribe-to-comments-reloaded.php' ) {
				$links['settings'] = sprintf( '<a href="%s"> %s </a>', admin_url( 'admin.php?page=stcr_options' ), __( 'Settings', 'subscribe-reloaded' ) );
			}

			return $links;
		}

		/**
		 * Retrieves the comment information from the database
		 */
		public function _get_comment_object( $_comment_ID ) {
			global $wpdb;

			return $wpdb->get_row(
				$wpdb->prepare(
					"
		SELECT comment_post_ID, comment_author_email, comment_approved, comment_type, comment_parent
		FROM $wpdb->comments
		WHERE comment_ID = %d LIMIT 1", $_comment_ID
				), OBJECT
			);
		}
		// end _get_comment_object

		/**
		 * Takes the appropriate action, when a new comment is posted
		 */
		public function new_comment_posted( $_comment_ID = 0, $_comment_status = 0 ) {
			// Retrieve the information about the new comment
			$info = $this->_get_comment_object( $_comment_ID );

			if ( empty( $info ) || $info->comment_approved == 'spam' ) {
				return $_comment_ID;
			}

			// Are subscriptions allowed for this post?
			$is_disabled = get_post_meta( $info->comment_post_ID, 'stcr_disable_subscriptions', true );
			if ( ! empty( $is_disabled ) ) {
				return $_comment_ID;
			}

			// Process trackbacks and pingbacks?
			if ( ( get_option( 'subscribe_reloaded_process_trackbacks', 'no' ) == 'no' ) && ( $info->comment_type == 'trackback' || $info->comment_type == 'pingback' ) ) {
				return $_comment_ID;
			}

			// Did this visitor request to be subscribed to the discussion? (and s/he is not subscribed)
			if ( ! empty( $_POST['subscribe-reloaded'] ) && ! empty( $info->comment_author_email ) ) {
			    // Check that the user select a valid subscription status, otherwise skip the subscription addition and continue to notify the
                // users that are subscribe.
				if ( in_array( $_POST['subscribe-reloaded'], array( 'replies', 'digest', 'yes' ) ) ) {

                    switch ($_POST['subscribe-reloaded']) {
                        case 'replies':
                            $status = 'R';
                            break;
                        case 'digest':
                            $status = 'D';
                            break;
                        default:
                            $status = 'Y';
                            break;
                    }

                    if (!$this->is_user_subscribed($info->comment_post_ID, $info->comment_author_email)) {
                        if ($this->isDoubleCheckinEnabled($info)) {
                            $this->sendConfirmationEMail($info);
                            $status = "{$status}C";
                        }
                        $this->add_subscription($info->comment_post_ID, $info->comment_author_email, $status);

                        // If comment is in the moderation queue
                        if ($info->comment_approved == 0) {
                            //don't send notification-emails to all subscribed users
                            return $_comment_ID;
                        }
                    }
                }
			}

			// Send a notification to all the users subscribed to this post
			if ( $info->comment_approved == 1 ) {
				$subscriptions = $this->get_subscriptions(
					array(
						'post_id',
						'status'
					), array(
					'equals',
					'equals'
				), array(
						$info->comment_post_ID,
						'Y'
					)
				);
				// Now verify if the comments has a parent comment, if so, then this comment is a reply.
				if ( ! empty( $info->comment_parent ) ) {
					$subscriptions = array_merge(
						$subscriptions, $this->get_subscriptions(
						'parent', 'equals', array(
							$info->comment_parent,
							$info->comment_post_ID
						)
					)
					);
				}

				foreach ( $subscriptions as $a_subscription ) {
					// Skip the user who posted this new comment
					if ( $a_subscription->email != $info->comment_author_email ) {
						$this->notify_user( $info->comment_post_ID, $a_subscription->email, $_comment_ID );
					}
				}
			}

			// If the case, notify the author
			if ( get_option( 'subscribe_reloaded_notify_authors', 'no' ) == 'yes' ) {
				$this->notify_user( $info->comment_post_ID, get_bloginfo( 'admin_email' ), $_comment_ID );
			}

			return $_comment_ID;
		}

		// end new_comment_posted

		public function isDoubleCheckinEnabled( $info ) {
			$approved_subscriptions = $this->get_subscriptions(
				array(
					'status',
					'email'
				), array(
				'equals',
				'equals'
			), array(
					'Y',
					$info->comment_author_email
				)
			);
			if ( ( get_option( 'subscribe_reloaded_enable_double_check', 'no' ) == 'yes' ) && ! is_user_logged_in() && empty( $approved_subscriptions ) ) {
				return true;
			} else {
				return false;
			}
		}



		/**
		 * Performs the appropriate action when the status of a given comment changes
		 */
		public function comment_status_changed( $_comment_ID = 0, $_comment_status = 0 ) {
			// Retrieve the information about the comment
			$info = $this->_get_comment_object( $_comment_ID );
			if ( empty( $info ) ) {
				return $_comment_ID;
			}

			switch ( $info->comment_approved ) {
				case '0': // Unapproved: change the status of the corresponding subscription (if exists) to 'pending'
					$this->update_subscription_status( $info->comment_post_ID, $info->comment_author_email, 'C' );
					break;

				case '1': // Approved
					$this->update_subscription_status( $info->comment_post_ID, $info->comment_author_email, '-C' );
					$subscriptions = $this->get_subscriptions(
						array(
							'post_id',
							'status'
						), array(
						'equals',
						'equals'
					), array(
							$info->comment_post_ID,
							'Y'
						)
					);
					if ( ! empty( $info->comment_parent ) ) {
						$subscriptions = array_merge(
							$subscriptions, $this->get_subscriptions(
							'parent', 'equals', array(
								$info->comment_parent,
								$info->comment_post_ID
							)
						)
						);
					}

					foreach ( $subscriptions as $a_subscription ) {
						if ( $a_subscription->email != $info->comment_author_email ) // Skip the user who posted this new comment
						{
							$this->notify_user( $info->comment_post_ID, $a_subscription->email, $_comment_ID );
						}
					}
					break;

				case 'trash':
				case 'spam':
					$this->comment_deleted( $_comment_ID );
					break;

				default:
					break;
			}

			return $_comment_ID;
		}
		// end comment_status

		/**
		 * Performs the appropriate action when a comment is deleted
		 */
		public function comment_deleted( $_comment_ID ) {
			global $wpdb;

			$info = $this->_get_comment_object( $_comment_ID );
			if ( empty( $info ) ) {
				return $_comment_ID;
			}

			// Are there any other approved comments sent by this user?
			$count_approved_comments = $wpdb->get_var(
				"
		SELECT COUNT(*)
		FROM $wpdb->comments
		WHERE comment_post_ID = '$info->comment_post_ID' AND comment_author_email = '$info->comment_author_email' AND comment_approved = 1"
			);
			if ( intval( $count_approved_comments ) == 0 ) {
				$this->delete_subscriptions( $info->comment_post_ID, $info->comment_author_email );
			}

			return $_comment_ID;
		}
		// end comment_deleted

		/**
		 * Subscribes the post author, if the corresponding option is set
		 */
		public function subscribe_post_author( $_post_ID ) {
			$new_post     = get_post( $_post_ID );
			$author_email = get_the_author_meta( 'user_email', $new_post->post_author );
			if ( ! empty( $author_email ) ) {
				$this->add_subscription( $_post_ID, $author_email, 'Y' );
			}
		}
		// // end subscribe_post_author

		/**
		 * Displays the appropriate management page
		 */
		public function subscribe_reloaded_manage( $_posts = '', $_query = '' ) {
			global $current_user;
			$stcr_unique_key = get_option( "subscribe_reloaded_unique_key" );
			$date = date_i18n( 'Y-m-d H:i:s' );
			$error_exits = false;
			$email = '';

			if ( ! isset( $_posts ) && ! empty( $_posts ) ) {
				return $_posts;
			}

			$post_ID = ! empty( $_POST['srp'] ) ? intval( $_POST['srp'] ) : ( ! empty( $_GET['srp'] ) ? intval( $_GET['srp'] ) : 0 );

			// Is the post_id passed in the query string valid?
			$target_post = get_post( $post_ID );
			if ( ( $post_ID > 0 ) && ! is_object( $target_post ) ) {
				return $_posts;
			}

			$action 	   = ! empty( $_POST['sra'] )  ? $_POST['sra']     : ( ! empty( $_GET['sra'] )  ?  $_GET['sra']   : 0  );
			$key    	   = ! empty( $_POST['srk'] )  ? $_POST['srk']     : ( ! empty( $_GET['srk'] )  ?  $_GET['srk']   : 0  );
			$sre    	   = ! empty( $_POST['sre'] )  ? $_POST['sre']     : ( ! empty( $_GET['sre'] )  ?  $_GET['sre']   : '' );
			$srek   	   = ! empty( $_POST['srek'] ) ? $_POST['srek']    : ( ! empty( $_GET['srek'] ) ?  $_GET['srek']  : '' );
			$link_source   = ! empty( $_POST['srsrc'] ) ? $_POST['srsrc']  : ( ! empty( $_GET['srsrc'] ) ?  $_GET['srsrc']  : '' );
			$key_expired   = ! empty( $_POST['key_expired'] ) ? $_POST['key_expired']  : ( ! empty( $_GET['key_expired'] ) ?  $_GET['key_expired']  : '0' );
            // Check if the current subscriber has va email using the $srek key.
			$email_by_key  = $this->utils->get_subscriber_email_by_key( $srek );
			// Check for a valid SRE key, otherwise stop execution.
			if( ! $email_by_key && ! empty($srek) ){
				$this->utils->stcr_logger( "\n [ERROR][$date] - Couldn\'t find an email with the SRE key: ( $srek )\n" );
                $email = '';
			}
			else
			{
			    if ( ! $email_by_key && empty($sre) )
                {
                    $email = '';
                }
                else if( $email_by_key && ! empty($email_by_key) )
                {
                    $email = $email_by_key;
                }
                else if ( ! empty($sre) )
                {
                    $email = $sre;
                }
                else
                {
                    $email = '';
                }
			}
			// Check the link source
			if( $link_source == "f" ) // Comes from the comment form.
			{
				// Check for a valid SRK key, until this point we know the email is correct but the $key has expired/change
				// or is wrong, in that case display the request management page template
				if( $email !== "" && $key !== 0 && $stcr_unique_key !== $key || $key_expired == "1" )
				{
					if( $key_expired == "1" )
					{
						$error_exits = true;
					}
					else
					{
						$this->utils->stcr_logger( "\n [ERROR][$date] - Couldn\'t find a valid SRK key with the email ( $email_by_key ) and the SRK key: ( $key )\n This is the current unique key: ( $stcr_unique_key )\n" );
						$error_exits = true;
					}
				}
			}
			else if( $link_source == "e" ) // Comes from the email link.
			{
				if( $email !== "" && $key !== 0 && ! $this->utils->_is_valid_key( $key, $email ) || $key_expired == "1" )
				{
					if( $key_expired == "1" )
					{
						$error_exits = true;
					}
					else
					{
						$this->utils->stcr_logger( "\n [ERROR][$date] - Couldn\'t find a valid SRK key with the email ( $email_by_key ) and the SRK key: ( $key )\n This is the current unique key: ( $stcr_unique_key )\n" );
						$error_exits = true;
					}
				}
			}


			if( $error_exits )
			{
				$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/key_expired.php';
			}
			else
			{
				// Subscribe without commenting
				if ( ! empty( $action ) &&
					( $action == 's' ) &&
					( $post_ID > 0 ) &&
					$key_expired != "1" )
				{
					$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/subscribe.php';
				} // Management page for post authors
				elseif ( ( $post_ID > 0 ) &&
					$this->is_author( $target_post->post_author ) )
				{
					$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/author.php';
				} // Confirm your subscription (double check-in)
				elseif ( ( $post_ID > 0 )  &&
					! empty( $email ) &&
					! empty( $key )   &&
					! empty( $action ) &&
					$this->utils->_is_valid_key( $key, $email ) &&
					$this->is_user_subscribed( $post_ID, $email, 'C' ) &&
					( $action == 'c' ) &&
					$key_expired != "1" )
				{
					$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/confirm.php';
				}
				elseif ( ( $post_ID > 0 )  &&
					! empty( $email ) &&
					! empty( $key )   &&
					! empty( $action ) &&
					$this->utils->_is_valid_key( $key, $email ) &&
					( $action == 'u' ) &&
					$key_expired != "1" )
				{
					$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/one-click-unsubscribe.php';
				}
				// Manage your subscriptions (user)
				elseif (   ! empty( $email ) &&
					( $key !== 0 && $this->utils->_is_valid_key( $key, $email ) || ( ! empty($current_user->data->user_email) && ( $current_user->data->user_email === $email && current_user_can( 'read' ) ) ) ) )
				{
					$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/user.php';
				}
				elseif (   ! empty( $email ) &&
					( $key === 0 && ( ! empty($current_user->data->user_email) && ( $current_user->data->user_email !== $email ) ) ) )
				{
					$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/wrong-request.php';
				}

				if ( empty( $include_post_content ) )
				{
					$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/request-management-link.php';
				}
			}

			global $wp_query;

			$manager_page_title = html_entity_decode( get_option( 'subscribe_reloaded_manager_page_title', 'Manage subscriptions' ), ENT_QUOTES, 'UTF-8' );
			if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
				$manager_page_title = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $manager_page_title );
			} else {
				$manager_page_title = __( $manager_page_title, 'subscribe-reloaded' );
			}

			$posts[] =
				(object) array(
					'ID'                    => '9999999',
					'post_author'           => '1',
					'post_date'             => '2001-01-01 11:38:56',
					'post_date_gmt'         => '2001-01-01 00:38:56',
					'post_content'          => $include_post_content,
					'post_title'            => $manager_page_title,
					'post_excerpt'          => '',
					'post_status'           => 'publish',
					'comment_status'        => 'closed',
					'ping_status'           => 'closed',
					'post_password'         => '',
					'to_ping'               => '',
					'pinged'                => '',
					'post_modified'         => '2001-01-01 11:00:01',
					'post_modified_gmt'     => '2001-01-01 00:00:01',
					'post_content_filtered' => '',
					'post_parent'           => '0',
					'menu_order'            => '0',
					'post_type'             => 'page',
					'post_mime_type'        => '',
					'post_category'         => '0',
					'comment_count'         => '0',
					'filter'                => 'raw',
					'guid'                  => get_bloginfo( 'url' ) . '/?page_id=9999999',
					'post_name'             => get_bloginfo( 'url' ) . '/?page_id=9999999',
					'ancestors'             => array()
				);

			// Make WP believe this is a real page, with no comments attached
			$wp_query->is_page   = true;
			$wp_query->is_single = false;
			$wp_query->is_home   = false;
			$wp_query->comments  = false;

			// Discard 404 errors thrown by other checks
			unset( $wp_query->query["error"] );
			$wp_query->query_vars["error"] = "";
			$wp_query->is_404              = false;

			// Seems like WP adds its own HTML formatting code to the content, we don't need that here
			remove_filter( 'the_content', 'wpautop' );
			// Look like the plugin is call twice and therefor subscribe to the "the_posts" filter again so we need to
			// tell to WordPress to not register again.
			remove_filter("the_posts", array( $this, "subscribe_reloaded_manage" ) );
			add_action( 'wp_head', array( $this, 'add_custom_header_meta' ) );

			return $posts;
		}
		// end subscribe_reloaded_manage





		/**
		 * Checks if current logged in user is the author of this post
		 */
		public function is_author( $_post_author ) {
			global $current_user;

			return ! empty( $current_user ) && ( ( $_post_author == $current_user->ID ) || current_user_can( 'manage_options' ) );
		}
		// end is_author

		/**
		 * Checks if a given email address is subscribed to a post
		 */
		public function is_user_subscribed( $_post_ID = 0, $_email = '', $_status = '' ) {
			global $current_user;

			if ( ( empty( $current_user->user_email ) && empty( $_COOKIE['comment_author_email_' . COOKIEHASH] ) && empty( $_email ) ) || empty( $_post_ID ) ) {
				return false;
			}

			$operator      = ( $_status != '' ) ? 'equals' : 'contains';
			$subscriptions = $this->get_subscriptions(
				array(
					'post_id',
					'status'
				), array(
				'equals',
				$operator
			), array(
					$_post_ID,
					$_status
				)
			);

			if ( empty( $_email ) ) {
				$user_email = ! empty( $current_user->user_email ) ? $current_user->user_email : ( ! empty( $_COOKIE['comment_author_email_' . COOKIEHASH] ) ? stripslashes( esc_attr( $_COOKIE['comment_author_email_' . COOKIEHASH] ) ) : '#undefined#' );
			} else {
				$user_email = $_email;
			}

			foreach ( $subscriptions as $a_subscription ) {
				if ( $user_email == $a_subscription->email ) {
					return true;
				}
			}

			return false;
		}
		// end is_user_subscribed

		/**
		 * Adds a new subscription
		 */
		public function add_subscription( $_post_id = 0, $_email = '', $_status = 'Y' ) {
			global $wpdb;
			// Does the post exist?
			$target_post = get_post( $_post_id );
			if ( ( $_post_id > 0 ) && ! is_object( $target_post ) ) {
				return;
			}

			// Filter unwanted statuses
			if ( ! in_array( $_status, array( 'Y', 'YC', 'R', 'RC', 'C', '-C' ) ) || empty( $_status ) ) {
				return;
			}

			// Using Wordpress local time
			$dt = date_i18n( 'Y-m-d H:i:s' );

			$clean_email = $this->utils->clean_email( $_email );
			$wpdb->query(
				$wpdb->prepare(
					"
		INSERT IGNORE INTO $wpdb->postmeta (post_id, meta_key, meta_value)
			SELECT %d, %s, %s
			FROM DUAL
			WHERE NOT EXISTS (
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE post_id = %d
					AND meta_key = %s
				LIMIT 0,1
			)", $_post_id, "_stcr@_$clean_email", "$dt|$_status", $_post_id, "_stcr@_$clean_email"
				)
			);

			$OK = $this->utils->add_user_subscriber_table( $clean_email );
			if ( ! $OK) {
				// Catch the error
			}
		}
		// end add_subscription

		/**
		 * Deletes one or more subscriptions from the database
		 */
		public function delete_subscriptions( $_post_id = 0, $_email = '' ) {
			global $wpdb;
			$has_subscriptions = false;

			if ( empty( $_post_id ) ) {
				return 0;
			}

			$posts_where = '';
			if ( ! is_array( $_post_id ) ) {
				$posts_where = "post_id = " . intval( $_post_id );
			} else {
				foreach ( $_post_id as $a_post_id ) {
					$posts_where .= "post_id = '" . intval( $a_post_id ) . "' OR ";
				}

				$posts_where = substr( $posts_where, 0, - 4 );
			}

			if ( ! empty( $_email ) ) {
				$emails_where = '';
				if ( ! is_array( $_email ) ) {
					$emails_where = "meta_key = '_stcr@_" . $this->utils->clean_email( $_email ) . "'";
					$has_subscriptions = $this->retrieve_user_subscriptions( $_post_id, $_email );
					if( $has_subscriptions === false) {
						$this->utils->remove_user_subscriber_table( $_email );
					}
				} else {
					foreach ( $_email as $a_email ) {
						$emails_where .= "meta_key = '_stcr@_" . $this->utils->clean_email( $a_email ) . "' OR ";
						// Deletion on every email on the subscribers table.
						$has_subscriptions = $this->retrieve_user_subscriptions( $_post_id, $a_email );
						if( $has_subscriptions === false ) {
							$this->utils->remove_user_subscriber_table( $a_email );
						}
					}

					$emails_where = substr( $emails_where, 0, - 4 );
				}
				return $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE ($posts_where) AND ($emails_where)" );
			} else {
				return $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%' AND ($posts_where)" );
			}
		}
		// end delete_subscriptions

		/**
		 * The function must search for subscription by a given post id.
		 *
		 * @param      $_post_id The post ID to search
		 * @param	   $_email	 The user email, use to search the subscriptions.
		 * @param bool $in        If set to true the search will return the subscription information, if false then it
		 *                        should retrieve all the subscriptions but not the given.
		 *
		 * @return bool|object 	If $in is true then it could return the subscription or false, false means not found,
		 * 						if $in is false the it could return the subscriptions or false, false means not found
		 */
		public function retrieve_user_subscriptions( $_post_id, $_email, $in = false ) {
			global $wpdb;
			$meta_key = '_stcr@_';
			$in_values = '';

			if( ! is_array( $_post_id ) ){
				if ( ! $in ) {
					$retrieve_subscriptions = "SELECT * FROM $wpdb->postmeta WHERE post_id <> %d AND meta_key = %s";
				} else if ( $in ) {
					$retrieve_subscriptions = "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s";
				}
				$result =$wpdb->get_results($wpdb->prepare( $retrieve_subscriptions, $_post_id, $meta_key.$_email ), OBJECT);
			} else {
				//			foreach( $_post_id as $key => $id ){
				//				$_post_id[$key] = "'" . $id . "'";
				//			}
				$in_values = implode( ",",$_post_id );
				if ( ! $in ) {
					$retrieve_subscriptions = "SELECT * FROM $wpdb->postmeta WHERE post_id NOT IN ($in_values) AND meta_key = %s";
				} else if ( $in ) {
					$retrieve_subscriptions = "SELECT * FROM $wpdb->postmeta WHERE post_id IN ($in_values) AND meta_key = %s";
				}
				$result =$wpdb->get_results($wpdb->prepare( $retrieve_subscriptions, $meta_key.$_email ), OBJECT);
			}

			return $result === false || $result == 0 || empty( $result ) ? false : $result;
		}

		/**
		 * Updates the status of an existing subscription
		 */
		public function update_subscription_status( $_post_id = 0, $_email = '', $_new_status = 'C' ) {
			global $wpdb;

			// Filter unwanted statuses
			if ( empty( $_new_status ) || ! in_array( $_new_status, array( 'Y', 'R', 'C', '-C' ) ) || empty( $_email ) ) {
				return 0;
			}

			if ( ! empty( $_post_id ) ) {
				$posts_where = '';
				if ( ! is_array( $_post_id ) ) {
					$posts_where = "post_id = " . intval( $_post_id );
				} else {
					foreach ( $_post_id as $a_post_id ) {
						$posts_where .= "post_id = '" . intval( $a_post_id ) . "' OR ";
					}

					$posts_where = substr( $posts_where, 0, - 4 );
				}
			} else { // Mass update subscriptions
				$posts_where = '1=1';
			}

			$emails_where = '';
			if ( ! is_array( $_email ) ) {
				$emails_where = "meta_key = '_stcr@_" . $this->utils->clean_email( $_email ) . "'";
			} else {
				foreach ( $_email as $a_email ) {
					$emails_where .= "meta_key = '_stcr@_" . $this->utils->clean_email( $a_email ) . "' OR ";
				}

				$emails_where = substr( $emails_where, 0, - 4 );
			}

			$meta_length = ( strpos( $_new_status, 'C' ) !== false ) ? 21 : 20;
			$new_status  = ( $_new_status == '-C' ) ? '' : $_new_status;

			return $wpdb->query(
				"
		UPDATE $wpdb->postmeta
		SET meta_value = CONCAT(SUBSTRING(meta_value, 1, $meta_length), '$new_status')
		WHERE ($posts_where) AND ($emails_where)"
			);
		}
		// end update_subscription_status

		/**
		 * Updates the email address of an existing subscription
		 */
		public function update_subscription_email( $_post_id = 0, $_email = '', $_new_email = '' ) {
			global $wpdb;

			// Nothing to do if the new email hasn't been specified
			if ( empty( $_email ) || empty( $_new_email ) || strpos( $_new_email, '@' ) == 0 ) {
				return;
			}

			$clean_values[] = "_stcr@_" . $this->utils->clean_email( $_new_email );
			$clean_values[] = "_stcr@_" . $this->utils->clean_email( $_email );
			$post_where     = '';
			if ( ! empty( $_post_id ) ) {
				$post_where     = ' AND post_id = %d';
				$clean_values[] = $_post_id;
			}

			$rowsAffected = $wpdb->query(
				$wpdb->prepare("UPDATE $wpdb->postmeta SET meta_key = %s  WHERE meta_key = %s $post_where",
					$clean_values )
			);

			if ( $rowsAffected > 0  || $rowsAffected !== false) {
				$salt = time();
				$rowsAffected = $wpdb->query(
					$wpdb->prepare("UPDATE ". $wpdb->prefix .
						"subscribe_reloaded_subscribers SET subscriber_email = %s,
						    salt = %d,
						    subscriber_unique_id = %s
			 				WHERE subscriber_email = %s",
						$_new_email, $salt, $this->utils->generate_temp_key( $salt . $_new_email ),$_email )
				);
			}
			return false;
		}
		// end update_subscription_email

		/**
		 * Retrieves a list of emails subscribed to this post
		 */
		public function get_subscriptions( $_search_field = array( 'email' ), $_operator = array( 'equals' ), $_search_value = array( '' ), $_order_by = 'dt', $_order = 'ASC', $_offset = 0, $_limit_results = 0 ) {
			global $wpdb;

			// Type adjustments
			$search_fields = ( ! is_array( $_search_field ) ) ? array( $_search_field ) : $_search_field;
			$operators     = ( ! is_array( $_operator ) ) ? array( $_operator ) : $_operator;
			$search_values = ( ! is_array( $_search_value ) ) ? array( $_search_value ) : $_search_value;

			// Find if exists a 'replies only' subscription for the parent comment
			if ( $search_fields[0] == 'parent' ) {

				$parent_comment_id = $search_values[0];
				$comment_post_id   = $search_values[1];

				// Get the parent comment author email so we can search for any Replies Only subscriptions
				$parent_comment_author_email = "\_stcr@\_" . get_comment_author_email( $parent_comment_id );

				// Check if $parent_comment_author_email has any Replies Only (R) subscriptions for $comment_post_id

				/*
								Heads up: this will return Replies Only subscriptions for a given post, *not* for a given comment.
								This plugin does not track subscriptions for specific comments but rather for entire posts, so there
								is no way to figure out if a specific parent comment has a subscription (of any type). To make the
								Replies Only feature half-work, we check if a parent comment author has *any* Replies Only subscriptions
								for a given post. If they do, we assume that they must want to get notified of replies to *any* of their
								comments on *that* post.
				*/

				return $wpdb->get_results(
					$wpdb->prepare(
						"
			SELECT pm.meta_id, REPLACE(pm.meta_key, '_stcr@_', '') AS email, pm.post_id, SUBSTRING(pm.meta_value, 1, 19) AS dt, SUBSTRING(pm.meta_value, 21) AS status, srs.subscriber_unique_id AS email_key
			FROM $wpdb->postmeta pm
			INNER JOIN {$wpdb->prefix}subscribe_reloaded_subscribers srs ON ( REPLACE(pm.meta_key, '_stcr@_', '') = srs.subscriber_email  )
			WHERE pm.meta_key LIKE %s
				AND pm.meta_value LIKE '%%R'
				AND pm.post_id = %d", $parent_comment_author_email, $comment_post_id
					), OBJECT
				);
			} else {
				$where_clause = '';
				foreach ( $search_fields as $a_idx => $a_field ) {
					$where_clause .= ' AND';
					$offset_status = ( $a_field == 'status' && $search_values[$a_idx] == 'C' ) ? 22 : 21;
					switch ( $a_field ) {
						case 'status':
							$where_clause .= " SUBSTRING(meta_value, $offset_status)";
							break;
						case 'post_id':
							$where_clause .= ' post_id';
							break;
						default:
							$where_clause .= ' SUBSTRING(meta_key, 8)';
					}
					switch ( $operators[$a_idx] ) {
						case 'equals':
							$where_clause .= " = %s";
							$where_values[] = $search_values[$a_idx];
							break;
						case 'does not contain':
							$where_clause .= " NOT LIKE %s";
							$where_values[] = "%{$search_values[$a_idx]}%";
							break;
						case 'starts with':
							$where_clause .= " LIKE %s";
							$where_values[] = "{$search_values[$a_idx]}%";
							break;
						case 'ends with':
							$where_clause .= " LIKE %s";
							$where_values[] = "%{$search_values[$a_idx]}";
							break;
						default: // contains
							$where_clause .= " LIKE %s";
							$where_values[] = "%{$search_values[$a_idx]}%";
					}
				}
				switch ( $_order_by ) {
					case 'status':
						$order_by = "status";
						break;
					case 'email':
						$order_by = 'meta_key';
						break;
					case 'dt':
						$order_by = 'dt';
						break;
					default:
						$order_by = 'post_id';
				}
				$order = ( $_order != 'ASC' && $_order != 'DESC' ) ? 'DESC' : $_order;

				// This is the 'official' way to have an offset without a limit
				$row_count = ( $_limit_results <= 0 ) ? '18446744073709551610' : $_limit_results;

				return $wpdb->get_results(
					$wpdb->prepare(
						"
			SELECT meta_id, REPLACE(meta_key, '_stcr@_', '') AS email, post_id, SUBSTRING(meta_value, 1, 19) AS dt, SUBSTRING(meta_value, 21) AS status, srs.subscriber_unique_id AS email_key
			FROM $wpdb->postmeta
			INNER JOIN {$wpdb->prefix}subscribe_reloaded_subscribers srs ON ( REPLACE(meta_key, '_stcr@_', '') = srs.subscriber_email  )
			WHERE meta_key LIKE '\_stcr@\_%%' $where_clause
			ORDER BY $order_by $order
			LIMIT $_offset,$row_count", $where_values
					), OBJECT
				);
			}
		}
		// end get_subscriptions

		/**
		 * Sends the notification message to a given user
		 */
		public function notify_user( $_post_ID = 0, $_email = '', $_comment_ID = 0 ) {
			$post                    = get_post( $_post_ID );
			$comment                 = get_comment( $_comment_ID );
			$post_permalink          = get_permalink( $_post_ID );
			$comment_permalink       = get_comment_link( $_comment_ID );
			$comment_reply_permalink = get_permalink( $_post_ID ) . '?replytocom=' . $_comment_ID . '#respond';
            $info                    = $this->_get_comment_object( $_comment_ID );

			// WPML compatibility
			if ( defined('ICL_SITEPRESS_VERSION') && defined('ICL_LANGUAGE_CODE') ) {
				// Switch language
				global $sitepress;
				$language = $sitepress->get_language_for_element( $_post_ID, 'post_' . $post->post_type );
				$sitepress->switch_lang($language);
			}

			$subject      = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_notification_subject', 'There is a new comment on the post [post_title]' ) ), ENT_QUOTES, 'UTF-8' );
			$message      = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_notification_content', '' ) ), ENT_QUOTES, 'UTF-8' );
			$manager_link = get_bloginfo( 'url' ) . get_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/' );
			$one_click_unsubscribe_link = $manager_link;

			if ( function_exists( 'qtrans_convertURL' ) ) {
				$manager_link = qtrans_convertURL( $manager_link );
			}

			$clean_email     = $this->utils->clean_email( $_email );
			$subscriber_salt = $this->utils->generate_temp_key( $clean_email );

			$manager_link .= ( ( strpos( $manager_link, '?' ) !== false ) ? '&' : '?' )
				. "srek=" . $this->utils->get_subscriber_key( $clean_email )
				. "&srk=$subscriber_salt";
			$one_click_unsubscribe_link .= ( ( strpos( $one_click_unsubscribe_link, '?' ) !== false ) ? '&' : '?' )
				. "srek=" . $this->utils->get_subscriber_key( $clean_email ) . "&srk=$subscriber_salt"
				. "&sra=u&srsrc=e" . "&srp=" . $_post_ID;

			$comment_content = $comment->comment_content;

			// Replace tags with their actual values
			$subject = str_replace( '[post_title]', $post->post_title, $subject );
			$subject = str_replace( '[blog_name]' , get_bloginfo('name'), $subject );

			$message = str_replace( '[post_permalink]', $post_permalink, $message );
			$message = str_replace( '[comment_permalink]', $comment_permalink, $message );
			$message = str_replace( '[comment_reply_permalink]', $comment_reply_permalink, $message );
			$message = str_replace( '[comment_author]', $comment->comment_author, $message );
			$message = str_replace( '[comment_content]', $comment_content, $message );
			$message = str_replace( '[manager_link]', $manager_link, $message );
			$message = str_replace( '[oneclick_link]', $one_click_unsubscribe_link, $message );
            $message = str_replace( '[comment_gravatar]', get_avatar($info->comment_author_email, 40), $message );

			// QTranslate support
			if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
				$subject = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $subject );
				$message = str_replace( '[post_title]', qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $post->post_title ), $message );
				$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
			} else {
				$message = str_replace( '[post_title]', $post->post_title, $message );
			}
			$message = apply_filters( 'stcr_notify_user_message', $message, $_post_ID, $clean_email, $_comment_ID );
			// Prepare email settings
			$email_settings = array(
				'subject'      => $subject,
				'message'      => $message,
				'toEmail'      => $clean_email,
				'XPostId'    => $_post_ID,
				'XCommentId' => $_comment_ID
			);
			$this->utils->send_email( $email_settings );
		}
		// end notify_user





		/**
		 * Displays the checkbox to allow visitors to subscribe
		 */
		function subscribe_reloaded_show($submit_field = '') {
			global $post, $wp_subscribe_reloaded;
			$checkbox_subscription_type = null;
            $_comment_ID = null;
            $post_permalink = get_permalink( $post->ID );
            $post_permalink = "post_permalink=" . $post_permalink;


			// Enable JS scripts.
			 $wp_subscribe_reloaded->stcr->utils->add_plugin_js_scripts();
			wp_enqueue_style( 'stcr-plugin-style' );

			$is_disabled = get_post_meta( $post->ID, 'stcr_disable_subscriptions', true );
			if ( ! empty( $is_disabled ) ) {
				return $_comment_ID;
			}

			$show_subscription_box = true;
			$html_to_show          = '';
			$user_link             = get_bloginfo( 'url' ) . get_option( 'subscribe_reloaded_manager_page', '' );

			if ( function_exists( 'qtrans_convertURL' ) ) {
				$user_link = qtrans_convertURL( $user_link );
			}

			$manager_link = ( strpos( $user_link, '?' ) !== false ) ?
				"$user_link&amp;srp=$post->ID&amp;srk=" . get_option( 'subscribe_reloaded_unique_key' ) :
				"$user_link?srp=$post->ID&amp;srk=" . get_option( 'subscribe_reloaded_unique_key' );

            $user_link = ( strpos( $user_link, '?' ) !== false ) ?
                "$user_link&" . $post_permalink :
                "$user_link?" . $post_permalink;

			if ( $wp_subscribe_reloaded->stcr->is_user_subscribed( $post->ID, '', 'C' ) ) {
				$html_to_show          = str_replace(
					'[manager_link]', $user_link,
					__( html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_subscribed_waiting_label', "Your subscription to this post needs to be confirmed. <a href='[manager_link]'>Manage your subscriptions</a>." ) ), ENT_QUOTES, 'UTF-8' ), 'subscribe-reloaded' )
				);
				$show_subscription_box = false;
			} elseif ( $wp_subscribe_reloaded->stcr->is_user_subscribed( $post->ID, '' ) ) {
				$html_to_show          = str_replace(
					'[manager_link]', $user_link ,
					__( html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_subscribed_label', "You are subscribed to this post. <a href='[manager_link]'>Manage</a> your subscriptions." ) ), ENT_QUOTES, 'UTF-8' ), 'subscribe-reloaded' )
				);
				$show_subscription_box = false;
			}

			if ( $wp_subscribe_reloaded->stcr->is_author( $post->post_author ) ) { // when the second parameter is empty, cookie value will be used
				if ( get_option( 'subscribe_reloaded_admin_subscribe', 'no' ) == 'no' ) {
					$show_subscription_box = false;
				}
				$html_to_show .= str_replace(
					'[manager_link]', $manager_link,
					__( html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_author_label', "You can <a href='[manager_link]'>manage the subscriptions</a> of this post." ) ), ENT_QUOTES, 'UTF-8' ), 'subscribe-reloaded' )
				);
			}

			if ( $show_subscription_box ) {
				$checkbox_label        = str_replace(
					'[subscribe_link]', "$manager_link&amp;sra=s&amp;srsrc=f",
					__( html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_checkbox_label', "Notify me of followup comments via e-mail. You can also <a href='[subscribe_link]'>subscribe</a> without commenting." ) ), ENT_QUOTES, 'UTF-8' ), 'subscribe-reloaded' )
				);
				$checkbox_inline_style = get_option( 'subscribe_reloaded_checkbox_inline_style', 'width:30px' );
				if ( ! empty( $checkbox_inline_style ) ) {
					$checkbox_inline_style = " style='$checkbox_inline_style'";
				}
				$checkbox_html_wrap = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_checkbox_html', '' ) ), ENT_QUOTES, 'UTF-8' );
				if ( get_option( 'subscribe_reloaded_enable_advanced_subscriptions', 'no' ) == 'no' ) {
					switch ( get_option( 'subscribe_reloaded_checked_by_default_value' ) ) {
						case '0':
							$checkbox_subscription_type = 'yes';
							break;
						case '1':
							$checkbox_subscription_type = 'replies';
							break;
					}
					$checkbox_field = "<input$checkbox_inline_style type='checkbox' name='subscribe-reloaded' id='subscribe-reloaded' value='$checkbox_subscription_type'" . ( ( get_option( 'subscribe_reloaded_checked_by_default', 'no' ) == 'yes' ) ? " checked='checked'" : '' ) . " />";
				} else {
					$checkbox_field = "<select name='subscribe-reloaded' id='subscribe-reloaded'>
								<option value='none' " . ( ( get_option( 'subscribe_reloaded_default_subscription_type' ) === '0' ) ? "selected='selected'" : '' ) . ">" . __( "Don't subscribe", 'subscribe-reloaded' ) . "</option>
								<option value='yes' " . ( ( get_option( 'subscribe_reloaded_default_subscription_type' ) === '1' ) ? "selected='selected'" : '' ) . ">" . __( "All", 'subscribe-reloaded' ) . "</option>
								<option value='replies' " . ( ( get_option( 'subscribe_reloaded_default_subscription_type' ) === '2' ) ? "selected='selected'" : '' ) . ">" . __( "Replies to my comments", 'subscribe-reloaded' ) . "</option>
							</select>";
				}
				if ( empty( $checkbox_html_wrap ) ) {
					$html_to_show = "$checkbox_field <label for='subscribe-reloaded'>$checkbox_label</label>" . $html_to_show;
				} else {
					$checkbox_html_wrap = str_replace( '[checkbox_field]', $checkbox_field, $checkbox_html_wrap );
					$html_to_show       = str_replace( '[checkbox_label]', $checkbox_label, $checkbox_html_wrap ) . $html_to_show;
				}
			}
			if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
				$html_to_show = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $html_to_show );
			}
			$output = '';
			// Check for the Comment Form location
			if( get_option('subscribe_reloaded_stcr_position') == 'yes' ) {
				$output .= "<div class='stcr-form hidden'>";
                $output .= "<!-- Subscribe to Comments Reloaded version ". $wp_subscribe_reloaded->stcr->current_version . " -->";
                $output .= "<!-- BEGIN: subscribe to comments reloaded -->" . $html_to_show . "<!-- END: subscribe to comments reloaded -->";
				$output .= "</div>";
			} else {
                $output .= "<!-- Subscribe to Comments Reloaded version ". $wp_subscribe_reloaded->stcr->current_version . " -->";
                $output .= "<!-- BEGIN: subscribe to comments reloaded -->" . $html_to_show . "<!-- END: subscribe to comments reloaded -->";
			}

			echo $output . $submit_field;
		} // end subscribe_reloaded_show

		public function setUserCoookie() {
			// Set a cookie if the user just subscribed without commenting
			$subscribe_to_comments_action  = ! empty( $_POST['sra'] ) ? $_POST['sra'] : ( ! empty( $_GET['sra'] ) ? $_GET['sra'] : 0 );
			$subscribe_to_comments_post_ID = ! empty( $_POST['srp'] ) ? intval( $_POST['srp'] ) : ( ! empty( $_GET['srp'] ) ? intval( $_GET['srp'] ) : 0 );

			if ( ! empty( $subscribe_to_comments_action ) && ! empty( $_POST['subscribe_reloaded_email'] ) &&
				( $subscribe_to_comments_action == 's' ) && ( $subscribe_to_comments_post_ID > 0 )
			) {
				$subscribe_to_comments_clean_email = $this->utils->clean_email( $_POST['subscribe_reloaded_email'] );
				setcookie( 'comment_author_email' . COOKIEHASH, $subscribe_to_comments_clean_email, time() + 1209600, '/' );
			}
		}
	} // end of class declaration
}