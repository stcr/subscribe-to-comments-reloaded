<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}

function subscribe_reloaded_update_option( $_option = '', $_value = '', $_type = '' ) {
	if ( ! isset( $_value ) ) {
		return true;
	}

	// Prevent XSS/CSRF attacks
	$_value = stripslashes( $_value );
	$_value = esc_attr( $_value ); // esc_attr Will encode all the text.

	switch ( $_type ) {
		case 'yesno':
			if ( $_value == 'yes' || $_value == 'no' ) {
				update_option( 'subscribe_reloaded_' . $_option, $_value );

				return true;
			}
			break;
		case 'integer':
			update_option( 'subscribe_reloaded_' . $_option, abs( intval( $_value ) ) );

			return true;
			break;
		case 'text-html-encode':
			update_option( 'subscribe_reloaded_' . $_option, htmlentities( $_value, ENT_QUOTES, 'UTF-8' ) );

			return true;
			break;
		default:
			update_option( 'subscribe_reloaded_' . $_option, $_value );

			return true;
			break;
	}

	return false;
}

function subscribe_reloaded_get_option( $_option = '', $_default = '' ) {
	$value = get_option( 'subscribe_reloaded_' . $_option, $_default );
	$value = html_entity_decode( stripslashes( $value ), ENT_QUOTES, 'UTF-8' );

	return stripslashes( $value );
}

global $wp_locale;

// Load localization files
load_plugin_textdomain( 'subscribe-reloaded', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

// Define the panels
$array_pages = array(
	"stcr_manage_subscriptions" => __( 'Manage subscriptions', 'subscribe-reloaded' ),
	"stcr_comment_form"         => __( 'Comment Form', 'subscribe-reloaded' ),
	"stcr_management_page"      => __( 'Management Page', 'subscribe-reloaded' ),
	"stcr_notifications"        => __( 'Notifications', 'subscribe-reloaded' ),
	"stcr_options"              => __( 'Options', 'subscribe-reloaded' ),
	// "stcr_subscribers_emails"   => __( 'Subscribers Emails', 'subscribe-reloaded' ),
	"stcr_you_can_help"         => __( 'You can help', 'subscribe-reloaded' ),
	"stcr_support"              => __( 'Support', 'subscribe-reloaded' ),
	"stcr_donate"               => __( 'Donate', 'subscribe-reloaded' )
);

// // Check for any notification to mark as read
// $notification =  isset( $_GET['n'] )      ? $_GET['n'] : '';
// $status       =  isset( $_GET['status'] ) ? $_GET['status'] : '';

// if ( ! empty( $notification ) && ! empty( $status ) && ( $status == 'unread' || $status == 'read' ) ) {
// 	$wp_subscribe_reloaded->stcr->utils->stcr_update_admin_notice_status( $notification, $status  );
// }

$current_page =  isset( $_GET['page'] ) ? $_GET['page'] : '';

// Text direction button-primary
// if ( $wp_locale->text_direction != 'ltr' ) {
// 	$array_pages = array_reverse( $array_pages, true );
// }

?>
<div class="wrap">
	<div id="subscribe-to-comments-icon" class="icon32 <?php echo $wp_locale->text_direction ?>"></div>
	<h2 class="medium">
		<?php
		foreach ( $array_pages as $page => $page_desc ) {
			echo '<a class="nav-tab nav-tab';
			echo ( $current_page == $page ) ? '-active' : '-inactive';
			echo ( $current_page == $page &&  $page == "stcr_donate" ) ? ' donate-tab-active' : '';
			if (  $page == "stcr_donate" ){
				echo ' donate-tab ';
			}
			echo '" href="admin.php?page=' . $page . '">' . $page_desc . '</a>';
		}
		?>
		<div class="clearFix"></div>
	</h2>

	<?php
		// if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel$current_panel.php" ) ) {
		// 	require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel$current_panel.php";
		// }
	?>
</div>
