<?php
/**
 * The refund request email template.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/common
 */

$wps_rma_products                  = wps_rma_get_meta_data( $order_id, 'wps_rma_return_product', true );
$wps_rma_rr_subject                = '';
$wps_rma_rr_reason                 = '';
$wps_rma_selected_request          = array();
$wps_rma_selected_request_products = array();
$wps_rma_order_item_product_hook   = 'woocommerce_order_item_product';
$wps_rma_visible_details_hook      = 'wps_rma_visible_customer_details';
$wps_rma_after_details_hook        = 'wps_rma_do_something_after_customer_details_email';
$wps_rma_restrict_user_mail_hook   = 'wps_rma_restrict_refund_request_user_mail';
$wps_rma_restrict_admin_mail_hook  = 'wps_rma_restrict_refund_request_admin_mail';
$wps_rma_switch_language_hook      = 'wpml_switch_language';
$wps_rma_shipping_fee_hook         = 'wps_rma_add_shipping_fee_tr';

// Get pending return request.
if ( ! empty( $wps_rma_products ) ) {
	foreach ( $wps_rma_products as $wps_rma_request_date => $wps_rma_request_product ) {
		if ( isset( $wps_rma_request_product['status'] ) && 'pending' === $wps_rma_request_product['status'] ) {
			$wps_rma_rr_subject = isset( $wps_rma_products[ $wps_rma_request_date ]['subject'] ) ? $wps_rma_products[ $wps_rma_request_date ]['subject'] : '';
			if ( isset( $wps_rma_products[ $wps_rma_request_date ]['reason'] ) ) {
				$wps_rma_rr_reason = $wps_rma_products[ $wps_rma_request_date ]['reason'];
			}
			$wps_rma_selected_request          = $wps_rma_request_product;
			$wps_rma_selected_request_products = isset( $wps_rma_request_product['products'] ) ? $wps_rma_request_product['products'] : array();
		}
		break;
	}
}

$wps_rma_rr_reason = ! empty( $wps_rma_rr_reason ) ? $wps_rma_rr_reason : esc_html__( 'No Reason', 'woo-refund-and-exchange-lite' );
$wps_rma_message   =
'<stlye></stlye><div class="wps_rma_refund_req_mail">
	<div class="header">
		<h2>' . $wps_rma_rr_subject . '</h2>
	</div>
	<div class="content">
		<div class="reason">
			<h4>' . esc_html__( 'Reason of Refund', 'woo-refund-and-exchange-lite' ) . '</h4>
			<p>' . $wps_rma_rr_reason . '</p>
		</div>
		<div class="Order">
			<h4>Order #' . $order_id . '</h4>
			<table width="100%" style="border-collapse: collapse;">
				<tbody>
					<tr>
						<th style="border: 1px solid #C7C7C7;">' . esc_html__( 'Product', 'woo-refund-and-exchange-lite' ) . '</th>
						<th style="border: 1px solid #C7C7C7;">' . esc_html__( 'Quantity', 'woo-refund-and-exchange-lite' ) . '</th>
						<th style="border: 1px solid #C7C7C7;">' . esc_html__( 'Price', 'woo-refund-and-exchange-lite' ) . '</th>
					</tr>';

$wps_rma_order_obj       = wc_get_order( $order_id );
$wps_rma_order_currency  = get_woocommerce_currency_symbol( $wps_rma_order_obj->get_currency() );
$wps_rma_total           = 0;

if ( ! empty( $wps_rma_selected_request_products ) ) {
	foreach ( $wps_rma_order_obj->get_items() as $wps_rma_item_id => $wps_rma_item ) {
		apply_filters( $wps_rma_order_item_product_hook, $wps_rma_item->get_product(), $wps_rma_item );
		foreach ( $wps_rma_selected_request_products as $wps_rma_requested_product ) {
			if ( isset( $wps_rma_requested_product['item_id'] ) && $wps_rma_item_id == $wps_rma_requested_product['item_id'] ) {
				if ( isset( $wps_rma_requested_product['variation_id'] ) && $wps_rma_requested_product['variation_id'] > 0 ) {
					$wps_rma_product_obj = wc_get_product( $wps_rma_requested_product['variation_id'] );
				} else {
					$wps_rma_product_obj = wc_get_product( $wps_rma_requested_product['product_id'] );
				}

				$wps_rma_subtotal = $wps_rma_requested_product['price'] * $wps_rma_requested_product['qty'];
				$wps_rma_total   += $wps_rma_subtotal;

				$wps_rma_item_meta      = new WC_Order_Item_Product( $wps_rma_item, $wps_rma_product_obj );
				$wps_rma_item_meta_html = wc_display_item_meta( $wps_rma_item_meta, array( 'echo' => false ) );
				$wps_rma_message       .= '<tr><td style="border: 1px solid #C7C7C7;">' . $wps_rma_item['name'] . '<br>';
				$wps_rma_message       .= '<small>' . $wps_rma_item_meta_html . '</small></td>
								<td style="border: 1px solid #C7C7C7;">' . $wps_rma_requested_product['qty'] . '</td>
								<td style="border: 1px solid #C7C7C7;">' . wps_wrma_format_price( $wps_rma_requested_product['price'] * $wps_rma_requested_product['qty'], $wps_rma_order_currency ) . '</td>
								</tr>';
			}
		}
	}
}

$wps_rma_shipping_price = '';
if ( isset( $wps_rma_selected_request['shipping_price'] ) && ! empty( $wps_rma_selected_request['shipping_price'] ) ) {
	$wps_rma_shipping_price = esc_html__( '(Shipping Charges Added)', 'woo-refund-and-exchange-lite' );
	$wps_rma_total         += $wps_rma_selected_request['shipping_price'];
}

$wps_rma_message .= '<tr>
				<th colspan="2" style="border: 1px solid #C7C7C7;">' . esc_html__( 'Refund Total', 'woo-refund-and-exchange-lite' ) . ':</th>
				<td style="border: 1px solid #C7C7C7;">' . wps_wrma_format_price( $wps_rma_total, $wps_rma_order_currency ) . $wps_rma_shipping_price . '</td>
				</tr>';

$wps_rma_customer_details =
		'<div class="Customer-detail"><h4>' . esc_html__( 'Customer details', 'woo-refund-and-exchange-lite' ) . '</h4>
		<ul>
			<li><p class="info">
				<span class="bold">' . esc_html__( 'Email', 'woo-refund-and-exchange-lite' ) . ': </span>' . $wps_rma_order_obj->get_billing_email() . '
			</p></li>
			<li><p class="info">
				<span class="bold">' . esc_html__( 'Tel', 'woo-refund-and-exchange-lite' ) . ': </span>' . $wps_rma_order_obj->get_billing_phone() . '
			</p></li>
		</ul>
	</div>
	<div class="details">
		<div class="Shipping-detail">
			<h4>' . esc_html__( 'Shipping Address', 'woo-refund-and-exchange-lite' ) . '</h4>
			' . $wps_rma_order_obj->get_formatted_shipping_address() . '
		</div>
		<div class="Billing-detail">
			<h4>' . esc_html__( 'Billing Address', 'woo-refund-and-exchange-lite' ) . '</h4>
			' . $wps_rma_order_obj->get_formatted_billing_address() . '
		</div>
	</div>';

if ( apply_filters( $wps_rma_visible_details_hook, true ) ) {
	$wps_rma_message .= $wps_rma_customer_details;
}

$wps_rma_message    = apply_filters( $wps_rma_after_details_hook, $wps_rma_message, $order_id );
$wps_rma_message   .= '</div>';
$wps_rma_attachment = array();
$wps_rma_to         = get_option( 'woocommerce_email_from_address', get_option( 'admin_email' ) );
$wps_rma_admin_email = WC()->mailer()->emails['wps_rma_refund_request_email'];
$wps_rma_restrict_mail_user  = apply_filters( $wps_rma_restrict_user_mail_hook, true );
$wps_rma_restrict_mail_admin = apply_filters( $wps_rma_restrict_admin_mail_hook, true );

if ( $wps_rma_restrict_mail_admin ) {
	$wps_rma_admin_email->trigger( $wps_rma_message, $wps_rma_attachment, $wps_rma_to, $order_id );
}

if ( $wps_rma_restrict_mail_user ) {
	// Conversion of the order wpml language.
	$wps_rma_lang = $wps_rma_order_obj->get_meta( 'wpml_language' );
	do_action( $wps_rma_switch_language_hook, $wps_rma_lang );

	$wps_rma_rr_reason = ! empty( $wps_rma_rr_reason ) ? $wps_rma_rr_reason : esc_html__( 'No Reason', 'woo-refund-and-exchange-lite' );
	$wps_rma_message   =
	'<stlye></stlye><div class="wps_rma_refund_req_mail">
		<div class="header">
			<h2>' . $wps_rma_rr_subject . '</h2>
		</div>
		<div class="content">
			<div class="reason">
				<h4>' . esc_html__( 'Reason of Refund', 'woo-refund-and-exchange-lite' ) . '</h4>
				<p>' . $wps_rma_rr_reason . '</p>
			</div>
			<div class="Order">
				<h4>Order #' . $order_id . '</h4>
				<table width="100%" style="border-collapse: collapse;">
					<tbody>
						<tr>
							<th style="border: 1px solid #C7C7C7;">' . esc_html__( 'Product', 'woo-refund-and-exchange-lite' ) . '</th>
							<th style="border: 1px solid #C7C7C7;">' . esc_html__( 'Quantity', 'woo-refund-and-exchange-lite' ) . '</th>
							<th style="border: 1px solid #C7C7C7;">' . esc_html__( 'Price', 'woo-refund-and-exchange-lite' ) . '</th>
						</tr>';

	$wps_rma_order_obj      = wc_get_order( $order_id );
	$wps_rma_order_currency = get_woocommerce_currency_symbol( $wps_rma_order_obj->get_currency() );
	$wps_rma_total          = 0;

	if ( ! empty( $wps_rma_selected_request_products ) ) {
		foreach ( $wps_rma_order_obj->get_items() as $wps_rma_item_id => $wps_rma_item ) {
			apply_filters( $wps_rma_order_item_product_hook, $wps_rma_item->get_product(), $wps_rma_item );
			foreach ( $wps_rma_selected_request_products as $wps_rma_requested_product ) {
				if ( isset( $wps_rma_requested_product['item_id'] ) && $wps_rma_item_id == $wps_rma_requested_product['item_id'] ) {
					if ( isset( $wps_rma_requested_product['variation_id'] ) && $wps_rma_requested_product['variation_id'] > 0 ) {
						$wps_rma_product_obj = wc_get_product( $wps_rma_requested_product['variation_id'] );
					} else {
						$wps_rma_product_obj = wc_get_product( $wps_rma_requested_product['product_id'] );
					}

					$wps_rma_subtotal = $wps_rma_requested_product['price'] * $wps_rma_requested_product['qty'];
					$wps_rma_total   += $wps_rma_subtotal;

					$wps_rma_item_meta      = new WC_Order_Item_Product( $wps_rma_item, $wps_rma_product_obj );
					$wps_rma_item_meta_html = wc_display_item_meta( $wps_rma_item_meta, array( 'echo' => false ) );
					$wps_rma_message       .= '<tr><td style="border: 1px solid #C7C7C7;">' . $wps_rma_item['name'] . '<br>';
					$wps_rma_message       .= '<small>' . $wps_rma_item_meta_html . '</small></td>
									<td style="border: 1px solid #C7C7C7;">' . $wps_rma_requested_product['qty'] . '</td>
									<td style="border: 1px solid #C7C7C7;">' . wps_wrma_format_price( $wps_rma_requested_product['price'] * $wps_rma_requested_product['qty'], $wps_rma_order_currency ) . '</td>
									</tr>';
				}
			}
		}
	}

	$wps_rma_shipping_price = '';
	if ( isset( $wps_rma_selected_request['shipping_price'] ) && ! empty( $wps_rma_selected_request['shipping_price'] ) ) {
		$wps_rma_shipping_price = esc_html__( '(Shipping Charges Added)', 'woo-refund-and-exchange-lite' );
		$wps_rma_total         += $wps_rma_selected_request['shipping_price'];
	}

	$wps_rma_message .= '<tr>
						<th colspan="2" style="border: 1px solid #C7C7C7;">' . esc_html__( 'Refund Total', 'woo-refund-and-exchange-lite' ) . ':</th>
						<td style="border: 1px solid #C7C7C7;">' . wps_wrma_format_price( $wps_rma_total, $wps_rma_order_currency ) . $wps_rma_shipping_price . '</td>
					</tr>';
	$wps_rma_message  = apply_filters( $wps_rma_shipping_fee_hook, $wps_rma_message );
	$wps_rma_message .= '</tbody>
			</table>
		</div>';

	$wps_rma_customer_details =
			'<div class="Customer-detail"><h4>' . esc_html__( 'Customer details', 'woo-refund-and-exchange-lite' ) . '</h4>
			<ul>
				<li><p class="info">
					<span class="bold">' . esc_html__( 'Email', 'woo-refund-and-exchange-lite' ) . ': </span>' . $wps_rma_order_obj->get_billing_email() . '
				</p></li>
				<li><p class="info">
					<span class="bold">' . esc_html__( 'Tel', 'woo-refund-and-exchange-lite' ) . ': </span>' . $wps_rma_order_obj->get_billing_phone() . '
				</p></li>
			</ul>
		</div>
		<div class="details">
			<div class="Shipping-detail">
				<h4>' . esc_html__( 'Shipping Address', 'woo-refund-and-exchange-lite' ) . '</h4>
				' . $wps_rma_order_obj->get_formatted_shipping_address() . '
			</div>
			<div class="Billing-detail">
				<h4>' . esc_html__( 'Billing Address', 'woo-refund-and-exchange-lite' ) . '</h4>
				' . $wps_rma_order_obj->get_formatted_billing_address() . '
			</div>
		</div>';

	if ( apply_filters( $wps_rma_visible_details_hook, true ) ) {
		$wps_rma_message .= $wps_rma_customer_details;
	}

	$wps_rma_message  = apply_filters( $wps_rma_after_details_hook, $wps_rma_message, $order_id );
	$wps_rma_message .= '</div>';
	$wps_rma_admin_email->trigger( $wps_rma_message, $wps_rma_attachment, $wps_rma_order_obj->get_billing_email(), $order_id );
}
