<?php  
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$allowed = true;
//Product Return request form
$current_user_id = get_current_user_id();   //check user is logged in or not
$mwb_rma_refund_settings = get_option( 'mwb_rma_refund_settings' ,array());
if(!wp_verify_nonce( $_REQUEST['mwb_rma_return_form_nonce'], 'mwb_rma_return_form_nonce' ) ) {
	$allowed = false;
}

if($allowed) {
	$subject = "";
	$reason = "";

	if(isset($_POST['order_id'])){
		$order_id = sanitize_text_field($_POST['order_id']);
	} elseif (isset($_GET['order_id'])) {
		$order_id = sanitize_text_field($_GET['order_id']);
	} else {
		$order_id = 0;
		$order_id = apply_filters('mwb_rma_get_refund_order_id',$order_id);
	}
	
	//check order id is valid
	if($order_id == 0) {
		$allowed = false;
		$allowed = apply_filters( 'mwb_rma_refund_form_allowed_user',$allowed);
	}

	if(!is_numeric($order_id)) {
		
		if(get_current_user_id() > 0) {
			$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
			$myaccount_page_url = get_permalink( $myaccount_page );
		}
		$allowed = false;
		$reason = __('Please choose an Order.','woo-refund-and-exchange-lite').'<a href="'.$myaccount_page_url.'">'.__('Click Here','woo-refund-and-exchange-lite').'</a>';
		$reason = apply_filters('mwb_rma_return_choose_order', $reason);
		update_post_meta($order_id,"mwb_rma_refund_request_not_allowed_reason",$reason);
	} else {
		$order_customer_id = get_post_meta($order_id, '_customer_user', true);
		if($current_user_id > 0) { // check order associated to customer account or not for registered user	
			if(!(current_user_can('administrator'))) {		
				if($order_customer_id != $current_user_id) {
					$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
					$myaccount_page_url = get_permalink( $myaccount_page );
					$allowed = false;
					$reason = __("This order #$order_id is not associated to your account. <a href='$myaccount_page_url'>Click Here</a>",'woo-refund-and-exchange-lite' );
					$reason = apply_filters('mwb_rma_return_choose_order', $reason);
					update_post_meta($order_id,"mwb_rma_refund_request_not_allowed_reason",$reason);
				}	
			}		
		}
		$allowed = apply_filters('mwb_rma_add_refund_form_session_conditions',$allowed,$order_id);
	}

	if($allowed) {
		$order = wc_get_order($order_id);
		$mwb_rma_refund_enable = isset($mwb_rma_refund_settings['mwb_rma_return_enable'])? $mwb_rma_refund_settings['mwb_rma_return_enable']:'';

		if ($mwb_rma_refund_enable == 'on' && !empty($mwb_rma_refund_enable)) {
			$allowed = true;
		} else {
			$allowed = false;
			$reason = __('Refund request is disabled.','woo-refund-and-exchange-lite' );
			$reason = apply_filters('mwb_rma_refund_request_disable', $reason);
			update_post_meta($order_id,"mwb_rma_refund_request_not_allowed_reason",$reason);
		}
		$day_diff = Woo_Refund_And_Exchange_Lite_Common_Functions::mwb_rma_find_order_day_diff($order);
		$day_allowed = isset($mwb_rma_refund_settings['mwb_rma_return_days'])?$mwb_rma_refund_settings['mwb_rma_return_days']:'';

		if($day_allowed >= $day_diff && $day_allowed != 0) {
			$allowed = true;
		} else {
			$allowed = false;
			$reason =  __('Days exceed to initiate Refund.','woo-refund-and-exchange-lite' );
			$reason = apply_filters('mwb_rma_return_day_exceed', $reason);
			update_post_meta($order_id,"mwb_rma_refund_request_not_allowed_reason",$reason);
		}

		$statuses = isset($mwb_rma_refund_settings['mwb_rma_return_order_status'])?$mwb_rma_refund_settings['mwb_rma_return_order_status']:array();
		$order_status ="wc-".$order->get_status();
		if(!in_array($order_status, $statuses)) {
			$allowed = false;
			$reason =  __('Refund request is disabled.','woo-refund-and-exchange-lite' );
			$reason = apply_filters('mwb_rma_refund_request_disable', $reason);
			update_post_meta($order_id,"mwb_rma_refund_request_not_allowed_reason",$reason);
		}

		$products = get_post_meta($order_id, 'mwb_rma_return_request_product', true);
		if(isset($products) && !empty($products) && is_array($products)) {
			$allowed = false;
			$reason = __('Refund request for this order is already initiated once.','woo-refund-and-exchange-lite' );
			$reason = apply_filters('mwb_rma_return_already_initiated', $reason);
			update_post_meta($order_id,"mwb_rma_refund_request_not_allowed_reason",$reason);
		}
		$allowed = apply_filters('mwb_rma_check_extra_refund_form_conditions',$allowed,$order_id,$products);
	}
}
get_header( 'shop' );

/**
 * woocommerce_before_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */
do_action( 'woocommerce_before_main_content' );

if($allowed){
	$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
	$show_customer_details = is_user_logged_in() && $order->get_user_id() === get_current_user_id();

	?>
	<div class="woocommerce woocommerce-account mwb_rma_refund_form_wrapper">
		<div id="mwb_rma_return_request_form_wrapper">
			<div id="mwb_rma_return_request_container">
				<h1>
					<?php 
					_e('Order Refund Request Form','woo-refund-and-exchange-lite' );
					?>
				</h1>
				<?php do_action( 'mwb_rma_return_form_sub_heading' ); ?>
			</div>
			<ul class="woocommerce-error" id="mwb_rma_return_alert" ></ul>
			<?php do_action( 'mwb_rma_before_product_table_wrapper',$order_id);?>
			<div class="mwb_rma_product_table_wrapper" >
				<table class="shop_table order_details mwb_rma_product_table">
					<thead>
						<tr>
							<?php do_action( 'mwb_rma_refund_form_add_checkbox'); ?>
							<th class="product-name"><?php _e( 'Product', 'woo-refund-and-exchange-lite' ); ?></th>
							<th class="product-qty"><?php _e( 'Quantity', 'woo-refund-and-exchange-lite' ); ?></th>
							<th class="product-total"><?php _e( 'Total', 'woo-refund-and-exchange-lite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$mwb_rma_in_tax = isset($mwb_rma_refund_settings['mwb_rma_return_tax_enable'])? $mwb_rma_refund_settings['mwb_rma_return_tax_enable']: 'no';
						$in_tax = false;
						if($mwb_rma_in_tax == 'on')
						{
							$in_tax = true;	
						}
						$mwb_rma_total_actual_price = 0;
						foreach( $order->get_items() as $item_id => $item ) 
						{
							if($item['qty'] > 0){
								if(isset($item['variation_id']) && $item['variation_id'] > 0)
								{
									$variation_id = $item['variation_id'];
									$product_id = $item['product_id'];
									
								}
								else
								{
									$product_id = $item['product_id'];
									
								}
								$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
								$thumbnail = wp_get_attachment_image($product->get_image_id(),'thumbnail');
								$productdata = wc_get_product($product_id);
								$mwb_rma_product_qty = $item['qty'];
								$purchase_note = get_post_meta( $product_id, '_purchase_note', true );
								$set_qty = ($pro_active)?1:$item['qty'];
								do_action( 'mwb_rma_refund_form_after_purchase_note',$item,$product);
								?>
								<tr class="mwb_rma_return_column" data-productid="<?php echo $product_id?>" data-variationid="<?php echo $item['variation_id']?>" data-itemid="<?php echo $item_id?>">
									<?php 
									$mwb_rma_pro_price = $order->get_item_subtotal( $item, $in_tax );
									$mwb_rma_actual_price = $order->get_item_total( $item, $in_tax );
									$mwb_rma_total_price_of_product = $set_qty*$mwb_rma_actual_price;
									$mwb_rma_total_actual_price += $mwb_rma_total_price_of_product;
									?>
									<?php do_action( 'mwb_rma_refund_form_product_select_checkbox',$product_id,$order_id); ?>
									<td class="product-name">
										<?php
										$is_visible        = $product && $product->is_visible();
										$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

										if(isset($thumbnail) && !empty($thumbnail))
										{

											echo '<div class="mwb_rma_prod_img">'.wp_kses_post( $thumbnail ).'</div>';
										}
										else
										{
											?>
											<img alt="Placeholder" width="150" height="150" class="attachment-thumbnail size-thumbnail wp-post-image" src="<?php echo plugins_url();?>/woocommerce/assets/images/placeholder.png">
											<?php 
										}
										?>
										<div class="mwb_rma_product_title">
											<?php 
											echo apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item['name'] ) : $item['name'], $item, $is_visible );
											echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item['qty'] ) . '</strong>', $item );
											do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order );
											wc_display_item_meta( $item );
											wc_display_item_downloads( $item );
											do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );
											?>
											<p>
												<b><?php _e( 'Price', 'woo-refund-and-exchange-lite' ); ?> :</b> <?php 
												echo wc_price( $mwb_rma_pro_price ); 
												if($in_tax == true)
												{	
													?>
													<small class="tax_label"><?php _e('(incl. tax)','woo-refund-and-exchange-lite'); ?></small>
													<?php 
												}	
												?>	
											</p>
										</div>
									</td>
									<td class="product-quantity">
										<input type="number" max="<?php echo $item['qty'];?>" min="1" value="<?php echo $set_qty;?>" <?php echo ($pro_active)?"":"disabled" ?> class="mwb_rma_return_product_qty form-control" name="mwb_rma_return_product_qty">
										
									</td>
									<td class="product-total">
										<span class="mwb_rma_product_total_amu"><?php echo wc_price($mwb_rma_total_price_of_product);?> </span><?php
										if($in_tax == true)
										{	
											?>
											<small class="tax_label"><?php _e('(incl. tax)','woo-refund-and-exchange-lite'); ?></small>
											<?php 
										}	
										?>
										<input type="hidden" id="quanty" value="<?php echo $item['qty']; ?>"> 

									</td>
								</tr>
								<?php if ( $show_purchase_note && $purchase_note ) { ?>

									<tr class="product-purchase-note">
										<td colspan="3"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); ?></td>
									</tr>
								<?php }
							}
						}
						?>
						<tr>
							<th scope="row" colspan="2"><?php _e('Total Refund Amount', 'woo-refund-and-exchange-lite') ?></th>
							<td class="mwb_rma_total_amount_wrap">
								<span id="mwb_rma_total_refund_amount"><?php echo wc_price($mwb_rma_total_actual_price);?></span>
								<?php 
								if($in_tax == true)
								{	
									?>
									<small class="tax_label"><?php _e( '(incl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
									<?php 
								}	
								?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<hr/>
			<?php do_action( 'mwb_rma_return_form_after_product_table_wrapper',$order_id); ?>
			<?php 
			$refund_rules_enable=isset($mwb_rma_refund_settings['mwb_rma_refund_rules_editor_enable'])? $mwb_rma_refund_settings['mwb_rma_refund_rules_editor_enable']: 'no';
			$refund_rules=isset($mwb_rma_refund_settings['mwb_rma_return_request_rules_editor'])? $mwb_rma_refund_settings['mwb_rma_return_request_rules_editor']: '';
			?>
			<div id="mwb_rma_return_form_detail_wrapper"  <?php if(isset($refund_rules_enable) && $refund_rules_enable =='on'){ if(isset($refund_rules) && !empty($refund_rules)) {?> class="mwb_rma_return_form_detail" <?php }}?> >
				<p class="form-row form-row form-row-wide">
					<label>
						<b>
							<?php 
							$subject_return_request = __('Subject of Refund Request :', 'woo-refund-and-exchange-lite' );
							echo apply_filters('mwb_rma_return_request_subject', $subject_return_request);
							?>
						</b>
					</label>

					<?php 
					$mwb_rma_mail_basic_settings = get_option('mwb_rma_mail_basic_settings',array());
					$predefined_return_reason = isset($mwb_rma_mail_basic_settings['mwb_rma_return_predefined_reason'])? $mwb_rma_mail_basic_settings['mwb_rma_return_predefined_reason']:array();

					if(isset($predefined_return_reason) && !empty($predefined_return_reason) && is_array($predefined_return_reason) )
					{	
						?>
						<div class="mwb_rma_subject_dropdown">
							<select name="mwb_rma_return_request_subject" id="mwb_rma_return_request_subject">
								<?php 
								foreach($predefined_return_reason as $predefine_reason)
								{
									if(!empty($predefine_reason)){
										?>
										<option value="<?php echo $predefine_reason?>"><?php echo $predefine_reason?></option>
										<?php 
									}
								}
								?>
								<option value=""><?php _e( 'Other', 'woo-refund-and-exchange-lite' )?></option>
							</select>
						</div>
						<?php 
					}
					?>
				</p>
				<p class="form-row form-row form-row-wide">
					<input type="text" name="mwb_rma_return_request_subject" class="input-text mwb_rma_return_request_subject" id="mwb_rma_return_request_subject_text" placeholder="<?php _e('Write your reason subject','woo-refund-and-exchange-lite');?>">
				</p>
				<?php 
				$predefined_return_desc = isset($mwb_rma_refund_settings['mwb_rma_return_request_description'])? $mwb_rma_refund_settings['mwb_rma_return_request_description']: 'no';
				if(!empty($predefined_return_desc) && $predefined_return_desc == 'on')
				{
					?>
					<p class="form-row form-row form-row-wide">
						<label>
							<b>
								<?php 
								$reason_return_request = __('Reason of Refund Request', 'woo-refund-and-exchange-lite' );
								echo apply_filters('mwb_rma_return_request_reason', $reason_return_request);
								?>
							</b>
						</label>
						<br/>
						<?php $placeholder = get_option( 'mwb_rma_return_placeholder_text' , 'Reason for Return Request' ); 
						if ($placeholder == '') {
							$placeholder =__('Reason for the Refund Request','woo-refund-and-exchange-lite');
						}
						?>
						<textarea name="mwb_rma_return_request_reason" cols="40" style="height: 222px;" class="mwb_rma_return_request_reason form-control" placeholder="<?php echo $placeholder; ?>"><?php echo $reason;?></textarea>
					</p>
					<?php 
				}
				else
				{
					?>
					<input type="hidden" name="mwb_rma_return_request_reason" class="mwb_rma_return_request_reason form-control" value="<?php _e('No Reason Enter', 'woo-refund-and-exchange-lite' )?>">
					<?php 				
				}	

				?>
				<form action="" method="post" id="mwb_rma_return_request_form" data-orderid="<?php echo $order_id;?>" enctype="multipart/form-data">
					<?php 
					$return_attachment = isset($mwb_rma_refund_settings['mwb_rma_return_attach_enable'])? $mwb_rma_refund_settings['mwb_rma_return_attach_enable']: 'no';
					if(isset($return_attachment) && !empty($return_attachment))
					{	
						if($return_attachment == 'on')
						{
							?>
							<label><b><?php _e('Attach Files', 'woo-refund-and-exchange-lite');?></b></label>
							<p class="form-row form-row form-row-wide">
								<span id="mwb_rma_return_request_files">
									<input type="hidden" name="mwb_rma_return_request_order" value="<?php echo $order_id;?>">
									<input type="hidden" name="action" value="<?php _e('mwb_rma_refund_upload_files', 'woo-refund-and-exchange-lite');?>">
									<input type="file" name="mwb_rma_return_request_files[]" class="input-text mwb_rma_return_request_files"></span>
									<input type="button" value="<?php _e('Add More', 'woo-refund-and-exchange-lite');?>" class="btn button mwb_rma_return_request_morefiles">
									<i><?php _e('Only .png, .jpeg extension file is approved.', 'woo-refund-and-exchange-lite' )?></i>
								</p>
								<?php 
							}
						}?>
						<p class="form-row form-row form-row-wide">
							<input type="submit" name="mwb_rma_return_request_submit" value="<?php _e('Submit Request', 'woo-refund-and-exchange-lite');?>" class="button btn">
							<div class="mwb_rma_return_notification"><img src="<?php echo MWB_RMA_URL?>public/images/loading.gif" width="40px"></div>
						</p>
					</form>
				</div>
				<div <?php if(isset($refund_rules_enable) && $refund_rules_enable =='on'){ ?>class="mwb_rma_refund_rules_wrapper" <?php }?> >
					<?php 	

					if(isset($refund_rules_enable) && $refund_rules_enable =='on'){
						if(isset($refund_rules) && !empty($refund_rules)){
							echo $refund_rules;
						}
					}
					?>
				</div>
				<div class="mwb_rma_customer_detail">
					<?php 
					wc_get_template( 'order/order-details-customer.php', array( 'order' =>  $order ) ); 
					?>
				</div>
			</div>
		</div>
		<?php
	}else{
		$get_reason = get_post_meta($order_id, 'mwb_rma_refund_request_not_allowed_reason',true);
		if(isset($get_reason) && !empty($get_reason)){
			$reason=$get_reason;
		}else{

			$return_request_not_send = __('Refund Request can\'t be send. ', 'woo-refund-and-exchange-lite' );
			$reason = apply_filters('mwb_rma_return_request_not_send', $return_request_not_send);
		}
		echo $reason;
	}

/**
 * woocommerce_after_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * woocommerce_sidebar hook.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
