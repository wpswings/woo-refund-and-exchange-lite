<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 * namespace woo_refund_and_exchange_lite_public.
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/public
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

		wp_enqueue_style( $this->plugin_name, WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'public/css/woo-refund-and-exchange-lite-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wrael_public_enqueue_scripts() {

		$pro_active = mwb_rma_pro_active();
		wp_register_script( $this->plugin_name, WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'public/js/woo-refund-and-exchange-lite-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->plugin_name,
			'wrael_public_param',
			array(
				'ajaxurl'               => admin_url( 'admin-ajax.php' ),
				'mwb_rma_nonce'         => wp_create_nonce( 'mwb_rma_ajax_security' ),
				'return_subject_msg'    => esc_html__( 'Please enter refund subject.', 'woo-refund-and-exchange-lite' ),
				'return_reason_msg'     => esc_html__( 'Please enter refund reason.', 'woo-refund-and-exchange-lite' ),
				'return_select_product' => esc_html__( 'Please select product to refund.', 'woo-refund-and-exchange-lite' ),
				'check_pro_active'      => esc_html( $pro_active ),
			)
		);
		wp_enqueue_script( $this->plugin_name );

	}

	/**
	 * Add return button on my-account order section.
	 *
	 * @param array  $actions is current order action.
	 * @param object $order is a current order.
	 */
	public function mwb_rma_refund_button( $actions, $order ) {
		$show_refund_button = mwb_rma_show_buttons( 'refund', $order );
		$view_msg           = get_option( 'mwb_rma_general_om', 'no' );
		$mwb_rma_return     = get_option( 'mwb_rma_refund_enable', false );
		$refund_hide        = get_option( 'mwb_rma_refund_button_pages', false );
		if ( isset( $view_msg ) && 'on' === $view_msg ) {
			$order_msg_button_text = get_option( 'mwb_rma_order_message_button_text', false );
			if ( isset( $order_msg_button_text ) && ! empty( $order_msg_button_text ) ) {
				$order_msg_button_text = $order_msg_button_text;
			} else {
				$order_msg_button_text = __( 'View Order Messages', 'woo-refund-and-exchange-lite' );
			}
			$mwb_rma_view_order_msg_page_id = get_option( 'mwb_rma_view_order_msg_page_id', true );
			$msg_url                        = get_permalink( $mwb_rma_view_order_msg_page_id );
			$msg_url                        = add_query_arg( 'order_id', $order->get_id(), $msg_url );
			$msg_url                        = wp_nonce_url( $msg_url, 'mwb_rma_nonce', 'mwb_rma_nonce' );
			$actions['view_msg']['url']     = $msg_url;
			$actions['view_msg']['name']    = $order_msg_button_text;
		}

		$item_refund_already = get_post_meta( $order->get_id(), 'mwb_rma_request_made', true );
		$item_refund_already =
		// change refund made bool.
		apply_filters( 'mwb_rma_refund_made', $item_refund_already );
		if ( ! empty( $item_refund_already ) ) {
			if ( isset( $item_refund_already[0] ) && ( 'pending' === $item_refund_already[0] || 'cancel' === $item_refund_already[0] ) ) {
				$refund_will_made = true;
			} else {
				$refund_will_made = false;
			}
		} else {
			$refund_will_made = true;
		}

		$refund_will_made =
		// Refund Will made or not bool.
		apply_filters( 'mwb_rma_refund_will_made', $refund_will_made );
		$show_on_pages = true;
		if ( ! empty( $refund_hide ) && in_array( 'order-page', $refund_hide, true ) ) {
			$show_on_pages = false;
		}
		if ( $mwb_rma_return && $refund_will_made && $show_on_pages && 'yes' === $show_refund_button ) {
			$return_button_text = get_option( 'mwb_rma_refund_button_text', false );
			if ( isset( $return_button_text ) && ! empty( $return_button_text ) ) {
				$return_button_text = $return_button_text;
			} else {
				$return_button_text = __( 'Refund', 'woo-refund-and-exchange-lite' );
			}
				$mwb_rma_return_request_form_page_id = get_option( 'mwb_rma_return_request_form_page_id' );
				$return_url                          = get_permalink( $mwb_rma_return_request_form_page_id );
				$return_url                          = add_query_arg( 'order_id', $order->get_id(), $return_url );
				$return_url                          = wp_nonce_url( $return_url, 'mwb_rma_nonce', 'mwb_rma_nonce' );
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
	public function mwb_rma_return_button_and_details( $order ) {
		global $wp;
		$condition          = mwb_rma_show_buttons( 'refund', $order );
		$get_order_currency = get_woocommerce_currency_symbol( $order->get_currency() );

		// View order message code start.
		$mwb_rma_view_order_msg_page_id    = get_option( 'mwb_rma_view_order_msg_page_id', true );
		$view_order_msg_url                = get_permalink( $mwb_rma_view_order_msg_page_id );
		$view_order_msg_url                = add_query_arg( 'order_id', $order->get_id(), $view_order_msg_url );
		$view_order_msg_url                = wp_nonce_url( $view_order_msg_url, 'mwb_rma_nonce', 'mwb_rma_nonce' );
		$mwb_rma_order_message_button_text = get_option( 'mwb_rma_order_message_button_text', false );
		if ( ! empty( $mwb_rma_order_message_button_text ) ) {
			$mwb_rma_order_message_button_text = $mwb_rma_order_message_button_text;
		} else {
			$mwb_rma_order_message_button_text = __( 'View Order Message', 'woo-refund-and-exchange-lite' );
		}
		$view_msg     = get_option( 'mwb_rma_general_om', 'no' );
		$redirect_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( isset( $redirect_uri ) ) {
			if ( isset( $view_msg ) && 'on' === $view_msg ) {
				?>
				<form action="<?php echo esc_html( add_query_arg( 'order_id', $order->get_id(), $view_order_msg_url ) ); ?>" method="post">
					<input type="hidden" value="<?php echo esc_html( $order->get_id() ); ?>" name="order_id">
					<p>
						<input type="submit" class="btn button" value="<?php echo esc_html( $mwb_rma_order_message_button_text ); ?>">
					</p>
				</form>
				<?php
			}
		}
		// View order message code end.

		// Show Refund button functionality start.
		$item_refund_already = get_post_meta( $order->get_id(), 'mwb_rma_request_made', true );

		$item_refund_already =
		// change refund made bool.
		apply_filters( 'mwb_rma_refund_made', $item_refund_already );
		if ( ! empty( $item_refund_already ) ) {
			if ( isset( $item_refund_already[0] ) && ( 'pending' === $item_refund_already[0] || 'cancel' === $item_refund_already[0] ) ) {
				$refund_will_made = true;
			} else {
				$refund_will_made = false;
			}
		} else {
			$refund_will_made = true;
		}
		// Refund Will made or not bool.
		$refund_will_made                    = apply_filters( 'mwb_rma_refund_will_made', $refund_will_made );
		$mwb_rma_return_request_form_page_id = get_option( 'mwb_rma_return_request_form_page_id', true );
		if ( $refund_will_made && 'yes' === $condition ) {
			$return_button_text = get_option( 'mwb_rma_refund_button_text', false );
			$return_button_view = get_option( 'mwb_rma_refund_button_pages', false );
			if ( isset( $return_button_text ) && ! empty( $return_button_text ) ) {
				$return_button_text = $return_button_text;
			} else {
				$return_button_text = __( 'Refund', 'woo-refund-and-exchange-lite' );
			}
			$page_id            = $mwb_rma_return_request_form_page_id;
			$return_url         = get_permalink( $page_id );
			$return_url         = add_query_arg( 'order_id', $order->get_id(), $return_url );
			$return_url         = wp_nonce_url( $return_url, 'mwb_rma_nonce', 'mwb_rma_nonce' );
			$refund_button_view = get_option( 'mwb_rma_refund_button_pages', false );
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
		// Show Refund button functionality end.

		// show return Product Details on order view page start.
		$product_datas = get_post_meta( $order->get_id(), 'mwb_rma_return_product', true );
		if ( isset( $product_datas ) && ! empty( $product_datas ) ) {
			?>
			<h2><?php esc_html_e( 'Refund Requested Product', 'woo-refund-and-exchange-lite' ); ?></h2>
			<?php
			$request_status = true;
			foreach ( $product_datas as $key => $product_data ) {
				$date        = date_create( $key );
				$date_format = get_option( 'date_format' );
				$date        = date_format( $date, $date_format );
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
							$line_items = $order->get_items();
							if ( is_array( $line_items ) && ! empty( $line_items ) ) {
								update_post_meta( $order->get_id(), 'mwb_rma_refund_new_line_items', $line_items );
							}
							$line_items = get_post_meta( $order->get_id(), 'mwb_rma_refund_new_line_items', true );
							// Return Products.
							$return_products = $product_data['products'];
							foreach ( $line_items as $item_id => $item ) {
								foreach ( $return_products as $return_product ) {
									if ( isset( $return_product['item_id'] ) ) {
										if ( $return_product['item_id'] == $item_id ) {
											?>
											<tr>
												<td>
													<?php
													$product = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );

													$is_visible        = $product && $product->is_visible();
													$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

													echo esc_html( $product_permalink ) ? sprintf( '<a href="%s">%s</a>', esc_html( $product_permalink ), esc_html( $product->get_name() ) ) : esc_html( $product->get_name() );
													echo '<strong class="product-quantity">' . sprintf( '&times; %s', esc_html( $return_product['qty'] ) ) . '</strong>';

													do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order );

													if ( WC()->version < '3.0.0' ) {
														$order->display_item_meta( $item );
														$order->display_item_downloads( $item );
													} else {
														wc_display_item_meta( $item );
														wc_display_item_downloads( $item );
													}

													do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );
													?>
												</td>
												<td class="product-total">
												<?php
													echo wp_kses_post( mwb_wrma_format_price( $return_product['price'] * $return_product['qty'], $get_order_currency ) );
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
							do_action( 'mwb_rma_add_extra_fields_row', $order->get_id() );
							?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Refund Amount', 'woo-refund-and-exchange-lite' ); ?></th>
								<th><?php echo wp_kses_post( mwb_wrma_format_price( $product_data['amount'], $get_order_currency ) ); ?></th>
							</tr>
						</tbody>
					</table>
					<?php
					if ( 'pending' === $product_data['status'] ) {
						$page_id    = $mwb_rma_return_request_form_page_id;
						$return_url = get_permalink( $page_id );
						$return_url = add_query_arg( 'order_id', $order->get_id(), $return_url );
						$return_url = wp_nonce_url( $return_url, 'mwb_rma_nonce', 'mwb_rma_nonce' );
						?>
							<form action="<?php echo esc_html( $return_url ); ?>" method="post">
								<input type="hidden" value="<?php echo esc_html( $order->get_id() ); ?>" name="order_id">
								<p>
									<input type="submit" class="btn button" value="<?php esc_html_e( 'Update Request', 'woo-refund-and-exchange-lite' ); ?>" name="mwb_mra_return_request">
								</p>
							</form>
							<?php
					}
					if ( 'complete' === $product_data['status'] ) {
						$appdate      = date_create( $product_data['approve_date'] );
						$format       = get_option( 'date_format' );
						$approve_date = date_format( $appdate, $format );
						?>
							<p><b><?php esc_html_e( 'Above product Refund request is approved on', 'woo-refund-and-exchange-lite' ); ?> <?php echo esc_html( $approve_date ); ?>.</b></p>
							<?php
					}

					if ( 'cancel' === $product_data['status'] ) {
						$appdate    = date_create( $product_data['cancel_date'] );
						$format     = get_option( 'date_format' );
						$canceldate = date_format( $appdate, $format );
						?>
							<p><b><?php esc_html_e( 'Above product Refund request is canceled on', 'woo-refund-and-exchange-lite' ); ?> <?php echo esc_html( $canceldate ); ?>.</b></p>
							<?php
					}
					?>
				<?php
			}
		}
		// show return Product Details on order view page end.
	}

	/**
	 * Shows the templtes.
	 *
	 * @param array $template .
	 */
	public function mwb_rma_product_return_template( $template ) {
		$mwb_rma_return_request_form_page_id = get_option( 'mwb_rma_return_request_form_page_id' );
		if ( function_exists( 'icl_object_id' ) ) {
			$ro_pageid = apply_filters( 'wpml_object_id', $mwb_rma_return_request_form_page_id, 'page', false, ICL_LANGUAGE_CODE );
		}
		if ( ( ( '' !== $mwb_rma_return_request_form_page_id ) && is_page( $mwb_rma_return_request_form_page_id ) ) || ( isset( $ro_pageid ) && is_page( $ro_pageid ) ) ) {
			$located = locate_template( 'woo-refund-and-exchange-lite/public/partials/mwb-rma-refund-request-form.php' );
			if ( ! empty( $located ) ) {

				$new_template = wc_get_template( 'woo-refund-and-exchange-lite/public/partials/mwb-rma-refund-request-form.php' );
			} else {
				$new_template = WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'public/partials/mwb-rma-refund-request-form.php';
			}
			$template = $new_template;
		}

		$mwb_rma_view_order_msg_page_id = get_option( 'mwb_rma_view_order_msg_page_id' );
		if ( has_filter( 'wpml_object_id' ) ) {
			$ro_pageid1 = apply_filters( 'wpml_object_id', $mwb_rma_view_order_msg_page_id, 'page', false, ICL_LANGUAGE_CODE );
		}
		if ( ( '' !== $mwb_rma_view_order_msg_page_id ) && ( is_page( $mwb_rma_view_order_msg_page_id ) ) || ( isset( $ro_pageid1 ) && is_page( $ro_pageid1 ) ) ) {
			$located = locate_template( 'woo-refund-and-exchange-lite/public/partials/mwb-rma-view-order-msg.php' );
			if ( ! empty( $located ) ) {

				$new_template = wc_get_template( 'woo-refund-and-exchange-lite/public/partials/mwb-rma-view-order-msg.php' );
			} else {
				$new_template = WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'public/partials/mwb-rma-view-order-msg.php';
			}
			$template = $new_template;
		}

		return $template;
	}

}
