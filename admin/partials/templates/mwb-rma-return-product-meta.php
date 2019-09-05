<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_int( $thepostid ) ) {
	$thepostid = $post->ID;
}
if ( ! is_object( $theorder ) ) {
	$theorder = wc_get_order( $thepostid );
}

$order = $theorder;
if( WC()->version < "3.0.0" )
{
	$order_id=$order->id;
}
else
{
	$order_id=$order->get_id();
}

$return_datas = get_post_meta($order_id, 'mwb_rma_return_request_product', true);
$line_items  = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );

if(isset($return_datas) && !empty($return_datas))
{
	foreach($return_datas as $key=>$return_data)
	{
		$date = date_create($key);
		$date_format = get_option('date_format');
		$date = date_format($date,$date_format);
		?>
		<p><?php _e( 'Following product refund request made on', 'woo-refund-and-exchange-lite' ); ?> <b><?php echo $date?>.</b></p>
		<div id="mwb_rma_return_meta_wrapper">
			<table>
				<thead>
					<tr>
						<th><?php _e( 'Item', 'woo-refund-and-exchange-lite' ); ?></th>
						<th><?php _e( 'Name', 'woo-refund-and-exchange-lite' ); ?></th>
						<th><?php _e( 'Cost', 'woo-refund-and-exchange-lite' ); ?></th>
						<th><?php _e( 'Qty', 'woo-refund-and-exchange-lite' ); ?></th>
						<th><?php _e( 'Total', 'woo-refund-and-exchange-lite' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php 
					$total = 0;
					$reduced_total=0;
					$return_products = $return_data['products'];
					$pro_id=[];
					$price_reduce_flag=false;
					$total_refund_amu=0;
					foreach ( $line_items as $item_id => $item ) 
					{
						foreach($return_products as $returnkey => $return_product)
						{
							if($item_id == $return_product['item_id'])
							{
								$refund_product_detail=$order->get_meta_data();
								foreach ($refund_product_detail as $rpd_value) {
									$refund_product_data=$rpd_value->get_data();
									if($refund_product_data['key'] == 'mwb_rma_return_request_product'){
										$refund_product_values=$refund_product_data['value'];
										foreach ($refund_product_values as $rpv_value) {
											$refund_product_values1=$rpv_value['products'];
											foreach ($refund_product_values1 as $rpv1_value) {
												
												$refund_product_id=$rpv1_value['product_id'];
												$refund_product_var_id=$rpv1_value['variation_id'];
												if($rpv1_value['variation_id'] > 0){
													if(!in_array($rpv1_value['variation_id'], $pro_id)){

														$pro_id[]=$rpv1_value['variation_id'];
													}
												}else{
													if(!in_array($rpv1_value['product_id'], $pro_id)){

														$pro_id[]=$rpv1_value['product_id'];
													}
												
												}
												$get_return_product = wc_get_product($refund_product_id);
												$new_refund_image = wp_get_attachment_image_src( get_post_thumbnail_id( $refund_product_id ), 'single-post-thumbnail' );
												$refund_product_new[] = array(
													'name'  => $get_return_product->get_name(),
													'sku'   => $get_return_product->get_sku(),
													'image' => $new_refund_image[0],
													'variation_id' => $refund_product_var_id,
												);
											}
										}
									}
								}
								$mwb_rma_refund_settings = get_option( 'mwb_rma_refund_settings' ,array());
								$mwb_rma_in_tax = isset($mwb_rma_refund_settings['mwb_rma_return_tax_enable'])? $mwb_rma_refund_settings['mwb_rma_return_tax_enable']: 'no';
								$in_tax = false;
								if($mwb_rma_in_tax == 'on')
								{
									$in_tax = true;	
								}
								$prod_price = $order->get_item_total( $item ,$in_tax );
								$total_pro_price = $prod_price*$return_product['qty'];
								if(class_exists('Mwb_Rma_Pro_Functions')){
									$reduced_price_data = Mwb_Rma_Pro_Functions::mwb_rma_refund_policy_price_deduction($total_pro_price,$order_id);
									$total_pro_price_reduced = $reduced_price_data['product_total'];
									$price_reduce_flag =  $reduced_price_data['price_flag'];
								}
								?>
								<tr>
									<td class="thumb">
									<?php
										if(isset($refund_product_new[$returnkey]['image']) && !empty($refund_product_new[$returnkey]['image'])){
											echo '<img src ="'.$refund_product_new[$returnkey]['image'].'">';
										}
									?>
									</td>
									<td>
										<?php
										if(isset($refund_product_new[$returnkey]['name']) && !empty($refund_product_new[$returnkey]['name'])){
										echo esc_html( $refund_product_new[$returnkey]['name'] );
										}
										if (isset($refund_product_new[$returnkey]['sku']) && !empty($refund_product_new[$returnkey] )) {
											echo '<div class="wc-order-item-sku"><strong>' . __( 'SKU:', 'woo-refund-and-exchange-lite' ) . '</strong> ' . esc_html(  $refund_product_new[$returnkey]['sku'] ) . '</div>';
										}
										$var_id = $refund_product_new[$returnkey]['variation_id'];
										if ( isset($var_id) && ! empty( $var_id ) ) {
											echo '<div class="wc-order-item-variation"><strong>' . __( 'Variation ID:', 'woo-refund-and-exchange-lite' ) . '</strong> ';
											if ( $var_id != 0 ) {
												echo esc_html( $var_id );
											}
											echo '</div>';
										}
										$item_meta      = new WC_Order_Item_Product( $item );
										wc_display_item_meta($item_meta);
										?>
									</td>
									<td><?php echo wc_price($prod_price);?></td>
									<td><?php echo $return_product['qty'];?></td>
									<td><?php if($price_reduce_flag){?><strike><?php }?><?php echo wc_price($total_pro_price); ?><?php if($price_reduce_flag){?></strike><?php } ?><?php if($price_reduce_flag){ echo wc_price($total_pro_price_reduced); }?></td>
								</tr>
								<?php
								$total += $total_pro_price;
								$reduced_total += $total_pro_price_reduced;
							}
						}
					}
					$total_refund_amu = ($price_reduce_flag)?$reduced_total:$total;
					if(class_exists('Mwb_Rma_Pro_Functions')){
						$total_refund_amu=Mwb_Rma_Pro_Functions::mwb_rma_reduce_global_ship_fee($order_id,$total_refund_amu);
					}
					?>
					<tr>
						<th colspan="4"><?php _e('Total Amount', 'woo-refund-and-exchange-lite');?></th>
						<th><?php if($price_reduce_flag){?><strike><?php }?><?php echo wc_price($total);?><?php if($price_reduce_flag){?></strike><?php } ?><?php if($price_reduce_flag){ echo wc_price($reduced_total); }?></th>
					</tr>

				</tbody>
			</table>
		</div>
		<?php 
		if(class_exists('Mwb_Rma_Pro_Functions')){
			$ref='refund';
			Mwb_Rma_Pro_Functions::mwb_rma_add_global_shipping($order_id,$ref,$return_data['status']);
		}
		?>
		<div class="mwb_rma_extra_reason">
			<p>
				<strong><?php _e('Refund Amount', 'woo-refund-and-exchange-lite');?> :</strong> <?php echo wc_price($total_refund_amu); ?>
			</p>
		</div>
		<div class="mwb_rma_reason">	
			<p><strong><?php _e('Subject', 'woo-refund-and-exchange-lite');?> :</strong><i> <?php echo $return_data['subject']?></i></p></p>
			<p><b><?php _e('Reason', 'woo-refund-and-exchange-lite');?> :</b></p>
			<p><?php echo $return_data['reason']?></p>
			<?php 
			$req_attachments = get_post_meta($order_id, 'mwb_rma_return_attachment', true);
			
			if(isset($req_attachments) && !empty($req_attachments))
			{	
				?>
				<p><b><?php _e('Attachment', 'woo-refund-and-exchange-lite');?> :</b></p>
				<?php
				if(is_array($req_attachments))
				{
					foreach($req_attachments as $da=>$attachments)
					{
						if($da == $key)
						{
							$count = 1;
							foreach($attachments['files'] as $attachment)
							{
								if($attachment != $order_id.'-')
								{
									?>
									<a href="<?php echo content_url()?>/attachment/<?php echo $attachment?>" target="_blank"><?php _e('Attachment','woo-refund-and-exchange-lite');?>-<?php echo $count;?></a>
									<?php 
									$count++;
								}else{
									?>
										<p><?php _e('No attachment from customer', 'woo-refund-and-exchange-lite');?></p>
									<?php
								}
							}	
							break;
						}
					}		
				}	
			}
			if($return_data['status'] == 'pending')
			{
				do_action( 'mwb_rma_return_ship_attach_upload_html',$order_id);	
				?>
				<p id="mwb_rma_return_package">
				<input type="button" value="<?php _e('Accept Request','woo-refund-and-exchange-lite');?>" class="button" id="mwb_rma_accept_return" data-orderid="<?php echo $order_id;?>" data-date="<?php echo $key;?>">
				<input type="button" value="<?php _e('Cancel Request','woo-refund-and-exchange-lite');?>" class="button" id="mwb_rma_cancel_return" data-orderid="<?php echo $order_id;?>" data-date="<?php echo $key;?>">
				</p>
				<?php 
			}
			?>
		</div>
		<div class="mwb_rma_return_loader">
			<img src="<?php echo MWB_RMA_URL?>admin/images/loader.gif" ">
		</div>
		<?php
		if($return_data['status'] == 'complete')
		{?>
			<input type="hidden" name="mwb_rma_total_amount_for_refund" class="mwb_rma_total_amount_for_refund" value="<?php echo mwb_rma_currency_seprator($total_refund_amu); ?>">
			<input type="hidden" value="<?php echo $return_data['subject']?>" id="mwb_rma_refund_reason">

			<?php
			$approve_date = date_create($return_data['approve_date']);
			$date_format = get_option('date_format');
			$approve_date = date_format($approve_date,$date_format);
			$mwb_rma_refund_amount = get_post_meta($order_id,'mwb_rma_refund_amount',true);

			_e( 'Following product refund request is approved on', 'woo-refund-and-exchange-lite' ); ?> <b><?php echo $approve_date?>.</b><?php

			if($mwb_rma_refund_amount !='yes'){?>
				<input type="button" name="mwb_rma_left_amount" class="button button-primary" data-orderid="<?php echo $order_id; ?>" id="mwb_rma_left_amount" Value="Refund Amount" > <?php
			}

			// to show manage stock button when refund request is approved
			$mwb_rma_refund_settings = get_option( 'mwb_rma_refund_settings' ,array());
			$manage_stock = isset($mwb_rma_refund_settings['mwb_rma_return_request_manage_stock'])? $mwb_rma_refund_settings['mwb_rma_return_request_manage_stock']:'';
			foreach ($pro_id as $pi_key => $pi_value) {
				$mwb_rma_manage_stock_for_return = get_post_meta($order_id,$pi_value.'mwb_rma_manage_stock_for_return',true);
				if($mwb_rma_manage_stock_for_return == '')
				{
					$mwb_rma_manage_stock_for_return = 'no';
					break;
				}
			}
			if($manage_stock == "on" && $mwb_rma_manage_stock_for_return == 'no')
			{
				?> <p id="mwb_rma_stock_button_wrapper"><?php _e( 'When Product Back in stock then for stock management click on ', 'woo-refund-and-exchange-lite' ); ?> <input type="button" name="mwb_rma_stock_back" class="button button-primary" id="mwb_rma_stock_back" data-type="mwb_rma_return" data-orderid="<?php echo $order_id; ?>" Value="Manage Stock" ></p> <?php
			}
		}
		?>
		<p>
		<?php
			// to show when refund request is cancelled
			if($return_data['status'] == 'cancel')
			{
				$approve_date=date_create($return_data['cancel_date']);
				$approve_date=date_format($approve_date,"F d, Y");
					
				_e( 'Following product refund request is cancelled on', 'woo-refund-and-exchange-lite' ); ?> <b><?php echo $approve_date?>.</b>
				<?php
			}
		?>
		</p>
		<?php
	}

}else{

	$mwb_rma_return_form_url='';	
	$mwb_rma_return_form_url = apply_filters( 'mwb_rma_return_form_url' ,$mwb_rma_return_form_url);
	
	?>
	<p><?php _e('No request from customer', 'woo-refund-and-exchange-lite');?></p>
	<?php
	if(isset($return_form_url) && !empty($return_form_url)){
		?>
		<a target="_blank" href="<?php echo $mwb_rma_return_form_url; ?>" class="button-primary button"><b><?php _e('Initiate Refund Request','woo-refund-and-exchange-lite'); ?></b></a>
		<?php
	}
}