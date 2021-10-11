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
		 * @param   object $wrael_request  data of requesting headers and other information.
		 * @return  Array $mwb_rma_rest_response    returns processed data and status of operations.
		 */
		public function mwb_rma_refund_request_process( $wrael_request ) {
			$data                  = $wrael_request->get_params();
			$order_id              = $data['order_id'] ? absint( $data['order_id'] ) : '';
			$products              = $data['products'] ? $data['products'] : '';
			$reason                = $data['reason'] ? $data['reason'] : '';
			$refund_method         = $data['refund_method'] ? $data['refund_method'] : 'manual_method';
			$mwb_rma_rest_response = array();
			// My code start
			if ( ! empty( $order_id ) ) {
				$order_obj = wc_get_order( $order_id );
				require_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'common/class-woo-refund-and-exchange-lite-common.php';
				$mwb_rma_obj = new Woo_Refund_And_Exchange_Lite_Common( 'Return Refund and Exchange for WooCommerce', '4.0.0' );
				if ( mwb_rma_pro_active() ) {
					if ( ! empty( $order_obj ) ) {
						$products1   = array();
						$array_merge = array();
						$ref_price   = 0;
						$flag        = true;
						$qty_flag    = true;
						foreach ( $order_obj->get_items() as $item_id => $item ) {	
							foreach ( json_decode( $products ) as $key => $value ) {
								$item_id1 = $value->item_id;
								if ( ! empty( $item_id1 ) && isset( $value->item_id ) && $item_id1 == $item_id  ) {
									$item_refund_already = get_post_meta( $order_id, 'mwb_rma_request_made', true );
									if ( ! empty( $item_refund_already ) && isset( $item_refund_already[ $item_id ] ) && 'completed' === $item_refund_already[ $item_id1 ] ) {
										$flag = false;
									} else if ( absint( $value->qty ) > $item->get_quantity() ) {
										$qty_flag = false;
									} else {
										$item_arr               = array();
										$item_arr['product_id'] = $item->get_product_id();
										if( $item->is_type('variable') ) {
											$variation_id = $item->get_variation_id();
										} else {
											$variation_id = 0;
										}
										$item_arr['item_id']      = $item_id;
										$item_arr['variation_id'] = $variation_id;
										$item_arr['qty']          = $value->qty;
										$mwb_rma_check_tax = get_option( $order_id . 'check_tax', false );
										$tax = $item->get_total_tax();
										if ( empty( $mwb_rma_check_tax ) ) {
											$item_arr['price'] = $item->get_total();
											$ref_price        += $item->get_total();
										} elseif ( 'mwb_rma_inlcude_tax' === $mwb_rma_check_tax ) {
											$item_arr['price'] = $item->get_total() + $tax;
											$ref_price        += $item->get_total() + $tax;
										} elseif ( 'mwb_rma_exclude_tax' === $mwb_rma_check_tax ) {
											$item_arr['price'] = $item->get_total() - $tax;
											$ref_price        += $item->get_total() - $tax;
										}
										$array_merge[] = $item_arr;
									}
								}
							}
						}
						if ( ! $flag ) {
							$mwb_rma_rest_response['message'] = 'error';
							$mwb_rma_rest_response['status']  = 404;
							$mwb_rma_rest_response['data']    = esc_html__( 'Return Request Already has been made and accpeted for the items you have given', 'woo-refund-and-exchange-lite' );
						} elseif( ! $qty_flag ) {
							$mwb_rma_rest_response['message'] = 'error';
							$mwb_rma_rest_response['status']  = 404;
							$mwb_rma_rest_response['data']    = esc_html__( 'Quanity given for items is greater than the order\'s items quantity', 'woo-refund-and-exchange-lite' );
						} else {
							$products1['products']      = $array_merge;
							$products1['order_id']      = $order_id;
							$products1['subject']       = $reason;
							$products1['refund_method'] = $refund_method;
							$products1['amount']        = $ref_price;
							$mwb_rma_resultsdata        = $mwb_rma_obj->mwb_rma_save_return_request_callback( $order_id, $refund_method, $products1 );
							if ( ! empty( $mwb_rma_resultsdata ) ) {
								$mwb_rma_rest_response['message'] = 'success';
								$mwb_rma_rest_response['status']  = 200;
								$mwb_rma_rest_response['data']    = esc_html__( 'Refund Request Send Successfully', 'woo-refund-and-exchange-lite' );
							} else {
								$mwb_rma_rest_response['message'] = 'error';
								$mwb_rma_rest_response['status']  = 404;
								$mwb_rma_rest_response['data']    = esc_html__( 'Some problem occur while refund requesting', 'woo-refund-and-exchange-lite' );
							}
						}
					}
				} else {
					$products1   = array();
					$array_merge = array();
					$ref_price = 0;
					if( ! empty( $order_obj ) ) {
						foreach ( $order_obj->get_items() as $item_id => $item ) {
							$item_arr = array();
							$item_arr['product_id'] = $item->get_product_id();
							if( $item->is_type('variable') ) {
								$variation_id = $item->get_variation_id();
							} else {
								$variation_id = 0;
							}
							$item_arr['item_id']      = $item_id;
							$item_arr['variation_id'] = $variation_id;
							$item_arr['qty']          = $item->get_quantity();
							$tax = $item->get_total_tax();
							if ( empty( $mwb_rma_check_tax ) ) {
									$item_arr['price'] = $item->get_total();
									$ref_price        += $item->get_total();
							} elseif ( 'mwb_rma_inlcude_tax' === $mwb_rma_check_tax ) {
								$item_arr['price'] = $item->get_total() + $tax;
								$ref_price        += $item->get_total() + $tax;
							} elseif ( 'mwb_rma_exclude_tax' === $mwb_rma_check_tax ) {
								$item_arr['price'] = $item->get_total() - $tax;
								$ref_price        += $item->get_total() - $tax;
							}
							$array_merge[]            = $item_arr;
						}
						$products1['products']      = $array_merge;
						$products1['order_id']      = $order_id;
						$products1['subject']       = $reason;
						$products1['refund_method'] = 'manual_method';
						$products1['amount']        = $ref_price;
					}
					$mwb_rma_resultsdata = $mwb_rma_obj->mwb_rma_save_return_request_callback( $order_id, 'manual_method', $products1 );
					$flag_refund_made    = false;
					$products = get_post_meta( $order_id, 'mwb_rma_return_product', true );
					if ( isset( $products ) && ! empty( $products ) ) {
						foreach ( $products as $date => $product ) {
							if ( 'complete' === $product['status'] ) {
								$flag_refund_made = true;
							}
						}
					}
					if ( $flag_refund_made ) {
						$mwb_rma_rest_response['message'] = 'error';
						$mwb_rma_rest_response['status']  = 404;
						$mwb_rma_rest_response['data']    = esc_html__( 'Return Request Already has been made and accpeted', 'woo-refund-and-exchange-lite' );
					} elseif ( ! empty( $mwb_rma_resultsdata ) ) {
						$mwb_rma_rest_response['message'] = 'success';
						$mwb_rma_rest_response['status']  = 200;
						$mwb_rma_rest_response['data']    = esc_html__( 'Return Request Send Successfully', 'woo-refund-and-exchange-lite' );
					} else {
						$mwb_rma_rest_response['message'] = 'error';
						$mwb_rma_rest_response['status']  = 404;
						$mwb_rma_rest_response['data']    = esc_html__( 'Some problem occur while refund requesting', 'woo-refund-and-exchange-lite' );
					}
				}
			}  else {
				$mwb_rma_rest_response['status'] = 404;
				$mwb_rma_rest_response['data']   = esc_html__( 'Please Provide the order id to perform the process', 'woo-refund-and-exchange-lite' );
			}
			return $mwb_rma_rest_response;
		}

		/**
		 * Define the function to process data for custom endpoint.
		 *
		 * @since    1.0.0
		 * @param   object $wrael_request  data of requesting headers and other information.
		 * @return  Array $mwb_rma_rest_response    returns processed data and status of operations.
		 */
		public function mwb_rma_refund_request_accept_process( $wrael_request ) {
			$mwb_mwr_rest_response = array();
			$data                  = $wrael_request->get_params();
			$order_id              = $data['order_id'] ? absint( $data['order_id'] ) : '';
			$flag                  = false;
			if ( ! empty( $order_id ) ) {
				$products = get_post_meta( $order_id, 'mwb_rma_return_product', true );
				if ( isset( $products ) && ! empty( $products ) ) {
					foreach ( $products as $date => $product ) {
						if ( 'pending' === $product['status'] ) {
							$flag = true;
						}
					}
				}
				if ( $flag ) {
					require_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/class-woo-refund-and-exchange-lite-admin.php';
					$mwb_rma_obj         = new Woo_Refund_And_Exchange_Lite_Admin( 'Return Refund and Exchange for WooCommerce', '4.0.0' );
					$mwb_rma_resultsdata = $mwb_rma_obj->mwb_rma_return_req_approve_callback( $order_id, $products );
					if ( ! empty( $mwb_rma_resultsdata ) ) {
						$mwb_rma_rest_response['status'] = 200;
						$mwb_rma_rest_response['data']   = esc_html__( 'Return Request Accepted Successfully', 'woo-refund-and-exchange-lite' );
					} else {
						$mwb_rma_rest_response['status'] = 404;
						$mwb_rma_rest_response['data']   = esc_html__( 'Some problem occur while refund request accepting', 'woo-refund-and-exchange-lite' );
					}
				} else {
					$mwb_rma_rest_response['status'] = 404;
					$mwb_rma_rest_response['data']   = esc_html__( 'You can only perform the refund request accept when the request has been made earlier', 'woo-refund-and-exchange-lite' );
				}
			} else {
				$mwb_rma_rest_response['status'] = 404;
				$mwb_rma_rest_response['data']   = esc_html__( 'Please Provide the order id to perform the process', 'woo-refund-and-exchange-lite' );
			}
			return $mwb_rma_rest_response;
		}

		/**
		 * Define the function to process data for custom endpoint.
		 *
		 * @since    1.0.0
		 * @param   object $wrael_request  data of requesting headers and other information.
		 * @return  Array $mwb_rma_rest_response    returns processed data and status of operations.
		 */
		public function mwb_rma_refund_request_cancel_process( $wrael_request ) {
			$mwb_mwr_rest_response = array();
			$data                  = $wrael_request->get_params();
			$order_id              = $data['order_id'] ? absint( $data['order_id'] ) : '';
			$flag                  = false;
			if ( ! empty( $order_id ) ) {
				$products = get_post_meta( $order_id, 'mwb_rma_return_product', true );
				if ( isset( $products ) && ! empty( $products ) ) {
					foreach ( $products as $date => $product ) {
						if ( 'pending' === $product['status'] ) {
							$flag = true;
						}
					}
				}
				if ( $flag ) {
					require_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/class-woo-refund-and-exchange-lite-admin.php';
					$mwb_rma_obj         = new Woo_Refund_And_Exchange_Lite_Admin( 'Return Refund and Exchange for WooCommerce', '4.0.0' );
					$mwb_rma_resultsdata = $mwb_rma_obj->mwb_rma_return_req_cancel_callback( $order_id, $products );
					if ( ! empty( $mwb_rma_resultsdata ) ) {
						$mwb_rma_rest_response['status'] = 200;
						$mwb_rma_rest_response['data']   = esc_html__( 'Return Request Cancel Successfully', 'woo-refund-and-exchange-lite' );
					} else {
						$mwb_rma_rest_response['status'] = 404;
						$mwb_rma_rest_response['data']   = esc_html__( 'Some problem occur while refund request cancelling', 'woo-refund-and-exchange-lite' );
					}
				} else {
					$mwb_rma_rest_response['status'] = 404;
					$mwb_rma_rest_response['data']       = esc_html__( 'You can only perform the refund request cancel when the request request has been made earlier', 'woo-refund-and-exchange-lite' );
				}
			} else {
				$mwb_rma_rest_response['status'] = 404;
				$mwb_rma_rest_response['data']   = esc_html__( 'Please Provide the order id to perform the process', 'woo-refund-and-exchange-lite' );
			}
			return $mwb_rma_rest_response;
		}
	}
}
