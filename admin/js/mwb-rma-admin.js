(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	jQuery(document).ready(function(){

	 	jQuery(".add_more_button").click(function(){
	 		var id = jQuery(this).closest('table').find("tr:first").find("input:text").attr('id');
	 		var td = jQuery(this).closest('table').find("tr:first").find("input:text").closest('td');
	 		var html='<a href="#" class="mwb_rma_remove_button">'+global_mwb_rma.remove+'</a><br>';
	 		var clone = jQuery('#'+id+'_wrapper').clone();
	 		clone.children().append(html).find("input:text").attr('value', '');
			clone.appendTo(td);
	 	});

	 	jQuery(document).on('click','.mwb_rma_remove_button', function(e){
	 		e.preventDefault();
	 		jQuery(this).parent().remove();
	 	});


	});


})( jQuery );
