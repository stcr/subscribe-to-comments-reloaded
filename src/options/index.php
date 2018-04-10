<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}
global $wp_subscribe_reloaded;
global $wp_locale;

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

<script type="text/javascript" src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

