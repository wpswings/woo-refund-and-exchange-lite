<?php
/**
 * Guest return link email template.
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
printf( esc_html__( 'You requested a return for order #%s. Click the button below to access your return request form.', 'woo-refund-and-exchange-lite' ), esc_html( $order ? $order->get_order_number() : $order_id ) );
?>
</p>

<p><?php esc_html_e( 'This link is valid for 24 hours and can only be used once.', 'woo-refund-and-exchange-lite' ); ?></p>

<p style="text-align:center;margin:24px 0;">
	<a href="<?php echo esc_url( $return_link ); ?>"
	   style="background-color:#2c3e50;color:#ffffff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;">
		<?php esc_html_e( 'Start Return Request', 'woo-refund-and-exchange-lite' ); ?>
	</a>
</p>

<p><?php esc_html_e( 'If the button does not work, copy and paste the following link into your browser:', 'woo-refund-and-exchange-lite' ); ?></p>
<p><?php echo esc_url( $return_link ); ?></p>

<p><?php esc_html_e( 'If you did not request this, you can safely ignore this email.', 'woo-refund-and-exchange-lite' ); ?></p>

<?php if ( ! empty( $additional_content ) ) : ?>
<p><?php echo wp_kses_post( $additional_content ); ?></p>
<?php endif; ?>

<?php
do_action( 'woocommerce_email_footer', $email );
