<?php
/**
 * The public-facing functionality of the plugin for return request form.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/public/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_user_logged_in() ) {
	$guest_user = true;
} else {
	$guest_user = false;
}
if ( isset( $_GET['mwb_rma_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['mwb_rma_nonce'] ) ), 'mwb_rma_nonce' ) && isset( $_GET['order_id'] ) && ( $guest_user || current_user_can( 'mwb-rma-refund-request' ) ) ) {
	$order_id = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
} else {
	$order_id = '';
}
$allowed = 'yes';
if ( ! empty( $order_id ) ) {
	$condition = mwb_rma_show_buttons( 'refund', wc_get_order( $order_id ) );
}
$rr_subject = '';
$rr_reason  = '';
if ( isset( $condition ) && 'yes' === $condition && isset( $order_id ) && ! empty( $order_id ) ) {
	if ( ! is_numeric( $order_id ) ) {
		if ( get_current_user_id() > 0 ) {
			$myaccount_page     = get_option( 'woocommerce_myaccount_page_id' );
			$myaccount_page_url = esc_url( get_permalink( $myaccount_page ) );
			$allowed            = 'no';
			$reason             = esc_html__( 'Please choose an Order.', 'woo-refund-and-exchange-lite' ) . '<a href="' . $myaccount_page_url . '">' . esc_html__( 'Click Here', 'woo-refund-and-exchange-lite' ) . '</a>';
		}
	} else {
		$order_customer_id = get_post_meta( $order_id, '_customer_user', true );
		if ( get_current_user_id() > 0 ) {
			if ( get_current_user_id() != $order_customer_id ) {
				$myaccount_page     = get_option( 'woocommerce_myaccount_page_id' );
				$myaccount_page_url = get_permalink( $myaccount_page );
				$allowed            = 'no';
				$reason             = wp_kses_post( "This order #$order_id is not associated to your account. <a href='$myaccount_page_url'>Click Here</a>" );
			}
		}
	}
} else {
	$allowed = 'no';
}
$products = get_post_meta( $order_id, 'mwb_rma_return_product', true );
// Get pending return request.
if ( isset( $products ) && ! empty( $products ) ) {
	foreach ( $products as $date => $product ) {
		if ( 'pending' === $product['status'] ) {
			$rr_subject = $products[ $date ]['subject'];
			if ( isset( $products[ $date ]['reason'] ) ) {
				$rr_reason = $products[ $date ]['reason'];
			}
			$product_data = $product['products'];
			$allowed      = 'yes';
		}
		break;
	}
}

if ( isset( $reason ) && ! empty( $reason ) ) {
	$condition = $reason;
}
get_header( 'shop' );

// Before Main Content.
do_action( 'woocommerce_before_main_content' );
if ( 'yes' === $allowed ) {
	$mwb_refund_wrapper_class = get_option( 'mwb_wrma_refund_form_wrapper_class' );
	$mwb_return_css           = get_option( 'mwb_wrma_return_custom_css' );
	?>
	<style><?php echo wp_kses_post( $mwb_return_css ); ?></style>
	<div class="mwb_rma_refund_form_wrapper mwb-rma-form__wrapper <?php echo esc_html( $mwb_refund_wrapper_class ); ?>">
		<div id="mwb_rma_return_request_container" class="mwb-rma-form__header">
			<h1><?php esc_html_e( 'Order Refund Request Form', 'woo-refund-and-exchange-lite' ); ?></h1>
		</div>
		<ul id="mwb_rma_return_alert"></ul>
		<div class="mwb_rma_product_table_wrapper mwb-rma-product__table-wrapper">
			<table class="mwb-rma-product__table">
				<thead>
					<tr>
						<?php
						// Add extra field in the thead of the table.
						do_action( 'mwb_rma_add_extra_column_refund_form', $order_id );
						?>
						<th><?php esc_html_e( 'Product', 'woo-refund-and-exchange-lite' ); ?></th>
						<th><?php esc_html_e( 'Quantity', 'woo-refund-and-exchange-lite' ); ?></th>
						<th><?php esc_html_e( 'Total', 'woo-refund-and-exchange-lite' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$mwb_total_actual_price = 0;
					$mwb_rma_check_tax      = get_option( $order_id . 'check_tax', false );
					$order_obj              = wc_get_order( $order_id );
					$show_purchase_note     = $order_obj->has_status(
					// Purchases note on the order.
						apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) )
					);
					$get_order_currency = get_woocommerce_currency_symbol( $order_obj->get_currency() );
					foreach ( $order_obj->get_items() as $item_id => $item ) {
						$item_refund_already = get_post_meta( $order_id, 'mwb_rma_request_made', true );
						$item_allowed        =
						// Remove Item From Refund.
						apply_filters( 'mwb_rma_remove_item_from_refund', false );
						if ( $item_allowed ) {
							if ( ! empty( $item_refund_already ) && isset( $item_refund_already[ $item_id ] ) && 'completed' === $item_refund_already[ $item_id ] ) {
								continue;
							}
						}
						if ( $item['qty'] > 0 ) {
							if ( isset( $item['variation_id'] ) && $item['variation_id'] > 0 ) {
								$variation_id = $item['variation_id'];
								$product_id   = $item['product_id'];
							} else {
								$product_id = $item['product_id'];
							}
						}
						$product =
						// Get Product.
						apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );
						$thumbnail = wp_get_attachment_image( $product->get_image_id(), 'thumbnail' );

						$coupon_discount = get_option( 'mwb_rma_refund_deduct_coupon', 'no' );
						if ( 'on' === $coupon_discount ) {
							$tax_inc = $item->get_total() + $item->get_subtotal_tax();
							$tax_exc = $item->get_total() - $item->get_subtotal_tax();
						} else {
							$tax_inc = $item->get_subtotal() + $item->get_subtotal_tax();
							$tax_exc = $item->get_subtotal() - $item->get_subtotal_tax();
						}
						if ( empty( $mwb_rma_check_tax ) ) {
							if ( 'on' === $coupon_discount ) {
								$mwb_actual_price = $item->get_total();
							} else {
								$mwb_actual_price = $item->get_subtotal();
							}
						} elseif ( 'mwb_rma_inlcude_tax' === $mwb_rma_check_tax ) {
							$mwb_actual_price = $tax_inc;
						} elseif ( 'mwb_rma_exclude_tax' === $mwb_rma_check_tax ) {
							$mwb_actual_price = $tax_exc;
						}
						$mwb_total_price_of_product = $item['qty'] * $mwb_actual_price;
						// Revoke Product Refund.
						$revoke_ref = apply_filters( 'mwb_rma_revoke_product_refund', $product_id );
						if ( $revoke_ref ) {
							$mwb_total_actual_price += $mwb_total_price_of_product;
						}
						$purchase_note = get_post_meta( $product_id, '_purchase_note', true );
						?>
						<tr class="mwb_rma_return_column" data-productid="<?php echo esc_html( $product_id ); ?>" data-variationid="<?php echo esc_html( $item['variation_id'] ); ?>" data-itemid="<?php echo esc_html( $item_id ); ?>">
							<?php
							// To show extra column field value in the tbody.
							do_action( 'mwb_rma_add_extra_column_field_value', $item_id, $product_id );
							?>
							<td class="product-name">
							    <div class="mwb-rma-product__wrap">
									<input type="hidden" name="mwb_rma_product_amount" class="mwb_rma_product_amount" value="<?php echo esc_html( $mwb_actual_price ); ?>">
										<?php
											$is_visible        = $product && $product->is_visible();
											$product_permalink =
											// Order item Permalink.
											apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order_obj );
										?><div class="mwb-rma__prod-img"><?php
										if ( isset( $thumbnail ) && ! empty( $thumbnail ) ) {
											echo wp_kses_post( $thumbnail );
										} else {
											?>
											<img alt="Placeholder" width="150" height="150" class="attachment-thumbnail size-thumbnail wp-post-image" src="<?php echo esc_html( plugins_url() ); ?>/woocommerce/assets/images/placeholder.png">
											<?php
										}
										?>
										</div>
										<div class="mwb_rma_product_title mwb-rma__product-title">
										<?php
										// Woo Order Item Name.
										$o_n = apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item['name'] ) : $item['name'], $item, $is_visible );
										echo wp_kses_post( $o_n );
										// Quanity Html.
										$q_h = apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item['qty'] ) . '</strong>', $item );
										echo wp_kses_post( $q_h );

										// Order Item meta Start.
										do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order_obj, true );
										if ( WC()->version < '3.0.0' ) {
											$order_obj->display_item_meta( $item );
											$order_obj->display_item_downloads( $item );
										} else {
											wc_display_item_meta( $item );
											wc_display_item_downloads( $item );
										}
										// Order Item meta End.
										do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order_obj, true );
										?>
										<p>
											<b><?php esc_html_e( 'Price', 'woo-refund-and-exchange-lite' ); ?> :</b> 
											<?php
												echo wp_kses_post( mwb_wrma_format_price( $mwb_actual_price, $get_order_currency ) );
											if ( 'mwb_rma_inlcude_tax' === $mwb_rma_check_tax ) {
												?>
												<small class="tax_label"><?php esc_html_e( '(incl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
												<?php
											} elseif ( 'mwb_rma_exclude_tax' === $mwb_rma_check_tax ) {
												?>
													<small class="tax_label"><?php esc_html_e( '(excl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
													<?php
											}
											?>
										</p>
									</div>
								</div>
							</td>
							<td class="product-quantity">
							<?php
							echo wp_kses_post(
							// Refund form Quantity html.
							apply_filters( 'mwb_rma_change_quanity', sprintf( '<input type="number" disabled value="' . esc_html( $item['qty'] ) . '" class="mwb_rma_return_product_qty" name="mwb_rma_return_product_qty">' ), $item['qty'] ));
							?>
							</td>
							<td class="product-total">
								<?php
								echo wp_kses_post( mwb_wrma_format_price( $mwb_total_price_of_product, $get_order_currency ) );

								if ( 'mwb_rma_inlcude_tax' === $mwb_rma_check_tax ) {
									?>
									<small class="tax_label"><?php esc_html_e( '(incl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
									<?php
								} elseif ( 'mwb_rma_exclude_tax' === $mwb_rma_check_tax ) {
									?>
										<small class="tax_label"><?php esc_html_e( '(excl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
										<?php
								}
								?>
							<input type="hidden" id="quanty" value="<?php echo esc_html( $item['qty'] ); ?>"> 
							</td>
						</tr>
						<?php if ( $show_purchase_note && $purchase_note ) : ?>
						<tr class="product-purchase-note">
							<td colspan="3"><?php echo wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) ); ?></td>
						</tr>
							<?php
						endif;
						?>
						<?php
					}
					?>
					<tr>
						<th scope="row" colspan="3"><?php esc_html_e( 'Total Refund Amount', 'woo-refund-and-exchange-lite' ); ?></th>
						<td class="mwb_rma_total_amount_wrap"><span id="mwb_rma_total_refund_amount"><?php echo wp_kses_post( mwb_wrma_format_price( $mwb_total_actual_price, $get_order_currency ) ); ?></span>
						<input type="hidden" name="mwb_rma_total_refund_price" class="mwb_rma_total_refund_price" value="<?php echo esc_html( $mwb_total_actual_price ); ?>">
							<?php
							if ( 'mwb_rma_inlcude_tax' === $mwb_rma_check_tax ) {
								?>
								<small class="tax_label"><?php esc_html_e( '(incl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
								<?php
							} elseif ( 'mwb_rma_exclude_tax' === $mwb_rma_check_tax ) {
								?>
									<small class="tax_label"><?php esc_html_e( '(excl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
									<?php
							}
							?>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="mwb_rma_return_notification_checkbox" style="display:none"><img src="<?php echo esc_html( esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL ) ); ?>public/images/loading.gif" width="40px"></div>
		</div>
		<?php
		$predefined_return_reason = get_option( 'mwb_rma_refund_reasons', '' );
		$predefined_return_reason = explode( ',', $predefined_return_reason );
		?>
		<div class="mwb-rma-refund-request__row mwb-rma-row__pd">
			<div class="mwb-rma-col">
				<?php

				// Add someting after table on the refund request form.
				do_action( 'mwb_rma_after_table', $order_id );
				$re_bank = get_option( 'mwb_rma_refund_manually_de', false );
				if ( 'on' === $re_bank ) {
					?>
					<div id="bank_details">
					<textarea name="" class="mwb_rma_bank_details" rows=4 id="mwb_rma_bank_details" maxlength="1000" placeholder="<?php esc_html_e( 'Please Enter the bank details for manual refund', 'woo-refund-and-exchange-lite' ); ?>"></textarea>
					</div>
					<?php
				}
				?>
				<div class="mwb_rma_subject_dropdown mwb-rma-subject__dropdown">
					<div>
						<label>
							<b>
								<?php
									echo esc_html__( 'Subject of Refund Request :', 'woo-refund-and-exchange-lite' );
								?>
							</b>
						</label>
					</div>
					<select name="mwb_rma_return_request_subject" id="mwb_rma_return_request_subject">
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
				<div class="mwb_rma_other_subject">
					<input type="text" name="ced_rnx_return_request_subject" class="mwb_rma_return_request_subject_text" id="mwb_rma_return_request_subject_text" maxlength="5000" placeholder="<?php esc_html_e( 'Write your refund reason', 'woo-refund-and-exchange-lite' ); ?>">
				</div>
				<?php
				$predefined_return_desc = get_option( 'mwb_rma_refund_description', false );
				if ( isset( $predefined_return_desc ) && 'on' === $predefined_return_desc ) {
					?>
					<div class="mwb_rma_reason_description">
						<div>	
							<label>
								<b>
								<?php
								echo esc_html__( 'Description for Refund Reason :', 'woo-refund-and-exchange-lite' );
								?>
								</b>
							</label>
						</div>
						<?php
						$predefined_return_reason_placeholder = get_option( 'mwb_rma_refund_reason_placeholder', false );
						if ( empty( $predefined_return_reason_placeholder ) ) {
							$predefined_return_reason_placeholder = esc_html__( 'Write you description for refund', 'woo-refund-and-exchange-lite' );
						}
						?>
						<textarea name="mwb_rma_return_request_reason" cols="40" style="height: 222px;" class="mwb_rma_return_request_reason" maxlength='10000' placeholder="<?php echo esc_html( $predefined_return_reason_placeholder ); ?>"><?php echo ! empty( $rr_reason ) ? esc_html( $rr_reason ) : ''; ?></textarea>
					</div>
					<?php
				}
				?>
				<?php
				// Add something above attachment on the refund request form.
				do_action( 'mwb_rma_above_the_attachment' );
				?>
				<form action="" method="post" id="mwb_rma_return_request_form" data-orderid="<?php echo esc_html( $order_id ); ?>" enctype="multipart/form-data">
					<?php
					$return_attachment = get_option( 'mwb_rma_refund_attachment', false );
					$attach_limit      = get_option( 'mwb_rma_attachment_limit', '15' );
					if ( empty( $attach_limit ) ) {
						$attach_limit = 5;
					}
					if ( isset( $return_attachment ) && ! empty( $return_attachment ) ) {
						if ( 'on' === $return_attachment ) {
							?>
							<div class="mwb_rma_attach_files">
								<label><b><?php esc_html_e( 'Attach Files:', 'woo-refund-and-exchange-lite' ); ?></b></label>
								<p>
									<span id="mwb_rma_return_request_files">
									<input type="hidden" name="mwb_rma_return_request_order" value="<?php echo esc_html( $order_id ); ?>">
									<input type="hidden" name="action" value="mwb_rma_refund_upload_files">
									<input type="file" name="mwb_rma_return_request_files[]" class="mwb_rma_return_request_files">
									</span>
									<div><input type="button" value="<?php esc_html_e( 'Add More', 'woo-refund-and-exchange-lite' ); ?>" class="mwb_rma_return_request_morefiles" data-count="1" data-max="<?php echo esc_html( $attach_limit ); ?>"></div>
									<i><?php esc_html_e( 'Only .png, .jpeg extension file is approved.', 'woo-refund-and-exchange-lite' ); ?></i>
								</p>
							</div>
							<?php
						}
					}
					?>
					<div>
						<input type="submit" name="mwb_rma_return_request_submit" value="<?php esc_html_e( 'Submit Request', 'woo-refund-and-exchange-lite' ); ?>" class="button btn">
						<div class="mwb_rma_return_notification"><img src="<?php echo esc_html( esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL ) ); ?>public/images/loading.gif" width="40px"></div>
					</div>
				</form>
			</div>
			<div class="mwb-rma-col mwb_rma_flex">        
				<div class="mwb_rma_refund_rule">
				<?php
				$refund_rules_enable = get_option( 'mwb_rma_refund_rules', 'no' );
				$refund_rules        = get_option( 'mwb_rma_refund_rules_editor', '' );
				if ( isset( $refund_rules_enable ) && 'on' === $refund_rules_enable && ! empty( $refund_rules ) ) {
					echo wp_kses_post( $refund_rules );
				}
				?>
				</div>
			</div>
		</div>
		<div class="mwb_rma_customer_detail">
				<?php
				wc_get_template( 'order/order-details-customer.php', array( 'order' => $order_obj ) );
				?>
		</div>
	</div>
	<?php
} else {
	if ( isset( $condition ) ) {
		echo esc_html( $condition );
	} else {
		echo esc_html__( 'Refund Request Can\'t make on this order', 'mwb-woocommerce-rma' );
	}
}
// Woo Main Content.
do_action( 'woocommerce_after_main_content' );

$mwb_wrma_show_sidebar_on_form =
// Side show/hide on refund request form.
apply_filters( 'mwb_rma_refund_form_sidebar', true );
if ( $mwb_wrma_show_sidebar_on_form ) {
	// Show Sidebar.
	do_action( 'woocommerce_sidebar' );
}

get_footer( 'shop' );

