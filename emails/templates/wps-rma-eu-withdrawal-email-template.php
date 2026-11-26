<?php
/**
 * EU Withdrawal acknowledgement email template.
 *
 * @package woo-refund-and-exchange-lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );

$order = wc_get_order( $order_id );
?>

<p>
<?php
/* translators: %s: order number */
printf( esc_html__( 'We confirm receipt of your withdrawal request for order #%s.', 'woo-refund-and-exchange-lite' ), esc_html( $order ? $order->get_order_number() : $order_id ) );
?>
</p>
<p><?php esc_html_e( 'Your right to withdraw from this contract is acknowledged in accordance with the EU Consumer Rights Directive (Directive 2023/2673). This email serves as confirmation on a durable medium.', 'woo-refund-and-exchange-lite' ); ?></p>

<?php if ( ! empty( $log_entry['timestamp'] ) ) : ?>
<p>
	<strong><?php esc_html_e( 'Date of request:', 'woo-refund-and-exchange-lite' ); ?></strong>
	<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $log_entry['timestamp'] ) ); ?>
</p>
<?php endif; ?>

<?php if ( $order && ! empty( $log_entry['items'] ) ) : ?>
<p><strong><?php esc_html_e( 'Items withdrawn:', 'woo-refund-and-exchange-lite' ); ?></strong></p>
<ul>
	<?php
	foreach ( $order->get_items() as $item_id => $item ) {
		if ( ! in_array( $item_id, (array) $log_entry['items'], true ) ) {
			continue;
		}
		$product = $item->get_product();
		echo '<li>' . esc_html( $product ? $product->get_name() : $item->get_name() ) . ' &times; ' . esc_html( $item->get_quantity() ) . '</li>';
	}
	?>
</ul>
<?php endif; ?>

<p><?php esc_html_e( 'Our team will process your request and contact you if any further information is needed. Refunds, where applicable, will be issued within 14 days.', 'woo-refund-and-exchange-lite' ); ?></p>

<?php if ( ! empty( $additional_content ) ) : ?>
<p><?php echo wp_kses_post( $additional_content ); ?></p>
<?php endif; ?>

<?php
do_action( 'woocommerce_email_footer', $email );
