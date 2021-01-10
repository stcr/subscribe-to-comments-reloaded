<?php
namespace stcr;

// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}

// globals
define( __NAMESPACE__.'\\VERSION','210110' );
define( __NAMESPACE__.'\\DEVELOPMENT', false );
define( __NAMESPACE__.'\\SLUG', "subscribe-to-comments-reloaded" );

// load files
require_once dirname(__FILE__).'/utils/stcr_manage.php';
require_once dirname(__FILE__).'/classes/stcr_i18n.php';
require_once dirname(__FILE__).'/utils/functions.php';

// Main plugin class
if( ! class_exists('\\'.__NAMESPACE__.'\\wp_subscribe_reloaded') ) {

	/**
	 * Main plugin class
     * 
     * __construct ( Constructor )
     * add_test_subscriptions ( Adds subscriptions for testing purposes )
     * define_wp_hooks ( Define the WordPress Hooks that will be used by the plugin. )
     * display_admin_header ( Display admin header menu )
     * subscribe_reloaded_load_plugin_textdomain ( Load localization files )
     * plugin_settings_link ( Add Settings link to plugin on plugins page )
     * _get_comment_object ( Retrieves the comment information from the database )
     * new_comment_posted ( Takes the appropriate action, when a new comment is posted )
     * is_double_check_enabled ( Is double check ( subscriptions need to be confirmed ) enabled )
     * comment_status_changed ( Actions when comments status changes ( approve/unapprove/spam/trash ) )
     * comment_deleted ( Actions when comment is deleted )
     * subscribe_post_author ( Subscribe the post author )
     * subscribe_reloaded_manage ( Displays the appropriate management page )
     * is_author ( Checks if current logged in user is the author )
     * is_user_subscribed ( Checks if a given email address is subscribed to a post )
     * add_subscription ( Adds a new subscription )
     * delete_subscriptions ( Deletes one or more subscriptions from the database )
     * retrieve_user_subscriptions ( Retries user subscriptions by post ID )
     * update_subscription_status ( Updates the status of an existing subscription )
     * update_subscription_email ( Updates the email address of an existing subscription )
     * get_subscriptions ( Retrieves a list of emails subscribed to a specific post )
     * notify_user ( Sends the notification message to a given user )
     * subscribe_reloaded_show ( Displays the checkbox to allow visitors to subscribe )
     * set_user_cookie ( Set a cookie if the user just subscribed without commenting )
     * management_page_sc ( Management page shortcode )
     * comment_content_prepend ( Add custom content before comment content )
	 */
	class wp_subscribe_reloaded extends stcr_manage {
		
		public $stcr_i18n;
		
		/**
		 * Constructor
		 */
		public function __construct() {

			// run parent constructor.
			parent::__construct();

			$this->salt = defined( 'NONCE_KEY' ) ? NONCE_KEY : 'please create a unique key in your wp-config.php';

			// show the checkbox - You can manually override this by adding the corresponding function in your template
			if ( get_option( 'subscribe_reloaded_show_subscription_box' ) === 'yes' ) {
                
                if ( get_option('subscribe_reloaded_stcr_position') == 'yes' ) {
                    add_action( 'comment_form', array($this, 'subscribe_reloaded_show'), 5, 0 );
                } else {
                    add_filter( 'comment_form_submit_field', array($this, 'subscribe_reloaded_show'), 5, 1 );
                }
                
                // when users must be logged in to comment and current visitor is not logged in
                add_action( 'comment_form_must_log_in_after', array($this, 'subscribe_reloaded_show'), 5, 0 );

			}

			$this->maybe_update();

			// define WordPress hooks the plugin uses
			$this->define_wp_hooks();

			// localization
            $this->stcr_i18n = new stcr_i18n();

			// add subscriptions for tests
            if ( DEVELOPMENT ) {
				$this->add_test_subscriptions( 1000, 18,'Y', 'dev', 30);
            }

			// management page shortcode
            add_shortcode( 'stcr_management_page', array( $this, 'management_page_sc' ) );

        }

		/**
		 * Adds subscriptions for testing purposes
		 * 
		 * @since 190705
		 */
        public function add_test_subscriptions( $iterations = 1 ,$post_id, $status = 'Y', $email_prefix = 'dev', $last_id_subs = 0 ) {
            for ( $i = $last_id_subs+1; $i <= $iterations; $i++) {
                $this->add_subscription( $post_id, "{$email_prefix}_{$i}" . time() . "@dev.com", $status );
            }
		}
		
        /**
         * Define the WordPress Hooks that will be used by the plugin.
         *
         * @since 180302
         */
        public function define_wp_hooks() {

            // new comment posted
			add_action( 'comment_post', array( $this, 'new_comment_posted' ), 12, 2 );
			
            // add hook for the subscribe_reloaded_purge, define on the constructure so that the hook is read on time.
            add_action('_cron_subscribe_reloaded_purge', array($this, 'subscribe_reloaded_purge'), 10 );
            add_action('_cron_log_file_purge', array($this, 'log_file_purge'), 10 );

            // load text domain
            add_action( 'plugins_loaded', array( $this, 'subscribe_reloaded_load_plugin_textdomain' ) );

            // front end
            if ( ! is_admin() ) {

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

				// if we are on the management page, filter the_posts
                if ( ( strpos( $_SERVER["REQUEST_URI"], $manager_page_permalink ) !== false ) ) {

                    $request_uri = $_SERVER['REQUEST_URI'];
                    $request_uri_arr = explode( $manager_page_permalink, $request_uri );

                    // don't show management page if a "child page" 
                    if ( empty( $request_uri_arr[1] ) || $request_uri_arr[1] == '/' || strpos( $request_uri_arr[1], '/?' ) === 0 ) {
                        add_filter( 'the_posts', array( $this, 'subscribe_reloaded_manage' ), 10, 2 );
                    }

                }
                
                // filter to add custom output before comment content
				add_filter( 'comment_text', array( $this, 'comment_content_prepend' ), 10, 2 );
				
				// script to move the subscription form
				add_action( 'wp_footer', array( $this, 'move_form_with_js' ), 20 );
				
                // enqueue scripts
				$this->utils->hook_plugin_scripts();
			
			// wp admin
            } else {

                // hook for WPMU - new blog created
                add_action( 'wpmu_new_blog', array( $this, 'new_blog' ), 10, 1 );

                // remove subscriptions for post that is being deleted
                add_action( 'delete_post', array( $this, 'delete_subscriptions' ), 10, 2 );

                // remove subscriptions when a comment is deleted or status changed
                add_action( 'deleted_comment', array( $this, 'comment_deleted' ) );
				add_action( 'wp_set_comment_status', array( $this, 'comment_status_changed' ) );
				
                // new columns in post/comment tables ( WP admin > Posts and WP admin > Comments )
                add_filter( 'manage_edit-comments_columns', array( $this, 'add_column_header' ) );
                add_filter( 'manage_posts_columns', array( $this, 'add_column_header' ) );
                add_action( 'manage_comments_custom_column', array( $this, 'add_comment_column' ) );
                add_action( 'manage_posts_custom_column', array( $this, 'add_post_column' ) );

                // Add appropriate entries in the admin menu
                add_action( 'admin_menu', array( $this, 'add_config_menu' ) );
                add_action( 'admin_print_styles-edit-comments.php', array( $this, 'add_post_comments_stylesheet' ) );
                add_action( 'admin_print_styles-edit.php', array( $this, 'add_post_comments_stylesheet' ) );

                // admin header
                add_action( 'in_admin_header', array( $this, 'display_admin_header' ), 100 );

				// admin init
				add_action( 'admin_init', array( $this, 'stcr_admin_init' ) );

                // admin notices
                add_action( 'admin_notices', array( $this, 'admin_notices' ) );

                // contextual help
                add_action( 'contextual_help', array( $this, 'contextual_help' ), 10, 3 );

                // shortcodes to use the management URL sitewide
                add_shortcode( 'subscribe-url', array( $this, 'subscribe_url_shortcode' ) );

                // action links for listing on WP admin > Plugins
				add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 10, 2 );
				
                // subscribe post authors, if auto subscribe for authors enabled
                if ( get_option( 'subscribe_reloaded_notify_authors' ) === 'yes' ) {
                    add_action( 'publish_post', array( $this, 'subscribe_post_author' ) );
				}
            
                // enqueue scripts
                $this->utils->hook_admin_scripts();

                // ajax for admin notices ( mark as read )
                $this->utils->stcr_create_ajax_notices();

				// download system information file
				add_action( 'admin_init', array( $this, 'sysinfo_download' ) );
				
				// exclude subscriptions on post duplication
				add_filter( 'duplicate_post_blacklist_filter', array( $this, 'duplicate_post_exclude_subs' ) );

			}
			
        }

		/**
		 * Display the admin header menu
		 * 
		 * @since 190705 Cleanup
		 */
        public function display_admin_header () {

			global $wp_locale;

			$slug = 'stcr_manage_subscriptions';
			$current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';
			
            // define the menu items
            $array_pages = array(
                'stcr_manage_subscriptions' => __( 'Manage subscriptions', 'subscribe-to-comments-reloaded' ),
                'stcr_comment_form'         => __( 'Comment Form', 'subscribe-to-comments-reloaded' ),
                'stcr_management_page'      => __( 'Management Page', 'subscribe-to-comments-reloaded' ),
                'stcr_notifications'        => __( 'Notifications', 'subscribe-to-comments-reloaded' ),
                'stcr_options'              => __( 'Options', 'subscribe-to-comments-reloaded' ),
                'stcr_support'              => __( 'Support', 'subscribe-to-comments-reloaded' ),
                'stcr_system'               => __( 'Options', 'subscribe-to-comments-reloaded' )
            );

            // do not proceed if not on a STCR admin page
            if ( ! array_key_exists(  $current_page, $array_pages) ) {
                return;
            }

            ?>

            <nav class="navbar navbar-expand-lg navbar-light bg-light <?php echo $wp_locale->text_direction ?>">
                
				<a class="navbar-brand"><img src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/images/stcr-logo-150.png" alt="" width="25" height="19"></a>
                
				<div class="collapse navbar-collapse">

                    <ul class="navbar-nav">
                        
						<?php
							
							// go through each stcr admin page
							foreach ( $array_pages as $page => $page_desc ) :

								// skip strc_system because it's added as part of stcr_options
								if ( $page == 'stcr_system' ) continue;

								?><li class="<?php echo $page == 'stcr_options' ? 'dropdown' : '';  ?>"><?php

									// dropdrown for options menu item
                                    if (  $page == 'stcr_options' ) :
										
										?>
										<a 
											class="nav-link dropdown-toggle <?php echo ( $current_page == $page || $current_page == 'stcr_system' ? ' stcr-active-tab' : '' ); ?>" 
											style="padding: 5px 12px 0 0;" 
											href="#" 
											id="navbarDropdown" 
											role="button" 
											data-toggle="dropdown" 
											aria-haspopup="true" 
											aria-expanded="false">
											<?php echo $page_desc; ?>
                                        </a>
										<div class="dropdown-menu" aria-labelledby="navbarDropdown">
											<a class="dropdown-item" href="admin.php?page=<?php echo $page; ?>"><?php echo __('StCR Options', 'subscribe-to-comments-reloaded'); ?></a>
											<div class="dropdown-divider"></div>
											<a class="dropdown-item" href="admin.php?page=stcr_system"><?php echo __('StCR System', 'subscribe-to-comments-reloaded'); ?></a>
										</div>
										<?php
									
									// regular menu items
                                    else :
										
										?>
										<a 
											class="navbar-brand <?php echo ( $current_page == $page ) ? ' stcr-active-tab' : ''; ?>" 
											href="admin.php?page=<?php echo $page; ?>">
											<?php echo $page_desc; ?>
										</a>
										<?php
										
									endif;

                                ?></li><?php
							
							endforeach;
							
                        ?>

                    </ul><!-- .navbar-nav -->

                </div><!-- .navbar-collapse -->

            </nav><!-- .navbar -->
			<?php

        }

		/**
		 * Load localization files
		 * 
		 * @since 190705 cleanup
		 */
		function subscribe_reloaded_load_plugin_textdomain() {

			load_plugin_textdomain( 'subscribe-to-comments-reloaded', FALSE, SLUG . '/langs/' );

		}

		/**
		 * Add Settings link to plugin on plugins page
		 * 
		 * @since 190705 cleanup
		 */
		public function plugin_settings_link( $links, $file ) {

			if ( $file == 'subscribe-to-comments-reloaded/subscribe-to-comments-reloaded.php' ) {
				$links['settings'] = sprintf( '<a href="%s"> %s </a>', admin_url( 'admin.php?page=stcr_options' ), __( 'Settings', 'subscribe-to-comments-reloaded' ) );
			}

			return $links;

		}

		/**
		 * Retrieves the comment information from the database
		 * 
		 * @since 190705 cleanup
		 */
		public function _get_comment_object( $_comment_ID ) {
			
			global $wpdb;

			return $wpdb->get_row(
				$wpdb->prepare(
					"SELECT comment_post_ID, comment_author_email, comment_approved, comment_type, comment_parent
					 FROM $wpdb->comments
					 WHERE comment_ID = %d 
					 LIMIT 1", $_comment_ID
				), OBJECT
			);

		}

		/**
		 * Takes the appropriate action, when a new comment is posted
		 * 
		 * @since 190705 cleanup
		 */
		public function new_comment_posted( $_comment_ID = 0, $_comment_status = 0 ) {
			
			// get information about the comment
			$info = $this->_get_comment_object( $_comment_ID );

			// return if no info found or comment marked as spam
			if ( empty( $info ) || $info->comment_approved == 'spam' ) {
				return $_comment_ID;
			}

			// return if subscriptions disabled for this post
			$is_disabled = get_post_meta( $info->comment_post_ID, 'stcr_disable_subscriptions', true );
			if ( ! empty( $is_disabled ) ) {
				return $_comment_ID;
			}

			// return if trackback/pingback ( if set not to notify on those )
			if ( ( get_option( 'subscribe_reloaded_process_trackbacks', 'no' ) == 'no' ) && ( $info->comment_type == 'trackback' || $info->comment_type == 'pingback' ) ) {
				return $_comment_ID;
			}

			// process the subscription
			if ( ! empty( $_POST['subscribe-reloaded'] ) && ! empty( $info->comment_author_email ) ) {
			   
				// check if subscription type is valid
				if ( in_array( $_POST['subscribe-reloaded'], array( 'replies', 'digest', 'yes' ) ) ) {

					// get subscription type
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

					// if not already subscribed
                    if ( ! $this->is_user_subscribed($info->comment_post_ID, $info->comment_author_email)) {

						// if double check enabled, send confirmation email and append C to status
                        if ( $this->is_double_check_enabled($info) ) {
                            $this->sendConfirmationEMail($info);
                            $status = "{$status}C";
						}

						// add the subscription
                        $this->add_subscription($info->comment_post_ID, $info->comment_author_email, $status);

                        // return ( do not proceed with sending notifications ) if comment held for moderation
                        if ( $info->comment_approved == 0 ) {
                            return $_comment_ID;
						}
						
					}
					
				}
				
			}

			// if comment approved, notify subscribed users about the comment
			if ( $info->comment_approved == 1 ) {

				// get all subscriptions
				$subscriptions = $this->get_subscriptions(
					array(
						'post_id',
						'status'
					), 
					array(
						'equals',
						'equals'
					), 
					array(
						$info->comment_post_ID,
						'Y'
					)
				);

				// is this a reply to an existing comment?
				if ( ! empty( $info->comment_parent ) ) {

					// merge subscriptions
					$subscriptions = array_merge(
						$subscriptions, 
						$this->get_subscriptions(
							'parent', 
							'equals', 
							array(
								$info->comment_parent,
								$info->comment_post_ID
							)
						)
					);

				}

				// post author info
				$post_author_id = get_post_field( 'post_author', $info->comment_post_ID );
				$post_author_data = get_userdata( $post_author_id );
				$post_author_email = $post_author_data->user_email;
				$post_author_notified = false;

				// notify subscribers
				foreach ( $subscriptions as $a_subscription ) {
					
					// skip comment author
					if ( $a_subscription->email != $info->comment_author_email ) {
						
						// notify the user
						$this->notify_user( $info->comment_post_ID, $a_subscription->email, $_comment_ID );

						// post author notified?
						if ( $a_subscription->email == $post_author_email ) {
							$post_author_notified = true;
						}

					}
					
				}

				// notify author
				if ( ! $post_author_notified && get_option( 'subscribe_reloaded_notify_authors', 'no' ) == 'yes' ) {
					
					// send email to author unless the author made the comment
					if ( $info->comment_author_email != $post_author_email ) {
						$this->notify_user( $info->comment_post_ID, $post_author_email, $_comment_ID );
					}

				}

			}

			// that's all, return
			return $_comment_ID;

		}

		/**
		 * Is double check ( subscriptions need to be confirmed ) enabled
		 * 
		 * @since 190705
		 */
		public function is_double_check_enabled( $info ) {

			$is_subscribe_to_post = false;
			$is_user_logged_in = is_user_logged_in();
			$is_option_enabled = false;
			if ( get_option( 'subscribe_reloaded_enable_double_check', 'no' ) == 'yes' ) {
				$is_option_enabled = true;
			}

			$approved_subscriptions = $this->get_subscriptions(
				array(
					'status',
					'email'
				), 
				array(
					'equals',
					'equals'
				), array(
					'Y',
					$info->comment_author_email
				)
			);

			// check if the user is already subscribed to the requested Post ID
            foreach ( $approved_subscriptions as $subscription ) {
                if ( $info->comment_post_ID == $subscription->post_id ) {
                    $is_subscribe_to_post = true;
                }
            }

			// option enabled AND user not logged in AND not already subscribed
			if ( $is_option_enabled && ! $is_user_logged_in && ( ! $is_subscribe_to_post || empty( $approved_subscriptions ) ) ) {
				return true;
			} else {
				return false;
			}

		}

		/**
		 * Actions when comments status changes ( approve/unapprove/spam/trash )
		 * 
		 * @since 190705 cleanup
		 */
		public function comment_status_changed( $_comment_ID = 0, $_comment_status = 0 ) {

			// get information about the comment
			$info = $this->_get_comment_object( $_comment_ID );
			
			// return, no information found
			if ( empty( $info ) ) {
				return $_comment_ID;
			}

			// go through the types of statuses
			switch ( $info->comment_approved ) {

				// unapproved
				case '0':
					
					$this->update_subscription_status( $info->comment_post_ID, $info->comment_author_email, 'C' );
					break;

				// approved
				case '1':

					$this->update_subscription_status( $info->comment_post_ID, $info->comment_author_email, '-C' );
					
					// get subscriptions
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
								'parent', 
								'equals', 
								array(
									$info->comment_parent,
									$info->comment_post_ID
								)
							)
						);

					}

					// go through subscriptions and notify subscribers
					foreach ( $subscriptions as $a_subscription ) {

						// skip the comment author
						if ( $a_subscription->email != $info->comment_author_email ) {
							$this->notify_user( $info->comment_post_ID, $a_subscription->email, $_comment_ID );
						}

					}

					break;

				case 'trash':

				case 'spam':
					
					// perform the same actions as if it were deleted
					$this->comment_deleted( $_comment_ID );
					break;

				default:
					break;

			}

			// return
			return $_comment_ID;

		}

		/**
		 * Actions when comment is deleted
		 * 
		 * @since 190705 cleanup
		 */
		public function comment_deleted( $_comment_ID ) {

			global $wpdb;

			// get information about the comments
			$info = $this->_get_comment_object( $_comment_ID );

			// return, no information found
			if ( empty( $info ) ) {
				return $_comment_ID;
			}

			// how many comments does the author have on this post
			$count_approved_comments = $wpdb->get_var(
				"SELECT COUNT(*)
				 FROM $wpdb->comments
				 WHERE comment_post_ID = '$info->comment_post_ID' 
				 	AND comment_author_email = '$info->comment_author_email' 
					AND comment_approved = 1"
			);

			// if author has no comments left on this post, remove his subscription
			if ( intval( $count_approved_comments ) == 0 ) {
				$this->delete_subscriptions( $info->comment_post_ID, $info->comment_author_email );
			}

			// return
			return $_comment_ID;
		}

		/**
		 * Subscribe the post author
		 * 
		 * @since 190705 cleanup
		 */
		public function subscribe_post_author( $_post_ID ) {

			$new_post     = get_post( $_post_ID );
			$author_email = get_the_author_meta( 'user_email', $new_post->post_author );

			if ( ! empty( $author_email ) ) {
				$this->add_subscription( $_post_ID, $author_email, 'Y' );
			}

		}	

		/**
		 * Displays the appropriate management page
		 * 
		 * @since 190705 cleanup
		 */
		public function subscribe_reloaded_manage( $_posts = '', $_query = '' ) {
			
			// vars
			global $current_user;
			$stcr_unique_key = get_option( 'subscribe_reloaded_unique_key' );
			$date = date_i18n( 'Y-m-d H:i:s' );
			$error_exits = false;
			$email = '';
			$virtual_page_enabled = get_option( 'subscribe_reloaded_manager_page_enabled', 'yes' );

			// if something exists at this URL and virtual page disabled, abort mission
			if ( ! empty( $_posts ) && $virtual_page_enabled == 'no' ) {
				return $_posts;
			}

			try {

				// get post ID
                $post_ID = !empty($_POST['srp']) ? intval($_POST['srp']) : (!empty($_GET['srp']) ? intval($_GET['srp']) : 0);

                // does a post with that ID exist
                $target_post = get_post($post_ID);
                if ( ( $post_ID > 0 ) && ! is_object($target_post) ) {
                    return $_posts;
                }

				// vars
                $action = !empty($_POST['sra']) ? $_POST['sra'] : (!empty($_GET['sra']) ? $_GET['sra'] : 0);
                $key = !empty($_POST['srk']) ? $_POST['srk'] : (!empty($_GET['srk']) ? $_GET['srk'] : 0);
                
                $sre = !empty($_POST['sre']) ? $_POST['sre'] : (!empty($_GET['sre']) ? $_GET['sre'] : '');
                if ( is_user_logged_in() ) {
                    $sre = $current_user->data->user_email;
                }

                $srek = !empty($_POST['srek']) ? $_POST['srek'] : (!empty($_GET['srek']) ? $_GET['srek'] : '');
                $link_source = !empty($_POST['srsrc']) ? $_POST['srsrc'] : (!empty($_GET['srsrc']) ? $_GET['srsrc'] : '');
                $key_expired = !empty($_POST['key_expired']) ? $_POST['key_expired'] : (!empty($_GET['key_expired']) ? $_GET['key_expired'] : '0');
				
				// check if the current subscriber has valid email using the $srek key.
				$email_by_key = $this->utils->get_subscriber_email_by_key($srek);
				
                // stop if invalid SRE key
                if ( ! $email_by_key && ! empty( $srek ) ) {

                    $this->utils->stcr_logger("\n [ERROR][$date] - Couldn\'t find an email with the SRE key: ( $srek )\n");
					$email = '';
					
				// valid key, proceed
                } else {

                    if ( ! $email_by_key && empty( $sre ) ) {
                        $email = '';
                    } else if ( $email_by_key && ! empty( $email_by_key ) ) {
                        $email = $email_by_key;
                    } else if ( ! empty( $sre ) ) {
                        $email = $this->utils->check_valid_email( $sre );
                    } else {
                        $email = '';
					}
					
				}
				
                // comes from the comment form.
                if ($link_source == 'f') {
					
					// Check for a valid SRK key, until this point we know the email is correct but the $key has expired/change
                    // or is wrong, in that case display the request management page template
                    if ($email !== "" && $key !== 0 && $stcr_unique_key !== $key || $key_expired == "1") {
                        if ($key_expired == "1") {
                            $error_exits = true;
                        } else {
                            $this->utils->stcr_logger("\n [ERROR][$date] - Couldn\'t find a valid SRK key with the email ( $email_by_key ) and the SRK key: ( $key )\n This is the current unique key: ( $stcr_unique_key )\n");
                            $error_exits = true;
                        }
					}
					
				// comes from email link
                } else if ($link_source == 'e') {

                    if ($email !== "" && $key !== 0 && !$this->utils->_is_valid_key($key, $email) || $key_expired == "1") {
                        if ($key_expired == "1") {
                            $error_exits = true;
                        } else {
                            $this->utils->stcr_logger("\n [ERROR][$date] - Couldn\'t find a valid SRK key with the email ( $email_by_key ) and the SRK key: ( $key )\n This is the current unique key: ( $stcr_unique_key )\n");
                            $error_exits = true;
                        }
					}
					
                }

				// error found, show message
                if ($error_exits) {
					$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/key_expired.php';
					
				// all fine, proceed
                } else {

                    // subscribe without commenting
                    if (
						!empty($action) &&
                        ($action == 's') &&
                        ($post_ID > 0) &&
                        $key_expired != "1"
                    ) {
						
						$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/subscribe.php';
						
					// post author
					} elseif (
						($post_ID > 0) &&
                        $this->is_author($target_post->post_author)
                    ) {

						$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/author.php';
						
					// confirm subscription
                    } elseif (
						($post_ID > 0) &&
                        !empty($email) &&
                        !empty($key) &&
                        !empty($action) &&
                        $this->utils->_is_valid_key($key, $email) &&
                        $this->is_user_subscribed($post_ID, $email, 'C') &&
                        ($action == 'c') &&
                        $key_expired != "1"
                    ) {

						$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/confirm.php';
						
					// unsubscribe
                    } elseif (
						($post_ID > 0) &&
                        !empty($email) &&
                        !empty($key) &&
                        !empty($action) &&
                        $this->utils->_is_valid_key($key, $email) &&
                        ($action == 'u') &&
                        $key_expired != "1"
                    ) {
						
						$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/one-click-unsubscribe.php';

					// user management page
                    } elseif (
						!empty($email) &&
                        ($key !== 0 && $this->utils->_is_valid_key($key, $email) || (!empty($current_user->data->user_email) && ($current_user->data->user_email === $email && current_user_can('read'))))
                    ) {
						
						$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/user.php';
					
					// wrong request
                    } elseif (
						!empty($email) &&
                        ($key === 0 && (!empty($current_user->data->user_email) && ($current_user->data->user_email !== $email)))
                    ) {
						
						$include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/wrong-request.php';

                    }

					// request management link
                    if (empty($include_post_content)) {
                        $include_post_content = include WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/templates/request-management-link.php';
					}
					
				}

                global $wp_query;

				// management page title
                $manager_page_title = html_entity_decode(get_option('subscribe_reloaded_manager_page_title', 'Manage subscriptions'), ENT_QUOTES, 'UTF-8');
                if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
                    $manager_page_title = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($manager_page_title);
                } else {
                    $manager_page_title = $manager_page_title;
                }

				// fake posts
                $posts[] =
                    (object)array(
                        'ID'                    => '9999999',
                        'post_autToggle hor'    => '1',
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
                        'guid'                  => get_bloginfo('url') . '/?page_id=9999999',
                        'post_name'             => get_bloginfo('url') . '/?page_id=9999999',
                        'ancestors'             => array()
                    );

                // Make WP believe this is a real page, with no comments attached
                $wp_query->is_page = true;
                $wp_query->is_single = false;
                $wp_query->is_home = false;
                $wp_query->comments = false;

                // Discard 404 errors thrown by other checks
                unset($wp_query->query["error"]);
                $wp_query->query_vars["error"] = "";
                $wp_query->is_404 = false;

                // Seems like WP adds its own HTML formatting code to the content, we don't need that here
				remove_filter('the_content', 'wpautop');
				
                // Look like the plugin is call twice and therefor subscribe to the "the_posts" filter again so we need to
                // tell to WordPress to not register again.
                remove_filter("the_posts", array($this, "subscribe_reloaded_manage"));
                add_action('wp_head', array($this, 'add_custom_header_meta'));

			// log the error
            } catch(\Exception $ex) {

                $this->utils->stcr_logger( "\n [ERROR][$date] - $ex->getMessage()\n" );
				$this->utils->stcr_logger( "\n [ERROR][$date] - $ex->getTraceAsString()\n" );
				
			}

			// return filtered posts
			return $posts;

		}
		
		/**
		 * Checks if current logged in user is the author
		 * 
		 * @since 190705 cleanup
		 */
		public function is_author( $_post_author ) {

			global $current_user;
			return ! empty( $current_user ) && ( ( $_post_author == $current_user->ID ) || current_user_can( 'manage_options' ) );

		}

		/**
		 * Checks if a given email address is subscribed to a post
		 * 
		 * @since 190705 cleanup
		 */
		public function is_user_subscribed( $_post_ID = 0, $_email = '', $_status = '' ) {

			global $current_user;

			// return, no info about email available
			if ( ( empty( $current_user->user_email ) && empty( $_COOKIE['comment_author_email_' . COOKIEHASH] ) && empty( $_email ) ) || empty( $_post_ID ) ) {
				return false;
			}

			$operator = ( $_status != '' ) ? 'equals' : 'contains';

			// get subscriptions
			$subscriptions = $this->get_subscriptions(
				array(
					'post_id',
					'status'
				), 
				array(
					'equals',
					$operator
				), array(
					$_post_ID,
					$_status
				)
            );

			// if email not supplied, tried to get it
			if ( empty( $_email ) ) {
				$user_email = ! empty( $current_user->user_email ) ? $current_user->user_email : ( ! empty( $_COOKIE['comment_author_email_' . COOKIEHASH] ) ? stripslashes( esc_attr( $_COOKIE['comment_author_email_' . COOKIEHASH] ) ) : '#undefined#' );

			// if supplied, use it
			} else {
				$user_email = $_email;
			}

			// go through all subscriptions and return true if the email is found
			foreach ( $subscriptions as $a_subscription ) {
				if ( $user_email == $a_subscription->email ) {
					return true;
				}
			}

			// return false, the email is not subscribed
			return false;

		}
		
		/**
		 * Adds a new subscription
		 * 
		 * @since 190705 cleanup
		 */
		public function add_subscription( $_post_id = 0, $_email = '', $_status = 'Y' ) {

			global $wpdb;

			// does the post exist?
			$target_post = get_post( $_post_id );
			if ( ( $_post_id > 0 ) && ! is_object( $target_post ) ) {
				return;
			}

			// return if status incorrect
			if ( ! in_array( $_status, array( 'Y', 'YC', 'R', 'RC', 'C', '-C' ) ) || empty( $_status ) ) {
				return;
			}

			// using Wordpress local time
			$dt = date_i18n( 'Y-m-d H:i:s' );

			// sanitize email
			$clean_email = $this->utils->clean_email( $_email );
			
			// insert subscriber into postmeta
			$wpdb->query( $wpdb->prepare(
				"INSERT IGNORE INTO $wpdb->postmeta (post_id, meta_key, meta_value)
				 SELECT %d, %s, %s
				 FROM DUAL
				 WHERE NOT EXISTS (
				 	SELECT post_id
				 	FROM $wpdb->postmeta
				 	WHERE post_id = %d
				 	AND meta_key = %s
				 	LIMIT 0,1
				)", $_post_id, "_stcr@_$clean_email", "$dt|$_status", $_post_id, "_stcr@_$clean_email"
			));

			// Insert user into subscribe_reloaded_subscribers table
			// TODO: Only on this section the user should be added to the subscribers table. On the send confirmation email is repeating this method.
			$OK = $this->utils->add_user_subscriber_table( $clean_email );
			
		}
		
		/**
		 * Deletes one or more subscriptions from the database
		 * 
		 * @since 190705 cleanup
		 */
		public function delete_subscriptions( $_post_id = 0, $_email = '' ) {

			// related to wp 5.5 delete_post coming with 2nd parameter of WP Post object
			if ( is_object( $_email ) ) {
				$_email = '';
			}

			global $wpdb;

			$has_subscriptions = false;

			// no post ID supplied, return 0
			if ( empty( $_post_id ) ) {
				return 0;
			}

			// generate search for the DB query
			$posts_where = '';
			if ( ! is_array( $_post_id ) ) {
				$posts_where = "post_id = " . intval( $_post_id );
			} else {
				foreach ( $_post_id as $a_post_id ) {
					$posts_where .= "post_id = '" . intval( $a_post_id ) . "' OR ";
				}
				$posts_where = substr( $posts_where, 0, - 4 );
			}

			// if email supplied, add it to the search for the DB query
			if ( ! empty( $_email ) ) {
				$emails_where = '';
				if ( ! is_array( $_email ) ) {
					$emails_where = "meta_key = '_stcr@_" . $this->utils->clean_email( $_email ) . "'";
					$has_subscriptions = $this->retrieve_user_subscriptions( $_post_id, $_email );
					if ( $has_subscriptions === false) {
						$this->utils->remove_user_subscriber_table( $_email );
					}
				} else {
					foreach ( $_email as $a_email ) {
						$emails_where .= "meta_key = '_stcr@_" . $this->utils->clean_email( $a_email ) . "' OR ";
						// Deletion on every email on the subscribers table.
						$has_subscriptions = $this->retrieve_user_subscriptions( $_post_id, $a_email );
						if ( $has_subscriptions === false ) {
							$this->utils->remove_user_subscriber_table( $a_email );
						}
					}

					$emails_where = substr( $emails_where, 0, - 4 );
				}

				// remove subscription from DB
				return $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE ($posts_where) AND ($emails_where)" );

			} else {

				// remove all subscriptions for specific post from DB
				return $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '\_stcr@\_%' AND ($posts_where)" );

			}

		}
		
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

			// single post
			if( ! is_array( $_post_id ) ){

				if ( ! $in ) {
					$retrieve_subscriptions = "SELECT * FROM $wpdb->postmeta WHERE post_id <> %d AND meta_key = %s";
				} else if ( $in ) {
					$retrieve_subscriptions = "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s";
				}

				$result = $wpdb->get_results( $wpdb->prepare( $retrieve_subscriptions, $_post_id, $meta_key.$_email ), OBJECT );

			// array of posts
			} else {
				
				$in_values = implode( ',', $_post_id );

				if ( ! $in ) {
					$retrieve_subscriptions = "SELECT * FROM $wpdb->postmeta WHERE post_id NOT IN ($in_values) AND meta_key = %s";
				} else if ( $in ) {
					$retrieve_subscriptions = "SELECT * FROM $wpdb->postmeta WHERE post_id IN ($in_values) AND meta_key = %s";
				}

				$result = $wpdb->get_results($wpdb->prepare( $retrieve_subscriptions, $meta_key.$_email ), OBJECT);

			}

			return $result === false || $result == 0 || empty( $result ) ? false : $result;

		}

		/**
		 * Updates the status of an existing subscription
		 * 
		 * @since 190705 cleanup
		 */
		public function update_subscription_status( $_post_id = 0, $_email = '', $_new_status = 'C' ) {

			global $wpdb;

			// if not a valid status, return
			if ( empty( $_new_status ) || ! in_array( $_new_status, array( 'Y', 'R', 'C', '-C' ) ) || empty( $_email ) ) {
				return 0;
			}

			// specific post ID supplied
			if ( ! empty( $_post_id ) ) {

				// generate the WHERE for post ID for the DB query
				$posts_where = '';
				if ( ! is_array( $_post_id ) ) {
					$posts_where = "post_id = " . intval( $_post_id );
				} else {
					foreach ( $_post_id as $a_post_id ) {
						$posts_where .= "post_id = '" . intval( $a_post_id ) . "' OR ";
					}
					$posts_where = substr( $posts_where, 0, - 4 );
				}
			
			// all posts
			} else {
				$posts_where = '1=1';
			}

			// generate WHERE for email for the DB query
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

			// update DB
			return $wpdb->query(
				"UPDATE $wpdb->postmeta
				 SET meta_value = CONCAT(SUBSTRING(meta_value, 1, $meta_length), '$new_status')
				 WHERE ($posts_where) AND ($emails_where)"
			);

		}

		/**
		 * Updates the email address of an existing subscription
		 * 
		 * @since 190705 cleanup
		 */
		public function update_subscription_email( $_post_id = 0, $_email = '', $_new_email = '' ) {

			global $wpdb;

			// return if no email supplied
			if ( empty( $_email ) || empty( $_new_email ) || strpos( $_new_email, '@' ) == 0 ) {
				return;
			}

			// sanitize old and new email
			$clean_values[] = "_stcr@_" . $this->utils->clean_email( $_new_email );
			$clean_values[] = "_stcr@_" . $this->utils->clean_email( $_email );

			// generate WHERE for DB query
			$post_where = '';
			if ( ! empty( $_post_id ) ) {
				$post_where = ' AND post_id = %d';
				$clean_values[] = $_post_id;
			}

			// update the email in postmeta table
			$rowsAffected = $wpdb->query( $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_key = %s  WHERE meta_key = %s $post_where", $clean_values ) );

			// update the email in subscribe_reloaded_subscribers table
			if ( $rowsAffected > 0 || $rowsAffected !== false) {
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
		
		/**
		 * Retrieves a list of emails subscribed to a specific post
		 * 
		 * @since 190705 cleanup
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
						"SELECT pm.meta_id, REPLACE(pm.meta_key, '_stcr@_', '') AS email, pm.post_id, SUBSTRING(pm.meta_value, 1, 19) AS dt, SUBSTRING(pm.meta_value, 21) AS status, srs.subscriber_unique_id AS email_key
						 FROM $wpdb->postmeta pm
						 INNER JOIN {$wpdb->prefix}subscribe_reloaded_subscribers srs ON ( REPLACE(pm.meta_key, '_stcr@_', '') = srs.subscriber_email  )
						 WHERE pm.meta_key LIKE %s
						 AND pm.meta_value LIKE '%%R'
						 AND pm.post_id = %d", $parent_comment_author_email, $comment_post_id
					), OBJECT
				);

			} else {

				// generate WHERE for the DB query
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

				// generated ORDER BY for the DB query
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

				// this is the 'official' way to have an offset without a limit
				$row_count = ( $_limit_results <= 0 ) ? '18446744073709551610' : $_limit_results;

				// run the DB query
				return $wpdb->get_results(
					$wpdb->prepare(
						"SELECT meta_id, REPLACE(meta_key, '_stcr@_', '') AS email, post_id, SUBSTRING(meta_value, 1, 19) AS dt, SUBSTRING(meta_value, 21) AS status, srs.subscriber_unique_id AS email_key
						 FROM $wpdb->postmeta
						 INNER JOIN {$wpdb->prefix}subscribe_reloaded_subscribers srs ON ( REPLACE(meta_key, '_stcr@_', '') = srs.subscriber_email  )
						 WHERE meta_key LIKE '\_stcr@\_%%' $where_clause
						 ORDER BY $order_by $order
						 LIMIT $_offset,$row_count", $where_values
					), OBJECT
				);

			}

		}
		
		/**
		 * Sends the notification message to a given user
		 * 
		 * @since 190705 cleanup
		 */
		public function notify_user( $_post_ID = 0, $_email = '', $_comment_ID = 0 ) {
			
			// vars
			$post                    = get_post( $_post_ID );
			$comment                 = get_comment( $_comment_ID );
			$post_permalink          = get_permalink( $_post_ID );
			$comment_permalink       = get_comment_link( $_comment_ID );
			$comment_reply_permalink = get_permalink( $_post_ID ) . '?replytocom=' . $_comment_ID . '#respond';
            $info                    = $this->_get_comment_object( $_comment_ID );
			
			// WPML compatibility
			if ( defined('ICL_SITEPRESS_VERSION') && defined('ICL_LANGUAGE_CODE') ) {
				global $sitepress;
				$language = $sitepress->get_language_for_element( $_post_ID, 'post_' . $post->post_type );
				$sitepress->switch_lang($language);
			}

			// vars
			$subject      = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_notification_subject', 'There is a new comment on the post [post_title]' ) ), ENT_QUOTES, 'UTF-8' );
			$message      = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_notification_content', '' ) ), ENT_QUOTES, 'UTF-8' );
			$manager_link = get_bloginfo( 'url' ) . get_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/' );
			$one_click_unsubscribe_link = $manager_link;

			// qTranslate compatibility
			if ( function_exists( 'qtrans_convertURL' ) ) {
				$manager_link = qtrans_convertURL( $manager_link );
			}

			// vars
			$clean_email     = $this->utils->clean_email( $_email );
			$subscriber_salt = $this->utils->generate_temp_key( $clean_email );
			$manager_link .= ( ( strpos( $manager_link, '?' ) !== false ) ? '&' : '?' )
				. "srek=" . $this->utils->get_subscriber_key( $clean_email )
				. "&srk=$subscriber_salt";
			$one_click_unsubscribe_link .= ( ( strpos( $one_click_unsubscribe_link, '?' ) !== false ) ? '&' : '?' )
				. "srek=" . $this->utils->get_subscriber_key( $clean_email ) . "&srk=$subscriber_salt"
				. "&sra=u&srsrc=e" . "&srp=" . $_post_ID;
			$comment_content = $comment->comment_content;

			// replace tags with their actual values
			$subject = str_replace( '[post_title]', $post->post_title, $subject );
			$subject = str_replace( '[comment_author]', $comment->comment_author, $subject );
			$subject = str_replace( '[blog_name]' , get_bloginfo('name'), $subject );
			$message = str_replace( '[post_permalink]', $post_permalink, $message );
			$message = str_replace( '[comment_permalink]', $comment_permalink, $message );
			$message = str_replace( '[comment_reply_permalink]', $comment_reply_permalink, $message );
			$message = str_replace( '[comment_author]', $comment->comment_author, $message );
			$message = str_replace( '[comment_content]', $comment_content, $message );
			$message = str_replace( '[manager_link]', $manager_link, $message );
			$message = str_replace( '[oneclick_link]', $one_click_unsubscribe_link, $message );
			$message = str_replace( '[comment_gravatar]', get_avatar($info->comment_author_email, 40), $message );
			$message = str_replace( '[comment_date]',  date( get_option( 'date_format'), strtotime( $comment->comment_date ) ), $message );
			$message = str_replace( '[comment_time]',  date( get_option( 'time_format'), strtotime( $comment->comment_date ) ), $message );

			// qTranslate support
			if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
				$subject = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $subject );
				$message = str_replace( '[post_title]', qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $post->post_title ), $message );
				$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
			} else {
				$message = str_replace( '[post_title]', $post->post_title, $message );
			}

			$message = apply_filters( 'stcr_notify_user_message', $message, $_post_ID, $clean_email, $_comment_ID );

			// email settings
			$email_settings = array(
				'subject'      => $subject,
				'message'      => $message,
				'toEmail'      => $clean_email,
				'XPostId'    => $_post_ID,
				'XCommentId' => $_comment_ID
			);

			// send email
			$this->utils->send_email( $email_settings );

		}
		
		/**
		 * Displays the checkbox to allow visitors to subscribe
		 * 
		 * @since 190705 cleanup
		 */
		function subscribe_reloaded_show( $submit_field = '' ) {

			// echo on action, return on filter
			$echo = false;
			if ( doing_action( 'comment_form' ) || doing_action( 'comment_form_must_log_in_after' ) ) {
				$echo = true;
            }

			// vars
			global $post, $wp_subscribe_reloaded;
			$checkbox_subscription_type = null;
            $_comment_ID = null;
            $post_permalink = get_permalink( $post->ID );
			$post_permalink = "post_permalink=" . $post_permalink;
			$post_type = get_post_type( $post->ID );
			$only_for_posts = get_option( 'subscribe_reloaded_only_for_posts', 'no' );
			$only_for_logged_in = get_option( 'subscribe_reloaded_only_for_logged_in', 'no' );

			// if not enabled for this post type, return default
			if ( $only_for_posts == 'yes' && $post_type !== 'post' ) {
				if ( $echo ) {
					echo $submit_field;
				} else {
					return $submit_field;
				}
				return;
			}

			// only for logged in users
			if ( $only_for_logged_in == 'yes' && ! is_user_logged_in() ) {
				if ( $echo ) {
					echo $submit_field;
				} else {
					return $submit_field;
				}
				return;
			}

			// enqueue scripts and styles
			$wp_subscribe_reloaded->stcr->utils->add_plugin_js_scripts();
			wp_enqueue_style( 'stcr-plugin-style' );

			// return if subscriptions disabled for this post
			$is_disabled = get_post_meta( $post->ID, 'stcr_disable_subscriptions', true );
			if ( ! empty( $is_disabled ) ) {
				return $_comment_ID;
			}

			// vars
			$show_subscription_box = true;
			$html_to_show          = '';
			$user_link             = get_bloginfo( 'url' ) . get_option( 'subscribe_reloaded_manager_page', '' );

			// qTranslate compatibility
			if ( function_exists( 'qtrans_convertURL' ) ) {
				$user_link = qtrans_convertURL( $user_link );
			}

			// link for management page
			$manager_link = ( strpos( $user_link, '?' ) !== false ) ?
				"$user_link&amp;srp=$post->ID&amp;srk=" . get_option( 'subscribe_reloaded_unique_key' ) :
				"$user_link?srp=$post->ID&amp;srk=" . get_option( 'subscribe_reloaded_unique_key' );

			// link for user
            $user_link = ( strpos( $user_link, '?' ) !== false ) ?
                "$user_link&" . $post_permalink :
                "$user_link?" . $post_permalink;

			// if subscription pending confirmation
			if ( $wp_subscribe_reloaded->stcr->is_user_subscribed( $post->ID, '', 'C' ) ) {
				$html_to_show = str_replace(
					'[manager_link]', $user_link,
					html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_subscribed_waiting_label', __( "Your subscription to this post needs to be confirmed. <a href='[manager_link]'>Manage your subscriptions</a>.", 'subscribe-to-comments-reloaded' ) ) ), ENT_QUOTES, 'UTF-8' )
				);
				$show_subscription_box = false;

			// if subscription active
			} elseif ( $wp_subscribe_reloaded->stcr->is_user_subscribed( $post->ID, '' ) ) {
				$html_to_show = str_replace(
					'[manager_link]', $user_link ,
					html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_subscribed_label', __( "You are subscribed to this post. <a href='[manager_link]'>Manage</a> your subscriptions.", 'subscribe-to-comments-reloaded' ) ) ), ENT_QUOTES, 'UTF-8' )
				);
				$show_subscription_box = false;
			}

			// if current user is author of the post
			if ( $wp_subscribe_reloaded->stcr->is_author( $post->post_author ) ) {
				if ( get_option( 'subscribe_reloaded_admin_subscribe', 'no' ) == 'no' ) {
					$show_subscription_box = false;
				}
				$html_to_show .= ' ';
				$html_to_show .= str_replace(
					'[manager_link]', $manager_link,
					html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_author_label', __( "You can <a href='[manager_link]'>manage the subscriptions</a> of this post.", 'subscribe-to-comments-reloaded' ) ) ), ENT_QUOTES, 'UTF-8' )
				);
			}

			// show the subscription form
			if ( $show_subscription_box ) {

				// label
				$checkbox_label = str_replace(
					'[subscribe_link]', "$manager_link&amp;sra=s&amp;srsrc=f",
					html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_checkbox_label', __( "Notify me of followup comments via e-mail. You can also <a href='[subscribe_link]'>subscribe</a> without commenting.", 'subscribe-to-comments-reloaded' ) ) ), ENT_QUOTES, 'UTF-8' )
				);

				// CSS style
				$checkbox_inline_style = get_option( 'subscribe_reloaded_checkbox_inline_style', 'width:30px' );
				if ( ! empty( $checkbox_inline_style ) ) {
					$checkbox_inline_style = " style='$checkbox_inline_style'";
				}

				$checkbox_html_wrap = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_checkbox_html', '' ) ), ENT_QUOTES, 'UTF-8' );

				// regular subscriptions form
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

				// advanced subscriptions form
				} else {
					$checkbox_field = "<select name='subscribe-reloaded' id='subscribe-reloaded'>
								<option value='none' " . ( ( get_option( 'subscribe_reloaded_default_subscription_type' ) === '0' ) ? "selected='selected'" : '' ) . ">" . __( "Don't subscribe", 'subscribe-to-comments-reloaded' ) . "</option>
								<option value='yes' " . ( ( get_option( 'subscribe_reloaded_default_subscription_type' ) === '1' ) ? "selected='selected'" : '' ) . ">" . __( "All", 'subscribe-to-comments-reloaded' ) . "</option>
								<option value='replies' " . ( ( get_option( 'subscribe_reloaded_default_subscription_type' ) === '2' ) ? "selected='selected'" : '' ) . ">" . __( "Replies to my comments", 'subscribe-to-comments-reloaded' ) . "</option>
							</select>";
				}

				if ( empty( $checkbox_html_wrap ) ) {
					$html_to_show = "$checkbox_field <label for='subscribe-reloaded'>$checkbox_label</label>" . $html_to_show;
				} else {
					$checkbox_html_wrap = str_replace( '[checkbox_field]', $checkbox_field, $checkbox_html_wrap );
					$html_to_show       = str_replace( '[checkbox_label]', $checkbox_label, $checkbox_html_wrap ) . $html_to_show;
				}

			}

			// qTranslate compatiblity
			if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
				$html_to_show = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $html_to_show );
			}

			$output = '';
			// Check for the Comment Form location
			if( get_option('subscribe_reloaded_stcr_position') == 'yes' ) {
				$output .= "<style type='text/css'>.stcr-hidden{display: none !important;}</style>";
				$output .= "<div class='stcr-form stcr-hidden'>";
                $output .= $html_to_show;
                $output .= "</div>";
            } else {
                $output .= $html_to_show;
			}

			// echo or return
			if ( $echo ) {
				echo $output . $submit_field;
			} else {
				return $output . $submit_field;
			}

		}

		/**
		 * Set a cookie if the user just subscribed without commenting
		 * 
		 * @since 190705
		 */
		public function set_user_cookie() {
			
			$subscribe_to_comments_action  = ! empty( $_POST['sra'] ) ? $_POST['sra'] : ( ! empty( $_GET['sra'] ) ? $_GET['sra'] : 0 );
			$subscribe_to_comments_post_ID = ! empty( $_POST['srp'] ) ? intval( $_POST['srp'] ) : ( ! empty( $_GET['srp'] ) ? intval( $_GET['srp'] ) : 0 );

			if ( ! empty( $subscribe_to_comments_action ) && ! empty( $_POST['subscribe_reloaded_email'] ) &&
				( $subscribe_to_comments_action == 's' ) && ( $subscribe_to_comments_post_ID > 0 )
			) {
                $subscribe_to_comments_clean_email = $this->utils->clean_email( $_POST['subscribe_reloaded_email'] );
                if ( get_option( 'subscribe_reloaded_use_cookies', 'yes' ) == 'yes' ) {
                    setcookie( 'comment_author_email_' . COOKIEHASH, $subscribe_to_comments_clean_email, time() + 1209600, '/' );
                }
			}
		}

        /**
         * Management page shortcode
         *
         * @since 190325
         */
        public function management_page_sc() {

            $data = $this->subscribe_reloaded_manage();
            return $data[0]->post_content;

        }

        /**
         * Add custom output before comment content
         * 
         * @since 190801
         */
        public function comment_content_prepend( $comment_text, $comment = null ) {

			// do not proceed if comment info is not passed
			if ( empty( $comment ) || ! isset( $comment->comment_approved ) ) {
				return $comment_text;
			}

            global $wp_subscribe_reloaded;
            global $post;

            $prepend = '';            

            // comment held for moderation and email is subscribed to the post
            if ( $comment->comment_approved == '0' && $wp_subscribe_reloaded->stcr->is_user_subscribed( $post->ID, $comment->comment_author_email, 'C' ) ) {
                $prepend = '<p><em>' . __( 'Check your email to confirm your subscription.', 'subscribe-to-comments-reloaded' ) . '</em></p>';
            }

            // pass it back
            return $prepend . $comment_text;

		}
		
		/**
		 * Move form with JS
		 * 
		 * @since 200626
		 */
		public function move_form_with_js() {

			$output = '';

			if ( get_option('subscribe_reloaded_stcr_position') == 'yes' ) {
				$output .= '<script type="text/javascript">document.addEventListener("DOMContentLoaded",function(){if(document.querySelectorAll("div.stcr-form").length){let e=document.querySelectorAll("div.stcr-form")[0],t=document.querySelectorAll("#commentform input[type=submit]")[0];t.parentNode.insertBefore(e,t),e.classList.remove("stcr-hidden")}});</script>';
			}
			
			echo $output;

		}

	}

}