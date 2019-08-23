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

}