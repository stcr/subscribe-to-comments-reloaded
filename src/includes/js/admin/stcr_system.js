/**
 * Handles system page JavaScript
 */

jQuery(document).ready(function(){

	jQuery('.download_report').on( 'click', function(e) {
		e.preventDefault();
		jQuery('form[name="stcr_sysinfo_form"]').submit();
	});

});