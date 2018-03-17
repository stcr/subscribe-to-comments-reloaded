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
	$_value = trim( stripslashes( $_value ) );

	switch ( $_type ) {
		case 'yesno':
			if ( $_value == 'yes' || $_value == 'no' ) {
				update_option( 'subscribe_reloaded_' . $_option, esc_attr( $_value ) );

				return true;
			}
			break;
		case 'integer':
			update_option( 'subscribe_reloaded_' . $_option, abs( intval( esc_attr( $_value ) ) ) );

			return true;
			break;
        case 'text':
            update_option( 'subscribe_reloaded_' . $_option, sanitize_text_field( $_value ) );

            return true;
        case 'text-html':
            update_option( 'subscribe_reloaded_' . $_option, esc_html( $_value ) );

            return true;
        case 'email':
            update_option( 'subscribe_reloaded_' . $_option, sanitize_email( esc_attr( $_value ) ) );

            return true;
        case 'url':
            update_option( 'subscribe_reloaded_' . $_option, esc_url( $_value ) );

            return true;
		default:
			update_option( 'subscribe_reloaded_' . $_option, esc_attr( $_value ) );

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

?>

<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet"/>
<link href="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/includes/css/fontawesome-all.min.css" rel="stylesheet"/>
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

