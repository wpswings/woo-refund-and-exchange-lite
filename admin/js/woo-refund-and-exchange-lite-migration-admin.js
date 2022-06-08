// Migration code
jQuery(document).ready( function($) {

	const ajaxUrl  		 = wrael_admin_migration_param.ajaxurl;
	const nonce    		 = wrael_admin_migration_param.wps_rma_nonce;
	const action          = wrael_admin_migration_param.wps_rma_callback;
	
	const pending_orders  = wrael_admin_migration_param.wps_rma_pending_orders;
	const pending_orders_count  = wrael_admin_migration_param.wps_rma_pending_orders_count;
	const rma_pending_users  = wrael_admin_migration_param.wps_rma_pending_users;
	const rma_pending_users_count  = wrael_admin_migration_param.wps_rma_pending_users_count;

	const rma_pending_order_msgs = wrael_admin_migration_param.wps_rma_pending_order_msgs
	const rma_pending_order_msgs_count = wrael_admin_migration_param.wps_rma_pending_order_msgs_count

	console.log(pending_orders	);
	/* Close Button Click */
	jQuery( document ).on( 'click','#wps_rma_migration_start-button',function(e){
		e.preventDefault();
		Swal.fire({
			icon: 'warning',
			title: 'We Have got ' + pending_orders_count + ' Orders Data,<br/> '+ rma_pending_order_msgs_count +' Order Messages Data And ' + rma_pending_users_count + ' Users Data',
			text: 'Click to start Migration',
			footer: 'Please do not reload/close this page until prompted',
			showCloseButton: true,
			showCancelButton: true,
			focusConfirm: false,
			confirmButtonText:
			  '<i class="fa fa-thumbs-up"></i> Start',
			confirmButtonAriaLabel: 'Thumbs up',
			cancelButtonText:
			  '<i class="fa fa-thumbs-down"> Cancel</i>',
			cancelButtonAriaLabel: 'Thumbs down'
		}).then((result) => {
			if (result.isConfirmed) {

				Swal.fire({
					title   : 'Orders Data are being migrated!',
					html    : 'Do not reload/close this tab.',
					footer  : '<span class="order-progress-report">' + pending_orders_count + ' are left to migrate',
					didOpen: () => {
						Swal.showLoading()
					}
				});
				startImport( pending_orders );

			} else if (result.isDismissed) {
			  Swal.fire('Migration Stopped', '', 'info');
			}
		})
	});

	const startImport = ( orders ) => {
		var event   = 'wps_rma_import_single_order';
		var request = { action, event, nonce, orders };
		jQuery.post( ajaxUrl , request ).done(function( response ){
			orders = JSON.parse( response );
		}).then(
		function( orders ) {
			orders = JSON.parse( orders ).orders;
			if ( jQuery.isEmptyObject(orders) ) {
				count = 0;
			} else {
				count = Object.keys(orders).length;
			}
			
			jQuery('.order-progress-report').text( count + ' are left to migrate' );
			if( ! jQuery.isEmptyObject(orders) ) {
				startImport(orders);
			} else {
				// All orders imported!
				Swal.fire({
					title   : 'Order Messages Data are being migrated!',
					html    : 'Do not reload/close this tab.',
					footer  : '<span class="order-progress-report">' + rma_pending_order_msgs_count + ' are left to migrate',
					didOpen: () => {
						Swal.showLoading()
					}
				});
				startOrderMsgs( rma_pending_order_msgs );
			}
		}, function(error) {
			console.error(error);
		});
	}

	const startOrderMsgs = ( order_msgs ) => {
		var event   = 'wps_rma_import_single_order_msg';
		var request = { action, event, nonce, order_msgs };
		jQuery.post( ajaxUrl , request ).done(function( response ){
			order_msgs = JSON.parse( response );
		}).then(
		function( order_msgs ) {
			order_msgs = JSON.parse( order_msgs ).order_msgs;
			if ( jQuery.isEmptyObject(order_msgs) ) {
				count = 0;
			} else {
				count = Object.keys(order_msgs).length;
			}
			
			jQuery('.order-progress-report').text( count + ' are left to migrate' );
			if( ! jQuery.isEmptyObject(order_msgs) ) {
				startOrderMsgs(order_msgs);
			} else {
				// All orders imported!
				Swal.fire({
					title   : 'Users Data are being migrated!',
					html    : 'Do not reload/close this tab.',
					footer  : '<span class="order-progress-report">' + rma_pending_users_count + ' are left to migrate',
					didOpen: () => {
						Swal.showLoading()
					}
				});
				startImportUsers( rma_pending_users );
			}
		}, function(error) {
			console.error(error);
		});
	}

	const startImportUsers = ( users ) => {
		var event   = 'wps_rma_import_single_user';
		var request = { action, event, nonce, users };
		jQuery.post( ajaxUrl , request ).done(function( response ){
			users = JSON.parse( response );
		}).then(
		function( users ) {
			users = JSON.parse( users ).users;
			if ( jQuery.isEmptyObject(users) ) {
				count = 0;
			} else {
				count = Object.keys(users).length;
			}
			jQuery('.order-progress-report').text( count + ' are left to migrate' );
			if( ! jQuery.isEmptyObject(users) ) {
				startImportUsers(users);
			} else {
				window.location.reload();
			}
		}, function(error) {
			console.error(error);
		});
	}
	// End of scripts.
});

var wps_rma_migration_success = function() {
	
	if ( wrael_admin_migration_param.wps_rma_pending_orders_count != 0 || wrael_admin_migration_param.wps_rma_pending_users_count != 0 || wrael_admin_migration_param.wps_rma_pending_order_msgs_count != 0 ) {
		jQuery( "#wps_rma_migration_start-button" ).click();
		jQuery( "#wps_rma_migration_start-button" ).show();
	}else{
		jQuery( "#wps_rma_migration_start-button" ).hide();
		
	}
}