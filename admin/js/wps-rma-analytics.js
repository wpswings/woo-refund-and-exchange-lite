/* RMA Analytics tab — date range guard */
( function () {
	'use strict';

	var startEl = document.getElementById( 'wps-rma-analytics-start' );
	var endEl   = document.getElementById( 'wps-rma-analytics-end' );

	if ( ! startEl || ! endEl ) {
		return;
	}

	startEl.addEventListener( 'change', function () {
		endEl.min = startEl.value;
		if ( endEl.value && endEl.value < startEl.value ) {
			endEl.value = startEl.value;
		}
	} );

	endEl.addEventListener( 'change', function () {
		startEl.max = endEl.value;
		if ( startEl.value && startEl.value > endEl.value ) {
			startEl.value = endEl.value;
		}
	} );
} )();
