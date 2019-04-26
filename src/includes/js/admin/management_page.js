/**
 * Created by reedyseth on 4/2/18.
 */
( function($){
    $(document).ready(function(){

        var option_manager_page = $("input[name='options[manager_page]']" );
        var missing_fields = [];

        // management_page_form
        $('form.management_page_form').submit(function(event){
            if( option_manager_page.val() === "" )
            {
            	var field = "options[manager_page]";

            	if( ! checkArrayKey( missing_fields, field ) )
            	{
            		missing_fields.push( // TODO: Only push if the array does not contain the key already.
                    {
                        message: "<?php _e( 'Missing information', 'subscribe-to-comments-reloaded' ) ?>",
                        field: field
                    } );
            	}
            }

            var missing_fields_size = missing_fields.length;

            if( missing_fields_size > 0 )
            {

                for( var i = 0; i < missing_fields_size; i++ )
                {
                    var field_obj = missing_fields[i];
                    // TODO: Implement the correct text messages  with internationalization
                    // $("form#add_new_subscription .validate-error-text-" + field_obj.field).text(field_obj.message).show();
                    $("form.management_page_form input[name='"+ field_obj.field +"']").addClass("validate-error-field");
                    $('html, body').animate({scrollTop:0}, 'slow');

                    missing_fields = []; // clean house
                    return false;
                }

            }
        });


        function checkArrayKey( arrayObj, keyToCheck )
		{
			var size = arrayObj.length;

			for(var i = 0; i < size; i++)
			{
				if( arrayObj[i].field == keyToCheck )
				{
					return true;
				}
			}
			return false;
		}
    });
} )( jQuery );