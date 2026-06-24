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
	 * Table row data.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Define table columns.
	 */
	public function get_columns() {
		return array(
			'cb'                     => '<input type="checkbox" />',
			'wps_rma_order_id'       => __( 'Order ID', 'woo-refund-and-exchange-lite' ),
			'wps_rma_request_type'   => __( 'Request Type', 'woo-refund-and-exchange-lite' ),
			'wps_rma_request_status' => __( 'Request Status', 'woo-refund-and-exchange-lite' ),
			'wps_rma_order_status'   => __( 'Order Status', 'woo-refund-and-exchange-lite' ),
			'wps_rma_request_date'   => __( 'Request Date', 'woo-refund-and-exchange-lite' ),
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
			default:
				return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
		}
	}

	/**
	 * Checkbox column.
	 *
	 * @param array $item Row data.
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="wps_rma_order_ids[]" value="%s" />',
			esc_attr( $item['wps_rma_order_id'] )
		);
	}

	/**
	 * Prepare table items.
	 */
	public function prepare_items() {
		$per_page              = 10;
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();

		$this->data = $this->wps_rma_request_data();
		$data       = $this->data;

		$current_page = $this->get_pagenum();
		$total_items  = $this->wps_get_total_request_data();

		$this->items = $data;
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Fetch and filter request rows.
	 *
	 * @return array
	 */
	public function wps_rma_request_data() {
		global $wpdb;

		$current_page    = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$orders_per_page = 10;
		$offset          = ( $current_page - 1 ) * $orders_per_page;

		$saved_data  = get_option( 'wsp_rma_report_filter' );
		$filter_type = isset( $saved_data['type'] ) ? sanitize_text_field( wp_unslash( $saved_data['type'] ) ) : null;
		$start_date  = isset( $saved_data['start_date'] ) ? sanitize_text_field( wp_unslash( $saved_data['start_date'] ) ) : null;
		$end_date    = isset( $saved_data['end_date'] ) ? sanitize_text_field( wp_unslash( $saved_data['end_date'] ) ) : null;

		$query_keys = $this->wps_get_query_keys( $filter_type );

		$query_placeholders = implode( ', ', array_fill( 0, count( $query_keys ), '%s' ) );

		if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
			$is_pro        = function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active();
			$order_id      = absint( $_REQUEST['s'] );
			$return_data   = wps_rma_get_meta_data( $order_id, 'wps_rma_return_product', true );
			$exchange_data = $is_pro ? wps_rma_get_meta_data( $order_id, 'wps_wrma_exchange_product', true ) : array();
			$cancel_data   = $is_pro ? wps_rma_get_meta_data( $order_id, 'wps_rma_cancel_req_date', true ) : '';

			$order_ids = ( ! empty( $return_data ) || ! empty( $exchange_data ) || ! empty( $cancel_data ) )
				? array( $order_id )
				: array();

			return $this->prepare_data_to_display( $order_ids );
		}

		if ( $start_date && $end_date ) {
			$start_date = date_i18n( 'Ymd', strtotime( $start_date ) );
			$end_date   = date_i18n( 'Ymd', strtotime( $end_date ) );

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$sql = $wpdb->prepare(
					"SELECT p.id FROM {$wpdb->prefix}wc_orders AS p
					INNER JOIN {$wpdb->prefix}wc_orders_meta AS pm ON p.id = pm.order_id
					WHERE pm.meta_key IN ($query_placeholders)
					AND pm.meta_value >= %s AND pm.meta_value <= %s
					ORDER BY p.id DESC LIMIT %d, %d",
					array_merge( $query_keys, array( $start_date, $end_date, $offset, $orders_per_page ) )
				);
			} else {
				$sql = $wpdb->prepare(
					"SELECT p.ID FROM {$wpdb->prefix}posts AS p
					INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
					WHERE p.post_type = 'shop_order'
					AND pm.meta_key IN ($query_placeholders)
					AND pm.meta_value >= %s AND pm.meta_value <= %s
					ORDER BY p.ID DESC LIMIT %d, %d",
					array_merge( $query_keys, array( $start_date, $end_date, $offset, $orders_per_page ) )
				);
			}
		} elseif ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$sql = $wpdb->prepare(
				"SELECT p.id FROM {$wpdb->prefix}wc_orders AS p
				INNER JOIN {$wpdb->prefix}wc_orders_meta AS pm ON p.id = pm.order_id
				WHERE pm.meta_key IN ($query_placeholders)
				ORDER BY p.id DESC LIMIT %d, %d",
				array_merge( $query_keys, array( $offset, $orders_per_page ) )
			);
		} else {
			$sql = $wpdb->prepare(
				"SELECT p.ID FROM {$wpdb->prefix}posts AS p
				INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
				WHERE p.post_type = 'shop_order'
				AND pm.meta_key IN ($query_placeholders)
				ORDER BY p.ID DESC LIMIT %d, %d",
				array_merge( $query_keys, array( $offset, $orders_per_page ) )
			);
		}

		$order_ids = $wpdb->get_col( $sql );
		return $this->prepare_data_to_display( $order_ids );
	}

	/**
	 * Count total matching rows for pagination.
	 *
	 * @return int
	 */
	public function wps_get_total_request_data() {
		global $wpdb;

		$saved_data  = get_option( 'wsp_rma_report_filter' );
		$filter_type = isset( $saved_data['type'] ) ? sanitize_text_field( wp_unslash( $saved_data['type'] ) ) : null;
		$start_date  = isset( $saved_data['start_date'] ) ? sanitize_text_field( wp_unslash( $saved_data['start_date'] ) ) : null;
		$end_date    = isset( $saved_data['end_date'] ) ? sanitize_text_field( wp_unslash( $saved_data['end_date'] ) ) : null;

		$query_keys = $this->wps_get_query_keys( $filter_type );

		$query_placeholders = implode( ', ', array_fill( 0, count( $query_keys ), '%s' ) );

		if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
			$is_pro        = function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active();
			$order_id      = absint( $_REQUEST['s'] );
			$return_data   = wps_rma_get_meta_data( $order_id, 'wps_rma_return_product', true );
			$exchange_data = $is_pro ? wps_rma_get_meta_data( $order_id, 'wps_wrma_exchange_product', true ) : array();
			$cancel_data   = $is_pro ? wps_rma_get_meta_data( $order_id, 'wps_rma_cancel_req_date', true ) : '';

			$order_ids = ( ! empty( $return_data ) || ! empty( $exchange_data ) || ! empty( $cancel_data ) )
				? array( $order_id )
				: array();

			return count( $this->prepare_data_to_display( $order_ids ) );
		}

		if ( $start_date && $end_date ) {
			$start_date = date_i18n( 'Ymd', strtotime( $start_date ) );
			$end_date   = date_i18n( 'Ymd', strtotime( $end_date ) );

			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				$sql = $wpdb->prepare(
					"SELECT p.id FROM {$wpdb->prefix}wc_orders AS p
					INNER JOIN {$wpdb->prefix}wc_orders_meta AS pm ON p.id = pm.order_id
					WHERE pm.meta_key IN ($query_placeholders)
					AND pm.meta_value >= %s AND pm.meta_value <= %s",
					array_merge( $query_keys, array( $start_date, $end_date ) )
				);
			} else {
				$sql = $wpdb->prepare(
					"SELECT p.ID FROM {$wpdb->prefix}posts AS p
					INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
					WHERE p.post_type = 'shop_order'
					AND pm.meta_key IN ($query_placeholders)
					AND pm.meta_value >= %s AND pm.meta_value <= %s",
					array_merge( $query_keys, array( $start_date, $end_date ) )
				);
			}
		} elseif ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$sql = $wpdb->prepare(
				"SELECT p.id FROM {$wpdb->prefix}wc_orders AS p
				INNER JOIN {$wpdb->prefix}wc_orders_meta AS pm ON p.id = pm.order_id
				WHERE pm.meta_key IN ($query_placeholders)",
				$query_keys
			);
		} else {
			$sql = $wpdb->prepare(
				"SELECT p.ID FROM {$wpdb->prefix}posts AS p
				INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
				WHERE p.post_type = 'shop_order'
				AND pm.meta_key IN ($query_placeholders)",
				$query_keys
			);
		}

		$order_ids = $wpdb->get_col( $sql );
		return is_array( $order_ids ) ? count( $order_ids ) : 0;
	}

	/**
	 * Map filter type to the relevant meta key(s).
	 * When pro is not active, only return requests are available.
	 *
	 * @param string|null $filter_type 'return', 'exchange', 'cancellation', or all.
	 * @return array
	 */
	private function wps_get_query_keys( $filter_type ) {
		$is_pro = function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active();

		if ( ! $is_pro ) {
			return array( 'wps_rma_return_req_date' );
		}

		if ( 'return' === $filter_type ) {
			return array( 'wps_rma_return_req_date' );
		} elseif ( 'exchange' === $filter_type ) {
			return array( 'wps_rma_exchange_req_date' );
		} elseif ( 'cancellation' === $filter_type ) {
			return array( 'wps_rma_cancel_req_date' );
		}
		return array( 'wps_rma_return_req_date', 'wps_rma_exchange_req_date', 'wps_rma_cancel_req_date' );
	}

	/**
	 * Build display rows from a list of order IDs.
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
						'wps_rma_order_id'       => $id,
						'wps_rma_request_status' => ucfirst( $return_data['status'] ),
						'wps_rma_request_type'   => 'Return',
						'wps_rma_request_date'   => date_i18n( wc_date_format(), $date ),
						'wps_rma_order_status'   => ucfirst( str_replace( '-', ' ', $order_status ) ),
					);
				}
			}

			if ( $is_pro ) {
				$exchange_request_data = $order->get_meta( 'wps_wrma_exchange_product' );
				$cancel_request_date   = $order->get_meta( 'wps_rma_cancel_req_date_show' );

				if ( is_array( $exchange_request_data ) && ! empty( $exchange_request_data ) ) {
					foreach ( $exchange_request_data as $date => $exchange_data ) {
						$wps_rma_data[] = array(
							'wps_rma_order_id'       => $id,
							'wps_rma_request_status' => ucfirst( $exchange_data['status'] ),
							'wps_rma_request_type'   => 'Exchange',
							'wps_rma_request_date'   => date_i18n( wc_date_format(), $date ),
							'wps_rma_order_status'   => ucfirst( str_replace( '-', ' ', $order_status ) ),
						);
					}
				}

				if ( ! empty( $cancel_request_date ) ) {
					$wps_rma_data[] = array(
						'wps_rma_order_id'       => $id,
						'wps_rma_request_status' => esc_html__( 'Cancelled', 'woo-refund-and-exchange-lite' ),
						'wps_rma_request_type'   => ucwords( str_replace( '_', ' ', $order->get_meta( 'wps_rma_cancel_req_reason' ) ) ),
						'wps_rma_request_date'   => date_i18n( wc_date_format(), strtotime( $cancel_request_date ) ),
						'wps_rma_order_status'   => ucfirst( str_replace( '-', ' ', $order_status ) ),
					);
				}
			}
		}

		return $wps_rma_data;
	}

	/**
	 * Available bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return apply_filters( 'wps_rma_lite_request_bulk_option', array() );
	}

	/**
	 * Handle bulk actions.
	 */
	public function process_bulk_action() {
		do_action( 'wps_rma_lite_process_bulk_request_action', $this->current_action(), $_POST );
	}
}
