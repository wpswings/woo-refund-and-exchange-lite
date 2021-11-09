jQuery( document ).on( 'ready', function(){
	$ = jQuery;
	//Refund request submit
	$('.mwb_rma_return_notification').hide();
	$( '#mwb_rma_return_request_form' ).on('submit',function(e){
		e.preventDefault();
		var orderid = $( this ).data( 'orderid' );
		var refund_amount = $( '.mwb_rma_total_refund_price' ).val();
		var alerthtml = '';
		var selected_product = {};
		var count = 0;
		var refund_method = '';
		var pro_act = wrael_common_param.check_pro_active;

		var rr_subject = $( '#mwb_rma_return_request_subject' ).val();
			
		if (rr_subject == '' || rr_subject == null) {
			rr_subject = $( '#mwb_rma_return_request_subject_text' ).val();
			if (rr_subject == '' || rr_subject == null) {
				alerthtml += '<li>' + wrael_common_param.return_subject_msg + '</li>';
			}
		}
		var rr_reason = $( '.mwb_rma_return_request_reason' ).val();
		if ( rr_reason == '' ) {
			alerthtml += '<li>' + wrael_common_param.return_reason_msg + '</li>';
		}

		if(pro_act){
			if (typeof mwb_rma_return_alert_condition_addon == 'function') {
				var alerthtml1 = mwb_rma_return_alert_condition_addon();
				if( alerthtml1 ) {
					alerthtml += alerthtml1;
				}
			}
		}
		if (alerthtml != '') {
			$( '#mwb_rma_return_alert' ).html( alerthtml );
			$( '#mwb_rma_return_alert' ).addClass('woocommerce-error');
			$( '#mwb_rma_return_alert' ).removeClass('woocommerce-message');
			$( '#mwb_rma_return_alert' ).css('background-color', 'red');
			$( 'html, body' ).animate(
			{
				scrollTop: $( '#mwb_rma_return_request_container' ).offset().top
			},
			800
			);
			return false;
		} else {
			$( '#mwb_rma_return_alert' ).hide();
			$( '#mwb_rma_return_alert' ).html( alerthtml );
		}
		if( pro_act ){
			if (typeof mwb_rma_refund_total == 'function') {
				var return_data = mwb_rma_refund_total();
				selected_product = return_data['selected_product'];
				refund_amount    = return_data['amount'];
			}
		}else{
			$('.mwb_rma_return_column').each(function(){
				if($(this).find('td:eq(0)').children('.mwb_rma_return_product')){
					var product_info = {};
					var variation_id = $(this).data('variationid');
					var product_id = $(this).data('productid');
					var item_id = $(this).data('itemid');
					var product_price = $(this).find('td:eq(0)').children('.mwb_rma_product_amount').val();
					var product_qty = $(this).find('td:eq(1)').children('.mwb_rma_return_product_qty').val();
					product_info['product_id'] = product_id;
					product_info['variation_id'] = variation_id;
					product_info['item_id'] = item_id;
					product_info['price'] = product_price;
					product_info['qty'] = product_qty;
					selected_product[count] = product_info;
					count++;
				}
			});
		}
		if(pro_act){
			if (typeof mwb_rma_refund_method == 'function') {
				refund_method = mwb_rma_refund_method();
			}
		}
		var data = {
			action	:'mwb_rma_save_return_request',
			products: selected_product,
			amount	: refund_amount,
			subject	: rr_subject,
			reason	: rr_reason,
			orderid : orderid,
			bankdetails : $( '#mwb_rma_bank_details' ).val(),
			refund_method : refund_method,
			security_check	: wrael_public_param.mwb_rma_nonce
		}

		var formData = new FormData(this);
		formData.append('action', 'mwb_rma_return_upload_files');
		formData.append('security_check', wrael_common_param.mwb_rma_nonce);
		$('.mwb_rma_return_notification').show();
		$.ajax({
			url: wrael_common_param.ajaxurl, 
			type: 'POST',             
			data: formData, 
			contentType: false,       
			cache: false,             
			processData:false,
			success: function(respond)   
			{
				//Send return request
				$.ajax({
					url: wrael_common_param.ajaxurl, 
					type: 'POST',  
					data: data,
					dataType :'json',	
					success: function(response) 
					{
						// Start redirect page countdown on refund request form
						if ( window.location.href.indexOf('refund-request-form') > -1 ) {
							var timeleft = 10;
							var downloadTimer = setInterval(function(){
								if(timeleft >= 0){
									$('#countdownTimer').html( timeleft );
								}
								timeleft -= 1;
							}, 1000);
						}
						// Start redirect page countdown on refund request form
						$('#mwb_rma_return_alert').removeClass('woocommerce-error');
						$('#mwb_rma_return_alert').addClass('woocommerce-message');
						$('#mwb_rma_return_alert').css('background-color', '#8FAE1B');
						$('#mwb_rma_return_alert' ).show();
						$('#mwb_rma_return_alert').html( response.msg + ' in ' + '<b><span id="countdownTimer"></span>' + ' seconds</b>' );
						$('.mwb_rma_return_notification').hide();
						$('html, body').animate({
							scrollTop: $('.woocommerce-breadcrumb').offset().top
						}, 800);

						if(typeof response.auto_accept != 'undefined') {
							if(response.auto_accept == true) {
								if (typeof mwb_rma_refund_auto_accept == 'function') {
									mwb_rma_refund_auto_accept(orderid);
								}
							} else {
									window.setTimeout(function() {
									window.location.href = wrael_common_param.myaccount_url;
								}, 11000);
							}
						} else {
								window.setTimeout(function() {
								window.location.href = wrael_common_param.myaccount_url;
							}, 11000);
						}
					}
				});
			}
		});
	});

	// Remove notice on the order message form.
	$(document).on('click','.mwb_remove_notice_msg',function(e) {
		e.preventDefault();
		$('.mwb_order_msg_notice_wrapper').hide();
	});

	// Send the order message frontend and backend with same code.
	$( document ).on( 'click', '#mwb_order_msg_submit', function (e) {
		e.preventDefault();
		var up_files = $('#mwb_order_msg_attachment');
		var msg      = $('#mwb_order_new_msg').val();
		var order_msg_type = $('#order_msg_type').val();
		var alerthtml = '';
		if ( msg == '' ) {
			alerthtml = '<p class="mwb_order_msg_sent_notice">'+  wrael_common_param.message_empty +'</p><a href="" class="mwb_remove_notice_msg">X</a>';
			$('.mwb_order_msg_notice_wrapper').css('display', 'flex');
			$('.mwb_order_msg_notice_wrapper').css('background-color', 'red');
			$('.mwb_order_msg_notice_wrapper').html(alerthtml);
			$('#mwb_order_msg_submit').css({'outline-color':'white', 'border-color':'white'});
			return false;
		}
		var order_id = $(this).data('id');

		var form_data = new FormData();

		// Read selected files
		var totalfiles = up_files[0].files.length;
		for (var index = 0; index < totalfiles; index++) {
					form_data.append('mwb_order_msg_attachment[]', up_files[0].files[index]);
		}
		form_data.append( 'action', 'mwb_rma_order_messages_save' );
		form_data.append( 'msg', msg );
		form_data.append( 'order_msg_type', order_msg_type );
		form_data.append( 'order_id', order_id );
		form_data.append( 'security_check', wrael_common_param.mwb_rma_nonce );

		// AJAX request
		$.ajax({
			url: wrael_common_param.ajaxurl, 
			type: 'post',
			data: form_data,
			dataType: 'json',
			contentType: false,
			processData: false,
			success: function ( response ) {
				if( response ) {
					var html = 	'<p class="mwb_order_msg_sent_notice">'+  wrael_common_param.message_sent +'</p><a href="" class="mwb_remove_notice_msg">X</a>';
						$('.mwb_order_msg_notice_wrapper').css('background-color', '#64CD83');
						$('.mwb_order_msg_notice_wrapper').css('display', 'flex');
						$('#mwb_order_msg_submit').css({'outline-color':'none', 'border-color':'none'});
						$('.mwb_order_msg_sub_container').load(document.URL +  ' .mwb-order-msg__row');
						$('#mwb_order_new_msg').val('');
						$('#mwb_order_msg_attachment').val('');
						$('.mwb_order_msg_notice_wrapper').html( html );
				}
			}
		});
	});

	// Order message refresh frontend and backend with same code.
	$('.mwb_order_msg_reload_notice_wrapper').hide();
	$(document).on('click','.mwb_reload_messages',function(e) {
		e.preventDefault();
		$(this).addClass('mwb-loader-icon');
		$('.mwb_order_msg_sub_container').load(document.URL +  ' .mwb-order-msg__row');
			setTimeout(function() {
				$('.mwb_reload_messages').removeClass('mwb-loader-icon');
				$('.mwb_order_msg_reload_notice_wrapper').show();
				$('.mwb_order_msg_reload_notice_wrapper').css({
					'background-color':'rgb(100, 205, 131)',
					'border-radius':'5px',
					'color': 'white',
					'padding-left': '10px'
				});
			}, 2000);
			setTimeout(function() {
					$('.mwb_order_msg_reload_notice_wrapper').hide();
			}, 3000);
	});

	// dismiss send msg frontend and backend with same code.
	$(document).on('click','.mwb_order_send_msg_dismiss',function(e) {
		e.preventDefault();
		$('.mwb_order_msg_notice_wrapper').hide();
	});
});

