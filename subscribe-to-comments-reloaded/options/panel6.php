<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}
?>
<h3><?php _e( 'Support the developers', 'subscribe-reloaded' ) ?></h3>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
	">
	<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypalobjects.com/es_XC/i/scr/pixel.gif" width="1" height="1">
</form>
<hr>

<p><?php _e( 'How valuable is the feature offered by this plugin to your visitors? Subscribe to Comments Reloaded is and will always be free, but consider supporting the author if this plugin made your web site better, especially if you are making money out of it. Any donation received will be reinvested in the development of Subscribe to Comments Reloaded, and to buy some food for my hungry family.', 'subscribe-reloaded' ) ?></p>

<h3><?php _e( "Don't want to donate? You can still help", 'subscribe-reloaded' ) ?></h3>
<p><?php _e( "If you don't want to donate money, please consider blogging about my plugin with a link to the plugin's page. Please let your readers know what makes your blog better. You can also contribute donating your time: do not hesitate to send me bug reports, your localization files, ideas on how to improve Subscribe to Comments Reloaded and so on. Whatever you do, thanks for using my plugin!", 'subscribe-reloaded' ) ?></p>

<h3><?php _e( "Vote and show your appreciation", 'subscribe-reloaded' ) ?></h3>
<p><?php _e( 'Tell other people if Subscribe to Comments Reloaded works for you and how good it is. <a href="http://wordpress.org/extend/plugins/subscribe-to-comments-reloaded/">Rate it</a> on its Plugin Directory page.', 'subscribe-reloaded' ) ?></p>