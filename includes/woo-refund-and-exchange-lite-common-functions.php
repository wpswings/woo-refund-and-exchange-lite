<?php
/**
 *
 * A class definition that to migrate the previous version setting.
 *
 * @link  https://wpswings.com/
 * @since 1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/includes
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! function_exists( 'wps_rma_show_buttons' ) ) {
	/**
	 * Check all the condition whether to show refund buttons or not.
	 *
	 * @param string $func  is current functinality name i.e refund/exchange/cancel.
	 * @param object $order is the order object.
	 */
	function wps_rma_show_buttons( $func, $order ) {
		$show_button          = 'yes';
		$setting_saved        = get_option( 'policies_setting_option', array() );
		$check                = get_option( 'wps_rma_' . $func . '_enable', false );
		$get_specific_setting = array();
		if ( 'on' === $check ) {
			if ( isset( $setting_saved['wps_rma_setting'] ) && ! empty( $setting_saved['wps_rma_setting'] ) ) {
				foreach ( $setting_saved['wps_rma_setting'] as $key => $value ) {
					if ( $func === $value['row_functionality'] ) {
						array_push( $get_specific_setting, $value );
					}
				}
			}
			$order_date = date_i18n( 'y-m-d', strtotime( $order->get_date_created() ) );
			$today_date = date_i18n( 'y-m-d' );
			$order_date = apply_filters( 'wps_order_status_start_date', strtotime( $order_date ), $order );
			$today_date = strtotime( $today_date );
			$days       = $today_date - $order_date;
			$day_diff   = floor( $days / ( 60 * 60 * 24 ) );

			if ( ! empty( $get_specific_setting ) ) {
				foreach ( $get_specific_setting as $key => $value ) {
					if ( isset( $value['row_policy'] ) && 'wps_rma_tax_handling' === $value['row_policy'] ) {
						if ( isset( $value['row_tax'] ) && 'wps_rma_inlcude_tax' === $value['row_tax'] ) {
							update_option( $func . '_wps_rma_tax_handling', 'wps_rma_inlcude_tax' );
						} elseif ( isset( $value['row_tax'] ) && 'wps_rma_exclude_tax' === $value['row_tax'] ) {
							update_option( $func . '_wps_rma_tax_handling', 'wps_rma_exclude_tax' );
						}
					} else {
						update_option( $func . '_wps_rma_tax_handling', '' );
					}
					if ( isset( $value['row_policy'] ) && 'wps_rma_maximum_days' === $value['row_policy'] ) {
						if ( isset( $value['row_value'] ) && ! empty( $value['row_value'] ) ) {
							if ( isset( $value['row_conditions1'] ) && 'wps_rma_less_than' === $value['row_conditions1'] ) {
								if ( isset( $value['row_value'] ) && $day_diff < floatval( $value['row_value'] ) ) {
									$show_button = 'yes';
								} else {
									$show_button = ucfirst( $func ) . esc_html__( ' days exceed must be less than ', 'woo-refund-and-exchange-lite' ) . $value['row_value'];
									break;
								}
							} elseif ( $value['row_conditions1'] && 'wps_rma_greater_than' === $value['row_conditions1'] ) {
								if ( isset( $value['row_value'] ) && $day_diff > floatval( $value['row_value'] ) ) {
									$show_button = 'yes';
								} else {
									$show_button = ucfirst( $func ) . esc_html__( ' days must be greater than ', 'woo-refund-and-exchange-lite' ) . $value['row_value'];
									break;
								}
							} elseif ( $value['row_conditions1'] && 'wps_rma_less_than_equal' === $value['row_conditions1'] ) {
								if ( isset( $value['row_value'] ) && $day_diff <= floatval( $value['row_value'] ) ) {
									$show_button = 'yes';
								} else {
									$show_button = ucfirst( $func ) . esc_html__( ' days must be less than equal to ', 'woo-refund-and-exchange-lite' ) . $value['row_value'];
									break;
								}
							} elseif ( $value['row_conditions1'] && 'wps_rma_greater_than_equal' === $value['row_conditions1'] ) {
								if ( isset( $value['row_value'] ) && $day_diff >= floatval( $value['row_value'] ) ) {
									$show_button = 'yes';
								} else {
									$show_button = ucfirst( $func ) . esc_html__( ' days must be greater than equal to ', 'woo-refund-and-exchange-lite' ) . $value['row_value'];
									break;
								}
							}
						} else {
							$show_button = ucfirst( $func ) . esc_html__( ' max days is blank', 'woo-refund-and-exchange-lite' );
							break;
						}
					} elseif ( isset( $value['row_policy'] ) && 'wps_rma_order_status' === $value['row_policy'] ) {
						if ( $value['row_conditions2'] && 'wps_rma_equal_to' === $value['row_conditions2'] ) {
							if ( isset( $value['row_statuses'] ) && in_array( 'wc-' . $order->get_status(), $value['row_statuses'], true ) ) {
								$show_button = 'yes';
							} else {
								$show_button = ucfirst( $func ) . esc_html__( ' request can not make on this order.', 'woo-refund-and-exchange-lite' );
								break;
							}
						} elseif ( $value['row_conditions2'] && 'wps_rma_not_equal_to' === $value['row_conditions2'] ) {
							if ( isset( $value['row_statuses'] ) && ! in_array( 'wc-' . $order->get_status(), $value['row_statuses'], true ) ) {
								$show_button = 'yes';
							} else {
								$show_button = ucfirst( $func ) . esc_html__( ' request can not make on this order.', 'woo-refund-and-exchange-lite' );
								break;
							}
						}
					}
				}
			}
		} else {
			$show_button = ucfirst( $func ) . esc_html__( ' request is disabled.', 'woo-refund-and-exchange-lite' );
		}
		$products = wps_rma_get_meta_data( $order->get_id(), 'wps_rma_return_product', true );
		if ( isset( $products ) && ! empty( $products ) && ! wps_rma_pro_active() && 'yes' === $show_button ) {
			foreach ( $products as $date => $product ) {
				if ( 'complete' === $product['status'] ) {
					$show_button = esc_html__( 'Refund request is already made and accepted', 'woo-refund-and-exchange-lite' );
				}
			}
		}
		if ( 'on' === get_option( 'wps_rma_return_time_policy' ) ) {
			$wps_rma_from_time = get_option( 'wps_rma_time_duration_from', false );
			$wps_rma_to_time   = get_option( 'wps_rma_time_duration_to', false );
			if ( $wps_rma_from_time && $wps_rma_to_time && strtotime( current_time( 'h:i A' ) ) < strtotime( $wps_rma_from_time ) || strtotime( current_time( 'h:i A' ) ) > strtotime( $wps_rma_to_time ) ) {
				$show_button = ucfirst( $func ) . esc_html__( 'is not available right now', 'woo-refund-and-exchange-lite' );
			}
		}
		return apply_filters( 'wps_rma_policies_functionality_extend', $show_button, $func, $order, $get_specific_setting );
	}
}

if ( ! function_exists( 'wps_wrma_format_price' ) ) {
	/**
	 * Format the price showing on the frontend and the backend
	 *
	 * @param string $price is current showing price.
	 * @param string $currency_symbol .
	 */
	function wps_wrma_format_price( $price, $currency_symbol ) {
		$price           = apply_filters( 'formatted_woocommerce_price', number_format( $price, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() ), $price, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );
		$price           = apply_filters( 'wps_rma_price_change_everywhere', $price );
		$currency_pos    = get_option( 'woocommerce_currency_pos' );
		switch ( $currency_pos ) {
			case 'left':
				$uprice = $currency_symbol . '<span class="wps_wrma_formatted_price">' . $price . '</span>';
				break;
			case 'right':
				$uprice = '<span class="wps_wrma_formatted_price">' . $price . '</span>' . $currency_symbol;
				break;
			case 'left_space':
				$uprice = $currency_symbol . '&nbsp;<span class="wps_wrma_formatted_price">' . $price . '</span>';
				break;
			case 'right_space':
				$uprice = '<span class="wps_wrma_formatted_price">' . $price . '</span>&nbsp;' . $currency_symbol;
				break;
		}
		return $uprice;
	}
}
if ( ! function_exists( 'wps_rma_pro_active' ) ) {
	/**
	 * Check Pro Active.
	 */
	function wps_rma_pro_active() {
		return apply_filters( 'wps_rma_check_pro_active', false );
	}
}
if ( ! function_exists( 'wps_rma_save_return_request_callback' ) ) {
	/**
	 * This function is a callback function to save return request.
	 *
	 * @param int    $order_id .
	 * @param string $refund_method .
	 * @param array  $products1 .
	 */
	function wps_rma_save_return_request_callback( $order_id, $refund_method, $products1 ) {
		update_option( $order_id . 'wps_rma_refund_method', $refund_method );
		if ( ! is_user_logged_in() ) {
			update_option( $order_id . 'wps_rma_refund_method', 'manual_method' );
		}
		$order = wc_get_order( $order_id );
		if ( empty( wps_rma_get_meta_data( $order_id, 'wps_rma_request_made', true ) ) ) {
			$item_id = array();
		} else {
			$item_id = wps_rma_get_meta_data( $order_id, 'wps_rma_request_made', true );
		}
		$item_ids = array();
		// Gift Card Code Compatibility.
		$gift_card_product = false;
		$gift_item_id      = '';
		$exp_flag          = false;
		if ( isset( $products1['products'] ) && ! empty( $products1['products'] ) && is_array( $products1['products'] ) ) {
			foreach ( $products1['products'] as $post_key => $post_value ) {
				$item_id[ $post_value['item_id'] ] = 'pending';
				$item_ids[]                        = $post_value['item_id'];

				// Giftcard compatibility code.
				$product_id = $post_value['product_id'];

				$product_types = wp_get_object_terms( $product_id, 'product_type' );
				if ( isset( $product_types[0] ) ) {
					$product_type = $product_types[0]->slug;
					if ( 'wgm_gift_card' === $product_type || 'gw_gift_card' === $product_type ) {
						$gift_card_product = true;
						$gift_item_id      = $post_value['item_id'];
					}
				}
			}
		}
		if ( $gift_card_product && ! empty( $gift_item_id ) ) {

			$coupon = wps_rma_get_meta_data( $order_id, $order_id . '#' . $gift_item_id, true );

			$couponcode = $coupon[0];

			$coupons = new WC_Coupon( $couponcode );

			$usage_count = $coupons->usage_count;

			$exp_date = $coupons->get_data();
			if ( isset( $exp_date['date_expires'] ) && ! empty( $exp_date['date_expires'] ) ) {
				$expiry_date   = $exp_date['date_expires']->date( 'd M Y H:i:s' );
				$now_date      = date_i18n( wc_date_format(), time() ) . ' ' . date_i18n( wc_time_format(), time() );
				$todaydatetime = strtotime( $now_date );
				$expdatetime   = strtotime( $expiry_date );
				$diff          = $expdatetime - $todaydatetime;
				if ( $diff < 0 ) {
					$exp_flag = true;
				}
			}

			if ( $exp_flag ) {
				$response['flag'] = false;
				$response['msg']  = esc_html__( 'Your Giftcard has been expired so you can not proceed with the exchange. Thanks', 'woo-refund-and-exchange-lite' );

				return $response;
			}

			if ( ! empty( $usage_count ) ) {
				$response['flag'] = false;
				$response['msg']  = esc_html__( 'Your Giftcard has been used so you can not proceed with the exchange. Thanks', 'woo-refund-and-exchange-lite' );

				return $response;
			}
		}
		wps_rma_update_meta_data( $order_id, 'wps_rma_request_made', $item_id );
		$products = wps_rma_get_meta_data( $order_id, 'wps_rma_return_product', true );
		$pending  = true;
		if ( isset( $products ) && ! empty( $products ) ) {
			foreach ( $products as $date => $product ) {
				if ( 'pending' === $product['status'] ) {
						$products[ $date ]           = $products1;
						$products[ $date ]['status'] = 'pending'; // update requested products.
						$pending                     = false;
						break;
				}
			}
		}
		if ( $pending ) {
			if ( ! is_array( $products ) ) {
				$products = array();
			}
			$products                    = array();
			$date                        = time();
			$products[ $date ]           = $products1;
			$products[ $date ]['status'] = 'pending';

		}

		wps_rma_update_meta_data( $order_id, 'wps_rma_return_product', $products );

		// Send refund request email to admin and customer.

		$restrict_mail = apply_filters( 'wps_rma_restrict_refund_request_mails', true );
		if ( $restrict_mail ) {
			do_action( 'wps_rma_refund_req_email', $order_id );
		}
		do_action( 'wps_rma_do_something_on_refund', $order_id, $item_ids );

		$order->update_status( 'wc-return-requested' );
		$order->add_order_note( esc_html__( 'User Request to refund product', 'woo-refund-and-exchange-lite' ), true );

		$response['auto_accept'] = apply_filters( 'wps_rma_auto_accept_refund', false );
		$response['flag']        = true;
		$response['msg']         = esc_html__( 'Refund request placed successfully. You have received a notification mail regarding this. You will redirect to the My Account Page', 'woo-refund-and-exchange-lite' );
		return $response;
	}
}
if ( ! function_exists( 'wps_rma_return_req_approve_callback' ) ) {
	/**
	 * Accept return request approve callback.
	 *
	 * @param string  $orderid .
	 * @param array() $products .
	 */
	function wps_rma_return_req_approve_callback( $orderid, $products ) {

		// Fetch and update the return request product.
		if ( isset( $products ) && ! empty( $products ) ) {
			foreach ( $products as $date => $product ) {
				if ( 'pending' === $product['status'] ) {
					$product_datas                     = $product['products'];
					$products[ $date ]['status']       = 'complete';
					$approvdate                        = time();
					$products[ $date ]['approve_date'] = $approvdate;
					break;
				}
			}
		}

		// Update the status.
		wps_rma_update_meta_data( $orderid, 'wps_rma_return_product', $products );

		$request_files = wps_rma_get_meta_data( $orderid, 'wps_rma_return_attachment', true );
		if ( isset( $request_files ) && ! empty( $request_files ) ) {
			foreach ( $request_files as $date => $request_file ) {
				if ( 'pending' === $request_file['status'] ) {
					$request_files[ $date ]['status'] = 'complete';
					break;
				}
			}
		}
		// Update the status.
		wps_rma_update_meta_data( $orderid, 'wps_rma_return_attachment', $request_files );
		$order_obj            = wc_get_order( $orderid );
		$line_items_refund    = array();
		$wps_rma_check_tax    = get_option( 'refund_wps_rma_tax_handling', false );
		$coupon_discount      = get_option( 'wps_rma_refund_deduct_coupon', 'no' );
		$refund_items_details = wps_rma_get_meta_data( $orderid, 'wps_rma_refund_items_details', true );
		if ( ! is_array( $refund_items_details ) ) {
			$refund_items_details = array();
		}
		// add refund item related info for wc_create_refund.
		if ( isset( $product_datas ) && ! empty( $product_datas ) ) {
			foreach ( $order_obj->get_items() as $item_id => $item ) {
				$product = apply_filters( 'woocommerce_order_item_product', $order_obj->get_product_from_item( $item ), $item );
				foreach ( $product_datas as $requested_product ) {
					if ( $item_id == $requested_product['item_id'] ) {
						if ( $item['product_id'] == $requested_product['product_id'] || $item['variation_id'] == $requested_product['variation_id'] ) {
							$product = apply_filters( 'woocommerce_order_item_product', $order_obj->get_product_from_item( $item ), $item );
							if ( 'on' === $coupon_discount ) {
								$prod_price = $item->get_total();
							} else {
								$prod_price = $item->get_subtotal();
							}
							if ( 'wps_rma_inlcude_tax' === $wps_rma_check_tax ) {
								$item_tax                                    = $item->get_subtotal_tax() / $requested_product['qty'];
								$line_items_refund[ $item_id ]['refund_tax'] = array( 1 => $item_tax );
							} elseif ( 'wps_rma_exclude_tax' === $wps_rma_check_tax ) {
								$prod_price -= $item->get_subtotal_tax() / $requested_product['qty'];
							}
							$line_items_refund[ $item_id ]['qty']          = $requested_product['qty'];
							$line_items_refund[ $item_id ]['refund_total'] = wc_format_decimal( $prod_price * $requested_product['qty'] / $item->get_quantity() );

							if ( ! empty( $refund_items_details ) && isset( $refund_items_details[ $item_id ] ) ) {
								$get_qty                          = $refund_items_details[ $item_id ];
								$refund_items_details[ $item_id ] = $get_qty + $requested_product['qty'];
							} else {
								$refund_items_details[ $item_id ] = $requested_product['qty'];
							}
						}
					}
				}
			}
			$pro_active = wps_rma_pro_active();
			$wps_rma_allow_refund_shipping_charge = get_option( 'wps_rma_allow_refund_shipping_charge' );

			if ( empty( $pro_active ) && 'on' == $wps_rma_allow_refund_shipping_charge ) {

				$shipping_methods = $order_obj->get_items( 'shipping' );
				if ( ! empty( $shipping_methods ) && is_array( $shipping_methods ) ) {
					foreach ( $shipping_methods as $index => $shipping_method ) {

						$new_refund_item = array(
							'refund_tax' => array(
								1 => $shipping_method->get_total_tax(),
							),
							'qty' => 1,
							'refund_total' => $shipping_method->get_total(),

						);
						$line_items_refund[ $index ] = $new_refund_item;
					}
				}
			}
		}
		wps_rma_update_meta_data( $orderid, 'wps_rma_refund_items_details', $refund_items_details );
		if ( ! empty( $line_items_refund ) ) {
			$refund_items_details = array(
				'amount'         => 0,
				'reason'         => esc_html__( 'Added the return item info', 'woo-refund-and-exchange-lite' ),
				'order_id'       => $orderid,
				'line_items'     => $line_items_refund,
				'refund_payment' => false,
				'restock_items'  => apply_filters( 'wps_rma_auto_restock_item_refund', false, $orderid ),
			);
			wps_rma_update_meta_data( $orderid, 'wps_rma_refund_items', $refund_items_details );
		}

		$update_item_status = wps_rma_get_meta_data( $orderid, 'wps_rma_request_made', true );
		foreach ( wps_rma_get_meta_data( $orderid, 'wps_rma_return_product', true ) as $key => $value ) {
			foreach ( $value['products'] as $key => $value ) {
				if ( isset( $update_item_status[ $value['item_id'] ] ) ) {
					$update_item_status[ $value['item_id'] ] = 'completed';
				}
			}
		}
		wps_rma_update_meta_data( $orderid, 'wps_rma_request_made', $update_item_status );
		// Send refund request accept email to customer.

		do_action( 'wps_rma_return_request_accept', $line_items_refund, $orderid );

		$restrict_mail =
		// Allow/Disallow Email.
		apply_filters( 'wps_rma_restrict_refund_app_mails', true );
		if ( $restrict_mail ) {
			// To Send Refund Request Accept Email.
			do_action( 'wps_rma_refund_req_accept_email', $orderid );
		}

		$order_obj->update_status( 'wc-return-approved' );
		$order_obj->add_order_note( esc_html__( 'User Request of Refund Product is approved', 'woo-refund-and-exchange-lite' ), true );
		$response             = array();
		$response['response'] = 'success';
		return $response;
	}
}
if ( ! function_exists( 'wps_rma_return_req_cancel_callback' ) ) {
	/**
	 * Cancel return request cancel callback.
	 *
	 * @param string $orderid .
	 * @param array  $products .
	 * @param bool   $is_customer_cancelled .
	 */
	function wps_rma_return_req_cancel_callback( $orderid, $products, $is_customer_cancelled ) {
		// Fetch the return request product.
		if ( isset( $products ) && ! empty( $products ) ) {
			foreach ( $products as $date => $product ) {
				if ( 'pending' === $product['status'] ) {
					$product_datas                    = $product['products'];
					$products[ $date ]['status']      = 'cancel';
					$canceldate                       = time();
					$products[ $date ]['cancel_date'] = $canceldate;
					break;
				}
			}
		}
		// Update the status.
		wps_rma_update_meta_data( $orderid, 'wps_rma_return_product', $products );

		$request_files = wps_rma_get_meta_data( $orderid, 'wps_rma_return_attachment', true );
		if ( isset( $request_files ) && ! empty( $request_files ) ) {
			foreach ( $request_files as $date => $request_file ) {
				if ( 'pending' === $request_file['status'] ) {
					$request_files[ $date ]['status'] = 'cancel';
				}
			}
		}
		// Update the status.
		wps_rma_update_meta_data( $orderid, 'wps_rma_return_attachment', $request_files );

		// Send the cancel refund request email to customer.

		$restrict_mail =
		// Allow/Disallow Email.
		apply_filters( 'wps_rma_restrict_refund_cancel_mails', true );
		if ( $restrict_mail ) {
			// To Send Refund Request Cancel Email.
			do_action( 'wps_rma_refund_req_cancel_email', $orderid );
		}

		do_action( 'wps_rma_return_request_cancel', $products, $orderid );

		$order_obj = wc_get_order( $orderid );

		$order_obj->update_status( 'wc-return-cancelled' );
		if ( $is_customer_cancelled ) {
			$order_obj->add_order_note( esc_html__( 'User Request of Refund is cancelled by customer', 'woo-refund-and-exchange-lite' ), true );
		} else {
			$order_obj->add_order_note( esc_html__( 'User Request of Refund is cancelled', 'woo-refund-and-exchange-lite' ), true );
		}
		$response             = array();
		$response['response'] = 'success';
		return $response;
	}
}
if ( ! function_exists( 'wps_json_validate' ) ) {
	/**
	 * Validate the json string .
	 *
	 * @param string $string .
	 */
	function wps_json_validate( $string ) {
		// decode the JSON data .
		$result = json_decode( $string );
		// switch and check possible JSON errors .
		switch ( json_last_error() ) {
			case JSON_ERROR_NONE:
				$error = ''; // JSON is valid // No error has occurred.
				break;
			case JSON_ERROR_DEPTH:
				$error = 'The maximum stack depth has been exceeded.';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'Invalid or malformed JSON.';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'Control character error, possibly incorrectly encoded.';
				break;
			case JSON_ERROR_SYNTAX:
				$error = 'Syntax error, malformed JSON.';
				break;
			// PHP >= 5.3.3 .
			case JSON_ERROR_UTF8:
				$error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
				break;
			// PHP >= 5.5.0 .
			case JSON_ERROR_RECURSION:
				$error = 'One or more recursive references in the value to be encoded.';
				break;
			// PHP >= 5.5.0 .
			case JSON_ERROR_INF_OR_NAN:
				$error = 'One or more NAN or INF values in the value to be encoded.';
				break;
			case JSON_ERROR_UNSUPPORTED_TYPE:
				$error = 'A value of a type that cannot be encoded was given.';
				break;
			default:
				$error = 'Unknown JSON error occured.';
				break;
		}

		if ( '' !== $error ) {
			// throw the Exception or exit // or whatever :) .
			exit( esc_html( $error ) );
		}
		// everything is OK .
		return $result;
	}
}
if ( ! function_exists( 'wps_rma_standard_check_multistep' ) ) {
	/** Check multistep to show */
	function wps_rma_standard_check_multistep() {
		$bool               = false;
		$wps_standard_check = get_option( 'wps_rma_plugin_standard_multistep_done', false );
		if ( ! empty( $wps_standard_check ) ) {
			$bool = true;
		}
		$check_refund   = get_option( 'mwb_rma_refund_enable', false );
		$check_refund2  = get_option( 'mwb_wrma_return_enable', false );
		$check_exchange = get_option( 'mwb_rma_exchange_enable', false );
		if ( $check_refund || $check_exchange || $check_refund2 ) {
			$bool = true;
		}
		$bool = apply_filters( 'wps_standard_multistep_done', $bool );
		return $bool;
	}
}
if ( ! function_exists( 'wps_rma_order_number' ) ) {
	/**
	 * Check Pro Active.
	 */
	/**
	 * Return the correct order number
	 *
	 * @param int $order_id .
	 * @return $order_id
	 */
	function wps_rma_order_number( $order_id ) {
		$active_plugins = get_option( 'active_plugins', array() );
		if ( in_array( 'woocommerce-sequential-order-numbers-pro/woocommerce-sequential-order-numbers-pro.php', $active_plugins, true ) ) {
			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				// HPOS usage is enabled.
				$order = wc_get_order( $order_id );
				$val   = $order->get_meta( '_order_number_formatted' );
			} else {
				// Traditional CPT-based orders are in use.
				$val = get_post_meta( $order_id, '_order_number_formatted', true );
			}
			if ( ! empty( $val ) ) {
				$order_id = $val;
			}
		} elseif ( in_array( 'wt-woocommerce-sequential-order-numbers/wt-advanced-order-number.php', $active_plugins, true ) ) {
			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				// HPOS usage is enabled.
				$order = wc_get_order( $order_id );
				$val   = $order->get_meta( '_order_number' );
			} else {
				// Traditional CPT-based orders are in use.
				$val = get_post_meta( $order_id, '_order_number', true );
			}
			if ( ! empty( $val ) ) {
				$order_id = $val;
			}
		}
		return $order_id;
	}
}

if ( ! function_exists( 'wps_rma_css_and_js_load_page' ) ) {
	/** Css and js file load */
	function wps_rma_css_and_js_load_page() {
		$load_flag         = false;
		$return_page_id    = get_option( 'wps_rma_return_request_form_page_id' );
		$order_msg_page_id = get_option( 'wps_rma_view_order_msg_page_id' );
		$exchange_page_id  = get_option( 'wps_rma_exchange_req_page' );
		$cancel_page_id    = get_option( 'wps_rma_cancel_req_page' );
		$guest_page_id     = get_option( 'wps_rma_guest_form_page' );

		if ( has_filter( 'wpml_object_id' ) ) {
			$return_page_id0    = apply_filters( 'wpml_object_id', $return_page_id, 'page', false, ICL_LANGUAGE_CODE );
			$order_msg_page_id0 = apply_filters( 'wpml_object_id', $order_msg_page_id, 'page', false, ICL_LANGUAGE_CODE );
			$exchange_page_id0  = apply_filters( 'wpml_object_id', $exchange_page_id, 'page', false, ICL_LANGUAGE_CODE );
			$cancel_page_id0    = apply_filters( 'wpml_object_id', $cancel_page_id, 'page', false, ICL_LANGUAGE_CODE );
			$guest_page_id0     = apply_filters( 'wpml_object_id', $guest_page_id, 'page', false, ICL_LANGUAGE_CODE );
		}
		if ( is_order_received_page() || is_account_page() || ( is_page( $return_page_id ) || is_page( $order_msg_page_id ) || is_page( $exchange_page_id ) || is_page( $cancel_page_id ) || is_page( $guest_page_id ) ) || ( has_filter( 'wpml_object_id' ) && ( is_page( $return_page_id0 ) || is_page( $order_msg_page_id0 ) || is_page( $exchange_page_id0 ) || is_page( $cancel_page_id0 ) || is_page( $guest_page_id0 ) ) ) ) {
			$load_flag = true;
		} elseif ( WC()->session && WC()->session->get( 'wps_wrma_exchange' ) && ( is_shop() || is_product() ) ) {
			$load_flag = true;
		}
		return apply_filters( 'wps_rma_css_and_js_load_page', $load_flag );
	}
}

if ( ! function_exists( 'wps_rma_generate_random_filename' ) ) {
	/**
	 * Generate a random string of specified length.
	 * 
	 * @param string $extension .
	 * @param int    $length .
	 */
	function wps_rma_generate_random_filename($extension = "jpg", $length = 10) {
		$random_string = bin2hex(random_bytes($length / 2));
	
		// Ensure the extension is properly formatted.
		$extension = ltrim($extension, '.');
	
		// Return the full filename with the extension.
		return $random_string . '.' . $extension;
	}
}