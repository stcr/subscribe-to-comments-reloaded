<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}
global $wp_subscribe_reloaded;
global $wp_locale;


//function subscribe_reloaded_update_option( $_option = '', $_value = '', $_type = '' ) {
//
//	if ( ! isset( $_value ) ) {
//		return false;
//	}
//
//	// Prevent XSS/CSRF attacks
//	$_value = trim( stripslashes( $_value ) );
//
//	switch ( $_type ) {
//		case 'yesno':
//			if ( $_value == 'yes' || $_value == 'no' ) {
//				update_option( 'subscribe_reloaded_' . $_option, esc_attr( $_value ) );
//			}
//			break;
//		case 'integer':
//			update_option( 'subscribe_reloaded_' . $_option, abs( intval( esc_attr( $_value ) ) ) );
//
//			break;
//        case 'text':
//            update_option( 'subscribe_reloaded_' . $_option, sanitize_text_field( $_value ) );
//
//            break;
//        case 'text-html':
//            update_option( 'subscribe_reloaded_' . $_option, esc_html( $_value ) );
//
//            break;
//        case 'email':
//            update_option( 'subscribe_reloaded_' . $_option, sanitize_email( esc_attr( $_value ) ) );
//
//            break;
//        case 'url':
//            update_option( 'subscribe_reloaded_' . $_option, esc_url( $_value ) );
//
//            break;
//		default:
//			update_option( 'subscribe_reloaded_' . $_option, esc_attr( $_value ) );
//
//            break;
//	}
//
//	return true;
//}

function subscribe_reloaded_get_option( $_option = '', $_default = '' ) {
	$value = get_option( 'subscribe_reloaded_' . $_option, $_default );
	$value = html_entity_decode( stripslashes( $value ), ENT_QUOTES, 'UTF-8' );

	return stripslashes( $value );
}
?>

<link href="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link href="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/Font-Awesome/web-fonts-with-css/css/fontawesome-all.min.css" rel="stylesheet"/>
<style type="text/css">
    #wpcontent {
        background: #f1f1f1 !important;
        padding-left: 0 !important;
        position: relative;
        /* WordPress Fonts */
        font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
        color: #464646 !important;
    }
    .navbar a { font-size: 1em !important; font-weight: 600; color: #464646 !important;}
</style>

