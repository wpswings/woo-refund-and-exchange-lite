//code for datatable

jQuery(document).ready(function() {

    jQuery('#wrael-datatable').DataTable({
        stateSave: true,
        dom: '<"wps-dt-buttons"fB>tr<"bottom"lip>',
        "ordering": true, // enable ordering
   
        
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
        ],
        language: {
            "lengthMenu": 'Rows per page _MENU_',

            paginate: { next: '<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.99984 0L0.589844 1.41L5.16984 6L0.589844 10.59L1.99984 12L7.99984 6L1.99984 0Z" fill="#8E908F"/></svg>', previous: '<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.00016 12L7.41016 10.59L2.83016 6L7.41016 1.41L6.00016 -1.23266e-07L0.000156927 6L6.00016 12Z" fill="#8E908F"/></svg>' }
        },
    });
});
jQuery(function($){
	
	const MDCText = mdc.textField.MDCTextField;
	const textField = [].map.call(
		document.querySelectorAll('.mdc-text-field'),
		function(el) {
			return new MDCText(el);
		}
	);
	const MDCRipple = mdc.ripple.MDCRipple;
	const buttonRipple = [].map.call(
		document.querySelectorAll('.mdc-button'),
		function(el) {
			return new MDCRipple(el);
		}
	);
	const MDCSwitch = mdc.switchControl.MDCSwitch;
	const switchControl = [].map.call(
		document.querySelectorAll('.mdc-switch'),
		function(el) {
			return new MDCSwitch(el);
		}
	);
	// add select2 for multiselect.
	if ($(document).find('.wps-defaut-multiselect').length > 0) {
		$(document)
		.find('.wps-defaut-multiselect')
		.select2();
	}
	// Add class in plugin submenu
	$("a[href='admin.php?page=woo_refund_and_exchange_lite_menu']").addClass('submenu-font-size-fix');
	
	$('.wps-password-hidden').click(function() {
		if ($('.wps-form__password').attr('type') == 'text') {
			$('.wps-form__password').attr('type', 'password');
		} else {
			$('.wps-form__password').attr('type', 'text');
		}
	});
	wpsRmaInitPolicySelect2( $( document ) );

	function wpsRmaGetRegisteredPolicies( $excludedRow ) {
		var registeredPolicies = [];

		$.each( $('.add_more_rma_policies'), function() {
			var $row = $( this );
			var functionality;
			var policy;
			var existing;

			if ( $excludedRow && $excludedRow.length && $row.is( $excludedRow ) ) {
				return true;
			}

			functionality = $row.children( '.wps_rma_on_functionality' ).val();
			policy = $row.children( '.wps_rma_settings' ).val();

			if ( ! functionality || ! policy ) {
				return true;
			}

			existing = registeredPolicies.filter( function( item ) {
				return item.name === functionality;
			} );

			if ( existing.length ) {
				existing[0].value.push( policy );
			} else {
				registeredPolicies.push( {
					name: functionality,
					value: [ policy ]
				} );
			}
		} );

		return registeredPolicies;
	}

	function wpsRmaPolicyAlreadyExists( $row, functionality, policy ) {
		var duplicatePolicy = false;

		if ( ! functionality || ! policy ) {
			return duplicatePolicy;
		}

		$.each( wpsRmaGetRegisteredPolicies( $row ), function( index, item ) {
			if ( functionality === item.name && $.inArray( policy, item.value ) !== -1 ) {
				duplicatePolicy = true;
				return false;
			}
		} );

		return duplicatePolicy;
	}
	function wpsRmaInitPolicySelect2( $scope ) {
		if ( ! $.fn.select2 ) {
			return;
		}

		$scope.find( '.wps_rma_order_statues, .wps_rma_ex_cate, .wps_rma_ex_prod' ).each( function() {
			var $element = $( this );
			var shouldInit = ! $element.prop( 'disabled' ) && $element.is( ':visible' );

			if ( ! shouldInit ) {
				if ( $element.hasClass( 'select2-hidden-accessible' ) ) {
					$element.select2( 'destroy' );
				}
				$element.nextAll( '.select2-container' ).first().hide();
				return;
			}

			if ( $element.hasClass( 'select2-hidden-accessible' ) ) {
				$element.nextAll( '.select2-container' ).first().show();
				return;
			}

			$element.select2( {
				width: '100%'
			} );
		} );
	}

	function wpsRmaTogglePolicyField( $row, selector, shouldShow ) {
		var $field = $row.children( selector );
		var isMultiSelect = $field.is( 'select[multiple]' );

		if ( ! $field.length ) {
			return;
		}

		$field.prop( 'disabled', ! shouldShow );
		$field.toggle( shouldShow );

		if ( ! shouldShow && isMultiSelect && $field.hasClass( 'select2-hidden-accessible' ) ) {
			$field.select2( 'destroy' );
		}

		if ( shouldShow && $.fn.select2 && isMultiSelect && ! $field.hasClass( 'select2-hidden-accessible' ) ) {
			$field.select2( {
				width: '100%'
			} );
		}

		$field.nextAll( '.select2-container' ).first().toggle( shouldShow );
	}

	function wpsRmaTogglePolicyRow( $row ) {
		var policy = $row.children( '.wps_rma_settings' ).val();
		var showMaxNumber = 'wps_rma_maximum_days' === policy || 'wps_rma_min_order' === policy;
		var showOrderStatuses = 'wps_rma_order_status' === policy;
		var showTaxHandling = 'wps_rma_tax_handling' === policy;
		var showConditionOne = '' === policy || 'wps_rma_maximum_days' === policy || 'wps_rma_min_order' === policy;
		var showConditionTwo = 'wps_rma_order_status' === policy || 'wps_rma_tax_handling' === policy || 'wps_rma_exclude_via_categories' === policy || 'wps_rma_exclude_via_products' === policy;

		wpsRmaTogglePolicyField( $row, '.wps_rma_max_number_days', showMaxNumber );
		wpsRmaTogglePolicyField( $row, '.wps_rma_order_statues', showOrderStatuses );
		wpsRmaTogglePolicyField( $row, '.wps_rma_tax_handling', showTaxHandling );
		$row.children( '.wps_rma_conditions1' ).toggle( showConditionOne );
		$row.children( '.wps_rma_conditions2' ).toggle( showConditionTwo );
		$row.children( '.wps_rma_conditions_label, .wps_rma_settings_label' ).show();
	}

	// Function to show correct setting respective selected setting.
	function show_correct_field(){
		$.each( $('.add_more_rma_policies'), function() {
			wpsRmaTogglePolicyRow( $( this ) );
		});

		if ( 'function' === typeof show_correct_field_pro ) {
			show_correct_field_pro( '' );
		}
	}
	window.wpsRmaInitPolicySelect2 = wpsRmaInitPolicySelect2;
	window.show_correct_field = show_correct_field;
	show_correct_field();
	// show correct setting respective selected setting and if remove if setting already is exist and also show an alert.
	$(document).on( 'change', '.wps_rma_settings, .wps_rma_on_functionality', function() {
		var $currentRow = $( this ).parent( '.add_more_rma_policies' );
		var current_fun = $currentRow.children( '.wps_rma_on_functionality' ).val();
		var current_set = $currentRow.children( '.wps_rma_settings' ).val();

		if ( wpsRmaPolicyAlreadyExists( $currentRow, current_fun, current_set ) ) {
			alert( wrael_admin_param.wps_policy_already_exist );
			$currentRow.remove();
			show_correct_field();
			return;
		}
	
		wpsRmaTogglePolicyRow( $currentRow );

		if ( 'function' === typeof show_correct_field_pro ) {
			show_correct_field_pro( '' );
		}
	});
	// Remove due to empty field.
	$(document).on( 'submit', '#save_policies_setting_form', function(e) {
		$.each( $(".wps_rma_settings"), function() {
			if( $( this ).val() == '' ) {
				$( this ).parent( '.add_more_rma_policies' ).remove();
			}
		});
	});

	// Replace function.
	function escapeRegExp(string){
		return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
	}		
	/* Define function to find and replace specified term with replacement string */
	function replaceAll(str, term, replacement) {
			return str.replace(new RegExp(escapeRegExp(term), 'g'), replacement);
	}
	// Add extra row setting and do the useful functionality.
	$(document).on( 'click', '#wps_rma_add_more', function() {
		var pro_act = wrael_admin_param.check_pro_active;
		var wps_rma_get_current_i = $('.add_more_rma_policies').last().children( '.wps_rma_get_current_i' ).val();
		wps_rma_get_current_i = parseInt( wps_rma_get_current_i ) + 1;
		var append_html = $( '#add_more_rma_policies_clone' ).html();
		append_html = replaceAll( append_html, 'wps_rma_setting[1]', 'wps_rma_setting['+ wps_rma_get_current_i +']' );
		append_html = replaceAll( append_html, 'wps_rma_order_statues1', 'wps_rma_order_statues' );
		if( pro_act ) {
			append_html = show_correct_field_pro( append_html );
		}
		$('#div_add_more_rma_policies').append( '<div class="add_more_rma_policies">' +append_html + '<input type="button" value="X" class="rma_policy_delete"></div>' );
		var $newPolicyRow = $('.add_more_rma_policies').last();
		$newPolicyRow.children( '.wps_rma_get_current_i' ).val( wps_rma_get_current_i );
		wpsRmaInitPolicySelect2( $newPolicyRow );
		if( 'function' === typeof wps_rma_do_something && pro_act ) {
			wps_rma_do_something();
		}
		show_correct_field();
	});
	// Delete selected row.
	$(document).on( 'click', '.rma_policy_delete', function() {
		$(this).parent( '.add_more_rma_policies' ).remove();
		show_correct_field();
	});
	// Refund Request Accept functionality
	$( '.wps_rma_return_loader' ).hide(); // Hide the loader in the refund request metabox
	$( document ).on( 'click', '#wps_rma_accept_return', function(){
			$( '#wps_rma_return_package' ).hide();
			$( '.wps_rma_return_loader' ).show();
			var orderid = $( this ).data( 'orderid' );
			var date   = $( this ).data( 'date' );
			var data = {
				action:'wps_rma_return_req_approve',
				orderid:orderid,
				date:date,
				security_check	: wrael_admin_param.wps_rma_nonce
			};
			$.ajax(
				{
					url: wrael_admin_param.ajaxurl,
					type: 'POST',
					data: data,
					dataType :'json',
					success: function(response) {
						$( '.wps_rma_return_loader' ).hide();
						$( '.refund-actions .cancel-action' ).hide();
						window.location.reload();
					}
				}
			);
		}
	);
	// Refund Request Cancel Functionality
	$( document ).on( 'click', '#wps_rma_cancel_return', function(){
		$( '.wps_rma_return_loader' ).show();
		var orderid = $( this ).data( 'orderid' );
		var date = $( this ).data( 'date' );
		var data = {
			action:'wps_rma_return_req_cancel',
			orderid:orderid,
			date:date,
			security_check	:	wrael_admin_param.wps_rma_nonce
		};
		$.ajax(
			{
				url: wrael_admin_param.ajaxurl,
				type: 'POST',
				data: data,
				dataType :'json',
				success: function(response){
					$( '.wps_rma_return_loader' ).hide();
					window.location.reload();
				}
		});
	});
	// Refund Amount functionality
	$( document ).on( 'click', '#wps_rma_left_amount', function(){
		$( this ).attr( 'disabled','disabled' );
		var order_id = $( this ).data( 'orderid' );
		var refund_method = $( this ).data( 'refund_method' );
		var refund_amount = $( '.wps_rma_total_amount_for_refund' ).val();
		$( '.wps_rma_return_loader' ).show();
		var data = {
			action:'wps_rma_refund_amount',
			order_id:order_id,
			refund_method:refund_method,
			refund_amount:refund_amount,
			security_check	:wrael_admin_param.wps_rma_nonce	
		};
		$.ajax({
			url: wrael_admin_param.ajaxurl, 
			type: 'POST',  
			data: data,
			dataType :'json',	
			success: function(response) {
				$( '.wps_rma_return_loader' ).show();
				if ( response.refund_method == '' || response.refund_method == 'manual_method' ) {
					$( 'html, body' ).animate(
						{
							scrollTop: $( '#order_shipping_line_items' ).offset().top
						},
						2000
					);
					
					$( 'div.wc-order-refund-items' ).slideDown();
					$( 'div.wc-order-data-row-toggle' ).not( 'div.wc-order-refund-items' ).slideUp();
					$( '#woocommerce-order-items' ).find( 'div.refund' ).show();
					$( 'div.wc-order-totals-items' ).slideUp();
	
					var refund_reason = $( '#wps_rma_refund_reason' ).val();
					$( '#refund_amount' ).val( refund_amount );
					$( '#refund_reason' ).val( refund_reason );
		
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
					window.location.reload();
				}
			}
		});
	});
	// Manage Stock functionality start
	$( document ).on( 'click', '#wps_rma_stock_back', function(){
		$( this ).attr( 'disabled','disabled' );
		var order_id = $( this ).data( 'orderid' );
		var type = $( this ).data( 'type' );
		var data = {
			action   : 'wps_rma_manage_stock' ,
			order_id : order_id ,
			type     : type,
			security_check : wrael_admin_param.wps_rma_nonce
		};
		$.ajax({
			url: wrael_admin_param.ajaxurl,
			type: 'POST',
			data: data,
			dataType :'json',
			success: function(response) {
				$( this ).removeAttr( 'disabled' );
				if (response.result) {
					$( '#post' ).prepend( '<div class="updated notice notice-success is-dismissible" id="message"><p>' + response.msg + '</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>' );
					$( 'html, body' ).animate(
						{
							scrollTop: $( 'body' ).offset().top
						},
						2000,
						'linear',
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
					$( '#post' ).prepend( '<div id="messege" class="notice notice-error is-dismissible" id="message"><p>' + response.msg + '</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>' );
					$( 'html, body' ).animate(
						{
							scrollTop: jQuer$( 'body' ).offset().top
						},
						2000,
						'linear',
						function(){
						}
					);
				}
			}
		});
	});
	// Regenerate Api Secret key
	$( document ).on( 'click', '#wps_rma_generate_key_setting', function(e){
		e.preventDefault();
		var data = {
			action:'wps_rma_api_secret_key',
			security_check	: wrael_admin_param.wps_rma_nonce
		};
		$.ajax(
		{
			url: wrael_admin_param.ajaxurl,
			type: 'POST',
			data: data,
			dataType :'json',
			success: function(response) {
				window.location.reload();
			}
		});
	});
	// pro setting tag
	setTimeout(function(){
		jQuery('.wps_rma_pro_class').parents('.wps-form-group').addClass('wps_rma_pro_class_wrap');
	},1)

	$('#wps_wrma_ship_products').select2();
	if ( $( '#wps_enable_ship_setting' ).is( ':checked' ) ) {
		$('#add_fee').show();
	}else{
	$('#add_fee').hide();
	}
	$('#wps_enable_ship_setting').on( 'change' , function(){
	if ( $( '#wps_enable_ship_setting' ).is( ':checked' ) ) {
		$('#add_fee').show();
	}else{
		$('#add_fee').hide();
	}
	});


	// Integration tab setting code start
	$( '.wps_rma_shipping_label_setting' ).show();
	$( '.wps_rma_shipping_setting' ).hide();
	$( '.wps_rma_shiprocket_setting').hide();
	$( '.show_returnship_label' ).addClass('shipClass');
	$('.wps_wrma_return_loader').hide();
	$('.wps_wrma_returnship_loader').hide();
	$( '.wps_rma_shiprocket_setting').hide();

	$( document ).on( 'click', '.show_returnship_label', function() {
		$( '.wps_rma_shipping_label_setting' ).show();
	  $( '.show_returnship_label' ).addClass('shipClass');
	  $( '.show_shipintegration' ).removeClass('shipClass');
	  $( '.show_shiprocketintegration' ).removeClass('shipClass');
		$( '.wps_rma_shipping_setting' ).hide();
		$( '.wps_rma_shiprocket_setting').hide();
	  });
	  $( document ).on( 'click', '.show_shipintegration', function() {
		  $( '.wps_rma_shipping_setting' ).show();
		$( '.show_shipintegration' ).addClass('shipClass');
		$( '.show_returnship_label' ).removeClass('shipClass');
		$( '.show_shiprocketintegration' ).removeClass('shipClass');
		  $( '.wps_rma_shipping_label_setting' ).hide();
		  $( '.wps_rma_shiprocket_setting').hide();
	  });
	  
	  $( document ).on( 'click', '.show_shiprocketintegration', function() {
		$( '.wps_rma_shiprocket_setting' ).show();
	  $( '.show_shiprocketintegration' ).addClass('shipClass');
	  $( '.show_returnship_label' ).removeClass('shipClass');
	  $( '.show_shipintegration' ).removeClass('shipClass');
		$( '.wps_rma_shipping_label_setting' ).hide();
		$( '.wps_rma_shipping_setting').hide();
	  });
	  
	  $(".button_wps_rma_pro_class").parent('button').prop( "disabled", true );
	  $(".button_wps_rma_pro_div").css( "background-color", 'rgba(0,0,0,.12)' );

	// PRO popup start
	$('.wps_rma_pro_class_wrap label,.wps_rma_pro_div label').attr('for', '');

	$(document).on('click', '.wps_rma_pro_class_wrap,.wps_rma_pro_div', function() {
		$('.wps-rma__popup-for-pro-shadow').show();
		$('.wps-rma__popup-for-pro').addClass('active-pro');
	})

	$(document).on('click', '.wps-rma__popup-for-pro-close', function() {
		$('.wps-rma__popup-for-pro-shadow').hide();
		$('.wps-rma__popup-for-pro').removeClass('active-pro');
	})

	$(document).on('click', '.wps-rma__popup-for-pro-shadow', function() {
		$(this).hide();
		$('.wps-rma__popup-for-pro').removeClass('active-pro');
	})

	$(document).on('click', '.wps_go_pro_link', function(e) {
		e.preventDefault();
		$('.wps-rma__popup-for-pro-shadow').show();
		$('.wps-rma__popup-for-pro').addClass('active-pro');
	})
	$('.button_wps_rma_pro_div').css('pointer-events','none');

	if ( $( '.wps_rma_date_time_picker1' ).length > 0 ) {
		$( '.wps_rma_date_time_picker1' ).timepicker({
			showPeriod: true,
		    showLeadingZero: true,
	    });
	}
	if ( $( '.wps_rma_date_time_picker2' ).length > 0 ) {
		$( '.wps_rma_date_time_picker2' ).timepicker({
			showPeriod: true,
		    showLeadingZero: true,
	    });
	}

	// Function to get URL parameters
	function getUrlParameter(name) {
		// Create a regular expression to find the parameter in the URL
		var regex = new RegExp('[?&]' + name + '=([^&]*)');
		var results = regex.exec(window.location.search);
		return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
	}
	$(document).on('click' , '.wps-rma-api-container .rma-api-section h2', function () {
		console.log('test');
		$(this).parent().toggleClass('open');
	});
});

jQuery(function($){
	if ( 'undefined' === typeof window.history || ! $( '.wps-rma-shell__frame' ).length ) {
		return;
	}

	var wpsRmaDynamicTabLoading = false;
	var wpsRmaDynamicTabSelector = '.wps-rma-shell__tab-link[href*="wrael_tab="], .wps-rma-shell__overflow-link[href*="wrael_tab="]';
	var wpsRmaDashboardTableOptions = {
		stateSave: true,
		dom: '<"wps-dt-buttons"fB>tr<"bottom"lip>',
		ordering: true,
		buttons: [ 'copyHtml5', 'excelHtml5', 'csvHtml5' ],
		language: {
			lengthMenu: 'Rows per page _MENU_',
			paginate: {
				next: '<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M1.99984 0L0.589844 1.41L5.16984 6L0.589844 10.59L1.99984 12L7.99984 6L1.99984 0Z" fill="#8E908F"/></svg>',
				previous: '<svg width="8" height="12" viewBox="0 0 8 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6.00016 12L7.41016 10.59L2.83016 6L7.41016 1.41L6.00016 -1.23266e-07L0.000156927 6L6.00016 12Z" fill="#8E908F"/></svg>'
			}
		}
	};

	function wpsRmaInitDashboardMdc( $scope ) {
		if ( 'undefined' === typeof mdc ) {
			return;
		}

		if ( mdc.textField && mdc.textField.MDCTextField ) {
			[].forEach.call( $scope[0].querySelectorAll( '.mdc-text-field' ), function( element ) {
				if ( ! element.dataset.wpsRmaMdcTextInit ) {
					new mdc.textField.MDCTextField( element );
					element.dataset.wpsRmaMdcTextInit = 'true';
				}
			} );
		}

		if ( mdc.ripple && mdc.ripple.MDCRipple ) {
			[].forEach.call( $scope[0].querySelectorAll( '.mdc-button' ), function( element ) {
				if ( ! element.dataset.wpsRmaMdcRippleInit ) {
					new mdc.ripple.MDCRipple( element );
					element.dataset.wpsRmaMdcRippleInit = 'true';
				}
			} );
		}

		if ( mdc.switchControl && mdc.switchControl.MDCSwitch ) {
			[].forEach.call( $scope[0].querySelectorAll( '.mdc-switch' ), function( element ) {
				if ( ! element.dataset.wpsRmaMdcSwitchInit ) {
					new mdc.switchControl.MDCSwitch( element );
					element.dataset.wpsRmaMdcSwitchInit = 'true';
				}
			} );
		}
	}

	function wpsRmaInitDashboardSelect2( $scope ) {
		if ( ! $.fn.select2 ) {
			return;
		}

		$scope.find( '.wps-defaut-multiselect, .wps_rma_order_statues, .wps_rma_ex_cate, .wps_rma_ex_prod, #wps_wrma_ship_products' ).each( function() {
			var $element = $( this );

			if ( $element.hasClass( 'select2-hidden-accessible' ) ) {
				return;
			}

			$element.select2( {
				width: '100%'
			} );
		} );
	}

		function wpsRmaInitPolicyRows( $scope ) {
			if ( ! $scope.find( '#save_policies_setting_form' ).length ) {
				return;
			}

			if ( 'function' === typeof window.wpsRmaInitPolicySelect2 ) {
				window.wpsRmaInitPolicySelect2( $scope );
			}
			if ( 'function' === typeof window.show_correct_field ) {
				window.show_correct_field();
			}

			if ( 'function' === typeof wps_rma_do_something ) {
				wps_rma_do_something();
			}
	}

	function wpsRmaInitDashboardDataTable( $scope ) {
		var $table = $scope.find( '#wrael-datatable' );

		if ( ! $table.length || ! $.fn.DataTable || ( $.fn.dataTable && $.fn.dataTable.isDataTable( $table[0] ) ) ) {
			return;
		}

		$table.DataTable( wpsRmaDashboardTableOptions );
	}

	function wpsRmaInitDashboardState( $scope ) {
		setTimeout( function() {
			$scope.find( '.wps_rma_pro_class' ).parents( '.wps-form-group' ).addClass( 'wps_rma_pro_class_wrap' );
		}, 1 );

		$scope.find( '.wps_wrma_return_loader, .wps_wrma_returnship_loader' ).hide();
		$scope.find( '.wps_rma_shipping_label_setting' ).show();
		$scope.find( '.wps_rma_shipping_setting, .wps_rma_shiprocket_setting' ).hide();
		$scope.find( '.show_returnship_label' ).addClass( 'shipClass' );
		$scope.find( '.button_wps_rma_pro_class' ).parent( 'button' ).prop( 'disabled', true );
		$scope.find( '.button_wps_rma_pro_div' ).css( {
			'background-color': 'rgba(0,0,0,.12)',
			'pointer-events': 'none'
		} );
		$scope.find( '.wps_rma_pro_class_wrap label, .wps_rma_pro_div label' ).attr( 'for', '' );

		if ( $scope.find( '#wps_enable_ship_setting' ).is( ':checked' ) ) {
			$scope.find( '#add_fee' ).show();
		} else {
			$scope.find( '#add_fee' ).hide();
		}

		if ( $.fn.timepicker ) {
			$scope.find( '.wps_rma_date_time_picker1' ).timepicker( {
				showPeriod: true,
				showLeadingZero: true
			} );
			$scope.find( '.wps_rma_date_time_picker2' ).timepicker( {
				showPeriod: true,
				showLeadingZero: true
			} );
		}
	}

	function wpsRmaInitDynamicFrame( $scope ) {
		if ( ! $scope.length ) {
			return;
		}

		wpsRmaInitDashboardMdc( $scope );
		wpsRmaInitDashboardSelect2( $scope );
		wpsRmaInitDashboardDataTable( $scope );
		wpsRmaInitDashboardState( $scope );
		wpsRmaInitPolicyRows( $scope );
	}

	function wpsRmaUpdatePasswordField() {
		var $passwordField = $( '.wps-form__password' );

		if ( ! $passwordField.length ) {
			return;
		}

		$passwordField.attr( 'type', 'text' === $passwordField.attr( 'type' ) ? 'password' : 'text' );
	}

	function wpsRmaFetchDashboardTab( url, shouldPushState ) {
		var $currentFrame = $( '.wps-rma-shell__frame' ).first();

		if ( ! $currentFrame.length || wpsRmaDynamicTabLoading ) {
			return;
		}

		if ( url === window.location.href ) {
			return;
		}

		wpsRmaDynamicTabLoading = true;
		$currentFrame.addClass( 'wps-rma-shell__frame--loading' );

		$.get( url ).done( function( response ) {
			var $response = $( '<div />' ).append( $.parseHTML( response, document, true ) );
			var $newFrame = $response.find( '.wps-rma-shell__frame' ).first();
			var newTitle = $response.filter( 'title' ).text() || $response.find( 'title' ).first().text();

			if ( ! $newFrame.length ) {
				window.location.href = url;
				return;
			}

			$currentFrame.replaceWith( $newFrame );
			wpsRmaInitDynamicFrame( $newFrame );

			if ( shouldPushState ) {
				window.history.pushState( { wpsRmaTabUrl: url }, '', url );
			}

			if ( newTitle ) {
				document.title = newTitle;
			}

			if ( $newFrame.offset() ) {
				window.scrollTo( 0, Math.max( $newFrame.offset().top - 24, 0 ) );
			}

			// Notify any listeners (e.g. the floating save bar) that new tab
			// content is live so they can re-check button visibility.
			$( document ).trigger( 'wps:rma:tabLoaded' );
		} ).fail( function() {
			window.location.href = url;
		} ).always( function() {
			wpsRmaDynamicTabLoading = false;
			$( '.wps-rma-shell__frame' ).first().removeClass( 'wps-rma-shell__frame--loading' );
		} );
	}

	$( document ).off( 'click.wpsRmaDynamicTabs', '.wps-password-hidden' ).on( 'click.wpsRmaDynamicTabs', '.wps-password-hidden', function() {
		wpsRmaUpdatePasswordField();
	} );

	$( document ).off( 'change.wpsRmaDynamicTabs', '#wps_enable_ship_setting' ).on( 'change.wpsRmaDynamicTabs', '#wps_enable_ship_setting', function() {
		var $container = $( this ).closest( '.wps-rma-shell__surface, body' );
		$container.find( '#add_fee' )[ $( this ).is( ':checked' ) ? 'show' : 'hide' ]();
	} );

	$( document ).off( 'click.wpsRmaDynamicTabs', wpsRmaDynamicTabSelector ).on( 'click.wpsRmaDynamicTabs', wpsRmaDynamicTabSelector, function( event ) {
		if ( event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || 1 !== event.which ) {
			return;
		}

		event.preventDefault();
		wpsRmaFetchDashboardTab( $( this ).attr( 'href' ), true );
	} );

	$( window ).off( 'popstate.wpsRmaDynamicTabs' ).on( 'popstate.wpsRmaDynamicTabs', function( event ) {
		if ( ! $( '.wps-rma-shell__frame' ).length ) {
			return;
		}

		var targetUrl = event.originalEvent.state && event.originalEvent.state.wpsRmaTabUrl ? event.originalEvent.state.wpsRmaTabUrl : window.location.href;
		wpsRmaFetchDashboardTab( targetUrl, false );
	} );

	window.history.replaceState( { wpsRmaTabUrl: window.location.href }, '', window.location.href );
	wpsRmaInitDynamicFrame( $( '.wps-rma-shell__frame' ).first() );
});

jQuery(function($) {
	var wpsRmaAuroraTemplateSettings = {
		wps_rma_return_template_css: [
			'wps_rma_refund_background_color',
			'wps_rma_refund_surface_color',
			'wps_rma_refund_accent_color',
			'wps_rma_refund_text_color',
			'wps_rma_refund_button_text_color'
		],
		wps_rma_exchange_template_css: [
			'wps_rma_exchange_background_color',
			'wps_rma_exchange_surface_color',
			'wps_rma_exchange_accent_color',
			'wps_rma_exchange_text_color',
			'wps_rma_exchange_button_text_color'
		],
		wps_rma_cancel_template_css: [
			'wps_rma_cancel_background_color',
			'wps_rma_cancel_surface_color',
			'wps_rma_cancel_accent_color',
			'wps_rma_cancel_text_color',
			'wps_rma_cancel_button_text_color'
		],
		wps_rma_order_msg_template_css: [
			'wps_rma_order_msg_background_color',
			'wps_rma_order_msg_surface_color',
			'wps_rma_order_msg_accent_color',
			'wps_rma_order_msg_text_color',
			'wps_rma_order_msg_button_text_color'
		]
	};
	var wpsRmaAuroraTemplateSelector = Object.keys( wpsRmaAuroraTemplateSettings ).map( function( fieldName ) {
		return 'input[name="' + fieldName + '"]';
	} ).join( ', ' );
	var wpsRmaAuroraToggleTimer = null;

	function wpsRmaGetAuroraFieldWrapper( $field ) {
		var $wrapper = $field.closest( 'tr' );

		if ( $wrapper.length ) {
			return $wrapper;
		}

		$wrapper = $field.closest( '.wps-form-group' );

		if ( $wrapper.length ) {
			return $wrapper;
		}

		$wrapper = $field.closest( '.forminp' );

		if ( $wrapper.length ) {
			return $wrapper;
		}

		return $field.parent();
	}

	function wpsRmaToggleAuroraCustomizer( $scope ) {
		$scope = $scope && $scope.length ? $scope : $( document );

		$.each( wpsRmaAuroraTemplateSettings, function( templateField, dependentFields ) {
			var $checkedTemplate = $scope.find( 'input[name="' + templateField + '"]:checked' );

			if ( ! $checkedTemplate.length ) {
				$checkedTemplate = $( 'input[name="' + templateField + '"]:checked' );
			}

			if ( ! $checkedTemplate.length ) {
				return;
			}

			$.each( dependentFields, function( _, dependentField ) {
				var $field = $scope.find( '#' + dependentField );

				if ( ! $field.length ) {
					$field = $( '#' + dependentField );
				}

				if ( ! $field.length ) {
					return;
				}

				wpsRmaGetAuroraFieldWrapper( $field ).toggle( 'template2' === $checkedTemplate.val() );
			} );
		} );
	}

	function wpsRmaScheduleAuroraCustomizer() {
		clearTimeout( wpsRmaAuroraToggleTimer );
		wpsRmaAuroraToggleTimer = setTimeout( function() {
			var $frame = $( '.wps-rma-shell__frame' ).first();
			wpsRmaToggleAuroraCustomizer( $frame.length ? $frame : $( document ) );
		}, 0 );
	}

	$( document ).off( 'change.wpsRmaAuroraCustomizer', wpsRmaAuroraTemplateSelector ).on( 'change.wpsRmaAuroraCustomizer', wpsRmaAuroraTemplateSelector, function() {
		var $container = $( this ).closest( '.wps-rma-shell__frame, .wrap' );
		wpsRmaToggleAuroraCustomizer( $container.length ? $container : $( document ) );
	} );

	if ( 'MutationObserver' in window && document.body ) {
		new MutationObserver( function( mutations ) {
			for ( var i = 0; i < mutations.length; i++ ) {
				if ( mutations[ i ].addedNodes.length || mutations[ i ].removedNodes.length ) {
					wpsRmaScheduleAuroraCustomizer();
					break;
				}
			}
		} ).observe( document.body, {
			childList: true,
			subtree: true
		} );
	}

	window.wpsRmaToggleAuroraCustomizer = wpsRmaToggleAuroraCustomizer;
	wpsRmaScheduleAuroraCustomizer();
});

jQuery(function($) {
	var modalSelector = '[data-wrael-expert-modal]';
	var openTriggerSelector = '[data-wrael-open-expert-modal]';
	var closeTriggerSelector = '[data-wrael-expert-modal-close]';
	var formSelector = '[data-wrael-expert-modal-form]';
	var statusSelector = '[data-wrael-expert-modal-status]';
	var successSelector = '[data-wrael-expert-modal-success]';
	var successMessageSelector = '[data-wrael-expert-modal-success-message]';
	var bodyLockClass = 'wps-rma-expert-modal-open';
	var successCloseTimer = null;

	function wpsRmaGetExpertModal() {
		return $( modalSelector ).first();
	}

	function wpsRmaSetExpertStatus( $modal, message, statusType ) {
		var $status = $modal.find( statusSelector ).first();

		if ( ! $status.length ) {
			return;
		}

		if ( ! message ) {
			$status.attr( 'hidden', true ).removeClass( 'is-success is-error' ).text( '' );
			return;
		}

		$status.removeAttr( 'hidden' ).removeClass( 'is-success is-error' ).addClass( 'is-' + statusType ).text( message );
	}

	function wpsRmaResetExpertModalState( $modal ) {
		var $form = $modal.find( formSelector ).first();
		var $success = $modal.find( successSelector ).first();
		var $successMessage = $modal.find( successMessageSelector ).first();
		var $submitButton = $form.find( 'button[type="submit"]' ).first();

		if ( $form.length ) {
			if ( $form.get( 0 ) && 'function' === typeof $form.get( 0 ).reset ) {
				$form.get( 0 ).reset();
			}

			$form.removeAttr( 'hidden' );
		}

		if ( $submitButton.length ) {
			$submitButton
				.prop( 'disabled', false )
				.text( $submitButton.attr( 'data-submit-label' ) || 'Submit Request' );
		}

		if ( $success.length ) {
			$success.attr( 'hidden', true ).removeClass( 'is-visible' );
		}

		if ( $successMessage.length ) {
			$successMessage.text( 'Thank you for submitting your request.' );
		}

		wpsRmaSetExpertStatus( $modal, '', '' );
	}

	function wpsRmaShowExpertSuccessState( $modal, message ) {
		var $form = $modal.find( formSelector ).first();
		var $success = $modal.find( successSelector ).first();
		var $successMessage = $modal.find( successMessageSelector ).first();

		if ( $form.length ) {
			$form.attr( 'hidden', true );
		}

		wpsRmaSetExpertStatus( $modal, '', '' );

		if ( $successMessage.length ) {
			$successMessage.text( message );
		}

		if ( $success.length ) {
			$success.removeAttr( 'hidden' );

			window.setTimeout( function() {
				$success.addClass( 'is-visible' );
			}, 20 );
		}
	}

	function wpsRmaToggleExpertModal( shouldOpen ) {
		var $modal = wpsRmaGetExpertModal();

		if ( ! $modal.length ) {
			return;
		}

		if ( successCloseTimer ) {
			window.clearTimeout( successCloseTimer );
			successCloseTimer = null;
		}

		if ( shouldOpen ) {
			$modal.removeAttr( 'hidden' );
			$( 'body' ).addClass( bodyLockClass );
			wpsRmaResetExpertModalState( $modal );
			return;
		}

		$modal.attr( 'hidden', true );
		$( 'body' ).removeClass( bodyLockClass );
		wpsRmaResetExpertModalState( $modal );
	}

	function wpsRmaNormalizeExpertPayload( formElement ) {
		var payload = {};
		var formData = new window.FormData( formElement );

		formData.forEach( function( value, key ) {
			var normalizedKey = key.replace( /\[\]$/, '' );

			if ( Object.prototype.hasOwnProperty.call( payload, normalizedKey ) ) {
				if ( ! Array.isArray( payload[ normalizedKey ] ) ) {
					payload[ normalizedKey ] = [ payload[ normalizedKey ] ];
				}

				payload[ normalizedKey ].push( value );
				return;
			}

			payload[ normalizedKey ] = value;
		} );

		return payload;
	}

	$( document ).off( 'click.wraelExpertModalOpen', openTriggerSelector ).on( 'click.wraelExpertModalOpen', openTriggerSelector, function(event) {
		event.preventDefault();
		wpsRmaToggleExpertModal( true );
	} );

	$( document ).off( 'click.wraelExpertModalClose', closeTriggerSelector ).on( 'click.wraelExpertModalClose', closeTriggerSelector, function(event) {
		event.preventDefault();
		wpsRmaToggleExpertModal( false );
	} );

	$( document ).off( 'keydown.wraelExpertModal' ).on( 'keydown.wraelExpertModal', function(event) {
		if ( 'Escape' === event.key ) {
			wpsRmaToggleExpertModal( false );
		}
	} );

	$( document ).off( 'submit.wraelExpertModal', formSelector ).on( 'submit.wraelExpertModal', formSelector, function(event) {
		var $form = $( this );
		var $modal = $form.closest( modalSelector );
		var $submitButton = $form.find( 'button[type="submit"]' ).first();
		var submitLabel = $submitButton.attr( 'data-submit-label' ) || $submitButton.text();
		var loadingLabel = $submitButton.attr( 'data-loading-label' ) || 'Sending...';

		event.preventDefault();
		wpsRmaSetExpertStatus( $modal, '', '' );
		$submitButton.prop( 'disabled', true ).text( loadingLabel );

		$.ajax( {
			url: wrael_admin_param.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: wrael_admin_param.wrael_expert_action,
				nonce: wrael_admin_param.wrael_expert_nonce,
				form_data: JSON.stringify( wpsRmaNormalizeExpertPayload( $form.get( 0 ) ) )
			}
		} ).done( function( response ) {
			var isSuccess = !! ( response && response.success );
			var message = response && response.data && response.data.message ? response.data.message : '';

			if ( ! message ) {
				message = isSuccess ? 'Thank you for submitting your request.' : 'We could not submit your request right now. Please try again.';
			}

			if ( isSuccess && message ) {
				wpsRmaShowExpertSuccessState( $modal, message );

				successCloseTimer = window.setTimeout( function() {
					wpsRmaToggleExpertModal( false );
				}, 3000 );
				return;
			}

			wpsRmaSetExpertStatus( $modal, message, 'error' );
		} ).fail( function( xhr ) {
			var message = 'We could not submit your request right now. Please try again.';

			if ( xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ) {
				message = xhr.responseJSON.data.message;
			}

			wpsRmaSetExpertStatus( $modal, message, 'error' );
		} ).always( function() {
			$submitButton.prop( 'disabled', false ).text( submitLabel );
		} );
	} );

	// Floating Save Setting bar — shows when the real submit button is out of the viewport.
	(function () {
		if ( ! $( '.wps-rma-shell__surface' ).length ) return;

		// Always re-query: tab switches replace the entire .wps-rma-shell__frame in the
		// DOM, so a cached $surface reference becomes stale after every tab load.
		function getSurface() {
			return $( '.wps-rma-shell__surface' );
		}

		var params = ( typeof wrael_admin_param !== 'undefined' ) ? wrael_admin_param : {};
		var $floatingBar = $(
			'<div class="wps-rma-floating-save" role="complementary" aria-label="Floating save">' +
				'<button type="button" class="wps-rma-floating-save__btn button button-primary">' + ( params.floating_save_btn || 'Save Setting' ) + '</button>' +
			'</div>'
		);
		$( 'body' ).append( $floatingBar );

		function getSubmitBtns() {
			// Include: explicit submit inputs AND buttons without type (defaults to submit).
			// Exclude: type="button" / type="reset".
			return getSurface().find( 'input[type="submit"], button:not([type="button"]):not([type="reset"])' );
		}

		function checkVisibility() {
			var $btns = getSubmitBtns();
			if ( ! $btns.length ) {
				$floatingBar.removeClass( 'is-visible' );
				return;
			}
			var anyInView = false;
			$btns.each( function () {
				var rect = this.getBoundingClientRect();
				if ( rect.top < window.innerHeight && rect.bottom > 0 ) {
					anyInView = true;
					return false;
				}
			} );
			$floatingBar.toggleClass( 'is-visible', ! anyInView );
		}

		$( window ).on( 'scroll.wpsRmaFloat resize.wpsRmaFloat', checkVisibility );
		setTimeout( checkVisibility, 300 );

		// Re-check whenever a tab finishes loading (wpsRmaFetchDashboardTab triggers
		// this after replaceWith so getSurface() will find the freshly inserted content).
		$( document ).on( 'wps:rma:tabLoaded', checkVisibility );

		$floatingBar.on( 'click', '.wps-rma-floating-save__btn', function () {
			var $btn = getSubmitBtns().last();
			if ( ! $btn.length ) return;

			// Resolve the form — for orphaned <tr>-wrapped submit inputs the browser
			// keeps the input inside the form; closest() still works.
			var $form = $btn.closest( 'form' );
			if ( ! $form.length ) {
				$form = getSurface().find( '.wps-wrael-gen-section-form, .wps-mwr-gen-section-form, form' ).first();
			}
			if ( ! $form.length ) return;

			var $floatBtn = $( this );
			var originalHtml = $floatBtn.html();
			$floatBtn.prop( 'disabled', true ).text( 'Saving…' );

			// Use fetch() instead of form.submit() to avoid the Chrome
			// "Form submission canceled because the form is not connected" warning.
			var formData = new FormData( $form[ 0 ] );
			var btnName  = $btn.attr( 'name' );
			if ( btnName ) {
				formData.append( btnName, $btn.val() || '' );
			}

			fetch( window.location.href, {
				method:      'POST',
				body:        formData,
				credentials: 'same-origin'
			} ).then( function () {
				window.location.reload();
			} ).catch( function () {
				$floatBtn.prop( 'disabled', false ).html( originalHtml );
			} );
		} );
	}());
});
