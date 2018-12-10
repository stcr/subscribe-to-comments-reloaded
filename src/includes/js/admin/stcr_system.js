/**
 * Created by reedyseth on 12/5/18.
 */
( function($){
    $(document).ready(function(){
        $('button.generate_system_info').on("click", function( event ){
            event.preventDefault();

            $('.generate_spinner').removeClass('stcr-hidden');

            // Get request response
            var reportPath = $('.reportPath').val();
            var result =  reportPath.split( "|" );
            var reportResult = result[0];
            var reportValue  = result[1];
            var reportAdditionalInfo  = result[2];
            // check result true|false
            if( reportResult == "true" )// if true add the path to the download link
            {
                // display download link
                $('.download_report').prop("href", reportValue + "?name=systemInformation" );
                $('.download_report').removeClass('stcr-hidden');
            }
            else if( reportResult == "false" ) // if false show a error message with the file permission issue.
            {
                $('.file-path').text(reportAdditionalInfo);
                // Bootstrap alert box
                $('.download_report_error').removeClass('stcr-hidden');
            }

            $('.generate_spinner').addClass('stcr-hidden');
        });

    });
} )( jQuery );