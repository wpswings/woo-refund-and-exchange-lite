<?php
/**
 * Fired during plugin activation
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'Woo_Refund_And_Exchange_Lite_Api_Process' ) ) {

	/**
	 * The plugin API class.
	 *
	 * This is used to define the functions and data manipulation for custom endpoints.
	 *
	 * @since      1.0.0
	 * @package    Hydroshop_Api_Management
	 * @subpackage Hydroshop_Api_Management/includes
	 * @author     MakeWebBetter <makewebbetter.com>
	 */
	class Woo_Refund_And_Exchange_Lite_Api_Process {

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {

		}

		/**
		 * Define the function to process data for custom endpoint.
		 *
		 * @since    1.0.0
		 * @param   Array $wrael_request  data of requesting headers and other information.
		 * @return  Array $mwb_rma_rest_response    returns processed data and status of operations.
		 */
		public function mwb_rma_default_process( $wrael_request ) {
			$mwb_rma_rest_response = array();

			// Write your custom code here.

			$mwb_rma_rest_response['status'] = 200;
			$mwb_rma_rest_response['data'] = $wrael_request->get_headers();
			return $mwb_rma_rest_response;
		}
	}
}
