<?php
/**
 * WP_List_Table for the RMA Request tab in the free plugin.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/admin/partials
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Displays the RMA request log table on the free plugin's RMA Request tab.
 */
class Woo_Refund_And_Exchange_Lite_Rma_Request_Table extends WP_List_Table {

	/**
	 * Data for the current page.
	 *
	 * @var array Row data for the current page.
	 */
	protected $data = array();

	/**
	 * Cached result of all-filter+SLA-filtered rows, shared between
	 * wps_rma_request_data() and wps_get_total_request_data() so the full
	 * order-meta scan runs only once per page load.
	 *
	 * @var array|null
	 */
	private $cached_filtered_rows = null;

	/**
	 * Request statuses that are considered "terminal" (no further action possible).
	 *
	 * @var array
	 */
	private static $terminal_statuses = array( 'accepted', 'cancel', 'cancelled' );

	/**
	 * SLA label → display config.
	 *
	 * @return array
	 */
	private function wps_sla_display_map() {
		return array(
			'on_track' => array( '#16a34a', '#f0fdf4', __( 'On Track', 'woo-refund-and-exchange-lite' ) ),
			'warning'  => array( '#d97706', '#fffbeb', __( 'Warning', 'woo-refund-and-exchange-lite' ) ),
			'overdue'  => array( '#dc2626', '#fef2f2', __( 'Overdue', 'woo-refund-and-exchange-lite' ) ),
			'approved' => array( '#0d9488', '#f0fdfa', __( 'Approved', 'woo-refund-and-exchange-lite' ) ),
			'accepted' => array( '#2563eb', '#eff6ff', __( 'Accepted', 'woo-refund-and-exchange-lite' ) ),
		);
	}

	// -------------------------------------------------------------------------
	// Columns
	// -------------------------------------------------------------------------

	/** Define table columns. */
	public function get_columns() {
		return array(
			'cb'                     => '<input type="checkbox" />',
			'wps_rma_order_id'       => __( 'Order ID', 'woo-refund-and-exchange-lite' ),
			'wps_rma_request_type'   => __( 'Request Type', 'woo-refund-and-exchange-lite' ),
			'wps_rma_request_status' => __( 'Request Status', 'woo-refund-and-exchange-lite' ),
			'wps_rma_order_status'   => __( 'Order Status', 'woo-refund-and-exchange-lite' ),
			'wps_rma_request_date'   => __( 'Request Date', 'woo-refund-and-exchange-lite' ),
			'wps_rma_sla_status'     => __( 'Deadline Status', 'woo-refund-and-exchange-lite' ),
			'wps_rma_action'         => __( 'Action', 'woo-refund-and-exchange-lite' ),
		);
	}

	/**
	 * Render each column value.
	 *
	 * @param array  $item        Row data.
	 * @param string $column_name Column key.
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'wps_rma_order_id':
				return '<a target="_blank" href="' . esc_url( admin_url( 'post.php?post=' . $item[ $column_name ] . '&action=edit' ) ) . '">' . esc_html( $item[ $column_name ] ) . '</a>';
			case 'wps_rma_order_status':
				if ( 'Exchange request' === $item[ $column_name ] ) {
					$item[ $column_name ] = 'Exchange requested';
				}
				return esc_html( $item[ $column_name ] );
			case 'wps_rma_sla_status':
				return $this->wps_rma_render_sla_status( $item );
			case 'wps_rma_action':
				return $this->wps_rma_render_action( $item );
			default:
				return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
		}
	}

	/** Render the checkbox column for bulk actions.
	 *
	 * @param array $item Row data.
	 * @return string HTML checkbox input.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="wps_rma_order_ids[]" value="%s" />',
			esc_attr( $item['wps_rma_order_id'] )
		);
	}

	// -------------------------------------------------------------------------
	// SLA logic
	// -------------------------------------------------------------------------

	/**
	 * Compute the internal SLA label for a row.
	 *
	 * Rules (evaluated in order):
	 *   - Request Status = "complete"   → 'approved'
	 *   - Request Status = "accepted"   → 'accepted'
	 *   - Request Status = cancel/cancelled → 'none'
	 *   - SLA not configured (0)        → 'none'
	 *   - hours_remaining <= 0          → 'overdue'
	 *   - hours_remaining <= reminder   → 'warning'
	 *   - otherwise                     → 'on_track'
	 *
	 * @param array $item Row data.
	 * @return string One of: 'on_track','warning','overdue','approved','accepted','none'
	 */
	public function wps_rma_compute_sla_label( $item ) {
		$request_status = strtolower( trim( $item['wps_rma_request_status'] ) );

		if ( 'complete' === $request_status ) {
			return 'approved';
		}
		if ( 'accepted' === $request_status ) {
			return 'accepted';
		}
		if ( in_array( $request_status, array( 'cancel', 'cancelled' ), true ) ) {
			return 'none';
		}

		$type = $item['wps_rma_request_type'];
		if ( 'Return' === $type ) {
			$sla_hours      = (int) get_option( 'wps_rma_return_sla_hours', 0 );
			$reminder_hours = (int) get_option( 'wps_rma_return_sla_reminder_hours', 6 );
		} elseif ( 'Exchange' === $type ) {
			$sla_hours      = (int) get_option( 'wps_rma_exchange_sla_hours', 0 );
			$reminder_hours = (int) get_option( 'wps_rma_exchange_sla_reminder_hours', 6 );
		} else {
			return 'none';
		}

		if ( ! $sla_hours || empty( $item['wps_rma_request_timestamp'] ) ) {
			return 'none';
		}

		$deadline        = (int) $item['wps_rma_request_timestamp'] + ( $sla_hours * HOUR_IN_SECONDS );
		$now             = current_time( 'timestamp' );
		$hours_remaining = ( $deadline - $now ) / HOUR_IN_SECONDS;

		if ( $hours_remaining <= 0 ) {
			return 'overdue';
		}
		if ( $hours_remaining <= $reminder_hours ) {
			return 'warning';
		}
		return 'on_track';
	}

	/**
	 * Render a colour-coded SLA badge for a row.
	 *
	 * On_track / warning also show hours remaining.
	 * Overdue, approved, accepted show a plain label.
	 *
	 * @param array $item Row data.
	 * @return string HTML.
	 */
	private function wps_rma_render_sla_status( $item ) {
		$label = $this->wps_rma_compute_sla_label( $item );
		$map   = $this->wps_sla_display_map();

		if ( ! isset( $map[ $label ] ) ) {
			return '&mdash;';
		}

		list( $color, $bg, $text ) = $map[ $label ];

		// For time-based statuses, append hours remaining to the label.
		if ( in_array( $label, array( 'on_track', 'warning' ), true ) ) {
			$type = $item['wps_rma_request_type'];
			$sla_hours = ( 'Return' === $type )
				? (int) get_option( 'wps_rma_return_sla_hours', 0 )
				: (int) get_option( 'wps_rma_exchange_sla_hours', 0 );

			if ( $sla_hours && ! empty( $item['wps_rma_request_timestamp'] ) ) {
				$deadline        = (int) $item['wps_rma_request_timestamp'] + ( $sla_hours * HOUR_IN_SECONDS );
				$hours_remaining = ( $deadline - current_time( 'timestamp' ) ) / HOUR_IN_SECONDS;
				/* translators: %d: hours remaining */
				$text .= ' — ' . sprintf( __( '%dh left', 'woo-refund-and-exchange-lite' ), (int) ceil( $hours_remaining ) );
			}
		}

		return sprintf(
			'<span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;color:%s;background:%s">%s</span>',
			esc_attr( $color ),
			esc_attr( $bg ),
			esc_html( $text )
		);
	}

	/**
	 * Render the Action column.
	 * Shows "View Order" link only for non-terminal, non-completed requests.
	 *
	 * @param array $item Row data.
	 * @return string HTML link or em-dash.
	 */
	private function wps_rma_render_action( $item ) {

		$url = admin_url( 'post.php?post=' . absint( $item['wps_rma_order_id'] ) . '&action=edit' );
		return '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer" class="button button-small">'
			. esc_html__( 'View Order', 'woo-refund-and-exchange-lite' )
			. '</a>';
	}

	// -------------------------------------------------------------------------
	// Prepare items (called once, uses cache)
	// -------------------------------------------------------------------------

	/** Prepare table items. */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$this->_column_headers = array( $columns, array(), $this->get_sortable_columns() );
		$this->process_bulk_action();

		$per_page     = 10;
		$current_page = $this->get_pagenum();
		$all          = $this->wps_rma_get_all_filtered_rows();
		$total_items  = count( $all );

		$this->data  = array_slice( $all, ( $current_page - 1 ) * $per_page, $per_page );
		$this->items = $this->data;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// Public helpers used by the partial
	// -------------------------------------------------------------------------

	/**
	 * Return the current page's rows for display.
	 *
	 * @deprecated kept for backward compat with the partial.
	 * @return array
	 */
	public function wps_rma_request_data() {
		$per_page     = 10;
		$current_page = $this->get_pagenum();
		$all          = $this->wps_rma_get_all_filtered_rows();
		return array_slice( $all, ( $current_page - 1 ) * $per_page, $per_page );
	}

	/**
	 * Return the total number of requests for the current filter state.
	 *
	 * @deprecated kept for backward compat with the partial.
	 * @return int
	 */
	public function wps_get_total_request_data() {
		return count( $this->wps_rma_get_all_filtered_rows() );
	}

	// -------------------------------------------------------------------------
	// Core data pipeline
	// -------------------------------------------------------------------------

	/**
	 * Fetch ALL matching order IDs, build rows, apply SLA-status filter.
	 * Result is cached on the object so it's computed only once per page load.
	 *
	 * @return array
	 */
	private function wps_rma_get_all_filtered_rows() {
		if ( null !== $this->cached_filtered_rows ) {
			return $this->cached_filtered_rows;
		}

		$order_ids = $this->wps_rma_fetch_all_order_ids();
		$rows      = $this->prepare_data_to_display( $order_ids );

		// For the free plugin, apply date-range filter in PHP (the SQL query fetches all
		// orders by meta-key presence; date comparison on serialized meta isn't possible in SQL).
		$is_pro     = function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active();
		$saved_data = get_option( 'wsp_rma_report_filter' );
		$start_date = isset( $saved_data['start_date'] ) ? sanitize_text_field( wp_unslash( $saved_data['start_date'] ) ) : null;
		$end_date   = isset( $saved_data['end_date'] ) ? sanitize_text_field( wp_unslash( $saved_data['end_date'] ) ) : null;

		if ( ! $is_pro && $start_date && $end_date ) {
			$start_ts = (int) strtotime( $start_date );
			$end_ts   = (int) strtotime( $end_date ) + DAY_IN_SECONDS - 1;
			$rows     = array_values(
				array_filter(
					$rows,
					function ( $row ) use ( $start_ts, $end_ts ) {
						return ! empty( $row['wps_rma_request_timestamp'] )
						&& $row['wps_rma_request_timestamp'] >= $start_ts
						&& $row['wps_rma_request_timestamp'] <= $end_ts;
					}
				)
			);
		}

		// Apply SLA status filter if one is saved.
		$sla_filter = isset( $saved_data['sla_status'] ) ? sanitize_text_field( wp_unslash( $saved_data['sla_status'] ) ) : '';

		if ( $sla_filter ) {
			$rows = array_values(
				array_filter(
					$rows,
					function ( $row ) use ( $sla_filter ) {
						return $this->wps_rma_compute_sla_label( $row ) === $sla_filter;
					}
				)
			);
		}

		$this->cached_filtered_rows = $rows;
		return $rows;
	}

	/**
	 * Return all matching order IDs for the current filter state.
	 * Search-by-ID takes priority over date/type filters.
	 * No LIMIT — pagination is done in PHP after SLA filtering.
	 *
	 * @return array
	 */
	private function wps_rma_fetch_all_order_ids() {
		global $wpdb;

		// --- search by order ID ---
		if ( isset( $_REQUEST['s'] ) && '' !== trim( $_REQUEST['s'] ) ) {
			$is_pro      = function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active();
			$order_id    = absint( $_REQUEST['s'] );
			$ret_data    = wps_rma_get_meta_data( $order_id, 'wps_rma_return_product', true );
			$exch_data   = $is_pro ? wps_rma_get_meta_data( $order_id, 'wps_wrma_exchange_product', true ) : array();
			$cancel_data = $is_pro ? wps_rma_get_meta_data( $order_id, 'wps_rma_cancel_req_date', true ) : '';

			return ( ! empty( $ret_data ) || ! empty( $exch_data ) || ! empty( $cancel_data ) )
				? array( $order_id )
				: array();
		}

		// --- date / type filter ---
		$saved_data  = get_option( 'wsp_rma_report_filter' );
		$filter_type = isset( $saved_data['type'] ) ? sanitize_text_field( wp_unslash( $saved_data['type'] ) ) : null;
		$start_date  = isset( $saved_data['start_date'] ) ? sanitize_text_field( wp_unslash( $saved_data['start_date'] ) ) : null;
		$end_date    = isset( $saved_data['end_date'] ) ? sanitize_text_field( wp_unslash( $saved_data['end_date'] ) ) : null;

		$is_pro             = function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active();
		$query_keys         = $this->wps_get_query_keys( $filter_type );
		$query_placeholders = implode( ', ', array_fill( 0, count( $query_keys ), '%s' ) );

		$hpos = class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' )
			&& \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();

		// SQL date-range filter only works for pro: pro stores flat Ymd date strings in
		// wps_rma_*_req_date. Free plugin stores a serialized array in wps_rma_return_product,
		// so date filtering must happen in PHP (see wps_rma_get_all_filtered_rows).
		if ( $is_pro && $start_date && $end_date ) {
			$start_date = date_i18n( 'Ymd', strtotime( $start_date ) );
			$end_date   = date_i18n( 'Ymd', strtotime( $end_date ) );

			if ( $hpos ) {
				$sql = $wpdb->prepare(
					"SELECT p.id FROM {$wpdb->prefix}wc_orders AS p
					INNER JOIN {$wpdb->prefix}wc_orders_meta AS pm ON p.id = pm.order_id
					WHERE pm.meta_key IN ($query_placeholders)
					AND pm.meta_value >= %s AND pm.meta_value <= %s
					ORDER BY p.id DESC",
					array_merge( $query_keys, array( $start_date, $end_date ) )
				);
			} else {
				$sql = $wpdb->prepare(
					"SELECT p.ID FROM {$wpdb->prefix}posts AS p
					INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
					WHERE p.post_type = 'shop_order'
					AND pm.meta_key IN ($query_placeholders)
					AND pm.meta_value >= %s AND pm.meta_value <= %s
					ORDER BY p.ID DESC",
					array_merge( $query_keys, array( $start_date, $end_date ) )
				);
			}
		} elseif ( $hpos ) {
			$sql = $wpdb->prepare(
				"SELECT p.id FROM {$wpdb->prefix}wc_orders AS p
				INNER JOIN {$wpdb->prefix}wc_orders_meta AS pm ON p.id = pm.order_id
				WHERE pm.meta_key IN ($query_placeholders)
				ORDER BY p.id DESC",
				$query_keys
			);
		} else {
			$sql = $wpdb->prepare(
				"SELECT p.ID FROM {$wpdb->prefix}posts AS p
				INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
				WHERE p.post_type = 'shop_order'
				AND pm.meta_key IN ($query_placeholders)
				ORDER BY p.ID DESC",
				$query_keys
			);
		}

		return $wpdb->get_col( $sql );
	}

	/**
	 * Map filter type to the relevant meta key(s) for the SQL existence check.
	 *
	 * Return requests are queried by wps_rma_return_product in all cases (free and pro)
	 * because that is the only meta key the free plugin writes at submission time.
	 * wps_rma_return_req_date is only written by the pro plugin's analytics hook, so it
	 * is absent on orders submitted before pro was installed — using it as the lookup key
	 * would silently drop those orders.
	 *
	 * Exchange/cancel queries still use their own date meta keys (pro only), since those
	 * are pro-only request types and are always written by the pro plugin at submission.
	 *
	 * Date filtering for return (and exchange when wps_wrma_exchange_product is used)
	 * happens in PHP inside wps_rma_get_all_filtered_rows(), not in SQL.
	 *
	 * @param string|null $filter_type as filter type.
	 * @return array
	 */
	private function wps_get_query_keys( $filter_type ) {
		$is_pro = function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active();

		if ( ! $is_pro ) {
			return array( 'wps_rma_return_product' );
		}
		if ( 'return' === $filter_type ) {
			// wps_rma_return_product is the only reliable key — present on ALL return orders.
			return array( 'wps_rma_return_product' );
		}
		if ( 'exchange' === $filter_type ) {
			return array( 'wps_rma_exchange_req_date' );
		}
		if ( 'cancellation' === $filter_type ) {
			return array( 'wps_rma_cancel_req_date' );
		}
		// "all" — include return orders alongside exchange/cancel.
		return array( 'wps_rma_return_product', 'wps_rma_exchange_req_date', 'wps_rma_cancel_req_date' );
	}

	/**
	 * Build display rows from a list of order IDs.
	 * Includes wps_rma_request_timestamp (Unix int) for SLA calculation.
	 *
	 * @param array $get_data Order IDs.
	 * @return array
	 */
	public function prepare_data_to_display( $get_data ) {
		$wps_rma_data = array();
		$is_pro       = function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active();

		if ( empty( $get_data ) || ! is_array( $get_data ) ) {
			return $wps_rma_data;
		}

		foreach ( $get_data as $id ) {
			$order = wc_get_order( $id );
			if ( ! $order ) {
				continue;
			}
			$order_status        = $order->get_status();
			$return_request_data = $order->get_meta( 'wps_rma_return_product' );

			if ( is_array( $return_request_data ) && ! empty( $return_request_data ) ) {
				foreach ( $return_request_data as $date => $return_data ) {
					$wps_rma_data[] = array(
						'wps_rma_order_id'          => $id,
						'wps_rma_request_status'    => ucfirst( $return_data['status'] ),
						'wps_rma_request_type'      => 'Return',
						'wps_rma_request_date'      => date_i18n( wc_date_format(), $date ),
						'wps_rma_request_timestamp' => (int) $date,
						'wps_rma_order_status'      => ucfirst( str_replace( '-', ' ', $order_status ) ),
					);
				}
			}

			if ( $is_pro ) {
				$exchange_request_data = $order->get_meta( 'wps_wrma_exchange_product' );
				$cancel_request_date   = $order->get_meta( 'wps_rma_cancel_req_date_show' );

				if ( is_array( $exchange_request_data ) && ! empty( $exchange_request_data ) ) {
					foreach ( $exchange_request_data as $date => $exchange_data ) {
						$wps_rma_data[] = array(
							'wps_rma_order_id'          => $id,
							'wps_rma_request_status'    => ucfirst( $exchange_data['status'] ),
							'wps_rma_request_type'      => 'Exchange',
							'wps_rma_request_date'      => date_i18n( wc_date_format(), $date ),
							'wps_rma_request_timestamp' => (int) $date,
							'wps_rma_order_status'      => ucfirst( str_replace( '-', ' ', $order_status ) ),
						);
					}
				}

				if ( ! empty( $cancel_request_date ) ) {
					$wps_rma_data[] = array(
						'wps_rma_order_id'          => $id,
						'wps_rma_request_status'    => esc_html__( 'Cancelled', 'woo-refund-and-exchange-lite' ),
						'wps_rma_request_type'      => ucwords( str_replace( '_', ' ', $order->get_meta( 'wps_rma_cancel_req_reason' ) ) ),
						'wps_rma_request_date'      => date_i18n( wc_date_format(), strtotime( $cancel_request_date ) ),
						'wps_rma_request_timestamp' => (int) strtotime( $cancel_request_date ),
						'wps_rma_order_status'      => ucfirst( str_replace( '-', ' ', $order_status ) ),
					);
				}
			}
		}

		return $wps_rma_data;
	}

	// -------------------------------------------------------------------------
	// Bulk actions
	// -------------------------------------------------------------------------

	/**
	 * Return the bulk actions available for this table.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return apply_filters( 'wps_rma_lite_request_bulk_option', array() );
	}

	/**
	 * Process the bulk action for the current table.
	 */
	public function process_bulk_action() {
		do_action( 'wps_rma_lite_process_bulk_request_action', $this->current_action(), $_POST );
	}
}
