/**
 * Scripts to handle the plugin behavior.
 *
 * @since 22-Sep-2015
 * @author reedyseth
 */
jQuery(document).ready(function($){
	sort_subscription_box();

	/**
	 * Move the Subscription box above the submit button
	 * @since 23-Sept-2015
	 * @author reedyseth
	 */
	function sort_subscription_box() {
		var submit_button = jQuery(':input[type="submit"]');
		var stcr_form = jQuery('div.stcr-form');
		var stcr_form_html = stcr_form.html();


		stcr_form.prevUntil('form').each(function() {
			var $this = $(this); // Cache this.
			if($this.find(':input[type="submit"]').length)
			{
				stcr_form.remove(), $this.before(stcr_form);
				jQuery('div.stcr-form').removeClass( 'hidden' );
				return false; // Break the each() loop.
			}
		});
	}
	/**
	 * Validate the email address.
	 * @since 09-Sep-2016
	 * @author reedyseth
	 */
	jQuery('form[name="sub-form"]').on('submit',function (event) {
		var emailRegex = /^[A-Z0-9._%+-]+@(?:[A-Z0-9-]+\.)+[A-Z]{2,}$/i;
		var email = jQuery('input[name="sre"]');

		if( email.val() !== "email" && email.val() === "" )
		{
			event.preventDefault();
			alert("Please enter your email.!!");
		}
		else if( email.val().match( emailRegex ) === null )
		{
			event.preventDefault();
			alert("Your email address is not valid.!!");
		}
	});
});

