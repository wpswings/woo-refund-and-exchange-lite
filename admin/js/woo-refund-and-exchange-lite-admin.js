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

		//to add select2 to select order status setting in refund
		jQuery(document).find('#mwb_rma_return_order_status').select2();

		//to add input type text on click of add more button
	 	jQuery(".add_more_button").click(function(){
	 		var id = jQuery(this).closest('table').find("tr:first").find("input:text").attr('id');
	 		var td = jQuery(this).closest('table').find("tr:first").find("input:text").closest('td');
	 		var html='<a href="#" class="mwb_rma_remove_button">'+global_mwb_rma.remove+'</a><br>';
	 		var clone = jQuery('#'+id+'_wrapper').clone();
	 		clone.children().append(html).find("input:text").attr('value', '');
			clone.appendTo(td);
	 	});

	 	//to remove parent on click of remove button
	 	jQuery(document).on('click','.mwb_rma_remove_button', function(e){
	 		e.preventDefault();
	 		jQuery(this).parent().remove();
	 	});

	 	//to accept return request on order edit page
	 	jQuery("#mwb_rma_accept_return").click(function(){
	 		jQuery(".mwb_rma_return_loader").show();
	 		var orderid = jQuery(this).data('orderid');
			var date = jQuery(this).data('date');
			var data = {
	 			action:'mwb_rma_return_req_approve',
	 			orderid:orderid,
	 			date:date,
	 			security_check	: global_mwb_rma.mwb_rma_nonce	
	 		};

	 		$.ajax({
	 			url: global_mwb_rma.ajaxurl, 
	 			type: "POST",  
	 			data: data,
	 			dataType :'json',	
	 			success: function(response) 
	 			{
	 				jQuery(".mwb_rma_return_loader").hide();
	 				location.reload(true);
	 			}
	 		});

	 	});

	 	//to cancel return request on order edit page
	 	jQuery("#mwb_rma_cancel_return").click(function(){
	 		jQuery(".mwb_rma_return_loader").show();
	 		var orderid = jQuery(this).data('orderid');
	 		var date = jQuery(this).data('date');
	 		var data = {
	 			action:'mwb_rma_return_req_cancel',
	 			orderid:orderid,
	 			date:date,
	 			security_check	:	global_mwb_rma.mwb_rma_nonce	
	 		};
	 		$.ajax({
	 			url: global_mwb_rma.ajaxurl, 
	 			type: "POST",  
	 			data: data,
	 			dataType :'json',	
	 			success: function(response) 
	 			{
	 				jQuery(".mwb_rma_return_loader").hide();
	 				location.reload(true);
	 			}
	 		});
	 	});	

	 	//when refund amount button is clicked it slides up to open woocommerce refund panel 
	 	jQuery("#mwb_rma_left_amount").click(function(){
	 		jQuery(this).attr('disabled','disabled');

	 		var order_id = jQuery(this).data('orderid');
	 		var refund_amount = jQuery(".mwb_rma_total_amount_for_refund").val();
	 		
	 		jQuery('html, body').animate({
	 			scrollTop: jQuery("#order_shipping_line_items").offset().top
	 		}, 2000);

	 		jQuery( 'div.wc-order-refund-items' ).slideDown();
	 		jQuery( 'div.wc-order-data-row-toggle' ).not( 'div.wc-order-refund-items' ).slideUp();
	 		jQuery( 'div.wc-order-totals-items' ).slideUp();
	 		jQuery( '#woocommerce-order-items' ).find( 'div.refund' ).show();
	 		jQuery( '.wc-order-edit-line-item .wc-order-edit-line-item-actions' ).hide();
	 		var refund_amount = jQuery(".mwb_rma_total_amount_for_refund").val();
	 		var refund_reason = jQuery("#mwb_rma_refund_reason").val();
	 		console.log(refund_amount);
	 		console.log(refund_reason);
	 		jQuery("#refund_amount").val(refund_amount);
	 		jQuery("#refund_reason").val(refund_reason);

	 		var total = accounting.unformat( refund_amount, woocommerce_admin.mon_decimal_point );

	 		jQuery( 'button .wc-order-refund-amount .amount' ).text( accounting.formatMoney( total, {
	 			symbol:    woocommerce_admin_meta_boxes.currency_format_symbol,
	 			decimal:   woocommerce_admin_meta_boxes.currency_format_decimal_sep,
	 			thousand:  woocommerce_admin_meta_boxes.currency_format_thousand_sep,
	 			precision: woocommerce_admin_meta_boxes.currency_format_num_decimals,
	 			format:    woocommerce_admin_meta_boxes.currency_format
	 		} ) );

	 	});	

	 	//to return order back in stock and display mange stock message
	 	jQuery(document).on('click','#mwb_rma_stock_back',function(){
	 		jQuery(this).attr('disabled','disabled');
	 		var order_id = jQuery(this).data('orderid');
	 		var type = jQuery(this).data('type');
	 		var data = { 
	 			action   : 'mwb_rma_manage_stock' ,
	 			order_id : order_id ,
	 			type     : type,
	 			security_check : global_mwb_rma.mwb_rma_nonce
	 		};
	 		jQuery.ajax({
	 			url: global_mwb_rma.ajaxurl, 
	 			type: "POST",             
	 			data: data,
	 			dataType :'json',
	 			success: function(response)   
	 			{
	 				if(response.result)
	 				{
	 					jQuery("#post").prepend('<div class="updated notice notice-success is-dismissible" id="message"><p>'+response.msg+'</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>'); 
	 					jQuery('html, body').animate({
	 						scrollTop: jQuery("body").offset().top
	 					}, 2000, "linear", function(){
	 						window.setTimeout(function() {
	 							window.location.reload();
	 						}, 1000);
	 					});
	 				}
	 				else
	 				{
	 					jQuery("#post").prepend('<div id="messege" class="notice notice-error is-dismissible" id="message"><p>'+response.msg+'</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>'); 
	 					jQuery('html, body').animate({
	 						scrollTop: jQuery("body").offset().top
	 					}, 2000, "linear", function(){
	 						window.setTimeout(function() {
	 							window.location.reload();
	 						}, 1000);
	 					}); 
	 				}
	 			}
	 		});
	 	});

	});

})( jQuery );
