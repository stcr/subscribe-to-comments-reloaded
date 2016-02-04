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
		// More info action
		$('a.more-info').on("click", function( event ) {
			event.preventDefault();
			var info_panel = $( this ).data( "infopanel" );
			info_panel = "." + info_panel;

			$( ".postbox-mass").css("overflow","hidden");

			if( $( info_panel ).hasClass( "hidden") )
			{
				$( info_panel ).slideDown( "fast" );
				$( info_panel).removeClass( "hidden" );
			}
			else
			{
				$( info_panel ).slideUp( "fast" );
				$( info_panel).addClass( "hidden" );
			}
		});
	});
} )( jQuery );
