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
	 var proid_price =[];
	jQuery(document).ready(function(){

		var productids = [];
	 	
	 	//Get the price of the products listed by sending ajax request on load

	 	jQuery( "tbody tr").each(function(){
	 		var attr =  $(this).attr('data-productid');
	 		var variation = $(this).attr('data-variationid');
	 		var quantity = $(this).find('.mwb_rma_product_title').find(".product-quantity").text();
	 		quantity = quantity.slice(1).trim();
	 		if ( typeof attr !== typeof undefined && attr !== false && typeof variation !== typeof undefined && variation !== false &&  typeof quantity !== typeof undefined && quantity !== false) {
	 			productids.push({"productid":attr, "variationid": variation ,"quantity" :quantity});
	 		}
	 	});
	 	jQuery.ajax({
	 		url: global_mwb_rma.ajaxurl, 
	 		type: "POST",             
	 		data: { action : 'mwb_rma_get_product_price' , product_id : productids , security_check	:	global_mwb_rma.mwb_rma_nonce },
	 		success: function(respond)   
	 		{
	 			proid_price = JSON.parse( respond );

	 		}
	 	});

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
			var refund_amount = 0;
			var rr_subject = jQuery("#mwb_rma_return_request_subject").val();
			var alerthtml = '';
			var selected_product = {};
			var count = 0;
			var pro_act = global_mwb_rma.pro_active;
			if(rr_subject == '' || rr_subject == null)
			{
				rr_subject = jQuery("#mwb_rma_return_request_subject_text").val();
				if(rr_subject == '' || rr_subject == null)
				{
					alerthtml += '<li>'+global_mwb_rma.return_subject_msg+'</li>';
				}
			}

			var rr_reason = jQuery(".mwb_rma_return_request_reason").val();
			
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

			if(pro_act){
				if (typeof mwb_rma_alert_conditions == 'function') {
					alerthtml = mwb_rma_alert_conditions(alerthtml);
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

			
			if(pro_act){
				if (typeof mwb_rma_total == 'function') {
					var return_data = mwb_rma_total();
					selected_product=return_data['selected_product'];
					refund_amount=return_data['amount'];
				}
			}else{
				jQuery(".mwb_rma_return_column").each(function(){
					if(jQuery(this).find("td:eq(0)").children('.mwb_rma_return_product')){
						var product_info = {};
						var variation_id = jQuery(this).data("variationid");
						var product_id = jQuery(this).data("productid");
						var item_id = jQuery(this).data("itemid");
						var product_qty = jQuery(this).find("td:eq(1)").children('.mwb_rma_return_product_qty').val();
						product_info['product_id'] = product_id;
						product_info['variation_id'] = variation_id;
						product_info['item_id'] = item_id;
						product_info['qty'] = product_qty;
						jQuery.each(proid_price, function( key, value ) {
							if( product_info['product_id']  == value.productid ){
								refund_amount = refund_amount+(parseFloat(value.price)*parseInt(product_info['qty']));
								product_info['price'] = value.price;
							}
						});
						selected_product[count] = product_info;
						count++;
					}
				});
			}
			
			//refund_amount = refund_amount.toFixed(2);
			// var data = {	
			// 	action	:'mwb_rma_return_product_info',
			// 	products: selected_product,
			// 	amount	: refund_amount,
			// 	subject	: rr_subject,
			// 	reason	: rr_reason,
			// 	orderid : orderid,
			// 	security_check	:	global_mwb_rma.mwb_rma_nonce	
			// }
			
			// jQuery(".mwb_rma_return_notification").show();

			// var formData = new FormData(this);
			// formData.append('action', 'mwb_rma_return_upload_files');
			// formData.append('security_check', global_mwb_rma.mwb_rma_nonce);
			// jQuery.ajax({
			// 	url: global_mwb_rma.ajaxurl, 
			// 	type: "POST",             
			// 	data: formData, 
			// 	contentType: false,       
			// 	cache: false,             
			// 	processData:false,
			// 	success: function(respond)   
			// 	{
			// 		//Send return request
					
			// 		jQuery.ajax({
			// 			url: global_mwb_rma.ajaxurl, 
			// 			type: "POST",  
			// 			data: data,
			// 			dataType :'json',	
			// 			success: function(response) 
			// 			{
			// 				jQuery(".mwb_rma_return_notification").hide();
			// 				jQuery("#mwb_rma_return_alert").html(response.msg);
			// 				jQuery("#mwb_rma_return_alert").removeClass('woocommerce-error');
			// 				jQuery("#mwb_rma_return_alert").addClass("woocommerce-message");
			// 				jQuery("#mwb_rma_return_alert").css("color", "#8FAE1B");
			// 				jQuery("#mwb_rma_return_alert").show();
			// 				jQuery('html, body').animate({
			// 					scrollTop: jQuery("#mwb_rma_return_request_container").offset().top
			// 				}, 800);
			// 				window.setTimeout(function() {
			// 						window.location.href = global_mwb_rma.myaccount_url;
			// 				}, 1000);
			// 			}
			// 		});

			// 	}
			// });
			
		});
	 	
	 });

})( jQuery );
