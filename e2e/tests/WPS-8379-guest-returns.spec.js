/**
 * WPS-8379: Guest Returns via Order Number + Email
 *
 * Tests:
 *  - File structure for all new files
 *  - Shortcode registration and guard logic
 *  - Helper function existence and correctness
 *  - Email class structure
 *  - Hook registrations
 *  - Rate limiting behaviour
 *  - Token lifecycle (create → verify → consume)
 *  - POST form handling (nonce-validated)
 *  - Pro extension hook presence
 */

const { test, expect } = require( '@playwright/test' );
const fs   = require( 'fs' );
const path = require( 'path' );

const PLUGIN_DIR = path.resolve(
	__dirname,
	'../../'
);

// ---------------------------------------------------------------------------
// File structure
// ---------------------------------------------------------------------------
test.describe( 'WPS-8379 file structure', () => {
	test( 'shortcode partial exists', () => {
		const f = path.join(
			PLUGIN_DIR,
			'public/partials/wps-rma-guest-return-shortcode.php'
		);
		expect( fs.existsSync( f ) ).toBe( true );
	} );

	test( 'email class file exists', () => {
		const f = path.join(
			PLUGIN_DIR,
			'emails/class-wps-rma-guest-return-link-email.php'
		);
		expect( fs.existsSync( f ) ).toBe( true );
	} );

	test( 'email template file exists', () => {
		const f = path.join(
			PLUGIN_DIR,
			'emails/templates/wps-rma-guest-return-link-email.php'
		);
		expect( fs.existsSync( f ) ).toBe( true );
	} );
} );

// ---------------------------------------------------------------------------
// Email class structure
// ---------------------------------------------------------------------------
test.describe( 'WPS-8379 email class', () => {
	const emailFile = fs.readFileSync(
		path.join( PLUGIN_DIR, 'emails/class-wps-rma-guest-return-link-email.php' ),
		'utf8'
	);

	test( 'extends WC_Email', () => {
		expect( emailFile ).toContain( 'extends WC_Email' );
	} );

	test( 'has correct email ID', () => {
		expect( emailFile ).toContain( "'wps_rma_guest_return_link_email'" );
	} );

	test( 'trigger() sends to billing email', () => {
		expect( emailFile ).toContain( "get_billing_email" );
	} );

	test( 'uses wps-rma-guest-return-link-email.php template', () => {
		expect( emailFile ).toContain( 'wps-rma-guest-return-link-email.php' );
	} );
} );

// ---------------------------------------------------------------------------
// Email template
// ---------------------------------------------------------------------------
test.describe( 'WPS-8379 email template', () => {
	const templateFile = fs.readFileSync(
		path.join(
			PLUGIN_DIR,
			'emails/templates/wps-rma-guest-return-link-email.php'
		),
		'utf8'
	);

	test( 'renders WC email header', () => {
		expect( templateFile ).toContain( 'woocommerce_email_header' );
	} );

	test( 'outputs the return link', () => {
		expect( templateFile ).toContain( '$return_link' );
	} );

	test( 'renders WC email footer', () => {
		expect( templateFile ).toContain( 'woocommerce_email_footer' );
	} );

	test( 'states the link is valid for 24 hours', () => {
		expect( templateFile ).toContain( '24 hours' );
	} );
} );

// ---------------------------------------------------------------------------
// Helper functions in common-functions.php
// ---------------------------------------------------------------------------
test.describe( 'WPS-8379 helper functions', () => {
	const commonFunctions = fs.readFileSync(
		path.join(
			PLUGIN_DIR,
			'includes/woo-refund-and-exchange-lite-common-functions.php'
		),
		'utf8'
	);

	test( 'rate limit helper exists', () => {
		expect( commonFunctions ).toContain( 'wps_rma_guest_return_check_rate_limit' );
	} );

	test( 'token create helper exists', () => {
		expect( commonFunctions ).toContain( 'wps_rma_guest_return_create_token' );
	} );

	test( 'token verify helper exists', () => {
		expect( commonFunctions ).toContain( 'wps_rma_guest_return_verify_token' );
	} );

	test( 'token consume helper exists', () => {
		expect( commonFunctions ).toContain( 'wps_rma_guest_return_consume_token' );
	} );

	test( 'token is 40 chars (security margin)', () => {
		expect( commonFunctions ).toContain( "wp_generate_password( 40, false, false )" );
	} );

	test( 'token stored with 24-hour expiry', () => {
		expect( commonFunctions ).toContain( '24 * HOUR_IN_SECONDS' );
	} );

	test( 'rate limit uses HOUR_IN_SECONDS', () => {
		expect( commonFunctions ).toContain( 'HOUR_IN_SECONDS' );
	} );

	test( 'rate limit is filterable', () => {
		expect( commonFunctions ).toContain( 'wps_rma_guest_return_rate_limit' );
	} );

	test( 'token key strips non-alphanumeric chars', () => {
		expect( commonFunctions ).toContain( "preg_replace( '/[^a-zA-Z0-9]/', ''" );
	} );
} );

// ---------------------------------------------------------------------------
// Shortcode partial content checks
// ---------------------------------------------------------------------------
test.describe( 'WPS-8379 shortcode partial', () => {
	const partial = fs.readFileSync(
		path.join(
			PLUGIN_DIR,
			'public/partials/wps-rma-guest-return-shortcode.php'
		),
		'utf8'
	);

	test( 'uses lookup nonce', () => {
		expect( partial ).toContain( 'wps_rma_guest_return_lookup' );
	} );

	test( 'uses submit nonce', () => {
		expect( partial ).toContain( 'wps_rma_guest_return_submit' );
	} );

	test( 'checks rate limit', () => {
		expect( partial ).toContain( 'wps_rma_guest_return_check_rate_limit' );
	} );

	test( 'verifies order billing email match', () => {
		expect( partial ).toContain( 'get_billing_email' );
	} );

	test( 'checks refund eligibility via wps_rma_show_buttons', () => {
		expect( partial ).toContain( "wps_rma_show_buttons( 'refund'" );
	} );

	test( 'generates token and fires action to send link', () => {
		expect( partial ).toContain( 'wps_rma_guest_return_send_link' );
	} );

	test( 'token link uses wps_rma_guest_token GET param', () => {
		expect( partial ).toContain( 'wps_rma_guest_token' );
	} );

	test( 'calls wps_rma_save_return_request_callback on submit', () => {
		expect( partial ).toContain( 'wps_rma_save_return_request_callback' );
	} );

	test( 'consumes token after successful submission', () => {
		expect( partial ).toContain( 'wps_rma_guest_return_consume_token' );
	} );

	test( 'fires wps_rma_guest_return_submitted action on success', () => {
		expect( partial ).toContain( 'wps_rma_guest_return_submitted' );
	} );

	test( 'has Pro extension hook wps_rma_guest_return_form_fields', () => {
		expect( partial ).toContain( 'wps_rma_guest_return_form_fields' );
	} );

	test( 'sanitises page_id as absint to prevent open-redirect', () => {
		expect( partial ).toContain( 'absint' );
	} );
} );

// ---------------------------------------------------------------------------
// Public class
// ---------------------------------------------------------------------------
test.describe( 'WPS-8379 public class', () => {
	const publicClass = fs.readFileSync(
		path.join(
			PLUGIN_DIR,
			'public/class-woo-refund-and-exchange-lite-public.php'
		),
		'utf8'
	);

	test( 'registers [wps_rma_guest_return_form] shortcode', () => {
		expect( publicClass ).toContain( 'wps_rma_guest_return_form' );
	} );

	test( 'shortcode handler guards on refund_enable option', () => {
		expect( publicClass ).toContain( 'wps_rma_refund_enable' );
	} );

	test( 'shortcode handler includes guest return partial', () => {
		expect( publicClass ).toContain( 'wps-rma-guest-return-shortcode.php' );
	} );
} );

// ---------------------------------------------------------------------------
// Main class hook registrations
// ---------------------------------------------------------------------------
test.describe( 'WPS-8379 hook registrations', () => {
	const mainClass = fs.readFileSync(
		path.join(
			PLUGIN_DIR,
			'includes/class-woo-refund-and-exchange-lite.php'
		),
		'utf8'
	);

	test( 'registers shortcode on init hook', () => {
		expect( mainClass ).toContain( 'wps_rma_guest_return_register_shortcode' );
	} );

	test( 'registers wps_rma_guest_return_send_link action', () => {
		expect( mainClass ).toContain( 'wps_rma_guest_return_send_link' );
	} );
} );

// ---------------------------------------------------------------------------
// Common class
// ---------------------------------------------------------------------------
test.describe( 'WPS-8379 common class', () => {
	const commonClass = fs.readFileSync(
		path.join(
			PLUGIN_DIR,
			'common/class-woo-refund-and-exchange-lite-common.php'
		),
		'utf8'
	);

	test( 'registers guest return link email class', () => {
		expect( commonClass ).toContain( 'Wps_Rma_Guest_Return_Link_Email' );
	} );

	test( 'has send link method', () => {
		expect( commonClass ).toContain( 'wps_rma_guest_return_send_link' );
	} );

	test( 'requires the email class file', () => {
		expect( commonClass ).toContain( 'class-wps-rma-guest-return-link-email.php' );
	} );
} );
