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
		$set_policies_arr = array(
			'mwb_rma_setting' =>
			array(
				0 => array(
					'row_functionality'    => 'refund',
					'row_policy'           => 'mwb_rma_order_status',
					'row_conditions2'      => 'mwb_rma_equal_to',
					'row_statuses'         => array( 'wc-processing' ),
					'incase_functionality' => 'incase',
				),
			),
		);
		update_option( 'policies_setting_option', $set_policies_arr );
	}
}
