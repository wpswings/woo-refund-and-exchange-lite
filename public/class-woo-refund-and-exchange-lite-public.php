<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 * namespace woo_refund_and_exchange_lite_public.
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/public
 */
class Woo_Refund_And_Exchange_Lite_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wrael_public_enqueue_styles() {
		if ( function_exists( 'wps_rma_css_and_js_load_page' ) && wps_rma_css_and_js_load_page() ) {
			wp_enqueue_style( $this->plugin_name, WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'public/css/woo-refund-and-exchange-lite-public.min.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wrael_public_enqueue_scripts() {
		if ( function_exists( 'wps_rma_css_and_js_load_page' ) && wps_rma_css_and_js_load_page() ) {
			$pro_active = wps_rma_pro_active();
			wp_register_script( $this->plugin_name, WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'public/js/woo-refund-and-exchange-lite-public.min.js', array( 'jquery' ), $this->version, false );
			wp_localize_script(
				$this->plugin_name,
				'wrael_public_param',
				
				array(
					'ajaxurl'               => admin_url( 'admin-ajax.php' ),
					'wps_rma_nonce'         => wp_create_nonce( 'wps_rma_ajax_security' ),
					'return_subject_msg'    => esc_html__( 'Please enter refund subject.', 'woo-refund-and-exchange-lite' ),
					'return_reason_msg'     => esc_html__( 'Please enter refund reason.', 'woo-refund-and-exchange-lite' ),
					'return_select_product' => esc_html__( 'Please select product to refund.', 'woo-refund-and-exchange-lite' ),
					'check_pro_active'      => esc_html( $pro_active ),
					'check_refund_method'   => get_option( 'wps_rma_refund_method' ),
					'wps_refund_manually'   => get_option( 'wps_rma_refund_manually_de' ),
				)
			);
			wp_enqueue_script( $this->plugin_name );
		}
	}

	/**
	 * Add return button on my-account order section.
	 *
	 * @param array  $actions is current order action.
	 * @param object $order is a current order.
	 */
	public function wps_rma_refund_button( $actions, $order ) {
		if ( ( version_compare( WC()->version, '9.6.0', '>=' ) && is_account_page() && is_wc_endpoint_url('view-order') ) || is_checkout() ) {
			return $actions;
		}
		$show_refund_button = wps_rma_show_buttons( 'refund', $order );
		$view_msg           = get_option( 'wps_rma_general_om', 'no' );
		$wps_rma_return     = get_option( 'wps_rma_refund_enable', false );
		$refund_hide        = get_option( 'wps_rma_refund_button_pages', false );
		if ( isset( $view_msg ) && 'on' === $view_msg ) {
			$order_msg_button_text = get_option( 'wps_rma_order_message_button_text', false );
			if ( isset( $order_msg_button_text ) && ! empty( $order_msg_button_text ) ) {
				$order_msg_button_text = $order_msg_button_text;
			} else {
				$order_msg_button_text = esc_html__( 'View Order Messages', 'woo-refund-and-exchange-lite' );
			}
			$wps_rma_view_order_msg_page_id = get_option( 'wps_rma_view_order_msg_page_id', true );
			$msg_url                        = get_permalink( $wps_rma_view_order_msg_page_id );
			$msg_url                        = add_query_arg( 'order_id', $order->get_id(), $msg_url );
			$msg_url                        = wp_nonce_url( $msg_url, 'wps_rma_nonce', 'wps_rma_nonce' );
			$actions['view_msg']['url']     = $msg_url;
			$actions['view_msg']['name']    = $order_msg_button_text;
		}

		$refund_will_made =
		// Refund Will made or not bool.
		apply_filters( 'wps_rma_refund_will_made', true, $order->get_id() );
		$show_on_pages = true;
		if ( ! empty( $refund_hide ) && in_array( 'order-page', $refund_hide, true ) ) {
			$show_on_pages = false;
		}
		if ( $wps_rma_return && $refund_will_made && $show_on_pages && 'yes' === $show_refund_button ) {
			$return_button_text = get_option( 'wps_rma_refund_button_text', false );
			if ( isset( $return_button_text ) && ! empty( $return_button_text ) ) {
				$return_button_text = $return_button_text;
			} else {
				$return_button_text = esc_html__( 'Refund', 'woo-refund-and-exchange-lite' );
			}
				$wps_rma_return_request_form_page_id = get_option( 'wps_rma_return_request_form_page_id' );
				$return_url                          = get_permalink( $wps_rma_return_request_form_page_id );
				$return_url                          = add_query_arg( 'order_id', $order->get_id(), $return_url );
				$return_url                          = wp_nonce_url( $return_url, 'wps_rma_nonce', 'wps_rma_nonce' );
				$actions['return']['url']            = $return_url;
				$actions['return']['name']           = $return_button_text;
		}
		return $actions;
	}

	/**
	 * Add Refund button and Show return products details and view order button
	 *
	 * @param object $order is current order.
	 */
	public function wps_rma_return_button_and_details( $order ) {
		global $wp;
		$condition          = wps_rma_show_buttons( 'refund', $order );
		$get_order_currency = get_woocommerce_currency_symbol( $order->get_currency() );

		// View order message code start.
		$wps_rma_view_order_msg_page_id    = get_option( 'wps_rma_view_order_msg_page_id', true );
		$view_order_msg_url                = get_permalink( $wps_rma_view_order_msg_page_id );
		$view_order_msg_url                = add_query_arg( 'order_id', $order->get_id(), $view_order_msg_url );
		$view_order_msg_url                = wp_nonce_url( $view_order_msg_url, 'wps_rma_nonce', 'wps_rma_nonce' );
		$wps_rma_order_message_button_text = get_option( 'wps_rma_order_message_button_text', false );
		if ( ! empty( $wps_rma_order_message_button_text ) ) {
			$wps_rma_order_message_button_text = $wps_rma_order_message_button_text;
		} else {
			$wps_rma_order_message_button_text = esc_html__( 'View Order Message', 'woo-refund-and-exchange-lite' );
		}
		$view_msg     = get_option( 'wps_rma_general_om', 'no' );
		$redirect_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( isset( $redirect_uri ) ) {
			if ( isset( $view_msg ) && 'on' === $view_msg ) {
				?>
				<form action="<?php echo esc_html( add_query_arg( 'order_id', $order->get_id(), $view_order_msg_url ) ); ?>" method="post">
					<input type="hidden" value="<?php echo esc_html( $order->get_id() ); ?>" name="order_id">
					<p>
						<input type="submit" class="btn button wps_rma_view_order" value="<?php echo esc_html( $wps_rma_order_message_button_text ); ?>">
					</p>
				</form>
				<?php
			}
		}
		// View order message code end.

		// Show Refund button functionality start.

		$refund_will_made =
		// Refund Will made or not bool.
		apply_filters( 'wps_rma_refund_will_made', true, $order->get_id() );
		$wps_rma_return_request_form_page_id = get_option( 'wps_rma_return_request_form_page_id', true );
		if ( $refund_will_made && 'yes' === $condition ) {
			$return_button_text = get_option( 'wps_rma_refund_button_text', false );
			$return_button_view = get_option( 'wps_rma_refund_button_pages', false );
			if ( isset( $return_button_text ) && ! empty( $return_button_text ) ) {
				$return_button_text = $return_button_text;
			} else {
				$return_button_text = esc_html__( 'Refund', 'woo-refund-and-exchange-lite' );
			}
			$page_id            = $wps_rma_return_request_form_page_id;
			$return_url         = get_permalink( $page_id );
			$return_url         = add_query_arg( 'order_id', $order->get_id(), $return_url );
			$return_url         = wp_nonce_url( $return_url, 'wps_rma_nonce', 'wps_rma_nonce' );
			$refund_button_view = get_option( 'wps_rma_refund_button_pages', false );
			if ( empty( $return_button_view ) ) {
				$refund_button_view = array( 'none' );
			}
			if ( ! in_array( get_the_title(), $refund_button_view, true ) ) {
				?>
				<form action="<?php echo esc_html( $return_url ); ?>" method="post">
					<input type="hidden" value="<?php echo esc_html( $order->get_id() ); ?>" name="order_id">
					<p><input type="submit" class="btn button" value="<?php echo esc_html( $return_button_text ); ?>" name="ced_new_return_request"></p>
				</form>
				<?php
			}
		}
		do_action( 'wps_rxc_button_order_details', $order );

		// Show Refund button functionality end.

		if ( ! is_wc_endpoint_url( 'order-received' ) ) {
			?>
			<div class="wps_rma_outer_wrap_info">
				<ul class="wps_rma_ul_wrap_info">
					<li class="wps_rma_li_wrap_info wps_rma_li_refund active"><h2><?php esc_html_e( 'Refund', 'woo-refund-and-exchange-lite' ); ?></h2></li>
					<?php
					if ( function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active() ) {
						?>
					<li class="wps_rma_li_wrap_info wps_rma_li_exchange"><h2><?php esc_html_e( 'Exchange', 'woo-refund-and-exchange-lite' ); ?></h2></li>
						<?php
					}
					?>
				</ul>
				<div class="wps_rma_refund_info_wrap wps_rma_ret_ex_info_wrap">
					<?php

					// show return Product Details on order view page start.
					$product_datas = wps_rma_get_meta_data( $order->get_id(), 'wps_rma_return_product', true );
					if ( isset( $product_datas ) && ! empty( $product_datas ) ) {
						?>
						<h2><?php esc_html_e( 'Refund Requested Product', 'woo-refund-and-exchange-lite' ); ?></h2>
						<?php
						$request_status = true;
						foreach ( $product_datas as $key => $product_data ) {
							$date = date_i18n( wc_date_format(), $key );
							?>
							<p><?php esc_html_e( 'Following product Refund request made on', 'woo-refund-and-exchange-lite' ); ?> <b><?php echo esc_html( $date ); ?>.</b></p>
								<table>
									<thead>
										<tr>
											<th class="product-name"><?php esc_html_e( 'Product', 'woo-refund-and-exchange-lite' ); ?></th>
											<th class="product-total"><?php esc_html_e( 'Total', 'woo-refund-and-exchange-lite' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$total      = 0;
										$line_items = $order->get_items();
										$shipping_price = $order->get_shipping_total();
										if ( is_array( $line_items ) && ! empty( $line_items ) ) {
											wps_rma_update_meta_data( $order->get_id(), 'wps_rma_refund_new_line_items', $line_items );
										}
										$line_items = wps_rma_get_meta_data( $order->get_id(), 'wps_rma_refund_new_line_items', true );
										// Return Products.
										$return_products = $product_data['products'];
										foreach ( $line_items as $item_id => $item ) {
											foreach ( $return_products as $return_product ) {
												if ( isset( $return_product['item_id'] ) ) {
													if ( $return_product['item_id'] == $item_id ) {
														$total += $return_product['price'] * $return_product['qty'];
														?>
														<tr>
															<td>
																<?php
																$product =
																// Get Product.
																apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );

																$is_visible        = $product && $product->is_visible();
																$product_permalink =
																// Order item Permalink.
																apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
																if ( ! empty( $product ) && is_object( $product ) ) {
																	echo esc_html( $product_permalink ) ? sprintf( '<a href="%s">%s</a>', esc_html( $product_permalink ), esc_html( $product->get_name() ) ) : esc_html( $product->get_name() );
																}
																echo '<strong class="product-quantity">' . sprintf( '&times; %s', esc_html( $return_product['qty'] ) ) . '</strong>';

																// Order item meta start.
																do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

																wc_display_item_meta( $item );
																wc_display_item_downloads( $item );
																// Order item meta end.
																do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
																?>
															</td>
															<td class="product-total">
															<?php
																echo wp_kses_post( wps_wrma_format_price( $return_product['price'] * $return_product['qty'], $get_order_currency ) );
															?>
															</td>
														</tr>
														<?php
													}
												}
											}
										}
										?>
										<?php
										// To show extra row in the order view refund request table.
										do_action( 'wps_rma_add_extra_fields_row', $order->get_id() );

										$shipping_price = '';
										if ( isset( $product_data['shipping_price'] ) && ! empty( $product_data['shipping_price'] ) ) {
											$shipping_price = esc_html__( '(Shipping Charges Added)', 'woo-refund-and-exchange-lite' );
											$total += $product_data['shipping_price'];
										}
										?>
										<tr>
											<th scope="row"><?php esc_html_e( 'Refund Amount', 'woo-refund-and-exchange-lite' ); ?></th>
											<th><?php echo wp_kses_post( wps_wrma_format_price( $total, $get_order_currency ) ); ?></th>
										</tr>
									</tbody>
								</table>
								<?php
								if ( 'pending' === $product_data['status'] ) {
									$page_id    = $wps_rma_return_request_form_page_id;
									$return_url = get_permalink( $page_id );
									$return_url = add_query_arg( 'order_id', $order->get_id(), $return_url );
									$return_url = wp_nonce_url( $return_url, 'wps_rma_nonce', 'wps_rma_nonce' );
									?>
										<form action="<?php echo esc_html( $return_url ); ?>" method="post">
											<input type="hidden" value="<?php echo esc_html( $order->get_id() ); ?>" name="order_id">
											<p>
												<input type="submit" class="btn button" value="<?php esc_html_e( 'Update Request', 'woo-refund-and-exchange-lite' ); ?>" name="wps_mra_return_request">
											</p>
										</form>
										<?php if( 'on' === get_option( 'wps_rma_refund_cancellation' ) ): ?>
										<form action="/" id="wps_rma_cancel_return_request" method="post">
											<input type="hidden" class="wps_rma_cancel_return_request" value="<?php echo esc_html( $order->get_id() ); ?>" name="order_id">
											<p>
												<input type="submit" class="btn button" value="<?php esc_html_e( 'Cancel Request', 'woo-refund-and-exchange-lite' ); ?>" name="wps_rma_cancel_return_request">
											</p>
										</form>
										<?php
										endif;
								}
								if ( 'complete' === $product_data['status'] ) {
									$approve_date = date_i18n( wc_date_format(), $product_data['approve_date'] );
									?>
										<p><b><?php esc_html_e( 'The above product Refund request is approved on', 'woo-refund-and-exchange-lite' ); ?> <?php echo esc_html( $approve_date ); ?>.</b></p>
										<?php
								}

								if ( 'cancel' === $product_data['status'] ) {
									$canceldate = date_i18n( wc_date_format(), $product_data['cancel_date'] );
									?>
										<p><b><?php esc_html_e( 'The above product Refund request is cancelled on', 'woo-refund-and-exchange-lite' ); ?> <?php echo esc_html( $canceldate ); ?>.</b></p>
										<?php
								}
								?>
							<?php
						}
					} elseif ( ! is_wc_endpoint_url( 'order-received' ) ) {

						esc_html_e( 'No Refund Request Found For this order', 'woo-refund-and-exchange-lite' );
					}
					?>
					</div>
					<?php
					do_action( 'wps_rma_exchange_cancel_information_after_order_table', $order );
					?>
			</div>
			<?php
			$is_refund_rules = false;
			if ( 'on' === get_option( 'wps_rma_refund_enable', false ) )  {
				$get_specific_setting = array();
				$get_setting          = get_option( 'policies_setting_option', array() );
				$order_statuses_policy = array_filter( isset( $get_setting['wps_rma_setting'] ) ? $get_setting['wps_rma_setting'] : array(), function ($item) {
					return 'refund' === $item['row_functionality'] && in_array($item['row_policy'], ['wps_rma_order_status']);
				});
				$order_max_date_policy = array_filter( isset( $get_setting['wps_rma_setting'] ) ? $get_setting['wps_rma_setting'] : array(), function ($item) {
					return  'refund' === $item['row_functionality'] && in_array($item['row_policy'], ['wps_rma_maximum_days']);
				});
				$order_statuses_policy = array_values($order_statuses_policy);
				$order_max_date_policy = array_values($order_max_date_policy);

				$refund_rules_enable = get_option( 'wps_rma_refund_rules', 'no' );
				$refund_rules        = get_option( 'wps_rma_refund_rules_editor', '' );

				$is_rules = 'on' === $refund_rules_enable && ! empty( $refund_rules ) ? true : false;

				if ( $is_rules || ! empty( $order_max_date_policy ) || ! empty( $order_max_date_policy ) ) { 
					$is_refund_rules = true;
					?>
					<br/>
					<div class="wps_rma_return_rules">
						<h2><?php esc_html_e( 'Refund Rules', 'woo-refund-and-exchange-lite' ); ?></h2>
						<?php
						if ( $is_rules ) {
							?>
							<div class="wps-rma-col wps_rma_flex">        
								<?php
									echo wp_kses_post( $refund_rules );
								?>
							</div>
							<?php
						}
						if ( ! empty( $order_statuses_policy ) && isset( $order_statuses_policy[0] ) && isset( $order_statuses_policy[0]['row_statuses'] ) && ! empty( $order_statuses_policy[0]['row_statuses'] ) ) {
							$allowed_statuses = $order_statuses_policy[0]['row_statuses'];
						}
						if ( ! empty( $order_max_date_policy ) && isset( $order_max_date_policy[0] ) && isset( $order_max_date_policy[0]['row_value'] ) && ! empty( $order_max_date_policy[0]['row_value'] ) ) {
							$allowed_days = $order_max_date_policy[0]['row_value'];
							$order_date = date_i18n( 'y-m-d', strtotime( $order->get_date_created() ) );
							$today_date = date_i18n( 'y-m-d' );
							$order_date = apply_filters( 'wps_order_status_start_date', strtotime( $order_date ), $order );
							$today_date = strtotime( $today_date );
							$days       = $today_date - $order_date;
							$day_diff   = floor( $days / ( 60 * 60 * 24 ) );
			
							if ( isset( $order_max_date_policy[0]['row_conditions1'] ) && 'wps_rma_less_than' === $order_max_date_policy[0]['row_conditions1'] ) {
								if ( $day_diff < floatval( $allowed_days ) ) {
									// translators: %s: days.
									$show_rules = sprintf( esc_html__( 'Refund are valid for %s days', 'woo-refund-and-exchange-lite' ), $allowed_days );
								} else {
									// translators: %s: days.
									$show_rules = sprintf( esc_html__( 'Refund days are exceed, must be less than %s days', 'woo-refund-and-exchange-lite' ), $allowed_days );
								}
							} elseif ( $order_max_date_policy[0]['row_conditions1'] && 'wps_rma_greater_than' === $order_max_date_policy[0]['row_conditions1'] ) {
								if ( $day_diff > floatval( $allowed_days ) ) {
									// translators: %s: days.
									$show_rules = sprintf( esc_html__( 'Refund are valid after %s days', 'woo-refund-and-exchange-lite' ), $allowed_days );
								} else {
									// translators: %s: days.
									$show_rules = sprintf( esc_html__( 'Refund are valid after %s days', 'woo-refund-and-exchange-lite' ), $allowed_days );
								}
							}
			
							if ( isset( $show_rules ) ) {
								?>
								<p><?php echo esc_html( $show_rules ); ?></p>
								<?php
							}
							if ( isset( $allowed_statuses ) && ! empty( $allowed_statuses ) ) {
								$cleaned_data = array_map(function($item) {
									return str_replace("wc-", "", $item);
								}, $allowed_statuses);
								$string = implode(", ", $cleaned_data);
								// translators: %s: statuses.
								$show_rules = sprintf( esc_html__( 'Refund allowed for these order statuses :- %s', 'woo-refund-and-exchange-lite' ), $string );
								?>
								<p><?php echo esc_html( $show_rules ) ?></p>
								<?php
							}
						}
						?>
					</div>
					<br/>
					<?php
				}
			}
			do_action( 'wps_rma_exchange_rules', $order, $is_refund_rules );
		}
		// show return Product Details on order view page end.
	}

	/**
	 * Shows the templtes.
	 *
	 * @param array $template .
	 */
	public function wps_rma_product_return_template( $template ) {
		$wps_rma_return_request_form_page_id = get_option( 'wps_rma_return_request_form_page_id' );
		if ( has_filter( 'wpml_object_id' ) ) {
			$ro_pageid = apply_filters( 'wpml_object_id', $wps_rma_return_request_form_page_id, 'page', false, ICL_LANGUAGE_CODE );
		}
		if ( ( ( '' !== $wps_rma_return_request_form_page_id ) && is_page( $wps_rma_return_request_form_page_id ) ) || ( isset( $ro_pageid ) && is_page( $ro_pageid ) ) ) {
			$get_id = get_option( 'wps_rma_refund_page' );
			if ( function_exists( 'vc_lean_map' ) && ! empty( $get_id ) ) {
				if ( isset( $_GET['wps_rma_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wps_rma_nonce'] ) ), 'wps_rma_nonce' ) && isset( $_GET['order_id'] ) ) {
					$url           = get_permalink( $get_id );
					$order_id      = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
					$wps_rma_nonce = sanitize_text_field( wp_unslash( $_GET['wps_rma_nonce'] ) );
					$args          = array(
						'order_id'      => $order_id,
						'wps_rma_nonce' => $wps_rma_nonce,
					);
					$url = add_query_arg( $args, $url );
					wp_safe_redirect( $url );
				}
			}
			$new_template = esc_html( WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH ) . 'public/partials/wps-rma-refund-request-form.php';
			$template     = $new_template;
		}

		$wps_rma_view_order_msg_page_id = get_option( 'wps_rma_view_order_msg_page_id' );
		if ( has_filter( 'wpml_object_id' ) ) {
			$ro_pageid1 = apply_filters( 'wpml_object_id', $wps_rma_view_order_msg_page_id, 'page', false, ICL_LANGUAGE_CODE );
		}
		if ( ( '' !== $wps_rma_view_order_msg_page_id ) && ( is_page( $wps_rma_view_order_msg_page_id ) || ( isset( $ro_pageid1 ) && is_page( $ro_pageid1 ) ) ) ) {
			$get_id = get_option( 'wps_rma_order_msg_page' );
			if ( function_exists( 'vc_lean_map' ) && ! empty( $get_id ) ) {
				if ( isset( $_GET['wps_rma_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wps_rma_nonce'] ) ), 'wps_rma_nonce' ) && isset( $_GET['order_id'] ) ) {
					$url           = get_permalink( $get_id );
					$order_id      = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
					$wps_rma_nonce = sanitize_text_field( wp_unslash( $_GET['wps_rma_nonce'] ) );
					$args          = array(
						'order_id'      => $order_id,
						'wps_rma_nonce' => $wps_rma_nonce,
					);
					$url = add_query_arg( $args, $url );
					wp_safe_redirect( $url );
				}
			}
			$new_template = esc_html( WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH ) . 'public/partials/wps-rma-view-order-msg.php';
			$template     = $new_template;
		}
		return $template;
	}
}
