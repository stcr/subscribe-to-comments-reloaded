/**
 * Script to handle all the Admin actions.
 */
( function($){
	$(document).ready(function() {
		/**
		 * Handle the notification dismiss action
		 * @author reedyseth
		 * @since 25-August-2015
		 */
		$('.stcr-dismiss-notice').on('click','a.dismiss', function() {
			var spinner = $( '' );
			var nonce = $(this).parent().parent().data('nonce');
			nonce = nonce.split('|');
			var data = {
				action: nonce[1],
				security: nonce[0]
			};
			_this =  $( this ).parent().parent();
			// Make the Ajax request.
			$.ajax({
				type: "post",
				url: ajaxurl,
				data: data,
				beforeSend: function() {
					_this.find(".stcr-loading-animation").removeClass("stcr-loading-information").show();
				},
				success: function ( response ){
					if ( response.success === true ) {
						_this.slideUp( 'fast' );
					}
					//console.debug('Notice dismissed, server response >> ' + response );
				},
				error: function( error ) {
					//console.debug( error );
				},
				complete: function() {
					_this.find(".stcr-loading-animation").hide();
				}
			}); //close jQuery.ajax
		});

        /**
         * Control the execution of the options restore process.
         * @author reedyseth
         * @since 08-February-2018
         */
		$('input.reset_all_options').on("click", function ( event ) {
			var confirmation = confirm("If you proceed this action cannot be undone, all settings will be wipe out");

			return confirmation;
        });
	});
} )( jQuery );
