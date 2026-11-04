/**
 * Adds a Save button to the "Receive updates over SMS" field on the order message page,
 * so the customer can save their number without sending a message.
 *
 * The field is rendered by the order message React app, so the button is added once it appears.
 */
( function () {
	'use strict';

	var params = window.wps_rma_sms_contact_object || {};

	function addSaveButton( input ) {
		if ( input.dataset.wpsRmaSaveAdded ) {
			return;
		}
		input.dataset.wpsRmaSaveAdded = '1';

		var wrap   = document.createElement( 'div' );
		var button = document.createElement( 'button' );
		var status = document.createElement( 'div' );

		wrap.className   = 'wps_rma_sms_contact_save';
		button.type      = 'button';
		button.className = 'wps_rma_sms_contact_save__btn';
		button.textContent = params.save_label;
		status.className = 'wps_rma_sms_contact_save__status';
		status.setAttribute( 'role', 'status' );

		wrap.appendChild( button );
		wrap.appendChild( status );
		input.closest( 'label' ).insertAdjacentElement( 'afterend', wrap );

		function save() {
			var root = document.getElementById( 'wps_rma_order_msg_react' );
			var data = new FormData();
			data.append( 'action', 'wps_rma_save_order_msg_contact' );
			data.append( 'nonce', params.nonce );
			data.append( 'order_id', root ? root.dataset.order_id : '' );
			data.append( 'wps_rma_customer_contact_order_message', input.value );

			button.disabled    = true;
			button.textContent = params.saving_label;
			status.textContent = '';
			status.classList.remove( 'is-error', 'is-success' );

			fetch( params.ajaxurl, { method: 'POST', body: data, credentials: 'same-origin' } )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( json ) {
					status.textContent = json && json.data && json.data.msg ? json.data.msg : params.error_label;
					status.classList.add( json && json.success ? 'is-success' : 'is-error' );
				} )
				.catch( function () {
					status.textContent = params.error_label;
					status.classList.add( 'is-error' );
				} )
				.finally( function () {
					button.disabled    = false;
					button.textContent = params.save_label;
				} );
		}

		button.addEventListener( 'click', save );
		input.addEventListener( 'keydown', function ( e ) {
			// Enter in this field saves the number instead of submitting the message form.
			if ( 'Enter' === e.key ) {
				e.preventDefault();
				save();
			}
		} );
	}

	function scan() {
		var input = document.getElementById( 'wps_rma_customer_contact_order_message' );
		if ( input ) {
			addSaveButton( input );
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var root = document.getElementById( 'wps_rma_order_msg_react' );
		if ( ! root ) {
			return;
		}
		scan();
		new MutationObserver( scan ).observe( root, { childList: true, subtree: true } );
	} );
}() );
