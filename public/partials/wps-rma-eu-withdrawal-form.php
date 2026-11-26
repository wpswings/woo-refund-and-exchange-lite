<?php
/**
 * EU Withdrawal Request Form template (2-step).
 *
 * Step 1 — identify order (guest: order ID + billing email; logged-in: linked via nonce URL)
 *         and select which items to withdraw.
 * Step 2 — confirmation screen before final submission.
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

get_header( 'shop' );
do_action( 'woocommerce_before_main_content' );

// Determine current step and collect data.
// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended

$step          = isset( $_POST['wps_rma_ew_step'] ) ? (int) sanitize_key( $_POST['wps_rma_ew_step'] ) : 1;
$order         = null;
$error_message = '';
$button_text   = get_option( 'wps_rma_eu_withdrawal_button_text', '' );
$button_text   = ! empty( $button_text ) ? $button_text : esc_html__( 'Withdraw from Contract', 'woo-refund-and-exchange-lite' );

// -------------------------------------------------------------------------
// Step 1 POST: validate order and move to step 2
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
		$step          = 1;
		$order         = null;
	} elseif ( ! is_user_logged_in() && $order->get_billing_email() !== $billing_email ) {
		$error_message = esc_html__( 'The email address does not match the billing email for this order.', 'woo-refund-and-exchange-lite' );
		$step          = 1;
		$order         = null;
	} elseif ( is_user_logged_in() && (int) $order->get_user_id() !== get_current_user_id() ) {
		$allowed_roles = array( 'editor', 'administrator', 'shop_manager' );
		$user          = wp_get_current_user();
		if ( ! array_intersect( $allowed_roles, (array) $user->roles ) ) {
			$error_message = esc_html__( 'This order does not belong to your account.', 'woo-refund-and-exchange-lite' );
			$step          = 1;
			$order         = null;
		}
	} elseif ( empty( $selected_items ) ) {
		$error_message = esc_html__( 'Please select at least one item to withdraw.', 'woo-refund-and-exchange-lite' );
		$step          = 1;
		if ( $order_id ) {
			$order = wc_get_order( $order_id );
		}
	} elseif ( ! wps_rma_eu_withdrawal_in_window( $order ) ) {
		$error_message = sprintf(
			/* translators: %d: number of days */
			esc_html__( 'The %d-day withdrawal window for this order has expired.', 'woo-refund-and-exchange-lite' ),
			(int) get_option( 'wps_rma_eu_withdrawal_window_days', 14 )
		);
		$step  = 1;
		$order = null;
	} elseif ( ! wps_rma_eu_withdrawal_eu_check( $order ) ) {
		$error_message = esc_html__( 'EU withdrawal rights do not apply to your billing country.', 'woo-refund-and-exchange-lite' );
		$step  = 1;
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
			$user          = wp_get_current_user();
			if ( (int) $order->get_user_id() === get_current_user_id() || array_intersect( $allowed_roles, (array) $user->roles ) ) {
				$valid = true;
			}
		} elseif ( $order->get_billing_email() === $billing_email ) {
			$valid = true;
		}
	}

	if ( $valid && wps_rma_eu_withdrawal_in_window( $order ) && wps_rma_eu_withdrawal_eu_check( $order ) ) {
		// Save audit log entry.
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

		// Trigger emails.
		do_action( 'wps_rma_eu_withdrawal_submitted', $order_id, $log_entry );

		$step = 4; // success screen
	} else {
		$error_message = esc_html__( 'Unable to process withdrawal. Please check your order details and try again.', 'woo-refund-and-exchange-lite' );
		$step          = 1;
		$order         = null;
	}
}

// -------------------------------------------------------------------------
// Try to pre-fill order from URL (logged-in with valid nonce link)
// -------------------------------------------------------------------------
if ( 1 === $step && is_user_logged_in() && isset( $_GET['order_id'], $_GET['wps_rma_ew_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wps_rma_ew_nonce'] ) ), 'wps_rma_eu_withdrawal_btn_' . absint( $_GET['order_id'] ) ) ) {
	$prefill_id    = absint( $_GET['order_id'] );
	$prefill_order = wc_get_order( $prefill_id );
	if ( $prefill_order && (int) $prefill_order->get_user_id() === get_current_user_id() ) {
		$order = $prefill_order;
	}
}

// phpcs:enable
?>

<div class="wps-rma-eu-withdrawal-wrap" style="max-width:720px;margin:30px auto;padding:20px;">

	<h2><?php esc_html_e( 'Withdraw from Contract', 'woo-refund-and-exchange-lite' ); ?></h2>

	<?php if ( ! empty( $error_message ) ) : ?>
		<div class="woocommerce-error" style="margin-bottom:16px;"><?php echo esc_html( $error_message ); ?></div>
	<?php endif; ?>

	<?php if ( 4 === $step ) : ?>
		<!-- SUCCESS SCREEN -->
		<div class="woocommerce-message">
			<strong><?php esc_html_e( 'Your withdrawal request has been submitted.', 'woo-refund-and-exchange-lite' ); ?></strong><br>
			<?php esc_html_e( 'An acknowledgement has been sent to your email address in accordance with the EU Consumer Rights Directive.', 'woo-refund-and-exchange-lite' ); ?>
		</div>
		<p><a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( '&larr; Back to My Orders', 'woo-refund-and-exchange-lite' ); ?></a></p>

	<?php elseif ( 2 === $step && $order instanceof WC_Order ) : ?>
		<!-- STEP 2: CONFIRM -->
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
				<?php
				foreach ( $order->get_items() as $item_id => $item ) {
					if ( ! in_array( $item_id, $selected_items, true ) ) {
						continue;
					}
					$product = $item->get_product();
					?>
					<tr>
						<td><?php echo $product ? esc_html( $product->get_name() ) : esc_html( $item->get_name() ); ?></td>
						<td><?php echo esc_html( $item->get_quantity() ); ?></td>
						<td><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<p><?php esc_html_e( 'Order', 'woo-refund-and-exchange-lite' ); ?>: <strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong></p>
		<form action="" method="POST">
			<?php wp_nonce_field( 'wps_rma_eu_withdrawal_step2', 'wps_rma_ew_confirm_nonce' ); ?>
			<input type="hidden" name="wps_rma_ew_step" value="3">
			<input type="hidden" name="wps_rma_ew_order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
			<input type="hidden" name="wps_rma_ew_billing_email" value="<?php echo esc_attr( $billing_email ); ?>">
			<?php foreach ( $selected_items as $item_id ) : ?>
				<input type="hidden" name="wps_rma_ew_items[]" value="<?php echo esc_attr( $item_id ); ?>">
			<?php endforeach; ?>
			<div style="display:flex;gap:12px;flex-wrap:wrap;">
				<button type="submit" class="button wps-rma-ew-submit-btn" style="background:#c0392b;color:#fff;border-color:#c0392b;">
					<?php esc_html_e( 'Confirm Withdrawal', 'woo-refund-and-exchange-lite' ); ?>
				</button>
				<a href="<?php echo esc_url( remove_query_arg( array( 'order_id', 'wps_rma_ew_nonce' ) ) ); ?>" class="button">
					<?php esc_html_e( 'Cancel', 'woo-refund-and-exchange-lite' ); ?>
				</a>
			</div>
		</form>

	<?php else : ?>
		<!-- STEP 1: SELECT ORDER & ITEMS -->
		<p><?php esc_html_e( 'Use this form to exercise your right to withdraw from a contract under the EU Consumer Rights Directive. The withdrawal period is 14 days from the order date (or delivery date, if configured by the merchant).', 'woo-refund-and-exchange-lite' ); ?></p>

		<form action="" method="POST">
			<?php wp_nonce_field( 'wps_rma_eu_withdrawal_step1', 'wps_rma_ew_nonce' ); ?>
			<input type="hidden" name="wps_rma_ew_step" value="2">

			<?php if ( ! is_user_logged_in() ) : ?>
				<div class="wps-rma-ew-row" style="margin-bottom:16px;">
					<label for="wps_rma_ew_order_id"><strong><?php esc_html_e( 'Order Number', 'woo-refund-and-exchange-lite' ); ?></strong></label><br>
					<input type="number" id="wps_rma_ew_order_id" name="wps_rma_ew_order_id" min="1" required
						value="<?php echo isset( $_POST['wps_rma_ew_order_id'] ) ? absint( wp_unslash( $_POST['wps_rma_ew_order_id'] ) ) : ''; // phpcs:ignore ?>"
						style="width:200px;padding:6px 10px;">
				</div>
				<div class="wps-rma-ew-row" style="margin-bottom:16px;">
					<label for="wps_rma_ew_billing_email"><strong><?php esc_html_e( 'Billing Email Address', 'woo-refund-and-exchange-lite' ); ?></strong></label><br>
					<input type="email" id="wps_rma_ew_billing_email" name="wps_rma_ew_billing_email" required
						value="<?php echo isset( $_POST['wps_rma_ew_billing_email'] ) ? esc_attr( sanitize_email( wp_unslash( $_POST['wps_rma_ew_billing_email'] ) ) ) : ''; // phpcs:ignore ?>"
						style="width:300px;padding:6px 10px;">
				</div>
			<?php else : ?>
				<input type="hidden" name="wps_rma_ew_billing_email" value="">
				<?php if ( $order instanceof WC_Order ) : ?>
					<input type="hidden" name="wps_rma_ew_order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
					<p><?php esc_html_e( 'Order', 'woo-refund-and-exchange-lite' ); ?>: <strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong></p>
				<?php else : ?>
					<div class="wps-rma-ew-row" style="margin-bottom:16px;">
						<label for="wps_rma_ew_order_id"><strong><?php esc_html_e( 'Order Number', 'woo-refund-and-exchange-lite' ); ?></strong></label><br>
						<input type="number" id="wps_rma_ew_order_id" name="wps_rma_ew_order_id" min="1" required
							value="<?php echo isset( $_POST['wps_rma_ew_order_id'] ) ? absint( wp_unslash( $_POST['wps_rma_ew_order_id'] ) ) : ''; // phpcs:ignore ?>"
							style="width:200px;padding:6px 10px;">
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $order instanceof WC_Order ) :
				$items = $order->get_items();
				if ( ! empty( $items ) ) :
			?>
				<div class="wps-rma-ew-items" style="margin-bottom:20px;">
					<p><strong><?php esc_html_e( 'Select items to withdraw:', 'woo-refund-and-exchange-lite' ); ?></strong></p>
					<table class="shop_table" style="width:100%;">
						<thead>
							<tr>
								<th style="width:30px;"><input type="checkbox" id="wps_rma_ew_select_all" onclick="document.querySelectorAll('.wps-rma-ew-item-cb').forEach(cb=>cb.checked=this.checked)"></th>
								<th><?php esc_html_e( 'Product', 'woo-refund-and-exchange-lite' ); ?></th>
								<th><?php esc_html_e( 'Qty', 'woo-refund-and-exchange-lite' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $items as $item_id => $item ) :
								$product = $item->get_product();
								?>
								<tr>
									<td><input type="checkbox" class="wps-rma-ew-item-cb" name="wps_rma_ew_items[]" value="<?php echo esc_attr( $item_id ); ?>" checked></td>
									<td><?php echo $product ? esc_html( $product->get_name() ) : esc_html( $item->get_name() ); ?></td>
									<td><?php echo esc_html( $item->get_quantity() ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; endif; ?>

			<button type="submit" class="button alt">
				<?php if ( $order instanceof WC_Order ) : ?>
					<?php esc_html_e( 'Continue to Confirmation', 'woo-refund-and-exchange-lite' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Find Order &amp; Continue', 'woo-refund-and-exchange-lite' ); ?>
				<?php endif; ?>
			</button>
		</form>
	<?php endif; ?>
</div>

<?php
do_action( 'woocommerce_after_main_content' );
get_footer( 'shop' );
