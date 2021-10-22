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
			$order_id              = isset( $data['order_id'] ) ? absint( $data['order_id'] ) : 0;
			$products              = isset( $data['products'] ) ? $data['products'] : '';
			$reason                = isset( $data['reason'] ) ? $data['reason'] : '';
			$refund_method         = isset( $data['refund_method'] ) ? $data['refund_method'] : '';
			$mwb_rma_rest_response = array();
			$order_obj             = wc_get_order( $order_id );
			if ( ! empty( $order_id ) && ! empty( $order_obj ) && ! empty( $reason ) ) {
				$check_refund = mwb_rma_show_buttons( 'refund', $order_obj );
				if ( 'yes' === $check_refund ) {
					if ( mwb_rma_pro_active() && ! empty( $products ) ) {
						$products1    = array();
						$array_merge  = array();
						$ref_price    = 0;
						$flag         = true;
						$qty_flag     = true;
						$item_flag    = true;
						$invalid_item = true;
						$invalid_qty  = true;
						$item_detail  = array();
						foreach ( $order_obj->get_items() as $item_id => $item ) {
							$item_detail[ $item_id ] = $item->get_quantity();
						}
						$json_validate = mwb_json_validate( $products );
						if ( $json_validate ) {
							foreach ( $order_obj->get_items() as $item_id => $item ) {
								foreach ( json_decode( $products ) as $key => $value ) {
									if ( isset( $value->item_id ) && isset( $value->qty ) && array_key_exists( $value->item_id, $item_detail ) ) {
										if ( $value->item_id === $item_id ) {
											$item_refund_already = get_post_meta( $order_id, 'mwb_rma_request_made', true );
											if ( ! empty( $item_refund_already ) && isset( $item_refund_already[ $item_id ] ) && 'completed' === $item_refund_already[ $item_id ] ) {
												$flag = false;
											} elseif ( $value->qty > $item->get_quantity() ) {
												$qty_flag = false;
											} else {
												$item_arr               = array();
												$item_arr['product_id'] = $item->get_product_id();
												if ( $item->is_type( 'variable' ) ) {
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
									} else {
										if ( ! isset( $value->item_id ) ) {
											$invalid_item = false;
										} elseif ( ! isset( $value->qty ) ) {
											$invalid_qty = false;
										} else {
											$item_flag = false;
										}
									}
								}
							}
						}
						if ( ! $flag ) {
							$mwb_rma_rest_response['message'] = 'error';
							$mwb_rma_rest_response['status']  = 404;
							$mwb_rma_rest_response['data']    = esc_html__( 'Return Request Already has been made and accpeted for the items you have given', 'woo-refund-and-exchange-lite' );
						} elseif ( ! $qty_flag ) {
							$mwb_rma_rest_response['message'] = 'error';
							$mwb_rma_rest_response['status']  = 404;
							$mwb_rma_rest_response['data']    = esc_html__( 'Quanity given for items is greater than the order\'s items quantity', 'woo-refund-and-exchange-lite' );
						} elseif ( ! $item_flag ) {
							$mwb_rma_rest_response['message'] = 'error';
							$mwb_rma_rest_response['status']  = 404;
							$mwb_rma_rest_response['data']    = esc_html__( 'These item id does not belongs to the order', 'woo-refund-and-exchange-lite' );
						} elseif ( ! $invalid_item ) {
							$mwb_rma_rest_response['message'] = 'error';
							$mwb_rma_rest_response['status']  = 404;
							$mwb_rma_rest_response['data']    = esc_html__( 'Please give the item ids which needs to be refund', 'woo-refund-and-exchange-lite' );
						} elseif ( ! $invalid_qty ) {
							$mwb_rma_rest_response['message'] = 'error';
							$mwb_rma_rest_response['status']  = 404;
							$mwb_rma_rest_response['data']    = esc_html__( 'Please give the item qty which needs to be refund', 'woo-refund-and-exchange-lite' );
						} elseif ( ! $json_validate ) {
							$mwb_rma_rest_response['message'] = 'error';
							$mwb_rma_rest_response['status']  = 404;
							$mwb_rma_rest_response['data']    = esc_html__( 'Products given by you dosn\'t a valid json format', 'woo-refund-and-exchange-lite' );
						} elseif ( empty( $products ) ) {
							$mwb_rma_rest_response['status'] = 404;
							$mwb_rma_rest_response['data']   = esc_html__( 'Please Provide the data for the products', 'woo-refund-and-exchange-lite' );
						} else {
							$products1['products']      = $array_merge;
							$products1['order_id']      = $order_id;
							$products1['subject']       = $reason;
							$products1['refund_method'] = $refund_method;
							$products1['amount']        = $ref_price;
							$mwb_rma_resultsdata        = mwb_rma_save_return_request_callback( $order_id, $refund_method, $products1 );
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
					} else {
						$products1   = array();
						$array_merge = array();
						$ref_price   = 0;
						if ( ! empty( $order_obj ) ) {
							foreach ( $order_obj->get_items() as $item_id => $item ) {
								$item_arr               = array();
								$item_arr['product_id'] = $item->get_product_id();
								if ( $item->is_type( 'variable' ) ) {
									$variation_id = $item->get_variation_id();
								} else {
									$variation_id = 0;
								}
								$item_arr['item_id']      = $item_id;
								$item_arr['variation_id'] = $variation_id;
								$item_arr['qty']          = $item->get_quantity();
								$tax                      = $item->get_total_tax();
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
							$products1['products']      = $array_merge;
							$products1['order_id']      = $order_id;
							$products1['subject']       = $reason;
							$products1['refund_method'] = 'manual_method';
							$products1['amount']        = $ref_price;
						}
						$mwb_rma_resultsdata = mwb_rma_save_return_request_callback( $order_id, 'manual_method', $products1 );
						$flag_refund_made    = false;
						$products            = get_post_meta( $order_id, 'mwb_rma_return_product', true );
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
				} else {
					$mwb_rma_rest_response['status'] = 404;
					$mwb_rma_rest_response['data']   = $check_refund;
				}
			} elseif ( empty( $reason ) ) {
				$mwb_rma_rest_response['status'] = 404;
				$mwb_rma_rest_response['data']   = esc_html__( 'Please Provide the reason for refund', 'woo-refund-and-exchange-lite' );
			} elseif ( empty( $products ) ) {
				$mwb_rma_rest_response['status'] = 404;
				$mwb_rma_rest_response['data']   = esc_html__( 'Please Provide the data for the products', 'woo-refund-and-exchange-lite' );
			} else {
				$mwb_rma_rest_response['status'] = 404;
				$mwb_rma_rest_response['data']   = esc_html__( 'Please Provide the correct order id to perform the process', 'woo-refund-and-exchange-lite' );
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
			$mwb_rma_rest_response = array();
			$data                  = $wrael_request->get_params();
			$order_id              = isset( $data['order_id'] ) ? absint( $data['order_id'] ) : 0;
			$flag                  = false;
			$order_obj             = wc_get_order( $order_id );
			$flag_completed        = false;
			if ( ! empty( $order_id ) && ! empty( $order_obj ) ) {
				$products = get_post_meta( $order_id, 'mwb_rma_return_product', true );
				if ( isset( $products ) && ! empty( $products ) ) {
					foreach ( $products as $date => $product ) {
						if ( 'pending' === $product['status'] ) {
							$flag = true;
						} elseif ( 'complete' === $product['status'] ) {
							$flag_completed = true;
						}
					}
				}
				if ( $flag ) {
					$mwb_rma_resultsdata = mwb_rma_return_req_approve_callback( $order_id, $products );
					if ( ! empty( $mwb_rma_resultsdata ) ) {
						$mwb_rma_rest_response['status'] = 200;
						$mwb_rma_rest_response['data']   = esc_html__( 'Return Request Accepted Successfully', 'woo-refund-and-exchange-lite' );
					} else {
						$mwb_rma_rest_response['status'] = 404;
						$mwb_rma_rest_response['data']   = esc_html__( 'Some problem occur while refund request accepting', 'woo-refund-and-exchange-lite' );
					}
				} elseif ( $flag_completed ) {
					$mwb_rma_rest_response['status'] = 404;
					$mwb_rma_rest_response['data']   = esc_html__( 'You have approved the refund request already', 'woo-refund-and-exchange-lite' );
				} else {
					$mwb_rma_rest_response['status'] = 404;
					$mwb_rma_rest_response['data']   = esc_html__( 'You can only accept the refund request when the request has been made earlier', 'woo-refund-and-exchange-lite' );
				}
			} else {
				$mwb_rma_rest_response['status'] = 404;
				$mwb_rma_rest_response['data']   = esc_html__( 'Please Provide the correct order id to perform the process', 'woo-refund-and-exchange-lite' );
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
			$mwb_rma_rest_response = array();
			$data                  = $wrael_request->get_params();
			$order_id              = isset( $data['order_id'] ) ? absint( $data['order_id'] ) : 0;
			$flag                  = false;
			$flag_cancel           = false;
			$order_obj             = wc_get_order( $order_id );
			if ( ! empty( $order_id ) && ! empty( $order_obj ) ) {
				$products = get_post_meta( $order_id, 'mwb_rma_return_product', true );
				if ( isset( $products ) && ! empty( $products ) ) {
					foreach ( $products as $date => $product ) {
						if ( 'pending' === $product['status'] ) {
							$flag = true;
						} elseif ( 'cancel' === $product['status'] ) {
							$flag_cancel = true;
						}
					}
				}
				if ( $flag ) {
					$mwb_rma_resultsdata = mwb_rma_return_req_cancel_callback( $order_id, $products );
					if ( ! empty( $mwb_rma_resultsdata ) ) {
						$mwb_rma_rest_response['status'] = 200;
						$mwb_rma_rest_response['data']   = esc_html__( 'Return Request Cancel Successfully', 'woo-refund-and-exchange-lite' );
					} else {
						$mwb_rma_rest_response['status'] = 404;
						$mwb_rma_rest_response['data']   = esc_html__( 'Some problem occur while refund request cancelling', 'woo-refund-and-exchange-lite' );
					}
				} elseif ( $flag_cancel ) {
					$mwb_rma_rest_response['status'] = 404;
					$mwb_rma_rest_response['data']   = esc_html__( 'You have cancelled the refund request already', 'woo-refund-and-exchange-lite' );
				} else {
					$mwb_rma_rest_response['status'] = 404;
					$mwb_rma_rest_response['data']   = esc_html__( 'You can only perform the refund request cancel when the request request has been made earlier', 'woo-refund-and-exchange-lite' );
				}
			} else {
				$mwb_rma_rest_response['status'] = 404;
				$mwb_rma_rest_response['data']   = esc_html__( 'Please Provide the correct order id to perform the process', 'woo-refund-and-exchange-lite' );
			}
			return $mwb_rma_rest_response;
		}
	}
}
