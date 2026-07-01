<?php
/**
 * RMA Request tab — displays the request log table.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$secure_nonce      = wp_create_nonce( 'wps-rma-request-tab-nonce' );
$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-rma-request-tab-nonce' );
if ( ! $id_nonce_verified ) {
	wp_die( esc_html__( 'Nonce Not verified', 'woo-refund-and-exchange-lite' ) );
}

$wps_rma_is_pro = function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active();

// Handle filter form submission.
if ( isset( $_POST['wps_rma_date_submit'] ) ) {
	$start_date  = isset( $_REQUEST['wps_rma_start_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wps_rma_start_date'] ) ) : null;
	$end_date    = isset( $_REQUEST['wps_rma_end_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wps_rma_end_date'] ) ) : null;
	$filter_type = isset( $_REQUEST['rma_report_request_filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['rma_report_request_filter'] ) ) : null;
	$sla_status  = isset( $_REQUEST['rma_sla_status_filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['rma_sla_status_filter'] ) ) : '';

	// "To" date cannot be earlier than "From" date.
	if ( $start_date && $end_date && strtotime( $end_date ) < strtotime( $start_date ) ) {
		$end_date = $start_date;
	}

	update_option( 'wsp_rma_report_filter', array(
		'type'       => $filter_type,
		'start_date' => $start_date,
		'end_date'   => $end_date,
		'sla_status' => $sla_status,
	) );
} elseif ( isset( $_POST['wps_rma_clear_filter'] ) ) {
	update_option( 'wsp_rma_report_filter', array(
		'type'       => null,
		'start_date' => null,
		'end_date'   => null,
		'sla_status' => '',
	) );
}

$saved_data  = get_option( 'wsp_rma_report_filter' );
$filter_type = isset( $saved_data['type'] ) ? sanitize_text_field( wp_unslash( $saved_data['type'] ) ) : null;
$start_date  = isset( $saved_data['start_date'] ) ? sanitize_text_field( wp_unslash( $saved_data['start_date'] ) ) : null;
$end_date    = isset( $saved_data['end_date'] ) ? sanitize_text_field( wp_unslash( $saved_data['end_date'] ) ) : null;
$sla_status  = isset( $saved_data['sla_status'] ) ? sanitize_text_field( wp_unslash( $saved_data['sla_status'] ) ) : '';

// Initialize and prepare table before HTML so search_box() and display() can be split.
require_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/class-woo-refund-and-exchange-lite-rma-request-table.php';
$wps_rma_request_table = null;
if ( class_exists( 'Woo_Refund_And_Exchange_Lite_Rma_Request_Table' ) ) {
	$wps_rma_request_table = new Woo_Refund_And_Exchange_Lite_Rma_Request_Table();
	$wps_rma_request_table->prepare_items();
}

// SLA status options used in dropdown and legend.
$wps_sla_options = array(
	''          => __( 'All Deadline Status', 'woo-refund-and-exchange-lite' ),
	'on_track'  => __( 'On Track',       'woo-refund-and-exchange-lite' ),
	'warning'   => __( 'Warning',         'woo-refund-and-exchange-lite' ),
	'overdue'   => __( 'Overdue',         'woo-refund-and-exchange-lite' ),
	'approved'  => __( 'Approved',        'woo-refund-and-exchange-lite' ),
);

$wps_sla_legend = array(
	'on_track' => array(
		'color' => '#16a34a',
		'bg'    => '#f0fdf4',
		'label' => __( 'On Track', 'woo-refund-and-exchange-lite' ),
		'desc'  => __( 'Request is active and within the configured resolution deadline window.', 'woo-refund-and-exchange-lite' ),
	),
	'warning'  => array(
		'color' => '#d97706',
		'bg'    => '#fffbeb',
		'label' => __( 'Warning', 'woo-refund-and-exchange-lite' ),
		'desc'  => __( 'Request is within the reminder window — action required soon.', 'woo-refund-and-exchange-lite' ),
	),
	'overdue'  => array(
		'color' => '#dc2626',
		'bg'    => '#fef2f2',
		'label' => __( 'Overdue', 'woo-refund-and-exchange-lite' ),
		'desc'  => __( 'Request has passed its resolution deadline without resolution.', 'woo-refund-and-exchange-lite' ),
	),
	'approved' => array(
		'color' => '#0d9488',
		'bg'    => '#f0fdfa',
		'label' => __( 'Approved', 'woo-refund-and-exchange-lite' ),
		'desc'  => __( 'Request has been marked Complete — resolution deadline met.', 'woo-refund-and-exchange-lite' ),
	),
);
?>
<style>
/* RMA Request tab — scoped styles */

/* Hide the floating Save Setting bar on this tab */
.wps-rma-floating-save { display: none !important; }

/* Card wrapper */
.wps-rma-req-card {
	background: #fff;
	border: 1px solid #e2e8f0;
	border-radius: 8px;
	padding: 24px 28px;
	margin-bottom: 20px;
}

/* Card header */
.wps-rma-req-card__head {
	margin-bottom: 16px;
	padding-bottom: 16px;
	border-bottom: 1px solid #f0f2f5;
}
.wps-rma-req-card__head h2 {
	font-size: 16px;
	font-weight: 600;
	color: #1e293b;
	margin: 0 0 4px;
	line-height: 1.4;
}
.wps-rma-req-card__head p {
	font-size: 13px;
	color: #64748b;
	margin: 0;
}

/* Controls row: filter form (left) + search form (right) on one line */
.wps-rma-req-controls-row {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	flex-wrap: nowrap;
	margin-bottom: 12px;
}

/* Filter form (left side) */
.wps-rma-req-filters {
	display: flex;
	flex-wrap: nowrap;
	align-items: center;
	gap: 6px;
	margin: 0;
	flex: 1;
}
.wps-rma-req-filters select {
	height: 32px;
	line-height: 30px;
	padding: 0 6px;
	font-size: 13px;
	border: 1px solid #dde1e7;
	border-radius: 4px;
	background: #fff;
	color: #1e293b;
	min-width: 120px;
	box-sizing: border-box;
}
.wps-rma-req-filters input[type="date"] {
	height: 32px;
	line-height: 30px;
	padding: 0 6px;
	font-size: 13px;
	border: 1px solid #dde1e7;
	border-radius: 4px;
	background: #fff;
	color: #1e293b;
	width: 140px;
	box-sizing: border-box;
}
.wps-rma-req-to-label {
	font-size: 12px;
	color: #64748b;
	font-weight: 500;
	text-transform: uppercase;
	letter-spacing: .04em;
	white-space: nowrap;
	flex-shrink: 0;
}
.wps-rma-req-filters .button {
	height: 32px;
	line-height: 30px;
	padding: 0 14px;
	font-size: 13px;
	white-space: nowrap;
	flex-shrink: 0;
}

/* Search form (right side of controls row) */
.wps-rma-req-search-form {
	flex-shrink: 0;
	margin: 0;
}
.wps-rma-req-search-form .search-box {
	display: flex;
	align-items: center;
	gap: 6px;
	margin: 0;
	padding: 0;
	float: none;
}
.wps-rma-req-search-form .search-box label { display: none; }
.wps-rma-req-search-form .search-box input[type="search"] {
	height: 32px;
	line-height: 30px;
	padding: 0 8px;
	font-size: 13px;
	width: 200px;
	border: 1px solid #dde1e7;
	border-radius: 4px;
	margin: 0;
	box-sizing: border-box;
}
.wps-rma-req-search-form .search-box input[type="submit"] {
	height: 32px;
	line-height: 30px;
	padding: 0 14px;
	font-size: 13px;
	margin: 0;
}

/* Table */
.wps-rma-req-table-wrap .tablenav { margin: 4px 0; }
.wps-rma-req-table-wrap .wp-list-table { border: none; box-shadow: none; }
.wps-rma-req-table-wrap .wp-list-table th,
.wps-rma-req-table-wrap .wp-list-table td { padding: 10px 12px; }

/* SLA Legend */
.wps-rma-sla-legend {
	border-top: 1px solid #f0f2f5;
	padding-top: 20px;
	margin-top: 20px;
}
.wps-rma-sla-legend__title {
	font-size: 13px;
	font-weight: 600;
	color: #1e293b;
	margin: 0 0 12px;
}
.wps-rma-sla-legend__grid {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
}
.wps-rma-sla-legend__item {
	display: flex;
	align-items: flex-start;
	gap: 8px;
	font-size: 12px;
	color: #475569;
	min-width: 180px;
	flex: 1 1 180px;
}
.wps-rma-sla-legend__badge {
	display: inline-block;
	padding: 2px 10px;
	border-radius: 12px;
	font-size: 11px;
	font-weight: 700;
	white-space: nowrap;
	flex-shrink: 0;
}
.wps-rma-sla-legend__desc {
	line-height: 1.5;
	padding-top: 1px;
}
</style>

<div class="wps-rma-req-card">

	<div class="wps-rma-req-card__head">
		<h2><?php esc_html_e( 'Request Log', 'woo-refund-and-exchange-lite' ); ?></h2>
		<p><?php esc_html_e( 'Filter request records by type, date range or deadline status, then search directly by order ID.', 'woo-refund-and-exchange-lite' ); ?></p>
	</div>

	<div class="wps-rma-req-controls-row">

		<form method="post" class="wps-rma-req-filters">

			<?php /* Request type dropdown */ ?>
			<?php if ( $wps_rma_is_pro ) : ?>
				<select name="rma_report_request_filter">
					<option value="all"          <?php selected( 'all',          $filter_type ); ?>><?php esc_html_e( 'All',          'woo-refund-and-exchange-lite' ); ?></option>
					<option value="return"       <?php selected( 'return',       $filter_type ); ?>><?php esc_html_e( 'Return',       'woo-refund-and-exchange-lite' ); ?></option>
					<option value="exchange"     <?php selected( 'exchange',     $filter_type ); ?>><?php esc_html_e( 'Exchange',     'woo-refund-and-exchange-lite' ); ?></option>
					<option value="cancellation" <?php selected( 'cancellation', $filter_type ); ?>><?php esc_html_e( 'Cancellation', 'woo-refund-and-exchange-lite' ); ?></option>
				</select>
			<?php else : ?>
				<select name="rma_report_request_filter">
					<option value="return"><?php esc_html_e( 'Return', 'woo-refund-and-exchange-lite' ); ?></option>
				</select>
			<?php endif; ?>

			<?php /* SLA status dropdown */ ?>
			<select name="rma_sla_status_filter">
				<?php foreach ( $wps_sla_options as $val => $label ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $val, $sla_status ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<input id="wps_rma_start_date" name="wps_rma_start_date" type="date" value="<?php echo esc_attr( $start_date ); ?>" max="<?php echo esc_attr( $end_date ); ?>" />
			<span class="wps-rma-req-to-label"><?php esc_html_e( 'To', 'woo-refund-and-exchange-lite' ); ?></span>
			<input id="wps_rma_end_date" name="wps_rma_end_date" type="date" value="<?php echo esc_attr( $end_date ); ?>" min="<?php echo esc_attr( $start_date ); ?>" />
			<input class="button button-primary" name="wps_rma_date_submit"  type="submit" value="<?php esc_attr_e( 'Filter', 'woo-refund-and-exchange-lite' ); ?>">
			<input class="button"               name="wps_rma_clear_filter" type="submit" value="<?php esc_attr_e( 'Clear',  'woo-refund-and-exchange-lite' ); ?>">
		</form>

		<?php if ( $wps_rma_request_table ) : ?>
		<form method="post" class="wps-rma-req-search-form">
			<input type="hidden" name="page" value="wps_rma_request_table_lite">
			<?php wp_nonce_field( 'wps_rma_request_table_lite', 'wps_rma_request_table_lite' ); ?>
			<?php $wps_rma_request_table->search_box( esc_html__( 'Search Orders By ID', 'woo-refund-and-exchange-lite' ), 'wps_rma_request_lite' ); ?>
		</form>
		<?php endif; ?>

	</div>

	<?php if ( $wps_rma_request_table ) : ?>
	<form method="post" class="wps-rma-req-table-wrap">
		<input type="hidden" name="page" value="wps_rma_request_table_lite">
		<?php wp_nonce_field( 'wps_rma_request_table_lite', 'wps_rma_request_table_lite' ); ?>
		<?php $wps_rma_request_table->display(); ?>
	</form>
	<?php endif; ?>

	<!-- SLA Status Legend -->
	<div class="wps-rma-sla-legend">
		<p class="wps-rma-sla-legend__title"><?php esc_html_e( 'Deadline Status Legend', 'woo-refund-and-exchange-lite' ); ?></p>
		<div class="wps-rma-sla-legend__grid">
			<?php foreach ( $wps_sla_legend as $entry ) : ?>
			<div class="wps-rma-sla-legend__item">
				<span class="wps-rma-sla-legend__badge"
				      style="color:<?php echo esc_attr( $entry['color'] ); ?>;background:<?php echo esc_attr( $entry['bg'] ); ?>;">
					<?php echo esc_html( $entry['label'] ); ?>
				</span>
				<span class="wps-rma-sla-legend__desc"><?php echo esc_html( $entry['desc'] ); ?></span>
			</div>
			<?php endforeach; ?>
		</div>
	</div>

	<script>
	( function () {
		var startInput = document.getElementById( 'wps_rma_start_date' );
		var endInput   = document.getElementById( 'wps_rma_end_date' );
		var filterForm = document.querySelector( '.wps-rma-req-filters' );

		if ( ! startInput || ! endInput ) {
			return;
		}

		startInput.addEventListener( 'change', function () {
			endInput.min = startInput.value;
			if ( startInput.value && endInput.value && endInput.value < startInput.value ) {
				endInput.value = startInput.value;
			}
		} );

		endInput.addEventListener( 'change', function () {
			startInput.max = endInput.value;
			if ( startInput.value && endInput.value && endInput.value < startInput.value ) {
				startInput.value = endInput.value;
			}
		} );

		if ( filterForm ) {
			filterForm.addEventListener( 'submit', function ( e ) {
				if ( startInput.value && endInput.value && endInput.value < startInput.value ) {
					e.preventDefault();
					alert( '<?php echo esc_js( __( '"To" date cannot be earlier than "From" date.', 'woo-refund-and-exchange-lite' ) ); ?>' );
				}
			} );
		}
	} )();
	</script>

</div>
