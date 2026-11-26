<?php
/**
 * EU Withdrawal settings tab template.
 *
 * @link  https://wpswings.com/
 * @since 1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wrael_wps_rma_obj;

$wps_rma_eu_withdrawal_settings = apply_filters( 'wps_rma_eu_withdrawal_settings_array', array() );
$woo_eu_withdrawal_email_url    = admin_url( 'admin.php?page=wc-settings&tab=email&section=wps_rma_eu_withdrawal_email' );
?>
<!-- EU Withdrawal settings form -->
<form action="" method="POST" class="wps-wrael-gen-section-form">
	<div class="wrael-secion-wrap">
		<?php
		$wps_rma_eu_withdrawal_html = $wrael_wps_rma_obj->wps_rma_plug_generate_html( $wps_rma_eu_withdrawal_settings );
		echo esc_html( $wps_rma_eu_withdrawal_html );
		wp_nonce_field( 'admin_save_data', 'wps_tabs_nonce' );
		?>
	</div>
</form>
<h6>
<?php
/* translators: %s: link */
printf(
	esc_html__( 'Configure EU Withdrawal acknowledgement email %s.', 'woo-refund-and-exchange-lite' ),
	'<a href="' . esc_url( $woo_eu_withdrawal_email_url ) . '">' . esc_html__( 'Click Here', 'woo-refund-and-exchange-lite' ) . '</a>'
);
?>
</h6>
<h6>
<?php esc_html_e( 'Shortcode: use [wps_rma_eu_withdrawal_form] on any page to embed the EU withdrawal request form.', 'woo-refund-and-exchange-lite' ); ?>
</h6>
