/**
 * Created by reedyseth on 12/5/18.
 */
( function($){
    $(document).ready(function(){

        var firstGeneration                 = true;
        var download_report_btn             = $('.download_report');
        var spinner                         = $('.generate_spinner');
        var reportPath_input                = $('.reportPath');
        var working_file_path               = $('.file-path');
        var download_report_error_container = $('.download_report_error');
        var generate_system_info_btn        = $('button.generate_system_info');
        var reportData_txt                  = $(".reportData");

        generate_system_info_btn.on("click", function( event ){
            event.preventDefault();

            var _this = $(this);

            if( firstGeneration === true )
            {
                // Get request response
                var reportPath = reportPath_input.val();
                var result =  reportPath.split( "|" );
                var reportResult = result[0];
                var reportValue  = result[1];
                var reportAdditionalInfo  = result[2];
                // check result true|false
                if( reportResult == "true" )// if true add the path to the download link
                {
                    // display download link
                    download_report_btn.prop("href", reportValue + "?name=systemInformation" );
                    download_report_btn.removeClass('stcr-hidden');
                }
                else if( reportResult == "false" ) // if false show a error message with the file permission issue.
                {
                    working_file_path.text(reportAdditionalInfo);
                    // Bootstrap alert box
                    download_report_error_container.removeClass('stcr-hidden');
                }

                firstGeneration = false;
            }
            else if( firstGeneration === false )
            {
                var nonce = _this.data('nonce');
                nonce = nonce.split('|');
                var data = {
                    action: nonce[1],
                    security: nonce[0],
                    fileName: "systemInformation.txt",
                    fileData: "" + reportData_txt.val()
                };

                // Make the Ajax request.
                $.ajax({
                    type: "post",
                    url: ajaxurl,
                    data: data,
                    beforeSend: function() {
                        spinner.removeClass('stcr-hidden');
                    },
                    success: function ( response ){

                        console.debug( response );
                        if ( response.success === true ) {
                            // check result true|false
                            if( response.data.result == "true" )// if true add the path to the download link
                            {
                                // display download link
                                download_report_btn.prop("href", response.data.path + "?name=systemInformation" );
                                download_report_btn.removeClass('stcr-hidden');
                            }
                            else if( response.data.result == "false" ) // if false show a error message with the file permission issue.
                            {
                                working_file_path.text(response.data.additionalInfo);
                                // Bootstrap alert box
                                download_report_error_container.removeClass('stcr-hidden');
                            }
                        }
                    }
                }).fail( function(a) {
                    console.log("An error occurred on the server.");
                }).done(function () {
                    spinner.addClass('stcr-hidden');
                }); //close jQuery.ajax
            }
        });

        download_report_btn.on( "click", function( event ) {
            // event.preventDefault();
            $( this ).addClass('stcr-hidden');
        });

    });
} )( jQuery );