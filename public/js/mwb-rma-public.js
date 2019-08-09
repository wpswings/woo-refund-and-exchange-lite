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

	 jQuery(document).ready(function(){

		//Add return reason text
	 	var mwb_rma_return_request_subject = jQuery("#mwb_rma_return_request_subject").val();
		if(mwb_rma_return_request_subject == null || mwb_rma_return_request_subject == ''){
			jQuery("#mwb_rma_return_request_subject_text").show();
		}else{
			jQuery("#mwb_rma_return_request_subject_text").hide();
		}

		jQuery("#mwb_rma_return_request_subject").change(function(){
			var reason = jQuery(this).val();
			if(reason == null || reason == ''){
				jQuery("#mwb_rma_return_request_subject_text").show();
			}else{
				jQuery("#mwb_rma_return_request_subject_text").hide();
			}
		});

		//Add more files to attachment
		jQuery(".mwb_rma_return_request_morefiles").click(function(){
			var html = '<br/><input type="file" class="input-text mwb_rma_return_request_files" name="mwb_rma_return_request_files[]">';
			jQuery("#mwb_rma_return_request_files").append(html);
		});

		jQuery("#mwb_rma_return_request_files").on('change',".mwb_rma_return_request_files",function(e){ 
			var files = {};
			var file_type = e.target.files;
			if(typeof file_type[0]['type'] != 'undefined')
			{
				var type = file_type[0]['type'];
			}	
			if(type == 'image/png' || type == 'image/jpg' || type == 'image/jpeg')
			{
			}
			else
			{
				jQuery(this).val("");
				jQuery('#mwb_rma_return_alert').html(global_mwb_rma.attachment_msg).show();

			}	
			
			var count = 0;
			jQuery(".mwb_rma_return_request_files").each(function(){
				var filename = jQuery(this).val();
				files[count] = e.target.files;
				count++;
			});
		});

		//Submit Return Request Form
		jQuery("#mwb_rma_return_request_form").on('submit', function(e){
			e.preventDefault();	
			var orderid = jQuery(this).data('orderid');
			var refund_amount = jQuery('.mwb_rma_total_refund_price').val();
			var rr_subject = jQuery("#mwb_rma_return_request_subject").val();
			var alerthtml = '';
			var selected_product = {};
			var count = 0;
			if(rr_subject == '' || rr_subject == null)
			{
				rr_subject = jQuery("#mwb_rma_return_request_subject_text").val();
				if(rr_subject == '' || rr_subject == null)
				{
					alerthtml += '<li>'+global_mwb_rma.return_subject_msg+'</li>';
				}
			}

			var rr_reason = $(".mwb_rma_return_request_reason").val();
			
			if(rr_reason == '' || rr_reason == null)
			{
				alerthtml += '<li>'+global_mwb_rma.return_reason_msg+'</li>';
			}
			else
			{
				var r_reason = '';
				r_reason = rr_reason.trim();
				if(r_reason == '' || r_reason == null)
				{
					alerthtml += '<li>'+global_mwb_rma.return_reason_msg+'</li>';
				}
			}	
			
			if(alerthtml != '')
			{
				jQuery("#mwb_rma_return_alert").show();
				jQuery("#mwb_rma_return_alert").html(alerthtml);
				jQuery('html, body').animate({
					scrollTop: jQuery("#mwb_rma_return_request_container").offset().top
				}, 800);
				return false;
			}
			else
			{
				jQuery("#mwb_rma_return_alert").hide();
				jQuery("#mwb_rma_return_alert").html(alerthtml);
			}	


			$(".mwb_rma_return_column").each(function(){
				if($(this).find("td:eq(0)").children('.mwb_rma_return_product')){
					var product_info = {};
					var variation_id = $(this).data("variationid");
					var product_id = $(this).data("productid");
					var item_id = $(this).data("itemid");
					var product_price = $(this).find('.mwb_rma_product_amount').val();
					var product_qty = $(this).find("td:eq(1)").children('.mwb_rma_return_product_qty').val();
					product_info['product_id'] = product_id;
					product_info['variation_id'] = variation_id;
					product_info['item_id'] = item_id;
					product_info['price'] = product_price;
					product_info['qty'] = product_qty;
					selected_product[count] = product_info;
					count++;
				}
			});

			var data = {	
				action	:'mwb_rma_return_product_info',
				products: selected_product,
				amount	: refund_amount,
				subject	: rr_subject,
				reason	: rr_reason,
				orderid : orderid,
				security_check	:	global_mwb_rma.mwb_rma_nonce	
			}
			
			var formData = new FormData(this);
			formData.append('action', 'mwb_rma_return_upload_files');
			formData.append('security_check', global_mwb_rma.mwb_rma_nonce);
			$.ajax({
				url: global_mwb_rma.ajaxurl, 
				type: "POST",             
				data: formData, 
				contentType: false,       
				cache: false,             
				processData:false,
				success: function(respond)   
				{
					//Send return request
					
					$.ajax({
						url: global_mwb_rma.ajaxurl, 
						type: "POST",  
						data: data,
						dataType :'json',	
						success: function(response) 
						{
							jQuery("#mwb_rma_return_alert").html(response.msg);
							$("#mwb_rma_return_alert").removeClass('woocommerce-error');
							$("#mwb_rma_return_alert").addClass("woocommerce-message");
							$("#mwb_rma_return_alert").css("color", "#8FAE1B");
							$("#mwb_rma_return_alert").show();
							jQuery('html, body').animate({
								scrollTop: jQuery("#mwb_rma_return_request_container").offset().top
							}, 800);
							window.setTimeout(function() {
									window.location.href = global_mwb_rma.myaccount_url;
							}, 1000);
						}
					});

				}
			});
			
		});
	 	
	 });

})( jQuery );
