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

function mwb_json_validate($string) {
	// decode the JSON data
	$result = json_decode($string);

	// switch and check possible JSON errors .
	switch (json_last_error()) {
		case JSON_ERROR_NONE:
			$error = ''; // JSON is valid // No error has occurred
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
		// PHP >= 5.3.3
		case JSON_ERROR_UTF8:
			$error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
			break;
		// PHP >= 5.5.0
		case JSON_ERROR_RECURSION:
			$error = 'One or more recursive references in the value to be encoded.';
			break;
		// PHP >= 5.5.0
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

	if ($error !== '') {
		// throw the Exception or exit // or whatever :)
		exit($error);
	}
	// everything is OK
	return $result;
}