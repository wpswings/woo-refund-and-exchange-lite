(function($) {
	"use strict";

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

	$(document).ready(function() {
		const MDCText = mdc.textField.MDCTextField;
		const textField = [].map.call(
			document.querySelectorAll(".mdc-text-field"),
			function(el) {
				return new MDCText(el);
			}
		);
		const MDCRipple = mdc.ripple.MDCRipple;
		const buttonRipple = [].map.call(
			document.querySelectorAll(".mdc-button"),
			function(el) {
				return new MDCRipple(el);
			}
		);
		const MDCSwitch = mdc.switchControl.MDCSwitch;
		const switchControl = [].map.call(
			document.querySelectorAll(".mdc-switch"),
			function(el) {
				return new MDCSwitch(el);
			}
		);

		$(".mwb-password-hidden").click(function() {
			if ($(".mwb-form__password").attr("type") == "text") {
				$(".mwb-form__password").attr("type", "password");
			} else {
				$(".mwb-form__password").attr("type", "text");
			}
		});
		$('.mwb_rma_order_statues').select2();

		var output_setting = [];
		function make_register_setting_obj() {
			let on_setting = [];
			$.each( $(".add_more_rma_policies"), function() {
				var fun = $( this ).children( ".mwb_rma_on_functionality" ).val();
				var set = $( this ).children( ".mwb_rma_settings" ).val();
				var myObj = new Object();
				myObj.name = fun;
				myObj.value = set;
				on_setting.push( myObj );
			});
			on_setting.forEach(function(item) {
				var existing = output_setting.filter(function(v, i) {
					return v.name == item.name;
				});
				if (existing.length) {
					var existingIndex = output_setting.indexOf(existing[0]);
					output_setting[existingIndex].value = output_setting[existingIndex].value.concat(item.value);
				} else {
					if (typeof item.value == 'string')
					item.value = [item.value];
					output_setting.push(item);
				}
			});
		}
		make_register_setting_obj();

		function show_correct_field(){
			$.each( $(".mwb_rma_settings"), function() {
				if( $( this ).val() == '' ) {
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_max_number_days' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions1' ).show();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions2' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_tax_handling' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).next().hide();
				} else if( $( this ).val() == 'mwb_rma_maximum_days' ) {
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_max_number_days' ).show();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions1' ).show();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions2' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_tax_handling' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).next().hide();
				} else if ( $( this ).val() == 'mwb_rma_order_status' ) {
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).show();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).next().show();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_tax_handling' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions1' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions2' ).show();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_max_number_days' ).hide();
				} else if ( $( this ).val() == 'mwb_rma_tax_handling' ) {
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_tax_handling' ).show();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).next().hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_max_number_days' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions1' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions_label' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_settings_label' ).hide();
					$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions2' ).hide();
				}
			});
		}
		show_correct_field();

		$(document).on( 'change', '.mwb_rma_settings, .mwb_rma_on_functionality', function() {
			var current_fun = $( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_on_functionality' ).val();
			var current_set = $( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_settings' ).val();;
			var current_set_obj = $( this );
			if( current_set != '' && current_set != null ) {
				output_setting.forEach(function(item) {
					if( current_fun == item.name && item.value != null &&  $.inArray( current_set, item.value ) != -1 ) {
						current_set_obj.parent( '.add_more_rma_policies' ).remove();
					}
				});
			}
		
			if( current_set_obj.val() == '' ) {
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_max_number_days' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions1' ).show();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions2' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_tax_handling' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).next().hide();
			} else if( current_set_obj.val() == 'mwb_rma_maximum_days' ) {
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_max_number_days' ).show();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions1' ).show();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions2' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_tax_handling' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).next().hide();
			} else if ( current_set_obj.val() == 'mwb_rma_order_status' ) {
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).show();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).next().show();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_tax_handling' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions1' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions2' ).show();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_max_number_days' ).hide();
			} else if ( current_set_obj.val() == 'mwb_rma_tax_handling' ) {
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_tax_handling' ).show();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_order_statues' ).next().hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_max_number_days' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions1' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions2' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_conditions_label' ).hide();
				$( this ).parent( '.add_more_rma_policies' ).children( '.mwb_rma_settings_label' ).hide();
			}
			output_setting = [];
			make_register_setting_obj();
		});
		$(document).on( 'submit', '#save_policies_setting_form', function(e) {
			$.each( $(".mwb_rma_settings"), function() {
				if( $( this ).val() == '' ) {
					$( this ).parent( '.add_more_rma_policies' ).remove();
				}
			});
		});
		function escapeRegExp(string){
			return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
		}
				
		/* Define functin to find and replace specified term with replacement string */
		function replaceAll(str, term, replacement) {
				return str.replace(new RegExp(escapeRegExp(term), 'g'), replacement);
		}
		$(document).on( 'click', '#mwb_rma_add_more', function() {
			var mwb_rma_get_current_i = $('.add_more_rma_policies').last().children( '.mwb_rma_get_current_i' ).val();
			mwb_rma_get_current_i = parseInt( mwb_rma_get_current_i ) + 1;
			var append_html = $( '#add_more_rma_policies_clone' ).html();
			append_html = replaceAll( append_html, 'mwb_rma_setting[1]', 'mwb_rma_setting['+ mwb_rma_get_current_i +']' );
			append_html = replaceAll( append_html, 'mwb_rma_order_statues1', 'mwb_rma_order_statues' );
			$('#div_add_more_rma_policies').append( '<div class="add_more_rma_policies">' +append_html + '<input type="button" value="X" class="rma_policy_delete"></div>' );
			$('.add_more_rma_policies').last().children( '.mwb_rma_get_current_i' ).val( mwb_rma_get_current_i );
			$('.mwb_rma_order_statues').select2();
			show_correct_field();
			make_register_setting_obj();
		});

		$(document).on( 'click', '.rma_policy_delete', function() {
			$(this).parent( '.add_more_rma_policies' ).remove();
			show_correct_field();
			make_register_setting_obj();
		});

	
	// order messages from admin related js start.
	$('.mwb_order_msg_notice_wrapper').hide();
	$('.mwb_order_msg_reload_notice_wrapper').hide();
	$( document ).on( 'click', '#mwb_order_msg_submit', function (e) {
		e.preventDefault();
		var up_files = $('#mwb_order_msg_attachment');
		var msg      = $('#mwb_order_new_msg').val();
		var alerthtml = '';
		if ( msg == '' ) {
			alerthtml = '<p class="mwb_order_msg_sent_notice">'+  wrael_admin_param.message_empty +'</p><a href="" class="mwb_remove_notice_msg">X</a>';
					$(".mwb_order_msg_notice_wrapper").addClass('mwb_msg_error');
					$('.mwb_order_msg_notice_wrapper').removeClass('mwb_msg_succuss_notice');
					$(".mwb_order_msg_notice_wrapper").css('display', 'flex');
					$(".mwb_order_msg_notice_wrapper").html(alerthtml);
			return false;
		}
		var order_id = $(this).data("id");

		var form_data = new FormData();

		// Read selected files
		var totalfiles = up_files[0].files.length;
		for (var index = 0; index < totalfiles; index++) {
			 	form_data.append("mwb_order_msg_attachment[]", up_files[0].files[index]);
		}
		form_data.append( "action", 'mwb_rma_order_messages_save' );
		form_data.append( "msg", msg );
		form_data.append( "order_id", order_id );
		form_data.append( "security_check", wrael_admin_param.mwb_rma_nonce );

		// AJAX request
		$.ajax({
			url: wrael_admin_param.ajaxurl, 
			type: 'post',
			data: form_data,
			dataType: 'json',
			contentType: false,
			processData: false,
			success: function ( response ) {
				if( response ) {
					var html = 	'<p class="mwb_order_msg_sent_notice">'+  wrael_admin_param.message_sent +'</p><a href="" class="mwb_remove_notice_msg">X</a>';
						$('.mwb_order_msg_notice_wrapper').addClass('mwb_msg_succuss_notice');
						$('.mwb_order_msg_notice_wrapper').removeClass('mwb_msg_error');
						$('.mwb_order_msg_notice_wrapper').html( html );
						$('.mwb_order_msg_notice_wrapper').css('display', 'flex');
						$('.mwb_admin_order_msg_sub_container').load(document.URL +  ' .mwb_order_msg_main_container');
						$('#mwb_order_new_msg').val("");
						$('#mwb_order_msg_attachment').val('');
				}
			}
		});
	});

	$(document).on('click','.mwb_remove_notice_msg',function(e) {
		e.preventDefault();
		$('.mwb_order_msg_notice_wrapper').hide();
	});

	$(document).on('click','.mwb_wrma_reload_messages',function(e) {
		e.preventDefault();
		$(this).addClass('mwb-loader-icon');
		$('.mwb_admin_order_msg_sub_container').load(document.URL +  ' .mwb_order_msg_main_container');
		setTimeout(function() {
			$('.mwb_wrma_reload_messages').removeClass('mwb-loader-icon');
						$('.mwb_order_msg_reload_notice_wrapper').show();
		}, 2000);
				 setTimeout(function() {
			 $('.mwb_order_msg_reload_notice_wrapper').hide();
		}, 3000);
	});
	// order messages from admin related js start.

	// Refund Request Accept functionality start
	$( ".mwb_rma_return_loader" ).hide();
	$( document ).on( 'click', '#mwb_rma_accept_return', function(){
			$( "#mwb_rma_return_package" ).hide();
			$( ".mwb_rma_return_loader" ).show();
			var orderid = $( this ).data( 'orderid' );
			var date   = $( this ).data( 'date' );
			var data = {
				action:'mwb_rma_return_req_approve',
				orderid:orderid,
				date:date,
				security_check	: wrael_admin_param.mwb_rma_nonce
			};
			$.ajax(
				{
					url: wrael_admin_param.ajaxurl,
					type: "POST",
					data: data,
					dataType :'json',
					success: function(response)
				{
						$( ".mwb_rma_return_loader" ).hide();
						$( ".refund-actions .cancel-action" ).hide();
						window.location.reload();

					}
				}
			);
		}
	);
	});
	// Refund Request Accept functionality end

	// Refund Request Cancel Functionality start
	$( document ).on( 'click', '#mwb_rma_cancel_return', function(){
		$( ".mwb_rma_return_loader" ).show();
		var orderid = $( this ).data( 'orderid' );
		var date = $( this ).data( 'date' );
		var data = {
			action:'mwb_rma_return_req_cancel',
			orderid:orderid,
			date:date,
			security_check	:	wrael_admin_param.mwb_rma_nonce
		};
		$.ajax(
			{
				url: wrael_admin_param.ajaxurl,
				type: "POST",
				data: data,
				dataType :'json',
				success: function(response)
			{
					$( ".mwb_rma_return_loader" ).hide();
					window.location.reload();
				}
		});
	});
	// Refund Request Cancel Functionality  end

	// Refund Amount functionality start
	$( document ).on( 'click', '#mwb_rma_left_amount', function(){
			$( this ).attr( 'disabled','disabled' );
			var check_pro_active = wrael_admin_param.check_pro_active;
			var order_id = $( this ).data( 'orderid' );
			var refund_method = $( this ).data( 'refund_method' );
			var refund_amount = $( ".mwb_rma_total_amount_for_refund" ).val();

			if( refund_method == '' || refund_method == 'manual_method' ) {
				$( 'html, body' ).animate(
					{
						scrollTop: $( "#order_shipping_line_items" ).offset().top
					},
					2000
				);
	
				$( 'div.wc-order-refund-items' ).slideDown();
				$( 'div.wc-order-data-row-toggle' ).not( 'div.wc-order-refund-items' ).slideUp();
				$( 'div.wc-order-totals-items' ).slideUp();
				$( '#woocommerce-order-items' ).find( 'div.refund' ).show();
				$( '.wc-order-edit-line-item .wc-order-edit-line-item-actions' ).hide();
				//var refund_amount = $( "#mwb_rma_refund_amount" ).val();
				var refund_reason = $( "#mwb_rma_refund_reason" ).val();
				$( "#refund_amount" ).val( refund_amount );
				$( "#refund_reason" ).val( refund_reason );
	
				var total = accounting.unformat( refund_amount, woocommerce_admin.mon_decimal_point );
	
				$( 'button .wc-order-refund-amount .amount' ).text(
					accounting.formatMoney(
						total,
						{
							symbol:    woocommerce_admin_meta_boxes.currency_format_symbol,
							decimal:   woocommerce_admin_meta_boxes.currency_format_decimal_sep,
							thousand:  woocommerce_admin_meta_boxes.currency_format_thousand_sep,
							precision: woocommerce_admin_meta_boxes.currency_format_num_decimals,
							format:    woocommerce_admin_meta_boxes.currency_format
						}
					)
				);
			} else {
				if( check_pro_active ) {
					if ( typeof mwb_rma_refund_method_wallet == 'function') {
						var response = mwb_rma_refund_method_wallet( order_id, refund_amount );
						if( response ) {
							window.location.reload();
						}
					}
				}

			}

	});
	// Refund Amount functionality end

	// Manage Stock functionality start
	jQuery( document ).on( 'click', '#mwb_rma_stock_back', function(){
		jQuery( this ).attr( 'disabled','disabled' );
		var order_id = jQuery( this ).data( 'orderid' );
		var type = jQuery( this ).data( 'type' );
		var data = {
			action   : 'mwb_rma_manage_stock' ,
			order_id : order_id ,
			type     : type,
			security_check : wrael_admin_param.mwb_rma_nonce
		};
		console.log( data );
		$.ajax({
			url: wrael_admin_param.ajaxurl,
			type: "POST",
			data: data,
			dataType :'json',
			success: function(response) {
				jQuery( this ).removeAttr( 'disabled' );
				if (response.result) {
					jQuery( "#post" ).prepend( '<div class="updated notice notice-success is-dismissible" id="message"><p>' + response.msg + '</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>' );
					jQuery( 'html, body' ).animate(
						{
							scrollTop: jQuery( "body" ).offset().top
						},
						2000,
						"linear",
						function(){
							window.setTimeout(
								function() {
									window.location.reload();
								},
								1000
							);
						}
					);
				} else {
					jQuery( "#post" ).prepend( '<div id="messege" class="notice notice-error is-dismissible" id="message"><p>' + response.msg + '</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>' );
					jQuery( 'html, body' ).animate(
						{
							scrollTop: jQuery( "body" ).offset().top
						},
						2000,
						"linear",
						function(){
						}
					);
				}
			}
		});
	});
	// Manage Stock functionality end
	$(window).load(function() {
		// add select2 for multiselect.
		if ($(document).find(".mwb-defaut-multiselect").length > 0) {
			$(document)
			.find(".mwb-defaut-multiselect")
			.select2();
		}
	});
})(jQuery);

