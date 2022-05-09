<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
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
include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/class-woo-refund-and-exchange-lite-admin.php';
$wrael_plugin_admin               = new Woo_Refund_And_Exchange_Lite_Admin( 'Return Refund and Exchange for WooCommerce', '4.0.1' );
$wps_rma_pending_orders_count     = $wrael_plugin_admin->wps_rma_get_count( 'pending', 'count', 'orders' );
$wps_rma_pending_users_count      = $wrael_plugin_admin->wps_rma_get_count( 'pending', 'count', 'users' );
$wps_rma_pending_order_msgs_count = $wrael_plugin_admin->wps_rma_get_count( 'pending', 'count', 'order_messages' );
if ( 0 !== $wps_rma_pending_orders_count || 0 !== $wps_rma_pending_users_count || 0 !== $wps_rma_pending_order_msgs_count ) {

		$wps_par_global_custom_css = 'const triggerError = () => {
		swal({
	
			title: "Attention Required!",
			text: "Please Migrate Your Database Keys First By Clicking On Below Button , Then You can Have Access To Your Dashboard Button",
			icon: "error",
			button: "Click To Import",
			closeOnClickOutside: false,
		}).then(function() {
			wps_rma_migration_success();
		});
	}
	triggerError();';
	wp_register_script( 'wps_rma_incompatible_css', false, array(), '4.0.0', 'all' );
	wp_enqueue_script( 'wps_rma_incompatible_css' );
	wp_add_inline_script( 'wps_rma_incompatible_css', $wps_par_global_custom_css );
}
