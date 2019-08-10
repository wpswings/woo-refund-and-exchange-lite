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
// print_r($line_items);

if(isset($return_datas) && !empty($return_datas))
{
	foreach($return_datas as $key=>$return_data)
	{
		$date = date_create($key);
		$date_format = get_option('date_format');
		$date = date_format($date,$date_format);
		?>
		<p><?php _e( 'Following product refund request made on', 'mwb-rma' ); ?> <b><?php echo $date?>.</b></p>
		<div id="mwb_rma_return_meta_wrapper">
			<table>
				<thead>
					<tr>
						<th><?php _e( 'Item', 'woocommerce-refund-and-exchange-lite' ); ?></th>
						<th><?php _e( 'Name', 'woocommerce-refund-and-exchange-lite' ); ?></th>
						<th><?php _e( 'Cost', 'woocommerce-refund-and-exchange-lite' ); ?></th>
						<th><?php _e( 'Qty', 'woocommerce-refund-and-exchange-lite' ); ?></th>
						<th><?php _e( 'Total', 'woocommerce-refund-and-exchange-lite' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php 
					$total = 0;
					$return_products = $return_data['products'];
					
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
											echo '<div class="wc-order-item-sku"><strong>' . __( 'SKU:', 'mwb-rma' ) . '</strong> ' . esc_html(  $refund_product_new[$returnkey]['sku'] ) . '</div>';
										}
										$var_id = $refund_product_new[$returnkey]['variation_id'];
										if ( isset($var_id) && ! empty( $var_id ) ) {
											echo '<div class="wc-order-item-variation"><strong>' . __( 'Variation ID:', 'mwb-rma' ) . '</strong> ';
											if ( $var_id != 0 ) {
												echo esc_html( $var_id );
											}
											echo '</div>';
										}
										?>
									</td>
									<td><?php echo wc_price($return_product['price']);?></td>
									<td><?php echo $return_product['qty'];?></td>
									<td><?php echo wc_price($return_product['price']*$return_product['qty']);?>
								</tr>
								<?php
								$total += $return_product['price']*$return_product['qty'];
							}
						}
					}
					?>
					<tr>
						<th colspan="4"><?php _e('Total Amount', 'mwb-rma');?></th>
						<th><?php echo wc_price($total);?></th>
					</tr>

				</tbody>
			</table>
		</div>
		<div class="mwb_rma_extra_reason">
		<?php
		if($return_data['status'] == 'pending')
		{
			?>
			<input type="hidden" value="<?php echo mwb_rma_currency_seprator($return_data['amount'])?>" id="ced_rnx_refund_amount">
			<input type="hidden" value="<?php echo $return_data['subject']?>" id="ced_rnx_refund_reason">
			<?php
		}
		?>
		<p>
			<strong><?php _e('Refund Amount', 'mwb-rma');?> :</strong> <?php echo wc_price($return_data['amount'])?> 
			<!-- <input type="hidden" name="ced_rnx_total_amount_for_refund" class="ced_rnx_total_amount_for_refund" value="<?php //echo ced_rnx_lite_currency_seprator($return_data['amount']); ?>"> -->
		</p>
		</div>
		<div class="ced_rnx_reason">	
			<p><strong><?php _e('Subject', 'mwb-rma');?> :</strong><i> <?php echo $return_data['subject']?></i></p></p>
			<p><b><?php _e('Reason', 'mwb-rma');?> :</b></p>
			<p><?php echo $return_data['reason']?></p>
			<?php 
			$req_attachments = get_post_meta($order_id, 'mwb_rma_return_attachment', true);
			
			if(isset($req_attachments) && !empty($req_attachments))
			{	
				?>
				<p><b><?php _e('Attachment', 'mwb-rma');?> :</b></p>
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
									<a href="<?php echo content_url()?>/attachment/<?php echo $attachment?>" target="_blank"><?php _e('Attachment','mwb-rma');?>-<?php echo $count;?></a>
									<?php 
									$count++;
								}
							}	
							break;
						}
					}		
				}	
			}
			if($return_data['status'] == 'pending')
			{	
				?>
				<p id="mwb_rma_return_package">
				<input type="button" value="<?php _e('Accept Request','mwb-rma');?>" class="button" id="mwb_rma_accept_return" data-orderid="<?php echo $order_id;?>" data-date="<?php echo $key;?>">
				<input type="button" value="<?php _e('Cancel Request','mwb-rma');?>" class="button" id="mwb_rma_cancel_return" data-orderid="<?php echo $order_id;?>" data-date="<?php echo $key;?>">
				</p>
				<?php 
			}
			?>
		</div>
		<?php
	}


}else{
	$mwb_rma_return_form_url='';	
	$mwb_rma_return_form_url = apply_filters( 'mwb_rma_return_form_url' ,$mwb_rma_return_form_url);
	
	?>
	<p><?php _e('No request from customer', 'mwb-rma');?></p>
	<?php
	if(isset($return_form_url) && !empty($return_form_url)){
		?>
		<a target="_blank" href="<?php echo $mwb_rma_return_form_url; ?>" class="button-primary button"><b><?php _e('Initiate Refund Request','mwb-rma'); ?></b></a>
		<?php
	}
}