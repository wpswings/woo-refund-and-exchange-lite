<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the html field for general tab.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wrael_mwb_rma_obj;
$mwb_rma_refund_html =
// Refund Setting Array.
apply_filters( 'mwb_rma_refund_settings_array', array() );
$woo_request_email_url = admin_url() . 'admin.php?page=wc-settings&tab=email&section=mwb_rma_refund_request_email';
$woo_accept_email_url  = admin_url() . 'admin.php?page=wc-settings&tab=email&section=mwb_rma_refund_request_accept_email';
$woo_cancel_email_url  = admin_url() . 'admin.php?page=wc-settings&tab=email&section=mwb_rma_refund_request_cancel_email';
?>
<!--  template file for admin settings. -->
<form action="" method="POST" class="mwb-wrael-gen-section-form">
	<div class="wrael-secion-wrap">
		<?php
		$mwb_rma_refund_html = $wrael_mwb_rma_obj->mwb_rma_plug_generate_html( $mwb_rma_refund_html );
		echo esc_html( $mwb_rma_refund_html );
		wp_nonce_field( 'admin_save_data', 'mwb_tabs_nonce' );
		?>
	</div>
</form>
<h6>
<?php
/* translators: %s: link */
echo sprintf( esc_html__( 'To Configure Refund Request Email %s.', 'woo-refund-and-exchange-lite' ), '<a href="' . esc_html( $woo_request_email_url ) . '">Click Here</a>' );
?>
</h6>
<h6>
<?php
/* translators: %s: link */
echo sprintf( esc_html__( 'To Configure Refund Request Accept Email %s.', 'woo-refund-and-exchange-lite' ), '<a href="' . esc_html( $woo_accept_email_url ) . '">Click Here</a>' );
?>
</h6>
<h6>
<?php
/* translators: %s: link */
echo sprintf( esc_html__( 'To Configure Refund Request Cancel Email %s.', 'woo-refund-and-exchange-lite' ), '<a href="' . esc_html( $woo_cancel_email_url ) . '">Click Here</a>' );
?>
</h6>
