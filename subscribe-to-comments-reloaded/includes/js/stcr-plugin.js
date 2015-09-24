/**
 * Scripts to handle the plugin behavior.
 *
 * @since 22-Sep-2015
 * @author reedyseth
 */
( function( $ ) {

	sort_subscription_box();

	/**
	 * Move the Subscription box above the submit button
	 * @since 23-Sept-2015
	 * @author reedyseth
	 */
	function sort_subscription_box() {
		var submit_button = $('form input.Cbutton');
		var stcr_form = $('div.stcr-form');
		var stcr_form_html = stcr_form.html();
		stcr_form.remove();
		$( stcr_form_html ).insertBefore( submit_button );
		$('div.stcr-form').removeClass( 'hidden' );
	}
} )( jQuery );