( function($){
    $(document).ready(function(){

        var emailRegex   = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        var oldsre_input = $("form#mass_update_address_form input[name='oldsre']");
        var sre_input    = $("form#mass_update_address_form input[name='sre']");


        oldsre_input.focus(function(){
            if (oldsre_input.val() == "<?php _e( 'email address', 'subscribe-reloaded' ) ?>")
            {
                oldsre_input.val("");
            }
            oldsre_input.css("color","#000");
        });

        oldsre_input.blur(function(){
            if (oldsre_input.val() == "")
            {
                oldsre_input.val("<?php _e( 'email address', 'subscribe-reloaded' ) ?>");
                oldsre_input.css("color","#ccc");
            }
        });

        sre_input.focus(function(){
            if (sre_input.val() == "<?php _e( 'optional - new email address', 'subscribe-reloaded' ) ?>")
            {
                sre_input.val("");
            }
            sre_input.css("color","#000");
        });

        sre_input.blur(function(){
            if (sre_input.val() == "")
            {
                sre_input.val("<?php _e( 'optional - new email address', 'subscribe-reloaded' ) ?>");
                sre_input.css("color","#ccc");
            }
        });

        $("form#mass_update_address_form").submit(function(){
            var old_email      = $.trim( $("form#mass_update_address_form input[name='oldsre']").val() );
            var email          = $.trim( $("form#mass_update_address_form input[name='sre']").val() );
            var missing_fields = [];

            if( old_email == "<?php _e( 'email address', 'subscribe-reloaded' ) ?>" || old_email == "")
            {
                missing_fields.push(
                    {
                        message: "<?php _e( 'Missing information', 'subscribe-reloaded' ) ?>",
                        field: "oldsre"
                    } );
            }
            else if( ! emailRegex.test(old_email) ) // check valid email
            {
                missing_fields.push(
                    {
                        message: "<?php _e( 'Invalid email address.', 'subscribe-reloaded' ) ?>",
                        field: "oldsre"
                    } );
            }

            var missing_fields_size = missing_fields.length;

            if( missing_fields_size > 0 )
            {

                for( var i = 0; i < missing_fields_size; i++ )
                {
                    var field_obj = missing_fields[i];
                    $("form#mass_update_address_form .validate-error-text-" + field_obj.field).text(field_obj.message).show();
                    $("form#mass_update_address_form input[name='"+ field_obj.field +"']").addClass("validate-error-field");
                }

                return false;
            }
            else
            {
                var answer = confirm('Please remember: this operation cannot be undone. Are you sure you want to proceed?');
                // var answer = confirm('<?php _e( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded' ) ?>');
                if( ! answer )
                {
                    return false;
                }
            }


        });
        // Add New Subscription
        var stcr_post_id_input = $("form#add_new_subscription input[name='srp']");
        var sre_input          = $("form#add_new_subscription input[name='sre']");

        stcr_post_id_input.blur(function(){
            if( $.isNumeric(stcr_post_id_input.val() ) ) // check numeric value
            {
                $(this).removeClass("validate-error-field");
                $("form#add_new_subscription .validate-error-text-srp").hide();
            }
        });

        sre_input.blur(function(){
            if( emailRegex.test(sre_input.val() ) ) // check email value
            {
                $(this).removeClass("validate-error-field");
                $("form#add_new_subscription .validate-error-text-sre").hide();
            }
        });

        $("form#add_new_subscription").submit(function(){
            var post_id        = $.trim(stcr_post_id_input.val());
            var email          = $.trim(sre_input.val());
            var missing_fields = [];

            if( post_id == "")
            {
                missing_fields.push(
                    {
                        message: "<?php _e( 'Missing information', 'subscribe-reloaded' ) ?>",
                        field: "srp"
                    } );
            }
            else if( ! $.isNumeric(post_id) ) // check numeric value
            {
                missing_fields.push(
                    {
                        message: "<?php _e( 'Enter a numeric Post ID.', 'subscribe-reloaded' ) ?>",
                        field: "srp"
                    } );
            }

            if( email == "")
            {
                missing_fields.push(
                    {
                        message: "<?php _e( 'Missing email information', 'subscribe-reloaded' ) ?>",
                        field: "sre"
                    } );
            }
            else if( ! emailRegex.test(email) ) // check valid email
            {
                missing_fields.push(
                    {
                        message: "<?php _e( 'Invalid email address.', 'subscribe-reloaded' ) ?>",
                        field: "sre"
                    } );
            }

            var missing_fields_size = missing_fields.length;

            if( missing_fields_size > 0 )
            {

                for( var i = 0; i < missing_fields_size; i++ )
                {
                    var field_obj = missing_fields[i];
                    $("form#add_new_subscription .validate-error-text-" + field_obj.field).text(field_obj.message).show();
                    $("form#add_new_subscription input[name='"+ field_obj.field +"']").addClass("validate-error-field");
                }

                return false;
            }
        });

        var search_input = $("form#search_subscriptions_form input[name='srv']");

        $("form#search_subscriptions_form").submit(function(){
            var search_value = $.trim(search_input.val());

            if( search_value == "")
            {
                search_input.val("<?php _e( 'Please enter a value', 'subscribe-reloaded' ) ?>");
                search_input.addClass("validate-error-field");

                return false;
            }
        });

        search_input.focus(function(){
            if( search_input.val() == "<?php _e( 'Please enter a value', 'subscribe-reloaded' ) ?>" )
            {
                search_input.val("");
            }
        });

        search_input.blur(function(){
            if( $.trim(search_input.val() ) != "" )
            {
                $(this).removeClass("validate-error-field");
            }
        });
    });

    // More info action
    $('div.more-info').on("click", function( event ) {
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

//        console.log( $("table.subscribers-table").html() );
    $("table.subscribers-table").DataTable( {
        columns: [ { sortable: false }, null, null, null, null],
        dom: '<"float-left"f><"float-right"l>t<ip>',
        responsive: {
            details: true,
            type: 'column'
        },
        columnDefs: [
            { responsivePriority: 1, targets: 0 },
            { responsivePriority: 2, targets: 3 },
            { responsivePriority: 3, targets: 4 }

        ]
    });
    // card-body
    var massUpdateSubsCollapse      = $('.mass-update-subs .fa-caret-down'),
        massUpdateSubsCollapseState = true,
        addNewSubsCollapse          = $('.add-new-subs .fa-caret-down'),
        addNewSubsCollapseState     = true;

    $('.mass-update-subs h6').on('click', function () {

        if ( massUpdateSubsCollapseState )
        {
            $(this).parent().find(".card-text").removeClass("stcr-hidden").addClass("original-card-padding");

            massUpdateSubsCollapse.removeClass("fa-caret-down").addClass("fa-caret-up");
            massUpdateSubsCollapseState = false;
        }
        else if ( ! massUpdateSubsCollapseState)
        {
            $(this).parent().find(".card-text").addClass("stcr-hidden").removeClass("original-card-padding");

            massUpdateSubsCollapse.removeClass("fa-caret-up").addClass("fa-caret-down");
            massUpdateSubsCollapseState = true;
        }
    });

    $('.add-new-subs h6').on('click', function () {

        if ( addNewSubsCollapseState )
        {
            $(this).parent().find(".card-text").removeClass("stcr-hidden").addClass("original-card-padding");

            addNewSubsCollapse.removeClass("fa-caret-down").addClass("fa-caret-up");
            addNewSubsCollapseState = false;
        }
        else if ( ! addNewSubsCollapseState)
        {
            $(this).parent().find(".card-text").addClass("stcr-hidden").removeClass("original-card-padding");

            addNewSubsCollapse.removeClass("fa-caret-up").addClass("fa-caret-down");
            addNewSubsCollapseState = true;
        }
    });

} )( jQuery );