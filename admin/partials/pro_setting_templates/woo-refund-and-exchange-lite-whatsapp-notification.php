<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the html field for general tab.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wrael_wps_rma_obj;
$mwr_whatsapp_notification_settings =
// Wallet Setting register filter.
apply_filters( 'wps_rma_whatsapp_notification_settings_array', array() );
?>
<!--  template file for admin settings. -->
<form action="" method="POST" class="wps-mwr-gen-section-form">
	<div class="mwr-secion-wrap">
		<?php
		$mwr_whatsapp_notification_html = $wrael_wps_rma_obj->wps_rma_plug_generate_html( $mwr_whatsapp_notification_settings );
		echo esc_html( $mwr_whatsapp_notification_html );
		wp_nonce_field( 'admin_save_data', 'wps_tabs_nonce' );
		?>
	</div>
</form>