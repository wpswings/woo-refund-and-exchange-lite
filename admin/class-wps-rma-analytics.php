<?php
/**
 * Analytics data computation for the RMA Analytics tab.
 *
 * @link  https://wpswings.com/
 * @since 4.6.5
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/admin
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Computes all analytics metrics for the RMA Analytics tab.
 * All data is read from existing order meta — no new DB tables required.
 */
class Wps_Rma_Analytics {

	/**
	 * Active filter values.
	 *
	 * @var array
	 */
	private $filter = array();

	/**
	 * Cached flat list of return request entries after filtering.
	 *
	 * @var array|null
	 */
	private $entries = null;

	/**
	 * @param array $filter {
	 *     @type string $start_date  Y-m-d start date (inclusive).
	 *     @type string $end_date    Y-m-d end date   (inclusive).
	 *     @type string $status      pending|complete|accepted|cancel|cancelled|'' for all.
	 *     @type string $reason      Exact reason string, or '' for all.
	 * }
	 */
	public function __construct( array $filter = array() ) {
		$this->filter = $filter;
	}

	// -------------------------------------------------------------------------
	// Public API
	// -------------------------------------------------------------------------

	/**
	 * KPI summary counts.
	 *
	 * @return array { total, pending, completed, cancelled }
	 */
	public function get_kpi() {
		$kpi = array(
			'total'     => 0,
			'pending'   => 0,
			'completed' => 0,
			'cancelled' => 0,
		);

		foreach ( $this->get_entries() as $entry ) {
			$kpi['total']++;
			if ( 'pending' === $entry['status'] ) {
				$kpi['pending']++;
			} elseif ( in_array( $entry['status'], array( 'complete', 'accepted' ), true ) ) {
				$kpi['completed']++;
			} elseif ( in_array( $entry['status'], array( 'cancel', 'cancelled' ), true ) ) {
				$kpi['cancelled']++;
			}
		}

		return $kpi;
	}

	/**
	 * Sum of (qty × unit price) + shipping across all filtered entries.
	 *
	 * @return float
	 */
	public function get_total_refund_value() {
		$total = 0.0;

		foreach ( $this->get_entries() as $entry ) {
			$total += $entry['shipping_price'];
			foreach ( $entry['products'] as $product ) {
				$qty   = isset( $product['qty'] ) ? (float) $product['qty'] : 0;
				$price = isset( $product['price'] ) ? (float) $product['price'] : 0;
				$total += $qty * $price;
			}
		}

		return $total;
	}

	/**
	 * Request counts grouped by time period.
	 * Granularity is auto-selected: day (<= 60 days range), week (<= 365), month (> 365).
	 *
	 * @return array  Keys are formatted date strings, values are counts.
	 */
	public function get_requests_over_time() {
		$entries = $this->get_entries();
		if ( empty( $entries ) ) {
			return array();
		}

		$timestamps = array_column( $entries, 'timestamp' );
		$range_days = max( 1, ( max( $timestamps ) - min( $timestamps ) ) / DAY_IN_SECONDS );

		if ( $range_days <= 60 ) {
			$format       = 'Y-m-d';
			$label_format = 'M j, Y';
		} elseif ( $range_days <= 365 ) {
			$format       = 'Y-W';
			$label_format = null; // handled specially below
		} else {
			$format       = 'Y-m';
			$label_format = 'M Y';
		}

		$groups = array();
		foreach ( $entries as $entry ) {
			$key = date( $format, $entry['timestamp'] ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			if ( ! isset( $groups[ $key ] ) ) {
				$groups[ $key ] = array( 'count' => 0, 'label' => '' );
			}
			$groups[ $key ]['count']++;
			if ( empty( $groups[ $key ]['label'] ) ) {
				if ( null === $label_format ) {
					// ISO week label e.g. "Week 41, 2026"
					$groups[ $key ]['label'] = 'Wk ' . date( 'W', $entry['timestamp'] ) . ', ' . date( 'Y', $entry['timestamp'] ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				} else {
					$groups[ $key ]['label'] = date_i18n( $label_format, $entry['timestamp'] );
				}
			}
		}

		ksort( $groups );

		$result = array();
		foreach ( $groups as $data ) {
			$result[ $data['label'] ] = $data['count'];
		}
		return $result;
	}

	/**
	 * Count per status label (for donut chart).
	 *
	 * @return array  label => count, sorted by count desc.
	 */
	public function get_status_breakdown() {
		$breakdown = array();

		foreach ( $this->get_entries() as $entry ) {
			$status = ! empty( $entry['status'] ) ? ucfirst( $entry['status'] ) : 'Unknown';
			$breakdown[ $status ] = isset( $breakdown[ $status ] ) ? $breakdown[ $status ] + 1 : 1;
		}

		arsort( $breakdown );
		return $breakdown;
	}

	/**
	 * Top returned products by request count.
	 *
	 * @param int $limit  Maximum rows to return.
	 * @return array  Each row: { product_id, name, sku, count, qty, value }
	 */
	public function get_top_products( $limit = 10 ) {
		$products = array();

		foreach ( $this->get_entries() as $entry ) {
			foreach ( $entry['products'] as $product ) {
				$pid = isset( $product['product_id'] ) ? (int) $product['product_id'] : 0;
				if ( ! $pid ) {
					continue;
				}
				$qty   = isset( $product['qty'] ) ? (float) $product['qty'] : 0;
				$price = isset( $product['price'] ) ? (float) $product['price'] : 0;

				if ( ! isset( $products[ $pid ] ) ) {
					$wc_product       = wc_get_product( $pid );
					$products[ $pid ] = array(
						'product_id' => $pid,
						'name'       => $wc_product ? $wc_product->get_name() : '#' . $pid,
						'sku'        => $wc_product ? $wc_product->get_sku() : '',
						'count'      => 0,
						'qty'        => 0,
						'value'      => 0.0,
					);
				}

				$products[ $pid ]['count']++;
				$products[ $pid ]['qty']   += $qty;
				$products[ $pid ]['value'] += $qty * $price;
			}
		}

		usort(
			$products,
			function ( $a, $b ) {
				return $b['count'] - $a['count'];
			}
		);

		return array_slice( $products, 0, $limit );
	}

	/**
	 * Request count per return reason.
	 *
	 * @return array  reason_label => count, sorted by count desc.
	 */
	public function get_reasons_breakdown() {
		$reasons = array();

		foreach ( $this->get_entries() as $entry ) {
			$reason           = ! empty( $entry['reason'] ) ? $entry['reason'] : __( '(No reason provided)', 'woo-refund-and-exchange-lite' );
			$reasons[ $reason ] = isset( $reasons[ $reason ] ) ? $reasons[ $reason ] + 1 : 1;
		}

		arsort( $reasons );
		return $reasons;
	}

	/**
	 * Predefined reasons configured by the admin (for the filter dropdown).
	 *
	 * @return string[]
	 */
	public function get_predefined_reasons() {
		$raw = get_option( 'wps_rma_refund_reasons', '' );
		return array_values( array_filter( array_map( 'trim', explode( ',', $raw ) ) ) );
	}

	// -------------------------------------------------------------------------
	// Internal data pipeline
	// -------------------------------------------------------------------------

	/**
	 * Fetch and cache all filtered return request entries.
	 * Each entry is a flat array representing one timestamp-keyed submission.
	 *
	 * @return array
	 */
	private function get_entries() {
		if ( null !== $this->entries ) {
			return $this->entries;
		}

		$order_ids = $this->fetch_order_ids();
		$entries   = array();

		$start_ts      = ! empty( $this->filter['start_date'] ) ? (int) strtotime( $this->filter['start_date'] ) : 0;
		$end_ts        = ! empty( $this->filter['end_date'] ) ? (int) strtotime( $this->filter['end_date'] ) + DAY_IN_SECONDS - 1 : PHP_INT_MAX;
		$status_filter = isset( $this->filter['status'] ) ? $this->filter['status'] : '';
		$reason_filter = isset( $this->filter['reason'] ) ? $this->filter['reason'] : '';

		foreach ( $order_ids as $order_id ) {
			$return_data = wps_rma_get_meta_data( $order_id, 'wps_rma_return_product', true );
			if ( ! is_array( $return_data ) || empty( $return_data ) ) {
				continue;
			}

			foreach ( $return_data as $timestamp => $entry ) {
				if ( ! is_array( $entry ) ) {
					continue;
				}

				$ts = (int) $timestamp;

				// Date range filter.
				if ( $start_ts && $ts < $start_ts ) {
					continue;
				}
				if ( $end_ts < PHP_INT_MAX && $ts > $end_ts ) {
					continue;
				}

				$status = isset( $entry['status'] ) ? strtolower( trim( $entry['status'] ) ) : '';

				// Status filter.
				if ( $status_filter && $status !== $status_filter ) {
					continue;
				}

				// Resolve reason — stored under the POST field name used in the form.
				$reason = '';
				if ( ! empty( $entry['ced_rnx_return_request_subject'] ) ) {
					$reason = sanitize_text_field( $entry['ced_rnx_return_request_subject'] );
				} elseif ( ! empty( $entry['reason'] ) ) {
					$reason = sanitize_text_field( $entry['reason'] );
				}

				// Reason filter.
				if ( $reason_filter && $reason !== $reason_filter ) {
					continue;
				}

				$entries[] = array(
					'order_id'      => (int) $order_id,
					'timestamp'     => $ts,
					'status'        => $status,
					'reason'        => $reason,
					'refund_method' => isset( $entry['refund_method'] ) ? sanitize_text_field( $entry['refund_method'] ) : '',
					'products'      => ( isset( $entry['products'] ) && is_array( $entry['products'] ) ) ? $entry['products'] : array(),
					'shipping_price' => isset( $entry['shipping_price'] ) ? (float) $entry['shipping_price'] : 0.0,
				);
			}
		}

		$this->entries = $entries;
		return $entries;
	}

	/**
	 * Fetch all order IDs that have at least one return request, HPOS-compatible.
	 *
	 * @return array
	 */
	private function fetch_order_ids() {
		global $wpdb;

		$hpos = class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' )
			&& OrderUtil::custom_orders_table_usage_is_enabled();

		if ( $hpos ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			return $wpdb->get_col(
				"SELECT DISTINCT pm.order_id
				FROM {$wpdb->prefix}wc_orders_meta AS pm
				INNER JOIN {$wpdb->prefix}wc_orders AS o ON pm.order_id = o.id
				WHERE pm.meta_key = 'wps_rma_return_product'
				ORDER BY pm.order_id DESC"
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_col(
			"SELECT DISTINCT pm.post_id
			FROM {$wpdb->prefix}postmeta AS pm
			INNER JOIN {$wpdb->prefix}posts AS p ON pm.post_id = p.ID
			WHERE p.post_type = 'shop_order'
			AND pm.meta_key = 'wps_rma_return_product'
			ORDER BY pm.post_id DESC"
		);
	}
}
