<?php
/**
 * Fired during plugin activation
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/includes
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
	 * @package    woo-refund-and-exchange-lite
	 * @subpackage woo-refund-and-exchange-lite/includes
	 * @author     Wp Swings <wpswings.com>
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
		 * @return  Array $wps_rma_rest_response    returns processed data and status of operations.
		 */
		public static function wps_rma_refund_request_process($wrael_request) {
			$data          = $wrael_request->get_params();
			$order_id      = isset($data['order_id']) ? absint($data['order_id']) : 0;
			$refund_items  = isset($data['refund_items']) ? $data['refund_items'] : '';
			$reason        = isset($data['reason']) ? $data['reason'] : '';
			$refund_method = isset($data['refund_method']) ? $data['refund_method'] : '';
			$response      = array();
			$order         = wc_get_order($order_id);
		
			if (!$order_id || !$order) {
				$response['status'] = 404;
				$response['data']   = esc_html__('Please provide a valid order ID.', 'woo-refund-and-exchange-lite');
				return $response;
			}
		
			$check_refund = wps_rma_show_buttons('refund', $order);
			if ('yes' !== $check_refund) {
				return ['status' => 404, 'data' => $check_refund];
			}
		
			$tax_option = get_option('refund_wps_rma_tax_handling', false);
		
			if (wps_rma_pro_active() && $refund_items) {
				return self::process_pro_refund($order, $order_id, $refund_items, $reason, $refund_method, $tax_option);
			}
		
			return self::process_basic_refund($order, $order_id, $reason, $tax_option);
		}

		/**
		 * Process the refund request for Pro version.
		 *
		 * @param object $order         The order object.
		 * @param int    $order_id     The order ID.
		 * @param array  $refund_items The refund items.
		 * @param string $reason       The reason for the refund.
		 * @param string $refund_method The refund method.
		 * @param string $tax_option   The tax option.
		 *
		 * @return array
		 */
		private static function process_pro_refund($order, $order_id, $refund_items, $reason, $refund_method, $tax_option) {
			$response     = [];
			$items_detail = [];
			foreach ($order->get_items() as $item_id => $item) {
				$product = $item->get_product();
				$id      = $product->get_type() === 'variation' ? $item->get_variation_id() : $item->get_product_id();
				$items_detail[$id] = $item->get_quantity();
			}
		
			if ( is_string( $refund_items ) && !wps_json_validate($refund_items)) {
				return ['message' => 'error', 'status' => 404, 'data' => esc_html__('Invalid JSON format.', 'woo-refund-and-exchange-lite')];
			}
			
			$refund_items_array = [];
			$refund_amount = 0;
			$flags = ['valid' => true, 'qty' => true, 'item' => true, 'invalid_item' => true, 'invalid_qty' => true, 'sale_item' => true];
			
			foreach ($order->get_items() as $item_id => $item) {
				$product = $item->get_product();
				$product_id = $item->get_product_id();
				$variation_id = $item->get_variation_id();
				
				$check_sale_item = get_option( 'wps_rma_refund_on_sale', 'no' );
				foreach ($refund_items as $data) {
					$id = isset($data['product_id']) ? $data['product_id'] : (isset($data['variation_id']) ? $data['variation_id'] : 0);
					if (!isset($items_detail[$id]) || !isset($data['qty'])) {
						$flags['item'] = isset($data['product_id']) || isset($data['variation_id']) ? false : true;
						$flags['invalid_item'] = !$flags['item'] ? $flags['invalid_item'] : false;
						$flags['invalid_qty'] = !isset($data['qty']) ? false : $flags['invalid_qty'];
						continue;
					}
					
					if ( $id !== $product_id && $id !== $variation_id ) {
						continue;
					}
					if ( 'on' != $check_sale_item && $product->is_on_sale() ) {
						$flags['sale_item'] = false;
						continue;
					}
					$already_refunded = wps_rma_get_meta_data($order_id, 'wps_rma_request_made', true);

					if ( isset( $already_refunded[$item_id] ) && !empty($already_refunded[$item_id]) && $already_refunded[$item_id] === 'completed') {
						$flags['valid'] = false;
						continue;
					}
		
					if ($data['qty'] > $item->get_quantity() || $data['qty'] <= 0) {
						$flags['qty'] = false;
						continue;
					}
		
					$item_price = $item->get_total() / $item->get_quantity();
					$tax_price  = $item->get_total_tax() / $item->get_quantity();
					$price = $item_price;
					if ( $tax_option === 'wps_rma_inlcude_tax' ) {
						$price += $tax_price;
					} elseif ( $tax_option === 'wps_rma_exclude_tax' ) {
						$price -= $tax_price;
					}
		
					$refund_items_array[] = [
						'product_id'   => $product_id,
						'item_id'      => $item_id,
						'variation_id' => $variation_id,
						'qty'          => $data['qty'],
						'price'        => $price
					];
					$refund_amount += $price;
				}
			}
		
			foreach ($flags as $flag => $value) {
				if (!$value) {
					$messages = [
						'valid'        => 'Return request already accepted for the items.',
						'qty'          => 'Quantity exceeds ordered amount.',
						'item'         => 'Product ID not part of this order.',
						'invalid_item' => 'Missing item ID for refund.',
						'invalid_qty'  => 'Missing quantity for refund.',
						'sale_item'    => 'Sale item not refundable.'
					];
					return ['message' => 'error', 'status' => 404, 'data' => esc_html__($messages[$flag], 'woo-refund-and-exchange-lite')];
				}
			}
		
			$refund_data = [
				'products'      => $refund_items_array,
				'order_id'      => $order_id,
				'subject'       => $reason,
				'refund_method' => $refund_method,
				'amount'        => $refund_amount
			];
		
			$result = wps_rma_save_return_request_callback($order_id, $refund_method, $refund_data);
			if ($result) {
				return ['message' => 'success', 'status' => 200, 'data' => esc_html__('Refund request sent successfully.', 'woo-refund-and-exchange-lite')];
			}
		
			return ['message' => 'error', 'status' => 404, 'data' => esc_html__('An error occurred while submitting the refund request.', 'woo-refund-and-exchange-lite')];
		}
		
		/**
		 * Process the refund request for Basic version.
		 *
		 * @param object $order       The order object.
		 * @param int    $order_id   The order ID.
		 * @param string $reason     The reason for the refund.
		 * @param string $tax_option The tax option.
		 *
		 * @return array
		 */
		private static function process_basic_refund($order, $order_id, $reason, $tax_option) {
			$refund_items = [];
			$refund_amount = 0;
		
			foreach ($order->get_items() as $item_id => $item) {
				$item_price = $item->get_total() / $item->get_quantity();
				$item_tax = $item->get_total_tax() / $item->get_quantity();
		
				$price = $item_price;
				if ($tax_option === 'wps_rma_inlcude_tax') {
					$price += $item_tax;
				} elseif ($tax_option === 'wps_rma_exclude_tax') {
					$price -= $item_tax;
				}
		
				$refund_items[] = [
					'item_id'      => $item_id,
					'product_id'   => $item->get_product_id(),
					'variation_id' => $item->is_type('variable') ? $item->get_variation_id() : 0,
					'qty'          => $item->get_quantity(),
					'price'        => $price
				];
		
				$refund_amount += $price;
			}
		
			$refund_data = [
				'products'      => $refund_items,
				'order_id'      => $order_id,
				'subject'       => $reason,
				'refund_method' => 'manual_method',
				'amount'        => $refund_amount
			];
		
			$existing_requests = wps_rma_get_meta_data($order_id, 'wps_rma_return_product', true);
			foreach ((array)$existing_requests as $request) {
				if ( isset( $request['status'] ) && $request['status'] === 'complete') {
					return ['message' => 'error', 'status' => 404, 'data' => esc_html__('Return request already accepted.', 'woo-refund-and-exchange-lite')];
				}
			}
		
			$result = wps_rma_save_return_request_callback($order_id, 'manual_method', $refund_data);
			if ($result) {
				return ['message' => 'success', 'status' => 200, 'data' => esc_html__('Return request sent successfully.', 'woo-refund-and-exchange-lite')];
			}
		
			return ['message' => 'error', 'status' => 404, 'data' => esc_html__('An error occurred while submitting the return request.', 'woo-refund-and-exchange-lite')];
		}
		

		/**
		 * Define the function to process data for custom endpoint.
		 *
		 * @since    1.0.0
		 * @param   object $wrael_request  data of requesting headers and other information.
		 * @return  Array $wps_rma_rest_response    returns processed data and status of operations.
		 */
		public static function wps_rma_refund_request_accept_process( $wrael_request ) {
			$data      = $wrael_request->get_params();
			$order_id  = isset( $data['order_id'] ) ? absint( $data['order_id'] ) : 0;
			$response  = [ 'status' => 404 ];
		
			if ( ! $order_id ) {
				$response['data'] = esc_html__( 'Please provide the order ID to perform the process.', 'woo-refund-and-exchange-lite' );
				return $response;
			}
		
			$order = wc_get_order( $order_id );
		
			if ( ! $order ) {
				$response['data'] = esc_html__( 'Please provide a valid order ID to perform the process.', 'woo-refund-and-exchange-lite' );
				return $response;
			}
		
			$get_refund_meta         = wps_rma_get_meta_data( $order_id, 'wps_rma_return_product', true );
			$has_pending      = false;
			$has_completed    = false;
		
			if ( ! empty( $get_refund_meta ) ) {
				foreach ( $get_refund_meta as $meta_data ) {
					if ( isset( $meta_data['status'] ) ) {
						if ( 'pending' === $meta_data['status'] ) {
							$has_pending = true;
						} elseif ( 'complete' === $meta_data['status'] ) {
							$has_completed = true;
						}
					}
				}
			}
		
			if ( $has_pending ) {
				$result = wps_rma_return_req_approve_callback( $order_id, $get_refund_meta );
				if ( ! empty( $result ) ) {
					return [
						'status' => 200,
						'data'   => esc_html__( 'Return request accepted successfully.', 'woo-refund-and-exchange-lite' ),
					];
				}
				$response['data'] = esc_html__( 'Some problem occurred while accepting the refund request.', 'woo-refund-and-exchange-lite' );
			} elseif ( $has_completed ) {
				$response['data'] = esc_html__( 'You have already approved the refund request.', 'woo-refund-and-exchange-lite' );
			} else {
				$response['data'] = esc_html__( 'You can only accept a refund request if one has been made earlier.', 'woo-refund-and-exchange-lite' );
			}
		
			return $response;
		}		

		/**
		 * Define the function to process data for custom endpoint.
		 *
		 * @since    1.0.0
		 * @param   object $wrael_request  data of requesting headers and other information.
		 * @return  Array $wps_rma_rest_response    returns processed data and status of operations.
		 */
		public static function wps_rma_refund_request_cancel_process( $wrael_request ) {
			$data     = $wrael_request->get_params();
			$order_id = isset( $data['order_id'] ) ? absint( $data['order_id'] ) : 0;
			$response = [ 'status' => 404 ];
		
			if ( ! $order_id ) {
				$response['data'] = esc_html__( 'Please provide the order ID to perform the process.', 'woo-refund-and-exchange-lite' );
				return $response;
			}
		
			$order = wc_get_order( $order_id );
		
			if ( ! $order ) {
				$response['data'] = esc_html__( 'Please provide a valid order ID to perform the process.', 'woo-refund-and-exchange-lite' );
				return $response;
			}
		
			$get_refund_meta    = wps_rma_get_meta_data( $order_id, 'wps_rma_return_product', true );
			$has_pending = false;
			$has_cancel  = false;
		
			if ( ! empty( $get_refund_meta ) ) {
				foreach ( $get_refund_meta as $meta_data ) {
					if ( isset( $meta_data['status'] ) ) {
						if ( 'pending' === $meta_data['status'] ) {
							$has_pending = true;
						} elseif ( 'cancel' === $meta_data['status'] ) {
							$has_cancel = true;
						}
					}
				}
			}
		
			if ( $has_pending ) {
				$result = wps_rma_return_req_cancel_callback( $order_id, $get_refund_meta, false );
				if ( ! empty( $result ) ) {
					return [
						'status' => 200,
						'data'   => esc_html__( 'Return request cancelled successfully.', 'woo-refund-and-exchange-lite' ),
					];
				}
				$response['data'] = esc_html__( 'Some problem occurred while cancelling the refund request.', 'woo-refund-and-exchange-lite' );
			} elseif ( $has_cancel ) {
				$response['data'] = esc_html__( 'You have already cancelled the refund request.', 'woo-refund-and-exchange-lite' );
			} else {
				$response['data'] = esc_html__( 'You can only cancel a refund request if one has been made earlier.', 'woo-refund-and-exchange-lite' );
			}
		
			return $response;
		}		
	}
}
