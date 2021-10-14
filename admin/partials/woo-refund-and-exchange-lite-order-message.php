<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the html field for general tab.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wrael_mwb_rma_obj;
$wrael_order_message_settings =
// Order Message Setting Array.
apply_filters( 'mwb_rma_order_message_settings_array', array() );
$woo_email_url = admin_url() . 'admin.php?page=wc-settings&tab=email';
?>
<!--  template file for admin settings. -->
<form action="" method="POST" class="mwb-wrael-gen-section-form">
	<div class="wrael-secion-wrap">
		<?php
		$wrael_order_message_settings = $wrael_mwb_rma_obj->mwb_rma_plug_generate_html( $wrael_order_message_settings );
		echo esc_html( $wrael_order_message_settings );
		wp_nonce_field( 'admin_save_data', 'mwb_tabs_nonce' );
		?>
	</div>
</form>

<h6><b>
<?php
/* translators: %s: search term */
echo sprintf( esc_html__( 'To Configure Order Message Email %s.', 'woo-refund-and-exchange-lite' ), '<a href="' . esc_html( $woo_email_url ) . '">Click Here</a>' );
?>
</b></h6>
