<?php
/**
 * Exit if accessed directly
 *
 * @package woo-refund-and-exchange-lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_int( $thepostid ) && isset( $post ) ) {
	$thepostid = $post->ID;
}
if ( ! is_object( $theorder ) ) {
	$theorder = wc_get_order( $thepostid );
}
if ( isset( $order ) && is_object( $order ) && ! $order instanceof WP_Post ) {
	$theorder = $order;
}
$order_obj = $theorder;
$order_id  = $order_obj->get_id();

if ( isset( $order_id ) && ! empty( $order_id ) ) {
	?>
		<div id="wps_rma_order_msg_react" class="wps_rma_order_msg_template_css" data-order_id="<?php echo esc_attr( $order_id ); ?>"></div>
	<?php
}

