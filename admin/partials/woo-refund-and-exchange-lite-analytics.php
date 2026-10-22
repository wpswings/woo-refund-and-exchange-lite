<?php
/**
 * RMA Analytics tab — displays refund and exchange analytics.
 *
 * @link  https://wpswings.com/
 * @since 4.6.5
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/class-wps-rma-analytics.php';

// -------------------------------------------------------------------------
// Filter form handling
// -------------------------------------------------------------------------

$wps_rma_analytics_filter_option = 'wps_rma_analytics_filter';

if ( isset( $_POST['wps_rma_analytics_apply'] ) ) {
	check_admin_referer( 'wps_rma_analytics_filter_nonce', 'wps_rma_analytics_nonce' );
	update_option(
		$wps_rma_analytics_filter_option,
		array(
			'start_date' => isset( $_POST['wps_rma_analytics_start'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_rma_analytics_start'] ) ) : '',
			'end_date'   => isset( $_POST['wps_rma_analytics_end'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_rma_analytics_end'] ) ) : '',
			'status'     => isset( $_POST['wps_rma_analytics_status'] ) ? sanitize_key( wp_unslash( $_POST['wps_rma_analytics_status'] ) ) : '',
			'reason'     => isset( $_POST['wps_rma_analytics_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['wps_rma_analytics_reason'] ) ) : '',
		)
	);
} elseif ( isset( $_POST['wps_rma_analytics_clear'] ) ) {
	check_admin_referer( 'wps_rma_analytics_filter_nonce', 'wps_rma_analytics_nonce' );
	delete_option( $wps_rma_analytics_filter_option );
}

$saved_filter = get_option( $wps_rma_analytics_filter_option, array() );
$f_start  = isset( $saved_filter['start_date'] ) ? $saved_filter['start_date'] : '';
$f_end    = isset( $saved_filter['end_date'] ) ? $saved_filter['end_date'] : '';
$f_status = isset( $saved_filter['status'] ) ? $saved_filter['status'] : '';
$f_reason = isset( $saved_filter['reason'] ) ? $saved_filter['reason'] : '';

// -------------------------------------------------------------------------
// Compute analytics data
// -------------------------------------------------------------------------

$analytics = new Wps_Rma_Analytics(
	array(
		'start_date' => $f_start,
		'end_date'   => $f_end,
		'status'     => $f_status,
		'reason'     => $f_reason,
	)
);

$kpi               = $analytics->get_kpi();
$total_value       = $analytics->get_total_refund_value();
$over_time         = $analytics->get_requests_over_time();
$status_breakdown  = $analytics->get_status_breakdown();
$top_products      = $analytics->get_top_products( 10 );
$reasons_breakdown = $analytics->get_reasons_breakdown();
$predefined_reasons = $analytics->get_predefined_reasons();
$is_pro            = function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active();

$wps_rma_status_colours = array(
	'Pending'   => '#d97706',
	'Complete'  => '#16a34a',
	'Accepted'  => '#2563eb',
	'Cancel'    => '#dc2626',
	'Cancelled' => '#dc2626',
);

$over_time_max  = ! empty( $over_time ) ? max( array_values( $over_time ) ) : 1;
$reasons_max    = ! empty( $reasons_breakdown ) ? max( array_values( $reasons_breakdown ) ) : 1;
$status_total   = array_sum( array_values( $status_breakdown ) );

$pro_upgrade_url = 'https://wpswings.com/product/rma-return-refund-exchange-for-woocommerce-pro/?utm_source=wpswings-rma-org&utm_medium=rma-org-backend&utm_campaign=analytics-upgrade';
?>

<div class="wps-rma-analytics">

	<!-- ===== Filter card ===== -->
	<div class="wps-rma-analytics__card">
		<div class="wps-rma-analytics__card-head">
			<h2><?php esc_html_e( 'Filters', 'woo-refund-and-exchange-lite' ); ?></h2>
			<p><?php esc_html_e( 'Narrow analytics to a specific date range, request status, or return reason.', 'woo-refund-and-exchange-lite' ); ?></p>
		</div>

		<form method="post" class="wps-rma-analytics__filters">
			<?php wp_nonce_field( 'wps_rma_analytics_filter_nonce', 'wps_rma_analytics_nonce' ); ?>

			<input type="date" id="wps-rma-analytics-start" name="wps_rma_analytics_start"
				value="<?php echo esc_attr( $f_start ); ?>"
				max="<?php echo esc_attr( $f_end ); ?>" />

			<span class="wps-rma-analytics__to-label"><?php esc_html_e( 'to', 'woo-refund-and-exchange-lite' ); ?></span>

			<input type="date" id="wps-rma-analytics-end" name="wps_rma_analytics_end"
				value="<?php echo esc_attr( $f_end ); ?>"
				min="<?php echo esc_attr( $f_start ); ?>" />

			<select name="wps_rma_analytics_status">
				<option value=""><?php esc_html_e( 'All Statuses', 'woo-refund-and-exchange-lite' ); ?></option>
				<?php
				$wps_rma_statuses = array(
					'pending'   => __( 'Pending', 'woo-refund-and-exchange-lite' ),
					'complete'  => __( 'Complete', 'woo-refund-and-exchange-lite' ),
					'accepted'  => __( 'Accepted', 'woo-refund-and-exchange-lite' ),
					'cancel'    => __( 'Cancelled', 'woo-refund-and-exchange-lite' ),
				);
				foreach ( $wps_rma_statuses as $val => $label ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $val, $f_status ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>

			<?php if ( ! empty( $predefined_reasons ) ) : ?>
				<select name="wps_rma_analytics_reason">
					<option value=""><?php esc_html_e( 'All Reasons', 'woo-refund-and-exchange-lite' ); ?></option>
					<?php foreach ( $predefined_reasons as $r ) : ?>
						<option value="<?php echo esc_attr( $r ); ?>" <?php selected( $r, $f_reason ); ?>><?php echo esc_html( $r ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>

			<button type="submit" name="wps_rma_analytics_apply" class="button button-primary"><?php esc_html_e( 'Apply', 'woo-refund-and-exchange-lite' ); ?></button>
			<button type="submit" name="wps_rma_analytics_clear" class="button"><?php esc_html_e( 'Clear', 'woo-refund-and-exchange-lite' ); ?></button>
		</form>
	</div>

	<!-- ===== KPI Cards ===== -->
	<div class="wps-rma-analytics__kpi-row">

		<div class="wps-rma-analytics__kpi wps-rma-analytics__kpi--total">
			<span class="wps-rma-analytics__kpi-label"><?php esc_html_e( 'Total Requests', 'woo-refund-and-exchange-lite' ); ?></span>
			<span class="wps-rma-analytics__kpi-value"><?php echo esc_html( number_format_i18n( $kpi['total'] ) ); ?></span>
		</div>

		<div class="wps-rma-analytics__kpi wps-rma-analytics__kpi--pending">
			<span class="wps-rma-analytics__kpi-label"><?php esc_html_e( 'Pending', 'woo-refund-and-exchange-lite' ); ?></span>
			<span class="wps-rma-analytics__kpi-value"><?php echo esc_html( number_format_i18n( $kpi['pending'] ) ); ?></span>
		</div>

		<div class="wps-rma-analytics__kpi wps-rma-analytics__kpi--completed">
			<span class="wps-rma-analytics__kpi-label"><?php esc_html_e( 'Completed', 'woo-refund-and-exchange-lite' ); ?></span>
			<span class="wps-rma-analytics__kpi-value"><?php echo esc_html( number_format_i18n( $kpi['completed'] ) ); ?></span>
		</div>

		<div class="wps-rma-analytics__kpi wps-rma-analytics__kpi--cancelled">
			<span class="wps-rma-analytics__kpi-label"><?php esc_html_e( 'Cancelled', 'woo-refund-and-exchange-lite' ); ?></span>
			<span class="wps-rma-analytics__kpi-value"><?php echo esc_html( number_format_i18n( $kpi['cancelled'] ) ); ?></span>
		</div>

		<div class="wps-rma-analytics__kpi wps-rma-analytics__kpi--value">
			<span class="wps-rma-analytics__kpi-label"><?php esc_html_e( 'Total Return Value', 'woo-refund-and-exchange-lite' ); ?></span>
			<span class="wps-rma-analytics__kpi-value wps-rma-analytics__kpi-value--sm"><?php echo wp_kses_post( wc_price( $total_value ) ); ?></span>
		</div>

	</div>

	<!-- ===== Charts row ===== -->
	<div class="wps-rma-analytics__charts-row">

		<!-- Requests over time — CSS bar chart -->
		<div class="wps-rma-analytics__card">
			<div class="wps-rma-analytics__card-head">
				<h2><?php esc_html_e( 'Requests Over Time', 'woo-refund-and-exchange-lite' ); ?></h2>
				<p><?php esc_html_e( 'Volume of return requests submitted over the selected period.', 'woo-refund-and-exchange-lite' ); ?></p>
			</div>

			<?php if ( ! empty( $over_time ) ) : ?>
				<div class="wps-rma-analytics__barchart">
					<?php foreach ( $over_time as $ot_label => $ot_count ) :
						$bar_pct = $over_time_max > 0 ? round( ( $ot_count / $over_time_max ) * 100 ) : 0;
					?>
					<div class="wps-rma-analytics__barchart-row">
						<span class="wps-rma-analytics__barchart-label" title="<?php echo esc_attr( $ot_label ); ?>"><?php echo esc_html( $ot_label ); ?></span>
						<div class="wps-rma-analytics__barchart-track">
							<div class="wps-rma-analytics__barchart-fill" style="width:<?php echo esc_attr( $bar_pct ); ?>%"></div>
						</div>
						<span class="wps-rma-analytics__barchart-count"><?php echo esc_html( $ot_count ); ?></span>
					</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="wps-rma-analytics__empty"><?php esc_html_e( 'No data for the selected filters.', 'woo-refund-and-exchange-lite' ); ?></p>
			<?php endif; ?>
		</div>

		<!-- Status breakdown -->
		<div class="wps-rma-analytics__card">
			<div class="wps-rma-analytics__card-head">
				<h2><?php esc_html_e( 'Status Breakdown', 'woo-refund-and-exchange-lite' ); ?></h2>
				<p><?php esc_html_e( 'Distribution of request statuses.', 'woo-refund-and-exchange-lite' ); ?></p>
			</div>

			<?php if ( ! empty( $status_breakdown ) ) : ?>
				<div class="wps-rma-analytics__status-list">
					<?php foreach ( $status_breakdown as $st_label => $st_count ) :
						$st_color = isset( $wps_rma_status_colours[ $st_label ] ) ? $wps_rma_status_colours[ $st_label ] : '#94a3b8';
						$st_pct   = $status_total > 0 ? round( ( $st_count / $status_total ) * 100 ) : 0;
					?>
					<div class="wps-rma-analytics__status-row">
						<span class="wps-rma-analytics__status-dot" style="background:<?php echo esc_attr( $st_color ); ?>"></span>
						<span class="wps-rma-analytics__status-name"><?php echo esc_html( $st_label ); ?></span>
						<div class="wps-rma-analytics__status-track">
							<div class="wps-rma-analytics__status-fill" style="width:<?php echo esc_attr( $st_pct ); ?>%;background:<?php echo esc_attr( $st_color ); ?>"></div>
						</div>
						<span class="wps-rma-analytics__status-count"><?php echo esc_html( $st_count ); ?></span>
						<span class="wps-rma-analytics__status-pct"><?php echo esc_html( $st_pct ); ?>%</span>
					</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="wps-rma-analytics__empty"><?php esc_html_e( 'No data for the selected filters.', 'woo-refund-and-exchange-lite' ); ?></p>
			<?php endif; ?>
		</div>

	</div>

	<!-- ===== Data tables row ===== -->
	<div class="wps-rma-analytics__tables-row">

		<!-- Top returned products -->
		<div class="wps-rma-analytics__card">
			<div class="wps-rma-analytics__card-head">
				<h2><?php esc_html_e( 'Top Returned Products', 'woo-refund-and-exchange-lite' ); ?></h2>
				<p><?php esc_html_e( 'Products with the most return requests.', 'woo-refund-and-exchange-lite' ); ?></p>
			</div>
			<?php if ( ! empty( $top_products ) ) : ?>
				<table class="wps-rma-analytics__table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Product', 'woo-refund-and-exchange-lite' ); ?></th>
							<th><?php esc_html_e( 'SKU', 'woo-refund-and-exchange-lite' ); ?></th>
							<th class="wps-rma-analytics__th-right"><?php esc_html_e( 'Requests', 'woo-refund-and-exchange-lite' ); ?></th>
							<th class="wps-rma-analytics__th-right"><?php esc_html_e( 'Qty', 'woo-refund-and-exchange-lite' ); ?></th>
							<th class="wps-rma-analytics__th-right"><?php esc_html_e( 'Value', 'woo-refund-and-exchange-lite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $top_products as $row ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( get_edit_post_link( $row['product_id'] ) ); ?>" target="_blank"><?php echo esc_html( $row['name'] ); ?></a></td>
								<td><?php echo esc_html( $row['sku'] ?: '—' ); ?></td>
								<td class="wps-rma-analytics__td-right"><?php echo esc_html( number_format_i18n( $row['count'] ) ); ?></td>
								<td class="wps-rma-analytics__td-right"><?php echo esc_html( number_format_i18n( $row['qty'] ) ); ?></td>
								<td class="wps-rma-analytics__td-right"><?php echo wp_kses_post( wc_price( $row['value'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p class="wps-rma-analytics__empty"><?php esc_html_e( 'No data for the selected filters.', 'woo-refund-and-exchange-lite' ); ?></p>
			<?php endif; ?>
		</div>

		<!-- Return rate by reason -->
		<div class="wps-rma-analytics__card">
			<div class="wps-rma-analytics__card-head">
				<h2><?php esc_html_e( 'Return Rate by Reason', 'woo-refund-and-exchange-lite' ); ?></h2>
				<p><?php esc_html_e( 'Root-cause breakdown of return request reasons.', 'woo-refund-and-exchange-lite' ); ?></p>
			</div>
			<?php if ( ! empty( $reasons_breakdown ) ) : ?>
				<table class="wps-rma-analytics__table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Reason', 'woo-refund-and-exchange-lite' ); ?></th>
							<th class="wps-rma-analytics__th-right"><?php esc_html_e( 'Requests', 'woo-refund-and-exchange-lite' ); ?></th>
							<th class="wps-rma-analytics__th-right"><?php esc_html_e( '%', 'woo-refund-and-exchange-lite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $reasons_breakdown as $reason_label => $reason_count ) :
							$pct       = $kpi['total'] > 0 ? round( ( $reason_count / $kpi['total'] ) * 100, 1 ) : 0;
							$bar_w_px  = $reasons_max > 0 ? round( ( $reason_count / $reasons_max ) * 80 ) : 0;
						?>
							<tr>
								<td>
									<div class="wps-rma-analytics__reason-bar-wrap">
										<span class="wps-rma-analytics__reason-bar" style="width:<?php echo esc_attr( $bar_w_px ); ?>px"></span>
										<?php echo esc_html( $reason_label ); ?>
									</div>
								</td>
								<td class="wps-rma-analytics__td-right"><?php echo esc_html( number_format_i18n( $reason_count ) ); ?></td>
								<td class="wps-rma-analytics__td-right"><?php echo esc_html( $pct ); ?>%</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p class="wps-rma-analytics__empty"><?php esc_html_e( 'No data for the selected filters.', 'woo-refund-and-exchange-lite' ); ?></p>
			<?php endif; ?>
		</div>

	</div>

	<!-- ===== Pro-locked sections ===== -->
	<?php if ( ! $is_pro ) : ?>
	<div class="wps-rma-analytics__card">
		<div class="wps-rma-analytics__card-head">
			<h2><?php esc_html_e( 'Advanced Analytics', 'woo-refund-and-exchange-lite' ); ?></h2>
			<p><?php esc_html_e( 'The following analytics sections are available with the Pro plugin.', 'woo-refund-and-exchange-lite' ); ?></p>
		</div>

		<div class="wps-rma-analytics__pro-grid">

			<div class="wps-rma-analytics__pro-card">
				<span class="wps-rma-analytics__pro-badge"><?php esc_html_e( 'Pro', 'woo-refund-and-exchange-lite' ); ?></span>
				<h3><?php esc_html_e( 'Refund Method Breakdown', 'woo-refund-and-exchange-lite' ); ?></h3>
				<p><?php esc_html_e( 'See how refunds are distributed across bank transfer, wallet, store credit, and other methods.', 'woo-refund-and-exchange-lite' ); ?></p>
				<a href="<?php echo esc_url( $pro_upgrade_url ); ?>" target="_blank" class="button button-small"><?php esc_html_e( 'Upgrade to Pro', 'woo-refund-and-exchange-lite' ); ?></a>
			</div>

			<div class="wps-rma-analytics__pro-card">
				<span class="wps-rma-analytics__pro-badge"><?php esc_html_e( 'Pro', 'woo-refund-and-exchange-lite' ); ?></span>
				<h3><?php esc_html_e( 'Return Rate by Category', 'woo-refund-and-exchange-lite' ); ?></h3>
				<p><?php esc_html_e( 'Identify which product categories generate the most return requests, with total qty and value per category.', 'woo-refund-and-exchange-lite' ); ?></p>
				<a href="<?php echo esc_url( $pro_upgrade_url ); ?>" target="_blank" class="button button-small"><?php esc_html_e( 'Upgrade to Pro', 'woo-refund-and-exchange-lite' ); ?></a>
			</div>

			<div class="wps-rma-analytics__pro-card">
				<span class="wps-rma-analytics__pro-badge"><?php esc_html_e( 'Pro', 'woo-refund-and-exchange-lite' ); ?></span>
				<h3><?php esc_html_e( 'Exchange Analytics', 'woo-refund-and-exchange-lite' ); ?></h3>
				<p><?php esc_html_e( 'Track exchange request volume, top exchanged products, and revenue retained via exchange instead of refund.', 'woo-refund-and-exchange-lite' ); ?></p>
				<a href="<?php echo esc_url( $pro_upgrade_url ); ?>" target="_blank" class="button button-small"><?php esc_html_e( 'Upgrade to Pro', 'woo-refund-and-exchange-lite' ); ?></a>
			</div>

			<div class="wps-rma-analytics__pro-card">
				<span class="wps-rma-analytics__pro-badge"><?php esc_html_e( 'Pro', 'woo-refund-and-exchange-lite' ); ?></span>
				<h3><?php esc_html_e( 'Cancellation Analytics', 'woo-refund-and-exchange-lite' ); ?></h3>
				<p><?php esc_html_e( 'View cancellation request counts, cancellation rate vs total RMA requests, and top cancellation reasons.', 'woo-refund-and-exchange-lite' ); ?></p>
				<a href="<?php echo esc_url( $pro_upgrade_url ); ?>" target="_blank" class="button button-small"><?php esc_html_e( 'Upgrade to Pro', 'woo-refund-and-exchange-lite' ); ?></a>
			</div>

			<div class="wps-rma-analytics__pro-card">
				<span class="wps-rma-analytics__pro-badge"><?php esc_html_e( 'Pro', 'woo-refund-and-exchange-lite' ); ?></span>
				<h3><?php esc_html_e( 'SLA Performance Summary', 'woo-refund-and-exchange-lite' ); ?></h3>
				<p><?php esc_html_e( 'Monitor what percentage of requests are on track, in warning, or overdue, and track average resolution time.', 'woo-refund-and-exchange-lite' ); ?></p>
				<a href="<?php echo esc_url( $pro_upgrade_url ); ?>" target="_blank" class="button button-small"><?php esc_html_e( 'Upgrade to Pro', 'woo-refund-and-exchange-lite' ); ?></a>
			</div>

			<div class="wps-rma-analytics__pro-card">
				<span class="wps-rma-analytics__pro-badge"><?php esc_html_e( 'Pro', 'woo-refund-and-exchange-lite' ); ?></span>
				<h3><?php esc_html_e( 'CSV Export', 'woo-refund-and-exchange-lite' ); ?></h3>
				<p><?php esc_html_e( 'Export the full filtered analytics dataset — order IDs, dates, statuses, products, values, reasons — as a CSV file.', 'woo-refund-and-exchange-lite' ); ?></p>
				<a href="<?php echo esc_url( $pro_upgrade_url ); ?>" target="_blank" class="button button-small"><?php esc_html_e( 'Upgrade to Pro', 'woo-refund-and-exchange-lite' ); ?></a>
			</div>

		</div>
	</div>
	<?php endif; ?>

</div><!-- /.wps-rma-analytics -->
