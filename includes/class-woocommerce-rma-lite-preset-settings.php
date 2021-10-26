<?php
/**
 * The file to used to preset the basic settings for rma policies.
 *
 * @link  https://makewebbetter.com/
 * @since 1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/includes
 */

/**
 * This is class to restore the saved data on particular keys.
 *
 * @name    Woocommerce_Rma_Lite_Preset_Settings
 * @category Class
 * @author   makewebbetter <webmaster@makewebbetter.com>
 */
class Woocommerce_Rma_Lite_Preset_Settings {
	/**
	 * Function to migrate the settings
	 */
	public function mwb_rma_lite_preset_settings() {
		$policies_setting = array( 'mwb_rma_setting' => array() );
		$statuss          = array(
			'row_policy'           => 'mwb_rma_order_status',
			'row_functionality'    => 'refund',
			'row_conditions2'      => 'mwb_rma_equal_to',
			'incase_functionality' => 'incase',
			'row_statuses'         => array( 'wc-completed' ),
		);
		array_push( $policies_setting['mwb_rma_setting'], $statuss );
		update_option( 'policies_setting_option', $policies_setting );
		$refund_request_add = array(
			'enabled'            => 'yes',
			'subject'            => 'Refund Request for order {order_id} message from {message_date}',
			'heading'            => 'Refund Request Email',
			'additional_content' => '',
			'email_type'         => 'html',
		);
		update_option( 'woocommerce_mwb_rma_refund_request_email_settings', $refund_request_add );
		$refund_request_accept_add = array(
			'enabled'            => 'yes',
			'subject'            => 'Refund Request Accept for order {order_id} message from {message_date}',
			'heading'            => 'Refund Request Accept Email',
			'additional_content' => '',
			'email_type'         => 'html',
		);
		update_option( 'woocommerce_mwb_rma_refund_request_accept_email_settings', $refund_request_accept_add );
		$refund_request_cancel_add = array(
			'enabled'            => 'yes',
			'subject'            => 'Refund Request Cancel for order {order_id} message from {message_date}',
			'heading'            => 'Refund Request Cancel Email',
			'additional_content' => '',
			'email_type'         => 'html',
		);
		update_option( 'woocommerce_mwb_rma_refund_request_cancel_email_settings', $refund_request_cancel_add );
		$refund_request_cancel_add = array(
			'enabled'            => 'yes',
			'subject'            => 'Your {site_title} order {order_id} message from {message_date}',
			'heading'            => 'Thank You',
			'additional_content' => '',
			'email_type'         => 'html',
		);
		update_option( 'woocommerce_mwb_rma_order_messages_email_settings', $refund_request_cancel_add );
	}
}
