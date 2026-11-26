<?php
/**
 * Guest Return Request Form — shortcode version (WPS-8379).
 *
 * Rendered by [wps_rma_guest_return_form] via output buffering.
 * Handles all four states via POST/GET without sessions:
 *
 *   Step 1  – Lookup form (order # + billing email)
 *   Step 2  – "Check your email" confirmation
 *   Step 3  – Refund request form (after token link is clicked)
 *   Step 4  – Submission success
 *
 * @link  https://wpswings.com/
 * @since 1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/public/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended

$wps_gr_step          = 1;
$wps_gr_error         = '';
$wps_gr_order         = null;
$wps_gr_token         = '';
$wps_gr_token_data    = false;

// -------------------------------------------------------------------------
// Step 1 POST: lookup form submitted — rate-limit, verify, send link
// -------------------------------------------------------------------------
if (
	isset( $_POST['wps_rma_gr_lookup_nonce'] ) &&
	wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_rma_gr_lookup_nonce'] ) ), 'wps_rma_guest_return_lookup' )
) {
	$wps_gr_order_id = isset( $_POST['wps_rma_gr_order_id'] ) ? absint( wp_unslash( $_POST['wps_rma_gr_order_id'] ) ) : 0;
	$wps_gr_email    = isset( $_POST['wps_rma_gr_email'] ) ? sanitize_email( wp_unslash( $_POST['wps_rma_gr_email'] ) ) : '';
	$wps_gr_page_id  = isset( $_POST['wps_rma_gr_page_id'] ) ? absint( wp_unslash( $_POST['wps_rma_gr_page_id'] ) ) : 0;

	if ( ! function_exists( 'wps_rma_guest_return_check_rate_limit' ) || ! wps_rma_guest_return_check_rate_limit() ) {
		$wps_gr_error = esc_html__( 'Too many requests. Please try again in an hour.', 'woo-refund-and-exchange-lite' );
	} elseif ( empty( $wps_gr_order_id ) || empty( $wps_gr_email ) ) {
		$wps_gr_error = esc_html__( 'Please enter your order number and billing email.', 'woo-refund-and-exchange-lite' );
	} else {
		$wps_gr_order = wc_get_order( $wps_gr_order_id );

		if ( empty( $wps_gr_order ) ) {
			$wps_gr_error = esc_html__( 'Order not found. Please check the order number and try again.', 'woo-refund-and-exchange-lite' );
		} elseif ( $wps_gr_order->get_billing_email() !== $wps_gr_email ) {
			$wps_gr_error = esc_html__( 'The email address does not match the billing email for this order.', 'woo-refund-and-exchange-lite' );
		} else {
			$wps_gr_eligible = function_exists( 'wps_rma_show_buttons' ) ? wps_rma_show_buttons( 'refund', $wps_gr_order ) : 'yes';
			if ( 'yes' !== $wps_gr_eligible ) {
				$wps_gr_error = esc_html( $wps_gr_eligible );
			} else {
				// All checks passed — generate token and send the link.
				if ( function_exists( 'wps_rma_guest_return_create_token' ) ) {
					$wps_gr_new_token = wps_rma_guest_return_create_token( $wps_gr_order_id, $wps_gr_email );
					$wps_gr_base_url  = $wps_gr_page_id ? get_permalink( $wps_gr_page_id ) : home_url();
					$wps_gr_link      = add_query_arg( 'wps_rma_guest_token', rawurlencode( $wps_gr_new_token ), $wps_gr_base_url );

					do_action( 'wps_rma_guest_return_send_link', $wps_gr_order_id, $wps_gr_link );

					$wps_gr_step = 2;
					$wps_gr_order = null; // Clear object; step 2 shows no order details.
				} else {
					$wps_gr_error = esc_html__( 'An error occurred. Please try again.', 'woo-refund-and-exchange-lite' );
				}
			}
		}
	}
}

// -------------------------------------------------------------------------
// Step 3 POST: refund form submitted — validate token + save request
// -------------------------------------------------------------------------
if (
	isset( $_POST['wps_rma_gr_submit_nonce'] ) &&
	wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_rma_gr_submit_nonce'] ) ), 'wps_rma_guest_return_submit' )
) {
	$wps_gr_token       = isset( $_POST['wps_rma_guest_token'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_rma_guest_token'] ) ) : '';
	$wps_gr_post_oid    = isset( $_POST['wps_rma_gr_order_id'] ) ? absint( wp_unslash( $_POST['wps_rma_gr_order_id'] ) ) : 0;
	$wps_gr_token_data  = function_exists( 'wps_rma_guest_return_verify_token' ) ? wps_rma_guest_return_verify_token( $wps_gr_token ) : false;

	if ( ! $wps_gr_token_data || (int) $wps_gr_token_data['order_id'] !== $wps_gr_post_oid ) {
		$wps_gr_error = esc_html__( 'Your return link has expired or is invalid. Please request a new one.', 'woo-refund-and-exchange-lite' );
		$wps_gr_step  = 1;
	} else {
		$wps_gr_order = wc_get_order( $wps_gr_post_oid );
		if ( ! $wps_gr_order ) {
			$wps_gr_error = esc_html__( 'Order not found.', 'woo-refund-and-exchange-lite' );
			$wps_gr_step  = 1;
		} else {
			$wps_gr_eligible = function_exists( 'wps_rma_show_buttons' ) ? wps_rma_show_buttons( 'refund', $wps_gr_order ) : 'yes';
			if ( 'yes' !== $wps_gr_eligible ) {
				$wps_gr_error = esc_html( $wps_gr_eligible );
				$wps_gr_step  = 1;
			} else {
				// Build the return_data array expected by wps_rma_save_return_request_callback().
				$wps_gr_raw_items = isset( $_POST['wps_rma_gr_items'] ) && is_array( $_POST['wps_rma_gr_items'] )
					? $_POST['wps_rma_gr_items']   // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					: array();

				$wps_gr_products    = array();
				$wps_gr_check_tax   = get_option( 'refund_wps_rma_tax_handling' );
				$coupon_discount     = get_option( 'wps_rma_refund_deduct_coupon', 'no' );

				foreach ( $wps_gr_order->get_items() as $item_id => $item ) {
					if ( ! isset( $wps_gr_raw_items[ $item_id ] ) ) {
						continue; // Item was unchecked by the customer.
					}
					$raw_qty = absint( $wps_gr_raw_items[ $item_id ]['qty'] ?? $item->get_quantity() );
					$qty     = min( $raw_qty, $item->get_quantity() );
					if ( $qty < 1 ) {
						continue;
					}

					if ( 'on' === $coupon_discount ) {
						$item_price = $item->get_total();
					} else {
						$item_price = $item->get_subtotal();
					}
					if ( 'wps_rma_inlcude_tax' === $wps_gr_check_tax ) {
						$item_price += ( 'on' === $coupon_discount ) ? $item->get_total_tax() : $item->get_subtotal_tax();
					}

					$wps_gr_products[] = array(
						'item_id'      => $item_id,
						'product_id'   => $item->get_product_id(),
						'variation_id' => $item->get_variation_id(),
						'qty'          => $qty,
						'price'        => $item->get_quantity() > 0 ? ( $item_price / $item->get_quantity() ) : 0,
					);
				}

				if ( empty( $wps_gr_products ) ) {
					$wps_gr_error = esc_html__( 'Please select at least one item to return.', 'woo-refund-and-exchange-lite' );
					$wps_gr_step  = 3;
				} else {
					$wps_gr_subject = isset( $_POST['wps_rma_gr_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_rma_gr_subject'] ) ) : '';
					$wps_gr_reason  = isset( $_POST['wps_rma_gr_reason'] ) ? sanitize_textarea_field( wp_unslash( $_POST['wps_rma_gr_reason'] ) ) : '';

					$wps_gr_return_data = array(
						'products' => $wps_gr_products,
						'subject'  => $wps_gr_subject,
						'reason'   => $wps_gr_reason,
					);

					$wps_gr_return_data = apply_filters( 'wps_rma_guest_return_request_data', $wps_gr_return_data, $wps_gr_post_oid );

					if ( function_exists( 'wps_rma_save_return_request_callback' ) ) {
						$wps_gr_response = wps_rma_save_return_request_callback( $wps_gr_post_oid, '', $wps_gr_return_data );

						if ( ! empty( $wps_gr_response['flag'] ) ) {
							// Invalidate the one-time token.
							if ( function_exists( 'wps_rma_guest_return_consume_token' ) ) {
								wps_rma_guest_return_consume_token( $wps_gr_token );
							}
							do_action( 'wps_rma_guest_return_submitted', $wps_gr_post_oid, $wps_gr_return_data );
							$wps_gr_step  = 4;
							$wps_gr_order = null;
						} else {
							$wps_gr_error = isset( $wps_gr_response['msg'] ) ? esc_html( $wps_gr_response['msg'] ) : esc_html__( 'Unable to process your request. Please try again.', 'woo-refund-and-exchange-lite' );
							$wps_gr_step  = 3;
						}
					} else {
						$wps_gr_error = esc_html__( 'An error occurred. Please try again.', 'woo-refund-and-exchange-lite' );
						$wps_gr_step  = 1;
					}
				}
			}
		}
	}
}

// -------------------------------------------------------------------------
// GET: token link clicked — load step 3 (refund form)
// -------------------------------------------------------------------------
// Only switch to step 3 if we haven't already processed a POST above.
if ( 1 === $wps_gr_step && isset( $_GET['wps_rma_guest_token'] ) ) {
	$wps_gr_token      = sanitize_text_field( wp_unslash( $_GET['wps_rma_guest_token'] ) );
	$wps_gr_token_data = function_exists( 'wps_rma_guest_return_verify_token' ) ? wps_rma_guest_return_verify_token( $wps_gr_token ) : false;

	if ( ! $wps_gr_token_data ) {
		$wps_gr_error = esc_html__( 'Your return link has expired or is invalid. Please request a new one below.', 'woo-refund-and-exchange-lite' );
	} else {
		$wps_gr_order = wc_get_order( $wps_gr_token_data['order_id'] );
		if ( $wps_gr_order instanceof WC_Order ) {
			$wps_gr_step = 3;
		} else {
			$wps_gr_error = esc_html__( 'Order not found.', 'woo-refund-and-exchange-lite' );
		}
	}
}

// phpcs:enable
?>

<div class="wps-rma-guest-return-wrap">
	<h2><?php esc_html_e( 'Request a Return', 'woo-refund-and-exchange-lite' ); ?></h2>

	<?php if ( ! empty( $wps_gr_error ) ) : ?>
		<div class="woocommerce-error"><?php echo esc_html( $wps_gr_error ); ?></div>
	<?php endif; ?>

	<?php if ( 4 === $wps_gr_step ) : ?>
		<?php /* Step 4: success */ ?>
		<div class="woocommerce-message">
			<strong><?php esc_html_e( 'Your return request has been submitted.', 'woo-refund-and-exchange-lite' ); ?></strong><br>
			<?php esc_html_e( 'You will receive a confirmation email shortly.', 'woo-refund-and-exchange-lite' ); ?>
		</div>

	<?php elseif ( 2 === $wps_gr_step ) : ?>
		<?php /* Step 2: link sent */ ?>
		<div class="woocommerce-message">
			<strong><?php esc_html_e( 'Check your email.', 'woo-refund-and-exchange-lite' ); ?></strong><br>
			<?php esc_html_e( 'We have sent a return link to your billing email address. The link is valid for 24 hours.', 'woo-refund-and-exchange-lite' ); ?>
		</div>

	<?php elseif ( 3 === $wps_gr_step && $wps_gr_order instanceof WC_Order ) : ?>
		<?php /* Step 3: refund form */ ?>
		<p><?php esc_html_e( 'Please select the items you wish to return and provide a reason.', 'woo-refund-and-exchange-lite' ); ?></p>

		<form method="POST">
			<?php wp_nonce_field( 'wps_rma_guest_return_submit', 'wps_rma_gr_submit_nonce' ); ?>
			<input type="hidden" name="wps_rma_guest_token" value="<?php echo esc_attr( $wps_gr_token ); ?>">
			<input type="hidden" name="wps_rma_gr_order_id" value="<?php echo esc_attr( $wps_gr_order->get_id() ); ?>">

			<table class="shop_table wps-rma-gr-items-table" style="width:100%;margin-bottom:20px;">
				<thead>
					<tr>
						<th style="width:30px;"></th>
						<th><?php esc_html_e( 'Product', 'woo-refund-and-exchange-lite' ); ?></th>
						<th><?php esc_html_e( 'Qty', 'woo-refund-and-exchange-lite' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $wps_gr_order->get_items() as $item_id => $item ) :
						$product = $item->get_product();
						$max_qty = $item->get_quantity();
						?>
						<tr>
							<td>
								<input type="checkbox"
									name="wps_rma_gr_items[<?php echo esc_attr( $item_id ); ?>][checked]"
									value="1"
									checked
									onchange="
										var qtyInput = this.closest('tr').querySelector('input[type=number]');
										if (qtyInput) qtyInput.disabled = !this.checked;">
							</td>
							<td><?php echo $product ? esc_html( $product->get_name() ) : esc_html( $item->get_name() ); ?></td>
							<td>
								<input type="number"
									name="wps_rma_gr_items[<?php echo esc_attr( $item_id ); ?>][qty]"
									value="<?php echo esc_attr( $max_qty ); ?>"
									min="1"
									max="<?php echo esc_attr( $max_qty ); ?>"
									style="width:60px;">
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php
			$wps_gr_reasons = get_option( 'wps_rma_refund_reasons', '' );
			$wps_gr_reasons = array_filter( array_map( 'trim', explode( ',', $wps_gr_reasons ) ) );
			if ( ! empty( $wps_gr_reasons ) ) :
			?>
			<p>
				<label for="wps_rma_gr_subject">
					<strong><?php esc_html_e( 'Reason for Return', 'woo-refund-and-exchange-lite' ); ?></strong>
					<span style="color:red;">*</span>
				</label><br>
				<select name="wps_rma_gr_subject" id="wps_rma_gr_subject" style="width:100%;max-width:400px;">
					<?php foreach ( $wps_gr_reasons as $wps_gr_reason_opt ) : ?>
						<option value="<?php echo esc_attr( $wps_gr_reason_opt ); ?>"><?php echo esc_html( $wps_gr_reason_opt ); ?></option>
					<?php endforeach; ?>
					<option value=""><?php esc_html_e( 'Other', 'woo-refund-and-exchange-lite' ); ?></option>
				</select>
			</p>
			<?php endif; ?>

			<?php if ( 'on' === get_option( 'wps_rma_refund_description', false ) ) : ?>
			<p>
				<label for="wps_rma_gr_reason">
					<strong><?php esc_html_e( 'Description', 'woo-refund-and-exchange-lite' ); ?></strong>
				</label><br>
				<textarea name="wps_rma_gr_reason" id="wps_rma_gr_reason" rows="4" style="width:100%;max-width:600px;" maxlength="10000"></textarea>
			</p>
			<?php endif; ?>

			<?php do_action( 'wps_rma_guest_return_form_fields', $wps_gr_order->get_id() ); ?>

			<button type="submit" class="button alt">
				<?php esc_html_e( 'Submit Return Request', 'woo-refund-and-exchange-lite' ); ?>
			</button>
		</form>

	<?php else : ?>
		<?php /* Step 1: lookup form */ ?>
		<p><?php esc_html_e( 'Enter your order number and the email address used at checkout. We will send a secure return link to your email.', 'woo-refund-and-exchange-lite' ); ?></p>

		<form method="POST">
			<?php
			wp_nonce_field( 'wps_rma_guest_return_lookup', 'wps_rma_gr_lookup_nonce' );
			global $post;
			$wps_gr_current_page_id = $post ? $post->ID : 0;
			?>
			<input type="hidden" name="wps_rma_gr_page_id" value="<?php echo esc_attr( $wps_gr_current_page_id ); ?>">

			<p>
				<label for="wps_rma_gr_order_id">
					<strong><?php esc_html_e( 'Order Number', 'woo-refund-and-exchange-lite' ); ?></strong>
				</label><br>
				<input type="number"
					id="wps_rma_gr_order_id"
					name="wps_rma_gr_order_id"
					min="1"
					required
					style="width:200px;"
					value="<?php echo isset( $_POST['wps_rma_gr_order_id'] ) ? absint( wp_unslash( $_POST['wps_rma_gr_order_id'] ) ) : ''; // phpcs:ignore ?>">
			</p>

			<p>
				<label for="wps_rma_gr_email">
					<strong><?php esc_html_e( 'Billing Email', 'woo-refund-and-exchange-lite' ); ?></strong>
				</label><br>
				<input type="email"
					id="wps_rma_gr_email"
					name="wps_rma_gr_email"
					required
					style="width:300px;"
					value="<?php echo isset( $_POST['wps_rma_gr_email'] ) ? esc_attr( sanitize_email( wp_unslash( $_POST['wps_rma_gr_email'] ) ) ) : ''; // phpcs:ignore ?>">
			</p>

			<button type="submit" class="button alt">
				<?php esc_html_e( 'Send Return Link', 'woo-refund-and-exchange-lite' ); ?>
			</button>
		</form>
	<?php endif; ?>
</div>
