/**
 * Initialize all the tinyMCE code.
 * @since 03-Aug-2015
 * @author reedyseth
 */
jQuery(document).ready(function() {
	tinymce.init({
		selector: "textarea.rte",
		plugins: [
			"link hr anchor"
		]
	});
});
