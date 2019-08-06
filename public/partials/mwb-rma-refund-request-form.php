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

if(!wp_verify_nonce( $_REQUEST['mwb_rma_return_form_nonce'], 'mwb_rma_return_form_nonce' ) )
{
	$allowed = false;

}

if($allowed){

	if(isset($_POST['order_id']))
	{
		$order_id = sanitize_text_field($_POST['order_id']);
	}
	elseif (isset($_GET['order_id'])) {
		$order_id = sanitize_text_field($_GET['order_id']);
	}
	else
	{
		$order_id = 0;
	}
	

	//check order id is valid
	if($order_id == 0 || $current_user_id == 0)
	{
		$allowed = false;
	}

	if(!is_numeric($order_id))
	{
		
		if(get_current_user_id() > 0)
		{
			$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
			$myaccount_page_url = get_permalink( $myaccount_page );
		}
		$allowed = false;
		$reason = __('Please choose an Order.','woocommerce-refund-and-exchange-lite').'<a href="'.$myaccount_page_url.'">'.__('Click Here','mwb-rma').'</a>';
		$reason = apply_filters('mwb_rma_return_choose_order', $reason);
	}
	else 
	{
		$order_customer_id = get_post_meta($order_id, '_customer_user', true);
		
		if($current_user_id > 0) // check order associated to customer account or not for registered user
		{			
			if($order_customer_id != $current_user_id)
			{
				$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
				$myaccount_page_url = get_permalink( $myaccount_page );
				$allowed = false;
				$reason = __("This order #$order_id is not associated to your account. <a href='$myaccount_page_url'>Click Here</a>",'woocommerce-refund-and-exchange-lite' );
				$reason = apply_filters('mwb_rma_return_choose_order', $reason);
			}			
		}
	}

	if($allowed)
	{
		$order = wc_get_order($order_id);
		$mwb_rma_refund_settings = get_option( 'mwb_rma_refund_settings' ,array());
		$mwb_rma_refund_enable = isset($mwb_rma_refund_settings['mwb_rma_return_enable'])? $mwb_rma_refund_settings['mwb_rma_return_enable']:'';

		if ($mwb_rma_refund_enable == 'on' && !empty($mwb_rma_refund_enable)) {
			$allowed = true;
			
		}else{
			$allowed = false;
			$reason = __('Refund request is disabled.','woocommerce-refund-and-exchange-lite' );
			$reason = apply_filters('ced_rnx_return_order_amount', $reason);
			// print_r($reason);
		}
	}


get_header( 'shop' );

if($allowed){
	print_r("dfgdfg");
}

}