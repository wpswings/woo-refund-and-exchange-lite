<?php
/**
 *
 * A class definition that to migrate the previous version setting.
 *
 * @link  https://makewebbetter.com/
 * @since 1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/includes
 */

/**
 * Check all the condition whether to show refund buttons or not.
 *
 * @param string $func  is current functinality name i.e refund/exchange/cancel.
 * @param object $order is the order object.
 */
function mwb_rma_show_buttons( $func, $order ) {
	$show_button          = 'no';
	$setting_saved        = get_option( 'policies_setting_option', array() );
	$check                = get_option( 'mwb_rma_' . $func . '_enable', false );
	$get_specific_setting = array();
	if ( 'on' === $check ) {
		if ( isset( $setting_saved['mwb_rma_setting'] ) && ! empty( $setting_saved['mwb_rma_setting'] ) ) {
			foreach ( $setting_saved['mwb_rma_setting'] as $key => $value ) {
				if ( $func === $value['row_functionality'] ) {
					array_push( $get_specific_setting, $value );
				}
			}
		}
		$order_date = date_i18n( 'F d Y', strtotime( $order->get_date_created() ) );
		$today_date = date_i18n( 'F d Y' );
		$order_date = apply_filters( 'mwb_order_status_start_date', strtotime( $order_date ), $order );
		$today_date = strtotime( $today_date );
		$days       = $today_date - $order_date;
		$day_diff   = floor( $days / ( 60 * 60 * 24 ) );
		// Check tax handing exist in array.
		$check_tax_handling = strpos( wp_json_encode( $get_specific_setting ), 'mwb_rma_tax_handling' ) > 0 ? true : false;
		if ( ! $check_tax_handling ) {
			update_option( $order->get_id() . 'check_tax', '' );
		}
		if ( empty( $get_specific_settings ) ) {
			$show_button = 'yes';
		}
		if ( ! empty( $get_specific_setting ) ) {
			foreach ( $get_specific_setting as $key => $value ) {
				if ( isset( $value['row_policy'] ) && 'mwb_rma_tax_handling' === $value['row_policy'] ) {
					if ( isset( $value['row_tax'] ) && 'mwb_rma_inlcude_tax' === $value['row_tax'] ) {
						update_option( $order->get_id() . 'check_tax', 'mwb_rma_inlcude_tax' );
					} elseif ( isset( $value['row_tax'] ) && 'mwb_rma_exclude_tax' === $value['row_tax'] ) {
						update_option( $order->get_id() . 'check_tax', 'mwb_rma_exclude_tax' );
					}
				}
				if ( isset( $value['row_policy'] ) && 'mwb_rma_maximum_days' === $value['row_policy'] ) {
					if ( isset( $value['row_value'] ) && ! empty( $value['row_value'] ) ) {
						if ( isset( $value['row_conditions1'] ) && 'mwb_rma_less_than' === $value['row_conditions1'] ) {
							if ( isset( $value['row_value'] ) && $day_diff < floatval( $value['row_value'] ) ) {
								$show_button = 'yes';
							} else {
								$show_button = ucfirst( $func ) . esc_html__( ' days exceed must be less than ', 'woo-refund-and-exchange-lite' ) . $value['row_value'];
								break;
							}
						} elseif ( $value['row_conditions1'] && 'mwb_rma_greater_than' === $value['row_conditions1'] ) {
							if ( isset( $value['row_value'] ) && $day_diff > floatval( $value['row_value'] ) ) {
								$show_button = 'yes';
							} else {
								$show_button = ucfirst( $func ) . esc_html__( ' days must be greater than ', 'woo-refund-and-exchange-lite' ) . $value['row_value'];
								break;
							}
						} elseif ( $value['row_conditions1'] && 'mwb_rma_less_than_equal' === $value['row_conditions1'] ) {
							if ( isset( $value['row_value'] ) && $day_diff <= floatval( $value['row_value'] ) ) {
								$show_button = 'yes';
							} else {
								$show_button = ucfirst( $func ) . esc_html__( ' days must be less than equal to ', 'woo-refund-and-exchange-lite' ) . $value['row_value'];
								break;
							}
						} elseif ( $value['row_conditions1'] && 'mwb_rma_greater_than_equal' === $value['row_conditions1'] ) {
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
				} elseif ( isset( $value['row_policy'] ) && 'mwb_rma_order_status' === $value['row_policy'] ) {
					if ( $value['row_conditions2'] && 'mwb_rma_equal_to' === $value['row_conditions2'] ) {
						if ( isset( $value['row_statuses'] ) && in_array( 'wc-' . $order->get_status(), $value['row_statuses'], true ) ) {
							$show_button = 'yes';
						} else {
							$show_button = ucfirst( $func ) . esc_html__( ' request can\'t make on this order.', 'woo-refund-and-exchange-lite' );
							break;
						}
					} elseif ( $value['row_conditions2'] && 'mwb_rma_not_equal_to' === $value['row_conditions2'] ) {
						if ( isset( $value['row_statuses'] ) && ! in_array( 'wc-' . $order->get_status(), $value['row_statuses'], true ) ) {
							$show_button = 'yes';
						} else {
							$show_button = ucfirst( $func ) . esc_html__( ' request can\'t make on this order.', 'woo-refund-and-exchange-lite' );
							break;
						}
					}
				}
			}
		}
	} else {
		$show_button = ucfirst( $func ) . esc_html__( ' request is disabled.', 'woo-refund-and-exchange-lite' );
	}
	$products = get_post_meta( $order->get_id(), 'mwb_rma_return_product', true );
	if ( isset( $products ) && ! empty( $products ) && ! mwb_rma_pro_active() && 'yes' === $show_button ) {
		foreach ( $products as $date => $product ) {
			if ( 'complete' === $product['status'] ) {
				$show_button = esc_html__( 'Refund request is already made and accepted', 'woo-refund-and-exchange-lite' );
			}
		}
	}
	return apply_filters( 'mwb_rma_policies_functionality_extend', $show_button, $func, $order, $get_specific_setting );
}


/**
 * Function to send messages.
 *
 * @name admin_setced_rnx_lite_send_order_msg_callback
 * @param string $order_id order id.
 * @param string $msg message.
 * @param string $sender sender.
 * @param string $to message to sent.
 * @link http://www.makewebbetter.com/
 */
function mwb_rma_lite_send_order_msg_callback( $order_id, $msg, $sender, $to ) {
	$flag       = false;
	$filename   = array();
	$attachment = array();
	if ( isset( $_FILES['mwb_order_msg_attachment']['tmp_name'] ) && ! empty( $_FILES['mwb_order_msg_attachment']['tmp_name'] ) ) {
		$count         = count( $_FILES['mwb_order_msg_attachment']['tmp_name'] );
		$file_uploaded = false;
		if ( isset( $_FILES['mwb_order_msg_attachment']['tmp_name'][0] ) && ! empty( $_FILES['mwb_order_msg_attachment']['tmp_name'][0] ) ) {
			$file_uploaded = true;
		}
		if ( $file_uploaded ) {
			for ( $i = 0; $i < $count; $i++ ) {
				if ( isset( $_FILES['mwb_order_msg_attachment']['tmp_name'][ $i ] ) ) {
					$directory = ABSPATH . 'wp-content/attachment';
					if ( ! file_exists( $directory ) ) {
						mkdir( $directory, 0755, true );
					}
					$sourcepath             = sanitize_text_field( wp_unslash( $_FILES['mwb_order_msg_attachment']['tmp_name'][ $i ] ) );
					$f_name                 = isset( $_FILES['mwb_order_msg_attachment']['name'][ $i ] ) ? sanitize_text_field( wp_unslash( $_FILES['mwb_order_msg_attachment']['name'][ $i ] ) ) : '';
					$targetpath             = $directory . '/' . $order_id . '-' . $f_name;
					$attachment[ $i ]       = $targetpath;
					$filename[ $i ]['name'] = isset( $_FILES['mwb_order_msg_attachment']['name'][ $i ] ) ? sanitize_text_field( wp_unslash( $_FILES['mwb_order_msg_attachment']['name'][ $i ] ) ) : '';
					$file_type              = isset( $_FILES['mwb_order_msg_attachment']['type'][ $i ] ) ? sanitize_text_field( wp_unslash( $_FILES['mwb_order_msg_attachment']['type'][ $i ] ) ) : '';
					if ( 'image/png' === $file_type || 'image/jpeg' === $file_type || 'image/jpg' === $file_type ) {
						$filename[ $i ] ['img'] = true;
					} else {
						$filename[ $i ]['img'] = false;
					}
					move_uploaded_file( $sourcepath, $targetpath );
				}
			}
		}
	}
	$date                         = strtotime( gmdate( 'Y-m-d H:i:s' ) );
	$order_msg[ $date ]['sender'] = $sender;
	$order_msg[ $date ]['msg']    = $msg;
	$order_msg[ $date ]['files']  = $filename;
	$get_msg                      = get_option( $order_id . '-mwb_cutomer_order_msg', array() );
	$msg_count                    = get_post_meta( $order_id, 'mwb_order_msg_count', 0 );
	if ( isset( $get_msg ) && ! empty( $get_msg ) ) {
		array_push( $get_msg, $order_msg );
	} else {
		$get_msg = array();
		array_push( $get_msg, $order_msg );
	}
	update_option( $order_id . '-mwb_cutomer_order_msg', $get_msg );
	$restrict_mail =
	// Allow/Disallow Email.
	apply_filters( 'mwb_rma_restrict_order_msg_mails', false );
	if ( ! $restrict_mail ) {
		$customer_email = WC()->mailer()->emails['mwb_rma_order_messages_email'];
		$email_status   = $customer_email->trigger( $msg, $attachment, $to, $order_id );
	}
	$flag = true;
	return $flag;
}

/**
 * Format the price showing on the frontend and the backend
 *
 * @param string $price is current showing price.
 * @param string $currency_symbol .
 */
function mwb_wrma_format_price( $price, $currency_symbol ) {
	$price           = apply_filters( 'mwb_rma_price_change_everywhere', $price );
	$currency_pos    = get_option( 'woocommerce_currency_pos' );
	switch ( $currency_pos ) {
		case 'left':
			$uprice = $currency_symbol . '<span class="mwb_wrma_formatted_price">' . $price . '</span>';
			break;
		case 'right':
			$uprice = '<span class="mwb_wrma_formatted_price">' . $price . '</span>' . $currency_symbol;
			break;
		case 'left_space':
			$uprice = $currency_symbol . '&nbsp;<span class="mwb_wrma_formatted_price">' . $price . '</span>';
			break;
		case 'right_space':
			$uprice = '<span class="mwb_wrma_formatted_price">' . $price . '</span>&nbsp;' . $currency_symbol;
			break;
	}
	return $uprice;
}

/**
 * Check Pro Active.
 */
function mwb_rma_pro_active() {
	$pro_active = false;
	$pro_active = apply_filters( 'mwb_rma_check_pro_active', $pro_active );
	return $pro_active;
}


/**
 * This function is a callback function to save return request.
 *
 * @param int    $order_id .
 * @param string $refund_method .
 * @param array  $products1 .
 */
function mwb_rma_save_return_request_callback( $order_id, $refund_method, $products1 ) {
	update_option( $order_id . 'mwb_rma_refund_method', $refund_method );
	$order = wc_get_order( $order_id );
	if ( empty( get_post_meta( $order_id, 'mwb_rma_request_made', true ) ) ) {
		$item_id = array();
	} else {
		$item_id = get_post_meta( $order_id, 'mwb_rma_request_made', true );
	}
	$item_ids = array();
	if ( isset( $products1['products'] ) && ! empty( $products1['products'] ) && is_array( $products1['products'] ) ) {
		foreach ( $products1['products'] as $post_key => $post_value ) {
			$item_id[ $post_value['item_id'] ] = 'pending';
			$item_ids[]                        = $post_value['item_id'];
		}
	}
	update_post_meta( $order_id, 'mwb_rma_request_made', $item_id );
	$products = get_post_meta( $order_id, 'mwb_rma_return_product', true );
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
		$date                        = date_i18n( wc_date_format(), time() );
		$products[ $date ]           = $products1;
		$products[ $date ]['status'] = 'pending';

	}

	update_post_meta( $order_id, 'mwb_rma_return_product', $products );

	// Send refund request email to admin and customer.

	$restrict_mail = apply_filters( 'mwb_rma_restrict_refund_request_mails', true );
	if ( $restrict_mail ) {
		do_action( 'mwb_rma_refund_req_email', $order_id );
	}
	do_action( 'mwb_rma_do_something_on_refund', $order_id, $item_ids );

	$order->update_status( 'wc-return-requested', esc_html__( 'User Request to refund product', 'woo-refund-and-exchange-lite' ) );

	$response['auto_accept'] = apply_filters( 'mwb_rma_auto_accept_refund', false );
	$response['flag']        = true;
	$response['msg']         = esc_html__( 'Refund request placed successfully. You have received a notification mail regarding this. You will redirect to the My Account Page', 'woo-refund-and-exchange-lite' );
	return $response;
}

/**
 * Accept return request approve callback.
 *
 * @param string  $orderid .
 * @param array() $products .
 */
function mwb_rma_return_req_approve_callback( $orderid, $products ) {
	// Fetch and update the return request product.
	if ( isset( $products ) && ! empty( $products ) ) {
		foreach ( $products as $date => $product ) {
			if ( 'pending' === $product['status'] ) {
				$product_datas                     = $product['products'];
				$products[ $date ]['status']       = 'complete';
				$approvdate                        = date_i18n( wc_date_format(), time() );
				$products[ $date ]['approve_date'] = $approvdate;
				break;
			}
		}
	}

	// Update the status.
	update_post_meta( $orderid, 'mwb_rma_return_product', $products );

	$request_files = get_post_meta( $orderid, 'mwb_rma_return_attachment', true );
	if ( isset( $request_files ) && ! empty( $request_files ) ) {
		foreach ( $request_files as $date => $request_file ) {
			if ( 'pending' === $request_file['status'] ) {
				$request_files[ $date ]['status'] = 'complete';
				break;
			}
		}
	}
	// Update the status.
	update_post_meta( $orderid, 'mwb_rma_return_attachment', $request_files );
	$total_price = 0;
	$order_obj = wc_get_order( $orderid );
	// Reduce the order item qty because of return.
	if ( isset( $product_datas ) && ! empty( $product_datas ) ) {
		foreach ( $order_obj->get_items() as $item_id => $item ) {
			$product = apply_filters( 'woocommerce_order_item_product', $order_obj->get_product_from_item( $item ), $item );
			foreach ( $product_datas as $requested_product ) {
				if ( $item_id == $requested_product['item_id'] ) {
					if ( $item['product_id'] == $requested_product['product_id'] || $item['variation_id'] == $requested_product['variation_id']) {
						$product     = apply_filters( 'woocommerce_order_item_product', $order_obj->get_product_from_item( $item ), $item );
						$item['qty'] = $item['qty'] - $requested_product['qty'];
						$args['qty'] = $item['qty'];
						wc_update_order_item_meta( $item_id, '_qty', $item['qty'] );

						$product = wc_get_product($product->get_id());

						if ( $product->backorders_require_notification() && $product->is_on_backorder( $args['qty'] ) ) {
							$item->add_meta_data( apply_filters( 'woocommerce_backordered_item_meta_name', __( 'Backordered', 'woocommerce' ) ), $args['qty'] - max( 0, $product->get_stock_quantity() ), true );
						}
						$item_data          = $item->get_data();
						$price_excluded_tax = wc_get_price_excluding_tax($product, array( 'qty' => 1 ));
						$price_tax_excluded = $item_data['total']/$item_data['quantity'];
						$args['subtotal']   = $price_excluded_tax*$args['qty'];
						$args['total']	    = $price_tax_excluded*$args['qty'];
						$item->set_order_id( $orderid );
						$item->set_props( $args );
						$item->save();
						$total_price += $requested_product['price'] * $requested_product['qty'];
						/* translators1: product name .
							* translators2: product qty.
							*/
						$order_obj->add_order_note( sprintf( __( '%s %s Item Quantity has been reduce because of the return', 'woo-refund-and-exchange-lite' ), $product->get_name(), $requested_product['qty'] ), false, true );
					}
				}
			}
		}
	}
	if ( $total_price > 0 ) {
		$new_fee = new WC_Order_Item_Fee();
		$new_fee->set_name( esc_attr( 'Refundable Amount' ) );
		$new_fee->set_total( $total_price );
		$new_fee->set_tax_class( '' );
		$new_fee->set_tax_status( 'none' );
		$new_fee->save();
		$order_obj->add_item( $new_fee );
	}
	$update_item_status = get_post_meta( $orderid, 'mwb_rma_request_made', true );
	foreach ( get_post_meta( $orderid, 'mwb_rma_return_product', true ) as $key => $value ) {
		foreach ( $value['products'] as $key => $value ) {
			if ( isset( $update_item_status[ $value['item_id'] ] ) ) {
				$update_item_status[ $value['item_id'] ] = 'completed';
			}
		}
	}
	update_post_meta( $orderid, 'mwb_rma_request_made', $update_item_status );
	// Send refund request accept email to customer.

	$restrict_mail =
	// Allow/Disallow Email.
	apply_filters( 'mwb_rma_restrict_refund_app_mails', true );
	if ( $restrict_mail ) {
		// To Send Refund Request Accept Email.
		do_action( 'mwb_rma_refund_req_accept_email', $orderid );
	}
	// Partial Stock Manage.
	do_action( 'mwb_rma_refund_partial_stock_product', $orderid );
	$order_obj->update_status( 'wc-return-approved', esc_html__( 'User Request of Refund Product is approved', 'woo-refund-and-exchange-lite' ) );
	$response             = array();
	$response['response'] = 'success';
	return $response;
}

/**
 * Cancel return request cancel callback.
 *
 * @param string  $orderid .
 * @param array() $products .
 */
function mwb_rma_return_req_cancel_callback( $orderid, $products ) {
	// Fetch the return request product.
	if ( isset( $products ) && ! empty( $products ) ) {
		foreach ( $products as $date => $product ) {
			if ( 'pending' === $product['status'] ) {
				$product_datas                    = $product['products'];
				$products[ $date ]['status']      = 'cancel';
				$canceldate                       = date_i18n( wc_date_format(), time() );
				$products[ $date ]['cancel_date'] = $canceldate;
				break;
			}
		}
	}
	// Update the status.
	update_post_meta( $orderid, 'mwb_rma_return_product', $products );

	$request_files = get_post_meta( $orderid, 'mwb_rma_return_attachment', true );
	if ( isset( $request_files ) && ! empty( $request_files ) ) {
		foreach ( $request_files as $date => $request_file ) {
			if ( 'pending' === $request_file['status'] ) {
				$request_files[ $date ]['status'] = 'cancel';
			}
		}
	}
	// Update the status.
	update_post_meta( $orderid, 'ced_rnx_return_attachment', $request_files );

	// Send the cancel refund request email to customer.

	$restrict_mail =
	// Allow/Disallow Email.
	apply_filters( 'mwb_rma_restrict_refund_cancel_mails', true );
	if ( $restrict_mail ) {
		// To Send Refund Request Cancel Email.
		do_action( 'mwb_rma_refund_req_cancel_email', $orderid );
	}
	$order_obj = wc_get_order( $orderid );
	$order_obj->update_status( 'wc-return-cancelled', esc_html__( 'User Request of Refund Product is cancel', 'woo-refund-and-exchange-lite' ) );
	$response             = array();
	$response['response'] = 'success';
	return $response;
}
/**
 * Validate the json string .
 *
 * @param string $string .
 */
function mwb_json_validate( $string ) {
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

	if ( $error !== '' ) {
		// throw the Exception or exit // or whatever :) .
		exit( $error );
	}
	// everything is OK .
	return $result;
}
