<?php
/**
 * The public-facing functionality of the plugin for return request form.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/public/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

$wps_wrma_show_sidebar_on_form =
// Side show/hide on refund request form.
apply_filters( 'wps_rma_refund_form_sidebar', true );
if ( $wps_wrma_show_sidebar_on_form ) {
	// Before Main Content.
	do_action( 'woocommerce_before_main_content' );
}

if ( isset( $_GET['wps_rma_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wps_rma_nonce'] ) ), 'wps_rma_nonce' ) && isset( $_GET['order_id'] ) && current_user_can( 'wps-rma-refund-request' ) ) {
	$order_id = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
	$order_obj = wc_get_order( $order_id );
	if ( ! empty( $order_id ) && ! empty( $order_obj ) ) {
		$is_refundable = wps_rma_show_buttons( 'refund', $order_obj );
		if ( 'yes' === $is_refundable ) {
			$order_customer_id = $order_obj->get_user_id();
			$user              = wp_get_current_user();
			$allowed_roles     = array( 'editor', 'administrator', 'shop_manager' );
			if ( get_current_user_id() != $order_customer_id && ! array_intersect( $allowed_roles, $user->roles ) ) {
				$myaccount_page     = get_option( 'woocommerce_myaccount_page_id' );
				$myaccount_page_url = get_permalink( $myaccount_page );
				$is_refundable      = wp_kses_post( "This order #$order_id is not associated to your account. <a href='$myaccount_page_url'>Click Here</a>" );
			}
		}

		if ( 'yes' === $is_refundable ) {
			$rr_subject = '';
			$rr_reason  = '';
			$shipping_already_requested = false;

			$pending_request = wps_rma_get_meta_data( $order_id, 'wps_rma_return_product', true );
			// Get pending return request data.
			if ( ! empty( $pending_request ) ) {
				foreach ( $pending_request as $date => $product ) {
					if ( 'pending' === $product['status'] ) {
						$rr_subject = $pending_request[ $date ]['subject'];
						if ( isset( $pending_request[ $date ]['reason'] ) ) {
							$rr_reason = $pending_request[ $date ]['reason'];
						}
						if ( isset( $pending_request[$date]['shipping_price'] ) ) {
							$shipping_already_requested = true;
						}
					}
					break;
				}
			}
			$wps_refund_wrapper_class = get_option( 'wps_wrma_refund_form_wrapper_class' );
			$wps_return_css           = get_option( 'wps_rma_refund_form_css' );
			$template_class = get_option( 'wps_rma_return_template_css', '' );

			if ( $template_class ) {
				$template_id = 'wps_rma_' . $template_class;
			} else {
				$template_id = 'wps_rma_return_request_container';
			}
			?>
			<style><?php echo wp_kses_post( $wps_return_css ); ?></style>
			<div class="wps_rma_refund_form_wrapper wps-rma-form__wrapper <?php echo esc_html( $wps_refund_wrapper_class ); ?> wps_rma_<?php echo esc_html( $template_class ); ?>" id="<?php echo esc_html( $template_id ); ?>">
				<div id="wps_rma_return_request_container" class="wps-rma-form__header">
					<h1 class="wps-rma-form__heading"><?php esc_html_e( 'Order\'s Product Refund Request Form', 'woo-refund-and-exchange-lite' ); ?></h1>
				</div>
				<ul id="wps_rma_return_alert" ></ul>
				<div class="wps_rma_product_table_wrapper wps-rma-product__table-wrapper">
					<table class="wps-rma-product__table">
						<thead >
							<tr>
								<?php
								// Add extra field in the thead of the table.
								do_action( 'wps_rma_add_extra_column_refund_form', $order_id );
								?>
								<th><?php esc_html_e( 'Product', 'woo-refund-and-exchange-lite' ); ?></th>
								<th><?php esc_html_e( 'Quantity', 'woo-refund-and-exchange-lite' ); ?></th>
								<th><?php esc_html_e( 'Total', 'woo-refund-and-exchange-lite' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$total_items_price = 0;
							$wps_rma_check_tax      = get_option( 'refund_wps_rma_tax_handling' );

							$get_order_currency   = get_woocommerce_currency_symbol( $order_obj->get_currency() );
							$refund_items_details = wps_rma_get_meta_data( $order_id, 'wps_rma_refund_items_details', true );
							$shipping_price = $order_obj->get_shipping_total();
							if ( 'wps_rma_inlcude_tax' === $wps_rma_check_tax ) {
								$shipping_price += $order_obj->get_shipping_tax();
							}
							foreach ( $order_obj->get_items() as $item_id => $item ) {
								$item_quantity = $item->get_quantity();
								$refund_qty    = $order_obj->get_qty_refunded_for_item( $item_id );
								$item_qty      = $item->get_quantity() + $refund_qty;
								// manage the return quantity, if there is refund reuqest has been made and approved.
								if ( ! empty( $refund_items_details ) && isset( $refund_items_details[ $item_id ] ) ) {
									$return_item_qty = $refund_items_details[ $item_id ];
									$item_qty        = $item->get_quantity() - $return_item_qty;
								}
								if ( $item_qty > 0 ) {
									if ( ! empty( $item->get_product_id() ) ) {
										$product_id   = $item->get_product_id();
									} else {
										$product_id = $item->get_product_id();
									}
									$product = wc_get_product( $product_id );

									$coupon_discount = get_option( 'wps_rma_refund_deduct_coupon', 'no' );
									if ( 'on' === $coupon_discount ) {
										$item_price_inc_tax = $item->get_total() + $item->get_total_tax();
										$item_price_exc_tax = $item->get_total() - $item->get_total_tax();
										$item_price = $item->get_total();
									} else {
										$item_price_inc_tax = $item->get_subtotal() + $item->get_subtotal_tax();
										$item_price_exc_tax = $item->get_subtotal() - $item->get_subtotal_tax();
										$item_price = $item->get_subtotal();
									}
									if ( 'wps_rma_inlcude_tax' === $wps_rma_check_tax ) {
										$item_price = $item_price_inc_tax;
									} elseif ( 'wps_rma_exclude_tax' === $wps_rma_check_tax ) {
										$item_price = $item_price_exc_tax;
									}
									$total_items_price += $item_price;
									?>
									<tr class="wps_rma_return_column" data-productid="<?php echo esc_html( $product_id ); ?>" data-variationid="<?php echo esc_html( $item['variation_id'] ); ?>" data-item_id="<?php echo esc_html( $item_id ); ?>">
										<?php
										// To show extra column field value in the tbody.
										do_action( 'wps_rma_add_extra_column_field_value', $item_id, $product_id, $order_obj );
										?>
										<td class="product-name">
											<input type="hidden" name="wps_rma_product_amount" class="wps_rma_product_amount" data-item_id="<?php echo esc_html( $item_id ); ?>" value="<?php echo esc_html( $item_price / $item->get_quantity() ); ?>">
											<div class="wps-rma-product__wrap">
												<?php
												$product_permalink = ( $product && $product->is_visible() ) ? $product->get_permalink( $item ) : '';
												$thumbnail = ( $product && wp_get_attachment_image( $product->get_image_id(), 'thumbnail' ) ) ? wp_get_attachment_image( $product->get_image_id(), 'thumbnail' ) : null;
												if ( ! empty( $thumbnail ) ) {
													echo wp_kses_post( $thumbnail );
												} else {
													?>
													<img alt="Placeholder" width="150" height="150" class="attachment-thumbnail size-thumbnail wp-post-image" src="<?php echo esc_html( plugins_url() ); ?>/woocommerce/assets/images/placeholder.png">
													<?php
												}
													?>
												<div class="wps_rma_product_title wps-rma__product-title">
													<?php
													echo wp_kses_post( $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name() );
													echo wp_kses_post( '<strong class="product-quantity">' . sprintf( '&times; %s', $item->get_quantity() ) . '</strong>' );
													?>
													<p>
														<b><?php esc_html_e( 'Price', 'woo-refund-and-exchange-lite' ); ?> :</b> 
														<?php
															echo wp_kses_post( wps_wrma_format_price( $item->get_total() / $item->get_quantity(), $get_order_currency ) );
														?>
													</p>
												</div>
											</div>
										</td>
										<td class="product-quantity">
										<?php
										$allow_html = array(
											'input' => array(
												'type'     => array(),
												'value'    => array(),
												'class'    => array(),
												'name'     => array(),
												'disabled' => array(),
												'min'      => 1,
												'max'      => $item_qty,
											),
										);
										$qty_html   = '<input type="number" max="'. esc_html( $item_qty ) .'" min="1" disabled value="' . esc_html( $item_qty ) . '" class="wps_rma_return_product_qty" name="wps_rma_return_product_qty">';
										echo // Refund form Quantity html.
										wp_kses( apply_filters( 'wps_rma_change_quanity', $qty_html, $item_qty ), $allow_html ); // phpcs:ignore
										?>
										</td>
										<td class="product-total">
											<?php
											echo wp_kses_post( wps_wrma_format_price( $item_price / $item->get_quantity(), $get_order_currency ) );

											if ( 'wps_rma_inlcude_tax' === $wps_rma_check_tax ) {
												?>
												<small class="tax_label"><?php esc_html_e( '(incl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
												<?php
											} elseif ( 'wps_rma_exclude_tax' === $wps_rma_check_tax ) {
												?>
													<small class="tax_label"><?php esc_html_e( '(excl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
													<?php
											}
											?>
										</td>
									</tr>
										<?php
									?>
									<?php
								}
							}
							$wps_rma_allow_refund_shipping_charge = get_option( 'wps_rma_allow_refund_shipping_charge' );
							if ( 'on' == $wps_rma_allow_refund_shipping_charge && $shipping_price && ! $shipping_already_requested ) { // add the shipping charges and avoid duplicate entry.
								$total_items_price += $shipping_price;
							}
							?>
							<tr>
								<th scope="row" colspan="<?php echo wps_rma_pro_active() ? '3' : '2'; ?>"><?php esc_html_e( 'Total Refund Amount', 'woo-refund-and-exchange-lite' ); ?></th>
								<td class="wps_rma_total_amount_wrap"><span id="wps_rma_total_refund_amount" data-total="<?php echo esc_html( $total_items_price ); ?>"><?php echo wp_kses_post( wps_wrma_format_price( $total_items_price, $get_order_currency ) ); ?></span>

									<?php
									if ( 'wps_rma_inlcude_tax' === $wps_rma_check_tax ) {
										?>
										<small class="tax_label"><?php esc_html_e( '(incl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
										<?php
									} elseif ( 'wps_rma_exclude_tax' === $wps_rma_check_tax ) {
										?>
										<small class="tax_label"><?php esc_html_e( '(excl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
										<?php
									}
									if ( 'on' == $wps_rma_allow_refund_shipping_charge && $shipping_price && ! $shipping_already_requested ) { // add the shipping charges and avoid duplicate entry.
										?>
										<input type="hidden" name="wps_rma_shipping_price" class="wps_rma_shipping_price" value="<?php echo esc_html( $shipping_price ); ?>" data-shipping_price="<?php echo esc_html( $shipping_price ); ?>">
										<small class="wps_shipping_label"><?php echo esc_html( 'Shipping Charges' ); ?></small>
										<?php
									}
									?>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="wps_rma_return_notification_checkbox" style="display:none"><img src="<?php echo esc_html( esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL ) ); ?>public/images/loading.gif" width="40px"></div>
				</div>
				<?php
				$predefined_return_reason = get_option( 'wps_rma_refund_reasons', '' );
				$predefined_return_reason = explode( ',', $predefined_return_reason );
				?>
				<div class="wps-rma-refund-request__row wps-rma-row__pd">
					<div class="wps-rma-col">
						<?php

						// Add someting after table on the refund request form.
						do_action( 'wps_rma_after_table', $order_id );
						$re_bank = get_option( 'wps_rma_refund_manually_de', false );
						if ( 'on' === $re_bank ) {
							?>
							<div id="bank_details">
							<label>
								<b>
									<?php
										echo esc_html__( 'Bank Account Details', 'woo-refund-and-exchange-lite' );
									?>
								</b>
							</label>
							<textarea name="" class="wps_rma_bank_details" rows=4 id="wps_rma_bank_details" maxlength="1000" placeholder="<?php esc_html_e( 'Please Enter the bank details for manual refund', 'woo-refund-and-exchange-lite' ); ?>"></textarea>
							</div>
							<?php
						}
						?>
						<div class="wps_rma_subject_dropdown wps-rma-subject__dropdown">
							<div>
								<label>
									<b>
										<?php
											echo esc_html__( 'Subject of Refund Request', 'woo-refund-and-exchange-lite' );
										?>
									</b>
								</label>
								<span class="wps_field_mendatory">*</span>
							</div>
							<select name="wps_rma_return_request_subject" id="wps_rma_return_request_subject">
								<?php
								if ( ! empty( $predefined_return_reason[0] ) ) {
									foreach ( $predefined_return_reason as $predefine_reason ) {
										$predefine_reason = trim( $predefine_reason );
										?>
										<option value="<?php echo esc_html( $predefine_reason ); ?>"  <?php selected( $predefine_reason, $rr_subject ); ?>><?php echo esc_html( $predefine_reason ); ?></option>
										<?php
									}
								}
								?>
								<option value=""><?php esc_html_e( 'Other', 'woo-refund-and-exchange-lite' ); ?></option>
							</select>
						</div>
						<div class="wps_rma_other_subject">
							<input type="text" name="ced_rnx_return_request_subject" class="wps_rma_return_request_subject_text" value="<?php echo esc_attr( $rr_subject ); ?>" id="wps_rma_return_request_subject_text" value="<?php echo esc_html( $rr_reason ); ?>" maxlength="5000" placeholder="<?php esc_html_e( 'Write your refund reason', 'woo-refund-and-exchange-lite' ); ?>">
						</div>
						<?php
						$predefined_return_desc = get_option( 'wps_rma_refund_description', false );
						if ( isset( $predefined_return_desc ) && 'on' === $predefined_return_desc ) {
							?>
							<div class="wps_rma_reason_description">
								<div>	
									<label>
										<b>
										<?php
										echo esc_html__( 'Description for Refund Reason', 'woo-refund-and-exchange-lite' );
										?>
										</b>
									</label>
									<span class="wps_field_mendatory">*</span>
								</div>
								<?php
								$predefined_return_reason_placeholder = get_option( 'wps_rma_refund_reason_placeholder', false );
								if ( empty( $predefined_return_reason_placeholder ) ) {
									$predefined_return_reason_placeholder = esc_html__( 'Write your description for a refund', 'woo-refund-and-exchange-lite' );
								}
								?>
								<textarea name="wps_rma_return_request_reason" cols="40" style="height: 222px;" class="wps_rma_return_request_reason" maxlength='10000' placeholder="<?php echo esc_html( $predefined_return_reason_placeholder ); ?>"><?php echo ! empty( $rr_reason ) ? esc_html( $rr_reason ) : ''; ?></textarea>
							</div>
							<?php
						}
						?>
						<?php
						// Add something above attachment on the refund request form.
						do_action( 'wps_rma_above_the_attachment' );
						?>
						<form action="" method="post" id="wps_rma_return_request_form" data-orderid="<?php echo esc_html( $order_id ); ?>" enctype="multipart/form-data">
							<?php
							$return_attachment = get_option( 'wps_rma_refund_attachment', false );
							$attach_limit      = get_option( 'wps_rma_attachment_limit', '15' );
							if ( empty( $attach_limit ) ) {
								$attach_limit = 5;
							}
							if ( isset( $return_attachment ) && ! empty( $return_attachment ) ) {
								if ( 'on' === $return_attachment ) {
									?>
									<label><b><?php esc_html_e( 'Attach Files', 'woo-refund-and-exchange-lite' ); ?></b></label>
									<div class="wps_rma_attach_files">
										<p>
											<span id="wps_rma_return_request_files">
											<input type="hidden" name="wps_rma_return_request_order" value="<?php echo esc_html( $order_id ); ?>">
											<input type="hidden" name="action" value="wps_rma_refund_upload_files">
											<input type="file" name="wps_rma_return_request_files[]" class="wps_rma_return_request_files">
											</span>
											<div><input type="button" value="<?php esc_html_e( 'Add More', 'woo-refund-and-exchange-lite' ); ?>" class="wps_rma_return_request_morefiles" data-count="1" data-max="<?php echo esc_html( $attach_limit ); ?>"></div>
											<i><?php esc_html_e( 'Only png, jpg and jpeg extension file is approved', 'woo-refund-and-exchange-lite' ); ?>.</i>
										</p>
									</div>
									<?php
								}
							}
							$wps_rma_enable_sms_notification = get_option( 'wps_rma_enable_sms_notification' );
							$wps_rma_enable_sms_notification_for_customer = get_option( 'wps_rma_enable_sms_notification_for_customer' );

							//whatsapp notification.
							$wps_rma_enable_whatsapp_notification = get_option( 'wps_rma_enable_whatsapp_notification' );
							if ( ( ( 'on' == $wps_rma_enable_sms_notification_for_customer && 'on' == $wps_rma_enable_sms_notification ) || 'on' == $wps_rma_enable_whatsapp_notification ) && ! empty( $pro_active ) ) {
								?>
								<div class="wps_rma_section wps_rma_notification" id="wps_rma_notification_div">
										<label><?php esc_html_e( 'Recieve Refund Related update over Message : ', 'woo-refund-and-exchange-lite' ); ?>	
										<input type="tel" name="wps_rma_customer_contact_refund" id="wps_rma_customer_contact_refund"></label>
										<div><?php esc_html_e( 'Phone number with country code. Ex : 1XXXXXXX987 ( "+" not allowed)', 'woo-refund-and-exchange-lite' ); ?></div>	
								</div>
								<?php
							}
							?>
							<div>
								<input type="submit" name="wps_rma_return_request_submit" value="<?php esc_html_e( 'Submit Request', 'woo-refund-and-exchange-lite' ); ?>" class="button btn">
								<div class="wps_rma_return_notification"><img src="<?php echo esc_html( esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL ) ); ?>public/images/loading.gif" width="40px"></div>
							</div>
						</form>
					</div>
						<?php
						$refund_rules_enable = get_option( 'wps_rma_refund_rules', 'no' );
						$refund_rules        = get_option( 'wps_rma_refund_rules_editor', '' );
						if ( isset( $refund_rules_enable ) && 'on' === $refund_rules_enable && ! empty( $refund_rules ) ) {
							?>
							<div class="wps-rma-col wps_rma_flex">        
								<div>
									<?php
										echo wp_kses_post( $refund_rules );
									?>
								</div>
							</div>
							<?php
						}
						?>
				</div>
				<div class="wps_rma_customer_detail">
					<?php
					if ( apply_filters( 'wps_rma_visible_customer_details', true ) ) {
						wc_get_template( 'order/order-details-customer.php', array( 'order' => $order_obj ) );
					}
					do_action( 'wps_rma_do_something_after_customer_details', $order_id );
					?>
				</div>
			</div>
			<?php
		} elseif ( $is_refundable ) {
			echo wp_kses_post( $is_refundable );
		} else {
			echo esc_html__( 'Refund Request Can\'t make on this order', 'woo-refund-and-exchange-lite' );
		}
	}
}
$wps_wrma_show_sidebar_on_form =
// Side show/hide on refund request form.
apply_filters( 'wps_rma_refund_form_sidebar', true );
if ( $wps_wrma_show_sidebar_on_form ) {
	// Woo Main Content.
	do_action( 'woocommerce_after_main_content' );
}

get_footer( 'shop' );

