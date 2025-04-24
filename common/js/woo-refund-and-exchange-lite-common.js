jQuery(function($){
	//Refund request submit
	$('.wps_rma_return_notification').hide();
	$( '#wps_rma_return_request_form' ).on('submit',function(e){
		e.preventDefault();
		var orderid = $( this ).data( 'orderid' );
		var alerthtml = '';
		var selected_product = new Array();
		var refund_method = '';
		var pro_act = wrael_common_param.check_pro_active;

		var rr_subject = $( '#wps_rma_return_request_subject' ).val();
			
		if (rr_subject == '' || rr_subject == null ) {
			rr_subject = $( '#wps_rma_return_request_subject_text' ).val();
			if (rr_subject == '' || rr_subject == null ) {
				alerthtml += '<li>' + wrael_common_param.return_subject_msg + '</li>';
			}
		}
		var rr_reason = $( '.wps_rma_return_request_reason' ).val();
		if ( typeof( rr_reason ) !== 'undefined' && ( rr_reason == '' || rr_reason == null ) ) {
			alerthtml += '<li>' + wrael_common_param.return_reason_msg + '</li>';
		}
		if( pro_act && typeof wps_rma_return_alert_condition_addon == 'function' ){
			alerthtml += wps_rma_return_alert_condition_addon();
		}
		var attachment_enable = wrael_common_param.refund_form_attachment;
		if ( attachment_enable && 'on' == attachment_enable ) {
			$('.wps_rma_return_request_files').each(function(){
				var up_files = $(this);
				var totalfiles = up_files[0].files.length;
				if ( totalfiles ) {
					var file_type = up_files[0].files[0].type;
					if ( 'image/png' == file_type || 'image/jpeg' == file_type || 'image/jpg' == file_type ) {
					} else {
						alerthtml += '<li>'+ wrael_common_param.file_not_supported + '</li>';
					}
				}
			});
		}

		var wps_rma_customer_contact_refund = null;
		var is_reached_max = false;
		if( pro_act ){
			$('.wps_rma_return_column').each(function(){
				if($(this).find('.wps_wrma_return_product').is(':checked')){
					const item_id = $(this).data('item_id');
					const qty = $(this).find('.wps_rma_return_product_qty').val();
					var return_products = {};
					return_products.item_id = item_id;
					return_products.qty = qty;
					selected_product.push( return_products );

					const max_qty = $(this).find('.wps_rma_return_product_qty').attr('max');
					if( parseInt( qty ) > parseInt( max_qty ) ) {
						is_reached_max = true;
					}
				}
			});
			wps_rma_customer_contact_refund = $('#wps_rma_customer_contact_refund').val();
			if ( typeof wps_rma_return_alert_condition_addon == 'function' ) {
				refund_method = wps_rma_refund_method();
			}
		} else {
			$('.wps_rma_return_column').each(function(){
				const item_id = $(this).data('item_id');
				const qty = $(this).find('.wps_rma_return_product_qty').val();
				var return_products = {};
				return_products.item_id = item_id;
				return_products.qty = qty;
				selected_product.push( return_products );

				const max_qty = $(this).find('.wps_rma_return_product_qty').attr('max');

				if( parseInt( qty ) > parseInt( max_qty ) ) {
					is_reached_max = true;
				}
			});
		}

		if ( is_reached_max ) {
			alerthtml += '<li>'+wrael_common_param.correct_quantity+'</li>';
		}
		var qty_error = false;
		$.each( selected_product, function( index, data ){
			if (!Number.isInteger(parseInt(data.qty)) || parseInt(data.qty) <= 0) {
				qty_error = true;
			}
		});
		if ( qty_error ) {
			alerthtml += '<li>'+ wrael_common_param.qty_error + '</li>';
		}
		if ( alerthtml == 'false' ) {
			alerthtml = '';
		}
		
		if (alerthtml != '') {
			$( '#wps_rma_return_alert' ).show();
			$( '#wps_rma_return_alert' ).html( alerthtml );
			$( '#wps_rma_return_alert' ).addClass('woocommerce-error');
			$( '#wps_rma_return_alert' ).removeClass('woocommerce-message');
			$( '#wps_rma_return_alert' ).css('background-color', 'red');
			$( 'html, body' ).animate(
			{
				scrollTop: $( '#wps_rma_return_request_container' ).offset().top
			},
			800
			);
			return false;
		} else {
			$( '#wps_rma_return_alert' ).hide();
			$( '#wps_rma_return_alert' ).html( alerthtml );
		}
	
		var data = {
			action	:'wps_rma_save_return_request',
			products: selected_product, 
			subject	: rr_subject,
			reason	: rr_reason,
			orderid : orderid,
			bankdetails : $( '#wps_rma_bank_details' ).val(),
			refund_method : refund_method,
			security_check	: wrael_common_param.wps_rma_nonce,
			all_product_checked : $('.wps_wrma_return_product_all').is(":checked"),
			wps_rma_customer_contact_refund : wps_rma_customer_contact_refund,
		}

		var formData = new FormData(this);
		formData.append('action', 'wps_rma_return_upload_files');
		formData.append('security_check', wrael_common_param.wps_rma_nonce);
		$('.wps_rma_return_notification').show();
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
						var timeleft = 10;
						var downloadTimer = setInterval(function(){
							if(timeleft >= 0){
								$('#countdownTimer').html( timeleft );
							}
							timeleft -= 1;
						}, 1000);

						// Start redirect page countdown on refund request form
						$('#wps_rma_return_alert').removeClass('woocommerce-error');
						$('#wps_rma_return_alert').addClass('woocommerce-message');
						$('#wps_rma_return_alert').css('background-color', '#8FAE1B');
						$('#wps_rma_return_alert' ).show();
						$('#wps_rma_return_alert').html( response.msg + ' in ' + '<b><span id="countdownTimer"></span>' + ' seconds</b>' );
						$('.wps_rma_return_notification').hide();
						$('html, body').animate({
							scrollTop: $('#wps_rma_return_request_container').offset().top
						}, 800);

						if(typeof response.auto_accept != 'undefined') {
							if(response.auto_accept == true) {
								if (typeof wps_rma_refund_auto_accept == 'function') {
									wps_rma_refund_auto_accept(orderid);
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
	$(document).on('click','.wps_remove_notice_msg',function(e) {
		e.preventDefault();
		$('.wps_order_msg_notice_wrapper').hide();
	});

	// Send the order message frontend and backend with same code.
	$( document ).on( 'click', '#wps_order_msg_submit', function (e) {
		e.preventDefault();
		var up_files = $('#wps_order_msg_attachment');
		var msg      = $('#wps_order_new_msg').val();
		var order_msg_type = $('#order_msg_type').val();
		var alerthtml = '';
		var order_id = $(this).data('id');

		var pro_act = wrael_common_param.check_pro_active;
		var wps_rma_customer_contact_order_message = null;
		if( pro_act ){
			wps_rma_customer_contact_order_message = $('#wps_rma_customer_contact_order_message').val();
		}

		var form_data = new FormData();

		// Read selected files
		var attachment_enable = wrael_common_param.order_msg_attachment;
		if ( attachment_enable && 'on' == attachment_enable && up_files[0] ) {
			var totalfiles = up_files[0].files.length;
			if ( totalfiles ) {
				for (var index = 0; index < totalfiles; index++) {
					var file_type = up_files[0].files[index].type;
					if ( 'image/png' == file_type || 'image/jpeg' == file_type || 'image/jpg' == file_type ) {
						form_data.append('wps_order_msg_attachment[]', up_files[0].files[index]);	
					} else {
						alerthtml = '<p class="wps_order_msg_sent_notice">'+ wrael_common_param.file_not_supported +'</p><a href="" class="wps_remove_notice_msg">X</a>';
					}
				}

			}
		}
		if ( msg == '' ) {
			alerthtml = '<p class="wps_order_msg_sent_notice">'+ wrael_common_param.message_empty +'</p><a href="" class="wps_remove_notice_msg">X</a>';
		}
		if ( alerthtml ) {
			$('.wps_order_msg_notice_wrapper').css('display', 'flex');
			$('.wps_order_msg_notice_wrapper').css('background-color', 'red');
			$('.wps_order_msg_notice_wrapper').html(alerthtml);
			$('#wps_order_msg_submit').css({'outline-color':'white', 'border-color':'white'});
			return false;
		}
		form_data.append( 'action', 'wps_rma_order_messages_save' );
		form_data.append( 'msg', msg );
		form_data.append( 'order_msg_type', order_msg_type );
		form_data.append( 'wps_rma_customer_contact_order_message', wps_rma_customer_contact_order_message );
		form_data.append( 'order_id', order_id );
		form_data.append( 'security_check', wrael_common_param.wps_rma_nonce );

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
					var html = 	'<p class="wps_order_msg_sent_notice">'+  wrael_common_param.message_sent +'</p><a href="" class="wps_remove_notice_msg">X</a>';
						$('.wps_order_msg_notice_wrapper').css('background-color', '#64CD83');
						$('.wps_order_msg_notice_wrapper').css('display', 'flex');
						$('#wps_order_msg_submit').css({'outline-color':'none', 'border-color':'none'});
						$('.wps_order_msg_sub_container').load(document.URL +  ' .wps-order-msg__row');
						$('#wps_order_new_msg').val('');
						$('#wps_order_msg_attachment').val('');
						$('.wps_order_msg_notice_wrapper').html( html );
				}
			}
		});
	});

	// Order message refresh frontend and backend with same code.
	$('.wps_order_msg_reload_notice_wrapper').hide();
	$(document).on('click','.wps_reload_messages',function(e) {
		e.preventDefault();
		$(this).addClass('wps-loader-icon');
		$('.wps_order_msg_sub_container').load(document.URL +  ' .wps-order-msg__row');
			setTimeout(function() {
				$('.wps_reload_messages').removeClass('wps-loader-icon');
				$('.wps_order_msg_reload_notice_wrapper').show();
				$('.wps_order_msg_reload_notice_wrapper').css({
					'background-color':'rgb(100, 205, 131)',
					'border-radius':'5px',
					'color': 'white',
					'padding-left': '10px'
				});
			}, 2000);
			setTimeout(function() {
					$('.wps_order_msg_reload_notice_wrapper').hide();
			}, 3000);
	});

	// dismiss send msg frontend and backend with same code.
	$(document).on('click','.wps_order_send_msg_dismiss',function(e) {
		e.preventDefault();
		$('.wps_order_msg_notice_wrapper').hide();
	});
	$( document ).on( 'submit', '#wps_rma_cancel_return_request', function(e){
		e.preventDefault();
		
		// Show confirmation alert
		if (!confirm(wrael_common_param.return_cancellation_alert)) {
			return; // Exit if the user clicks "Cancel"
		}
		const order_id = $('.wps_rma_cancel_return_request').val();

		var data = {
			action	:'wps_rma_cancel_return_request',
			order_id: order_id,
			security_check	: wrael_common_param.wps_rma_nonce,
		}
		//cancel return request
		$.ajax({
			url: wrael_common_param.ajaxurl,
			type: 'POST',
			data: data,
			dataType :'json',	
			success: function(response) 
			{
				window.location.href = window.location.href;
			}
		});
	});
});

