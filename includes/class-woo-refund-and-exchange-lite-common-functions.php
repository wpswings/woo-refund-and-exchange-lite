<?php

/**
 * Fired during plugin activation
 *
 * @link       www.makewebbetter.com
 * @since      1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/includes
 */

/**
 * defines all global functions used in the plugin
 *
 * @since      1.0.0
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */

class Woo_Refund_And_Exchange_Lite_Common_Functions {

	/**
	 * This function is used for the simple mail template
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 * @param unknown $mail_header,$subject,$message,$mail_footer
	 * @return html_content
	 */
	public static function mwb_rma_mail_template_html($mail_header,$subject,$message,$mail_footer) {
		$html_content = '<html>
                            <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                                <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                            </head>
                            <body>
                                <table cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                    	<td style="text-align: center; margin-top: 30px; margin-bottom: 10px; color: #99B1D8; font-size: 12px;">'.$mail_header.'
                                       	</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table align="center" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-family:Open Sans; max-width: 600px; width: 100%;">
                                                <tr>
                                                    <td style="padding: 36px 48px; width: 100%; background-color:#557DA1;color: #fff; font-size: 30px; font-weight: 300; font-family:helvetica;">'.$subject.'</td>
                                                </tr>
                                                <tr>
                                                    <td style="width:100%; padding: 36px 48px 10px; background-color:#fdfdfd; font-size: 14px; color: #737373;">'.$message.'</td>
                                                </tr>
                                        	</table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: center; margin-top: 30px; color: #99B1D8; font-size: 12px;">
                                                        '.$mail_footer.'
                                        </td>
                                    </tr>
                               	</table>
                           	</body>
        				</html>';
    	return $html_content;

	}

	/**
	 * This function is used for the common parts of mail
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 * @param unknown $order_id,$message_detail
	 * @return message
	 */
 	public static function create_mail_html($order_id,$message_detail){
 		
	$mwb_rma_mail_basic_settings = get_option('mwb_rma_mail_basic_settings',array());
	$mail_header = stripslashes(isset($mwb_rma_mail_basic_settings['mwb_rma_mail_header'])? $mwb_rma_mail_basic_settings['mwb_rma_mail_header']:'');
	$mail_footer = stripslashes(isset($mwb_rma_mail_basic_settings['mwb_rma_mail_footer'])? $mwb_rma_mail_basic_settings['mwb_rma_mail_footer']:'');

	$order = new WC_Order($order_id);
	$message = '<html>
				<body>
					<style>
						body {
							box-shadow: 2px 2px 10px #ccc;
							color: #767676;
							font-family: Arial,sans-serif;
							margin: 80px auto;
							max-width: 700px;
							padding-bottom: 30px;
							width: 100%;
						}

						h2 {
							font-size: 30px;
							margin-top: 0;
							color: #fff;
							padding: 40px;
							background-color: #557da1;
						}

						h4 {
							color: #557da1;
							font-size: 20px;
							margin-bottom: 10px;
						}

						.content {
							padding: 0 40px;
						}

						.Customer-detail ul li p {
							margin: 0;
						}

						.details .Shipping-detail {
							width: 40%;
							float: right;
						}

						.details .Billing-detail {
							width: 60%;
							float: left;
						}

						.details .Shipping-detail ul li,.details .Billing-detail ul li {
							list-style-type: none;
							margin: 0;
						}

						.details .Billing-detail ul,.details .Shipping-detail ul {
							margin: 0;
							padding: 0;
						}

						.clear {
							clear: both;
						}

						table,td,th {
							border: 2px solid #ccc;
							padding: 15px;
							text-align: left;
						}

						table {
							border-collapse: collapse;
							width: 100%;
						}

						.info {
							display: inline-block;
						}

						.bold {
							font-weight: bold;
						}

						.footer {
							margin-top: 30px;
							text-align: center;
							color: #99B1D8;
							font-size: 12px;
						}
						dl.variation dd {
							font-size: 12px;
							margin: 0;
						}
					</style>

					<div style="text-align: center; padding: 10px;" class="header">
						'.$mail_header.'
					</div>';

		$message .= $message_detail;

		$message .='<div class="Customer-detail">
							<h4>'.__('Customer details', 'woo-refund-and-exchange-lite').'</h4>
							<ul>
								<li><p class="info">
									<span class="bold">'.__('Email', 'woo-refund-and-exchange-lite').': </span>'.get_post_meta($order_id, '_billing_email', true).'
								</p></li>
								<li><p class="info">
									<span class="bold">'.__('Tel', 'woo-refund-and-exchange-lite').': </span>'.get_post_meta($order_id, '_billing_phone', true).'
								</p></li>
							</ul>
						</div>
						<div class="details">
							<div class="Shipping-detail">
								<h4>'.__('Shipping Address', 'woo-refund-and-exchange-lite').'</h4>
								'.$order->get_formatted_shipping_address().'
							</div>
							<div class="Billing-detail">
								<h4>'.__('Billing Address', 'woo-refund-and-exchange-lite').'</h4>
								'.$order->get_formatted_billing_address().'
							</div>
							<div class="clear"></div>
						</div>
						
					</div>
					<div class="footer" style="text-align:center;padding: 10px;">
						'.$mail_footer.'
					</div>

				</body>
				</html>';

		return $message;
				
	}

	public static function mwb_rma_find_order_day_diff($order) {
		$order_date = date_i18n( 'd-m-Y', strtotime( $order->get_date_created() ) );
		$today_date = date_i18n( 'd-m-Y' );
		$order_date = strtotime($order_date);
		$today_date = strtotime($today_date);
		$days = $today_date - $order_date;
		$day_diff = floor($days/(60*60*24));
		return $day_diff;
	}

	public static function mwb_rma_pro_active() {
		global $pro_active;
		$pro_active = false;
		$pro_active = apply_filters( 'mwb_rma_check_pro_active' , $pro_active );
		return $pro_active;
	}

	public static function mwb_rma_refund_approved($orderid,$date,$auto_accept='') {
		$products = get_post_meta($orderid, 'mwb_rma_return_request_product', true);
		if(isset($products) && !empty($products))
		{
			foreach($products as $date=>$product)
			{
				if($product['status'] == 'pending')
				{
					$product_datas = $product['products'];
					$products[$date]['status'] = 'complete';
					$approvdate = date("d-m-Y");
					$products[$date]['approve_date'] = $approvdate;
					break;
				}
			}
		}

				 //Update the status
		update_post_meta($orderid, 'mwb_rma_return_request_product', $products);

		$request_files = get_post_meta($orderid, 'mwb_rma_return_attachment', true);

		if(isset($request_files) && !empty($request_files))
		{
			foreach($request_files as $date=>$request_file)
			{
				if($request_file['status'] == 'pending')
				{
					$request_files[$date]['status'] = 'complete';
					break;
				}
			}
		}

		//Update the status
		update_post_meta($orderid, 'mwb_rma_return_attachment', $request_files);


		$mwb_rma_mail_basic_settings = get_option('mwb_rma_mail_basic_settings',array());
		$mwb_rma_mail_refund_settings = get_option('mwb_rma_mail_refund_settings',array());

		$order = new WC_Order($orderid);
		$fmail =  isset($mwb_rma_mail_basic_settings['mwb_rma_mail_from_email'])? $mwb_rma_mail_basic_settings['mwb_rma_mail_from_email']:'';
		$fname =  isset($mwb_rma_mail_basic_settings['mwb_rma_mail_from_name'])? $mwb_rma_mail_basic_settings['mwb_rma_mail_from_name']:'';


		$approve = stripslashes(isset($mwb_rma_mail_refund_settings['mwb_rma_mail_return_approve_message'])? $mwb_rma_mail_refund_settings['mwb_rma_mail_return_approve_message']:'');
		if(class_exists('Mwb_Rma_Pro_Functions')){
			
			$template_data = Mwb_Rma_Pro_Functions::return_wallet_enable_refund_msg($orderid,$approve,$auto_accept);
			$approve = $template_data['approve'];
			$refund_approve_template = $template_data['refund_approve_template'];

		}
		$approve = Woo_Refund_And_Exchange_Lite_Common_Functions::mwb_rma_mail_shortcode($approve,$orderid);
		$mwb_rma_shortcode = '';
		$mwb_rma_shortcode = $approve;
		if(class_exists('Mwb_Rma_Pro_Functions')){
			
			$mwb_rma_shortcode = Mwb_Rma_Pro_Functions::mwb_rma_add_shortcodeto_mail($mwb_rma_shortcode,$orderid);
		}
		$approve = $mwb_rma_shortcode;

		$message_details = '';
		$message_details = '<div class="header">
		<h2>'.__('Your Refund Request is Approved', 'woo-refund-and-exchange-lite').'</h2>
		</div>
		<div class="content">
		<div class="reason">
		<p>'.$approve.'</p>
		</div>
		<div class="Order">
		<h4>Order #'.$orderid.'</h4>
		<table>
		<tbody>
		<tr>
		<th>'.__('Product', 'woo-refund-and-exchange-lite').'</th>
		<th>'.__('Quantity', 'woo-refund-and-exchange-lite').'</th>
		<th>'.__('Price', 'woo-refund-and-exchange-lite').'</th>
		</tr>';
		$order = wc_get_order($orderid);
		$requested_products = $products[$date]['products'];

		if(isset($requested_products) && !empty($requested_products))
		{
			$total = 0;
			foreach( $order->get_items() as $item_id => $item )
			{
				foreach($requested_products as $requested_product)
				{
					if($item_id == $requested_product['item_id'])
					{

						if(isset($requested_product['variation_id']) && $requested_product['variation_id'] > 0)
						{
							$prod = wc_get_product($requested_product['variation_id']);
						}
						else
						{
							$prod = wc_get_product($requested_product['product_id']);
						}
						$mwb_rma_refund_settings = get_option( 'mwb_rma_refund_settings' ,array());
						$mwb_rma_in_tax = isset($mwb_rma_refund_settings['mwb_rma_return_tax_enable'])? $mwb_rma_refund_settings['mwb_rma_return_tax_enable']: 'no';
						$in_tax = false;
						if($mwb_rma_in_tax == 'on')
						{
							$in_tax = true;	
						}
						$prod_price = $order->get_item_total( $item ,$in_tax );
						
						$subtotal = $prod_price*$requested_product['qty'];
						if(class_exists('Mwb_Rma_Pro_Functions')){
							$pro_subtotal = Mwb_Rma_Pro_Functions::mwb_rma_refund_policy_price_deduction($subtotal,$orderid);
							$subtotal=$pro_subtotal['product_total'];
						}
						$total += $subtotal;
						if(class_exists('Mwb_Rma_Pro_Functions')){
							$total=Mwb_Rma_Pro_Functions::mwb_rma_reduce_global_ship_fee($orderid,$total);
						}
						$item_meta      = new WC_Order_Item_Product( $item );
						$item_meta_html = wc_display_item_meta($item_meta,array('echo'=> false));
						$message_details.= '<tr>
						<td>'.$item['name'].'<br>';
						$message_details.= '<small>'.$item_meta_html.'</small>
						<td>'.$requested_product['qty'].'</td>
						<td>'.wc_price($subtotal).'</td>
						</tr>';

					}
				}
			}			
			$message_details.= '<tr>
			<th colspan="2">Total:</th>
			<td>'.wc_price($total).'</td>
			</tr>';
		}
		$message_details.= ' <tr>
		<th colspan="2">'.__('Refund Total', 'woo-refund-and-exchange-lite').':</th>
		<td>'.wc_price($total).'</td>
		</tr>
		</tbody>
		</table>
		</div>';
		$message = Woo_Refund_And_Exchange_Lite_Common_Functions::create_mail_html($orderid,$message_details);
		if(isset($refund_approve_template) && $refund_approve_template != '') {
			$html_content = $refund_approve_template;
		}else{

			$html_content = $message;
		}
		$to = get_post_meta($orderid, '_billing_email', true);
		$headers = array();
		$headers[] = "From: $fname <$fmail>";
		$headers[] = "Content-Type: text/html; charset=UTF-8";
		$subject = isset($mwb_rma_mail_refund_settings['mwb_rma_mail_return_approve_subject'])? $mwb_rma_mail_refund_settings['mwb_rma_mail_return_approve_subject']:'';
		wc_mail($to, $subject, $html_content, $headers);
		$order->update_status('wc-refund-approved', __('User Request of Refund Product is approved','woo-refund-and-exchange-lite'));
		$order->calculate_totals();
		return 'succuss' ;
	}

	public static function mwb_rma_mail_shortcode( $message , $order_id) {
		$firstname = get_post_meta($order_id, '_billing_first_name', true);
		$lname = get_post_meta($order_id, '_billing_last_name', true);
		$fullname = $firstname." ".$lname;
		$message = str_replace('[username]', $fullname, $message);
		$message = str_replace('[order]', "#".$order_id, $message);
		$message = str_replace('[siteurl]', home_url(), $message);
		return $message;
	}

}