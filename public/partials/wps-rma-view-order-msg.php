<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/public/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header( 'shop' );

if ( apply_filters( 'wps_rma_refund_form_sidebar', true ) ) {
	do_action( 'woocommerce_before_main_content' );
}
if ( isset( $_GET['wps_rma_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wps_rma_nonce'] ) ), 'wps_rma_nonce' ) && isset( $_GET['order_id'] ) ) {
	$order_id = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
	$order    = wc_get_order( $order_id );
	if ( ! empty( $order_id ) && ! empty( $order ) ) {
		$user_id = $order->get_user_id();
		if ( function_exists( 'get_current_user_id' ) && ! empty( get_current_user_id() ) && ( 1 === get_current_user_id() || $user_id === get_current_user_id() ) ) {
			?>
				<div id="wps_rma_order_msg_react" class="wps_rma_order_msg_react_wrapper" data-order_id="<?php echo esc_attr( $order_id ); ?>"></div>
			<?php
		}
	}
}

if ( apply_filters( 'wps_rma_refund_form_sidebar', true ) ) {
	do_action( 'woocommerce_after_main_content' );
}

get_footer( 'shop' );
