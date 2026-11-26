<?php
/**
 * EU Withdrawal Request Form — shortcode version (no header/footer wrappers).
 *
 * Rendered by [wps_rma_eu_withdrawal_form] via output buffering.
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

// Determine current step and collect data.
// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended

$step          = isset( $_POST['wps_rma_ew_step'] ) ? (int) sanitize_key( $_POST['wps_rma_ew_step'] ) : 1;
$order         = null;
$billing_email = '';
$selected_items = array();
$error_message = '';

// -------------------------------------------------------------------------
// Step 1 POST: validate order and move to step 2 (confirmation)
// -------------------------------------------------------------------------
if ( 2 === $step && isset( $_POST['wps_rma_ew_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_rma_ew_nonce'] ) ), 'wps_rma_eu_withdrawal_step1' ) ) {

	$order_id      = isset( $_POST['wps_rma_ew_order_id'] ) ? absint( wp_unslash( $_POST['wps_rma_ew_order_id'] ) ) : 0;
	$billing_email = isset( $_POST['wps_rma_ew_billing_email'] ) ? sanitize_email( wp_unslash( $_POST['wps_rma_ew_billing_email'] ) ) : '';
	$selected_items = isset( $_POST['wps_rma_ew_items'] ) && is_array( $_POST['wps_rma_ew_items'] ) ? array_map( 'absint', $_POST['wps_rma_ew_items'] ) : array();

	if ( $order_id ) {
		$order = wc_get_order( $order_id );
	}

	if ( empty( $order ) ) {
		$error_message = esc_html__( 'Order not found. Please check the order number and try again.', 'woo-refund-and-exchange-lite' );
		$step = 1;
		$order = null;
	} elseif ( ! is_user_logged_in() && $order->get_billing_email() !== $billing_email ) {
		$error_message = esc_html__( 'The email address does not match the billing email for this order.', 'woo-refund-and-exchange-lite' );
		$step = 1;
		$order = null;
	} elseif ( is_user_logged_in() && (int) $order->get_user_id() !== get_current_user_id() ) {
		$allowed_roles = array( 'editor', 'administrator', 'shop_manager' );
		$user = wp_get_current_user();
		if ( ! array_intersect( $allowed_roles, (array) $user->roles ) ) {
			$error_message = esc_html__( 'This order does not belong to your account.', 'woo-refund-and-exchange-lite' );
			$step = 1;
			$order = null;
		}
	} elseif ( empty( $selected_items ) ) {
		$error_message = esc_html__( 'Please select at least one item to withdraw.', 'woo-refund-and-exchange-lite' );
		$step = 1;
		if ( $order_id ) {
			$order = wc_get_order( $order_id );
		}
	} elseif ( ! wps_rma_eu_withdrawal_in_window( $order ) ) {
		$error_message = sprintf(
			/* translators: %d: number of days */
			esc_html__( 'The %d-day withdrawal window for this order has expired.', 'woo-refund-and-exchange-lite' ),
			(int) get_option( 'wps_rma_eu_withdrawal_window_days', 14 )
		);
		$step = 1;
		$order = null;
	} elseif ( ! wps_rma_eu_withdrawal_eu_check( $order ) ) {
		$error_message = esc_html__( 'EU withdrawal rights do not apply to your billing country.', 'woo-refund-and-exchange-lite' );
		$step = 1;
		$order = null;
	}
}

// -------------------------------------------------------------------------
// Step 2 POST: process final withdrawal submission
// -------------------------------------------------------------------------
if ( 3 === $step && isset( $_POST['wps_rma_ew_confirm_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_rma_ew_confirm_nonce'] ) ), 'wps_rma_eu_withdrawal_step2' ) ) {

	$order_id       = isset( $_POST['wps_rma_ew_order_id'] ) ? absint( wp_unslash( $_POST['wps_rma_ew_order_id'] ) ) : 0;
	$billing_email  = isset( $_POST['wps_rma_ew_billing_email'] ) ? sanitize_email( wp_unslash( $_POST['wps_rma_ew_billing_email'] ) ) : '';
	$selected_items = isset( $_POST['wps_rma_ew_items'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['wps_rma_ew_items'] ) ) : array();
	$order          = $order_id ? wc_get_order( $order_id ) : null;

	$valid = false;
	if ( $order && ! empty( $selected_items ) ) {
		if ( is_user_logged_in() ) {
			$allowed_roles = array( 'editor', 'administrator', 'shop_manager' );
			$user = wp_get_current_user();
			if ( (int) $order->get_user_id() === get_current_user_id() || array_intersect( $allowed_roles, (array) $user->roles ) ) {
				$valid = true;
			}
		} elseif ( $order->get_billing_email() === $billing_email ) {
			$valid = true;
		}
	}

	if ( $valid && wps_rma_eu_withdrawal_in_window( $order ) && wps_rma_eu_withdrawal_eu_check( $order ) ) {
		$log_entry = array(
			'timestamp'     => current_time( 'timestamp' ), // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
			'items'         => $selected_items,
			'ip'            => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			'status'        => 'submitted',
			'billing_email' => $billing_email ? $billing_email : $order->get_billing_email(),
		);
		$existing_log   = wps_rma_get_meta_data( $order_id, 'wps_rma_eu_withdrawal_log', true );
		$existing_log   = is_array( $existing_log ) ? $existing_log : array();
		$existing_log[] = $log_entry;
		wps_rma_update_meta_data( $order_id, 'wps_rma_eu_withdrawal_log', $existing_log );

		do_action( 'wps_rma_eu_withdrawal_submitted', $order_id, $log_entry );

		$step = 4;
	} else {
		$error_message = esc_html__( 'Unable to process withdrawal. Please check your order details and try again.', 'woo-refund-and-exchange-lite' );
		$step = 1;
		$order = null;
	}
}

// Pre-fill order from URL for logged-in users.
if ( 1 === $step && is_user_logged_in() && isset( $_GET['order_id'], $_GET['wps_rma_ew_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wps_rma_ew_nonce'] ) ), 'wps_rma_eu_withdrawal_btn_' . absint( $_GET['order_id'] ) ) ) {
	$prefill_order = wc_get_order( absint( $_GET['order_id'] ) );
	if ( $prefill_order && (int) $prefill_order->get_user_id() === get_current_user_id() ) {
		$order = $prefill_order;
	}
}
// phpcs:enable
?>

<div class="wps-rma-eu-withdrawal-wrap">
	<h2><?php esc_html_e( 'Withdraw from Contract', 'woo-refund-and-exchange-lite' ); ?></h2>

	<?php if ( ! empty( $error_message ) ) : ?>
		<div class="woocommerce-error"><?php echo esc_html( $error_message ); ?></div>
	<?php endif; ?>

	<?php if ( 4 === $step ) : ?>
		<div class="woocommerce-message">
			<strong><?php esc_html_e( 'Your withdrawal request has been submitted.', 'woo-refund-and-exchange-lite' ); ?></strong><br>
			<?php esc_html_e( 'An acknowledgement has been sent to your email in accordance with the EU Consumer Rights Directive.', 'woo-refund-and-exchange-lite' ); ?>
		</div>

	<?php elseif ( 2 === $step && $order instanceof WC_Order ) : ?>
		<p><?php esc_html_e( 'Please review your withdrawal request before submitting.', 'woo-refund-and-exchange-lite' ); ?></p>
		<table class="shop_table wps-rma-ew-table" style="width:100%;margin-bottom:20px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Product', 'woo-refund-and-exchange-lite' ); ?></th>
					<th><?php esc_html_e( 'Qty', 'woo-refund-and-exchange-lite' ); ?></th>
					<th><?php esc_html_e( 'Total', 'woo-refund-and-exchange-lite' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $order->get_items() as $item_id => $item ) :
					if ( ! in_array( $item_id, $selected_items, true ) ) { continue; }
					$product = $item->get_product();
					?>
					<tr>
						<td><?php echo $product ? esc_html( $product->get_name() ) : esc_html( $item->get_name() ); ?></td>
						<td><?php echo esc_html( $item->get_quantity() ); ?></td>
						<td><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<form method="POST">
			<?php wp_nonce_field( 'wps_rma_eu_withdrawal_step2', 'wps_rma_ew_confirm_nonce' ); ?>
			<input type="hidden" name="wps_rma_ew_step" value="3">
			<input type="hidden" name="wps_rma_ew_order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
			<input type="hidden" name="wps_rma_ew_billing_email" value="<?php echo esc_attr( $billing_email ); ?>">
			<?php foreach ( $selected_items as $item_id ) : ?>
				<input type="hidden" name="wps_rma_ew_items[]" value="<?php echo esc_attr( $item_id ); ?>">
			<?php endforeach; ?>
			<button type="submit" class="button" style="background:#c0392b;color:#fff;border-color:#c0392b;">
				<?php esc_html_e( 'Confirm Withdrawal', 'woo-refund-and-exchange-lite' ); ?>
			</button>
		</form>

	<?php else : ?>
		<p><?php esc_html_e( 'Use this form to exercise your EU right to withdraw from a contract. The withdrawal period is configurable by the merchant (default 14 days).', 'woo-refund-and-exchange-lite' ); ?></p>
		<form method="POST">
			<?php wp_nonce_field( 'wps_rma_eu_withdrawal_step1', 'wps_rma_ew_nonce' ); ?>
			<input type="hidden" name="wps_rma_ew_step" value="2">

			<?php if ( ! is_user_logged_in() ) : ?>
				<p>
					<label for="wps_rma_ew_order_id"><strong><?php esc_html_e( 'Order Number', 'woo-refund-and-exchange-lite' ); ?></strong></label><br>
					<input type="number" id="wps_rma_ew_order_id" name="wps_rma_ew_order_id" min="1" required
						value="<?php echo isset( $_POST['wps_rma_ew_order_id'] ) ? absint( wp_unslash( $_POST['wps_rma_ew_order_id'] ) ) : ''; // phpcs:ignore ?>"
						style="width:200px;">
				</p>
				<p>
					<label for="wps_rma_ew_billing_email"><strong><?php esc_html_e( 'Billing Email', 'woo-refund-and-exchange-lite' ); ?></strong></label><br>
					<input type="email" id="wps_rma_ew_billing_email" name="wps_rma_ew_billing_email" required
						value="<?php echo isset( $_POST['wps_rma_ew_billing_email'] ) ? esc_attr( sanitize_email( wp_unslash( $_POST['wps_rma_ew_billing_email'] ) ) ) : ''; // phpcs:ignore ?>"
						style="width:300px;">
				</p>
			<?php else : ?>
				<input type="hidden" name="wps_rma_ew_billing_email" value="">
				<?php if ( $order instanceof WC_Order ) : ?>
					<input type="hidden" name="wps_rma_ew_order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
					<p><?php echo esc_html__( 'Order', 'woo-refund-and-exchange-lite' ) . ': #' . esc_html( $order->get_order_number() ); ?></p>
				<?php else : ?>
					<p>
						<label for="wps_rma_ew_order_id"><strong><?php esc_html_e( 'Order Number', 'woo-refund-and-exchange-lite' ); ?></strong></label><br>
						<input type="number" id="wps_rma_ew_order_id" name="wps_rma_ew_order_id" min="1" required
							value="<?php echo isset( $_POST['wps_rma_ew_order_id'] ) ? absint( wp_unslash( $_POST['wps_rma_ew_order_id'] ) ) : ''; // phpcs:ignore ?>"
							style="width:200px;">
					</p>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $order instanceof WC_Order ) :
				$items = $order->get_items();
				if ( ! empty( $items ) ) :
			?>
				<p><strong><?php esc_html_e( 'Select items to withdraw:', 'woo-refund-and-exchange-lite' ); ?></strong></p>
				<table class="shop_table" style="width:100%;margin-bottom:16px;">
					<thead>
						<tr>
							<th style="width:30px;"></th>
							<th><?php esc_html_e( 'Product', 'woo-refund-and-exchange-lite' ); ?></th>
							<th><?php esc_html_e( 'Qty', 'woo-refund-and-exchange-lite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $items as $item_id => $item ) :
							$product = $item->get_product();
						?>
							<tr>
								<td><input type="checkbox" name="wps_rma_ew_items[]" value="<?php echo esc_attr( $item_id ); ?>" checked></td>
								<td><?php echo $product ? esc_html( $product->get_name() ) : esc_html( $item->get_name() ); ?></td>
								<td><?php echo esc_html( $item->get_quantity() ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; endif; ?>

			<button type="submit" class="button alt">
				<?php esc_html_e( 'Continue', 'woo-refund-and-exchange-lite' ); ?>
			</button>
		</form>
	<?php endif; ?>
</div>
