
jQuery( document ).on( 'ready', function(){
	$ = jQuery;
	// Show refund subject field if other option is selected.
	var mwb_rma_return_request_subject = $( '#mwb_rma_return_request_subject' ).val();
	if (mwb_rma_return_request_subject == null || mwb_rma_return_request_subject == '') {
		$( '#mwb_rma_return_request_subject_text' ).show();
	} else {
		$( '#mwb_rma_return_request_subject_text' ).hide();
	}

	// onchange Show refund subject field if other option is selected.
	$( '#mwb_rma_return_request_subject' ).on( 'click', function(){
		var reason = $( this ).val();
		if (reason == null || reason == '') {
			$( '#mwb_rma_return_request_subject_text' ).show();
		} else {
			$( '#mwb_rma_return_request_subject_text' ).hide();
		}
	});

	// Add more file field on the refund request form.
	$( '.mwb_rma_return_request_morefiles' ).on( 'click', function(){		
		var count = $(this).data('count');
		var max  = $(this).data('max');
		var html = '<div class="add_field_input_div"><input type="file" class="mwb_rma_return_request_files" name="mwb_rma_return_request_files[]"><span class="mwb_rma_delete_field">X</span><br></div>';

		if(count < max ){
			$( '#mwb_rma_return_request_files' ).append( html );
			$(document).find('.mwb_rma_return_request_morefiles').data('count', count+1);
		}
	});
	// delete file field on the refund request form.
	$( document ).on( 'click', '.mwb_rma_delete_field', function(){
		var count = $(document).find('.mwb_rma_return_request_morefiles').data( 'count' );
		$(document).find('.mwb_rma_return_request_morefiles').data( 'count', count - 1 );
		$(this).parent( '.add_field_input_div' ).remove();
	});

	// show the bank details field on selected refund method.
	var mwb_wrma_refund_method = $('input[name=mwb_wrma_refund_method]:checked').val();
	if ('' !== mwb_wrma_refund_method && 'manual_method' === mwb_wrma_refund_method ) {
		$( '#bank_details' ).show();
	} else if( ! wrael_public_param.check_pro_active ) {
		$( '#bank_details' ).show();
	} else {
		$( '#bank_details' ).hide();
	}
	// onchange show the bank details field on selected refund method.
	$( document ).on( 'click', 'input[name=mwb_wrma_refund_method]', function() {
		if ('' !== $(this).val() && 'manual_method' === $(this).val() ) {
			$( '#bank_details' ).show();
		} else {
			$( '#bank_details' ).hide();
		}
	});
});
