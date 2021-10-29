(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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
	
	$( document ).on( 'ready', function(){
		var mwb_rma_return_request_subject = $( "#mwb_rma_return_request_subject" ).val();
		if (mwb_rma_return_request_subject == null || mwb_rma_return_request_subject == '') {
			$( "#mwb_rma_return_request_subject_text" ).show();
		} else {
			$( "#mwb_rma_return_request_subject_text" ).hide();
		}
		$( '#mwb_rma_return_request_subject' ).on( 'click', function(){
			var reason = $( this ).val();
			if (reason == null || reason == '') {
				$( "#mwb_rma_return_request_subject_text" ).show();
			} else {
				$( "#mwb_rma_return_request_subject_text" ).hide();
			}
		});
		$( '.mwb_rma_return_request_morefiles' ).on( 'click', function(){		
			var count = jQuery(this).data('count');
			var max  = jQuery(this).data('max');
			var html = '<div class="add_field_input_div"><input type="file" class="mwb_rma_return_request_files" name="mwb_rma_return_request_files[]"><span class="mwb_rma_delete_field">X</span><br></div>';

			if(count < max ){
				$( "#mwb_rma_return_request_files" ).append( html );
				$(document).find(".mwb_rma_return_request_morefiles").data('count', count+1);
			}
		});
		$( document ).on( 'click', '.mwb_rma_delete_field', function(){
			var count = $(document).find(".mwb_rma_return_request_morefiles").data( 'count' );
			$(document).find(".mwb_rma_return_request_morefiles").data( 'count', count - 1 );
			$(this).parent( '.add_field_input_div' ).remove();
		});

		var mwb_wrma_refund_method = $('input[name=mwb_wrma_refund_method]:checked').val();
		if ('' !== mwb_wrma_refund_method && 'manual_method' === mwb_wrma_refund_method ) {
			$( '#bank_details' ).show();
		} else if( ! wrael_public_param.check_pro_active ) {
			$( '#bank_details' ).show();
		} else {
			$( '#bank_details' ).hide();
		}
		$( document ).on( 'click', 'input[name=mwb_wrma_refund_method]', function() {
			if ('' !== $(this).val() && 'manual_method' === $(this).val() ) {
				$( '#bank_details' ).show();
			} else {
				$( '#bank_details' ).hide();
			}
		});
	});
})( jQuery );
