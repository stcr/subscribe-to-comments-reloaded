/**
 * Created by reedyseth on 4/2/18.
 */
( function($){
    $(document).ready(function(){

        var option_manager_page = $("input[name='options[manager_page]']" ).val();
        var missing_fields = [];

        // management_page_form
        $('form.management_page_form').submit(function(event){
            if( option_manager_page === "" )
            {
                missing_fields.push(
                    {
                        message: "<?php _e( 'Missing information', 'subscribe-reloaded' ) ?>",
                        field: "options[manager_page]"
                    } );
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
                    return false;
                }

            }
        });
    });
} )( jQuery );