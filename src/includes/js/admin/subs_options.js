/**
 * Created by reedyseth on 4/2/18.
 */
( function($){
    $(document).ready(function(){
        $('.helpDescription').webuiPopover({
            style: 'helpDescriptionContent',
            dismissible: true,
            type: 'html'
        });

    });
} )( jQuery );