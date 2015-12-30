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

// Load localization files
load_plugin_textdomain( 'subscribe-reloaded', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

// Define the panels
$array_panels = array(
	__( 'Manage subscriptions', 'subscribe-reloaded' ),
	__( 'Comment Form', 'subscribe-reloaded' ),
	__( 'Management Page', 'subscribe-reloaded' ),
	__( 'Notifications', 'subscribe-reloaded' ),
	__( 'Options', 'subscribe-reloaded' ),
	__( 'You can help', 'subscribe-reloaded' ),
	__( 'Support', 'subscribe-reloaded' ),
	__( 'Donate', 'subscribe-reloaded' )
);

// What panel to display
$current_panel = empty( $_GET['subscribepanel'] ) ? 1 : intval( $_GET['subscribepanel'] );
// Check for any notification to mark as read
$notification =  isset( $_GET['n'] )      ? $_GET['n'] : '';
$status       =  isset( $_GET['status'] ) ? $_GET['status'] : '';

if ( ! empty( $notification ) && ! empty( $status ) && ( $status == 'unread' || $status == 'read' ) ) {
	$wp_subscribe_reloaded->stcr->utils->stcr_update_admin_notice_status( $notification, $status  );
}

// Text direction button-primary
if ( $wp_locale->text_direction != 'ltr' ) {
	$array_panels = array_reverse( $array_panels, true );
}

?>
<div class="wrap">
	<div id="subscribe-to-comments-icon" class="icon32 <?php echo $wp_locale->text_direction ?>"></div>
	<h2 class="medium">
		<?php
		foreach ( $array_panels as $a_panel_id => $a_panel_details ) {
			echo '<a class="nav-tab nav-tab';
			echo ( $current_panel == $a_panel_id + 1 ) ? '-active' : '-inactive';
			echo ( $current_panel == $a_panel_id + 1 &&  $a_panel_id + 1 == 8) ? ' donate-tab-active' : '';
			if ( $a_panel_id + 1 == 8){
				echo ' donate-tab ';
			}
			echo '" href="options-general.php?page=subscribe-to-comments-reloaded/options/index.php&subscribepanel=' . ( $a_panel_id + 1 ) . '">' . $a_panel_details . '</a>';
		}
		?>
		<div class="clearFix"></div>
	</h2>

	<?php if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel$current_panel.php" ) ) {
		require_once WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel$current_panel.php";
	} ?>
</div>
