<?php 
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once MWB_RMA_DIR_PATH.'admin/partials/class-woo-refund-and-exchange-lite-settings.php';
$mwb_rma_admin_settings = new mwb_rma_admin_settings();

$status = wc_get_order_statuses();

$refund_setting_array = array(
	array(
		'label' => __( 'Refund' , 'woo-refund-and-exchange-lite'),
		'data'	=> array(
			array(
				'title'         => __( 'Enable', 'woo-refund-and-exchange-lite' ),
				'desc'          => __( 'Enable Refund Request for the customer so that they can refund products', 'woo-refund-and-exchange-lite' ),
				'type'          => 'checkbox',
				'id' 			=> 'mwb_rma_return_enable',
				'desc_tip'		=> __( 'When enabled the customers can raise refund requests against their products.', 'woo-refund-and-exchange-lite' ),
			),

			array(
				'title'         => __( 'Include Tax', 'woo-refund-and-exchange-lite' ),
				'desc'          => __( 'Include Tax with Product Refund Request.', 'woo-refund-and-exchange-lite' ),
				'type'          => 'checkbox',
				'id' 			=> 'mwb_rma_return_tax_enable',
				'desc_tip'		=> __( 'Refund Amount will be calculated including Tax when user Refund products', 'woo-refund-and-exchange-lite' ),
			),

			array(
				'title'         => __( 'Maximum Number of Days', 'woo-refund-and-exchange-lite' ),
				'desc'          => __( 'If days exceeds from the day of order placed then Refund Request will not be send. If value is 0 or blank then Refund button will not visible at order detail page.', 'woo-refund-and-exchange-lite' ),
				'desc_tip'		=>  __( 'If days exceeds from the day of order placed then Refund Request will not be send. If value is 0 or blank then Refund button will not visible at order detail page.', 'woo-refund-and-exchange-lite' ), 
				'type'          => 'number',
				'custom_attributes'   => array(
					'min'	=>	'0'
				),
				'id' 			=> 'mwb_rma_return_days',
			),
			array(
				'title'         => __( 'Enable Attachment on Request Form', 'woo-refund-and-exchange-lite' ),
				'desc'          => __( 'Enable this for user to send the attachment. User can attach <i>.png, .jpg, .jpeg</i> type files.', 'woo-refund-and-exchange-lite' ),
				'type'          => 'checkbox',
				'id' 			=> 'mwb_rma_return_attach_enable',
				'desc_tip'		=> __( "The user's can attach images on the refund request form if they want.", 'woo-refund-and-exchange-lite' ),
			),
			array(
				'title'         => __( 'Enable Refund Reason Description', 'woo-refund-and-exchange-lite' ),
				'desc'          => __( 'Enable this for user to allow user to send the detail description of Refund request.', 'woo-refund-and-exchange-lite' ),
				'desc_tip'		=> __( 'A textarea is shown to the customers on the refund request form where they can give the description stating their refund reason.' , 'woo-refund-and-exchange-lite'),
				'type'          => 'checkbox',
				'id' 			=> 'mwb_rma_return_request_description',
			),
			array(
				'title'         => __( 'Enable Manage Stock', 'woo-refund-and-exchange-lite' ),
				'desc'          => __( 'Enable this to increase product stock when Refund request is accepted.', 'woo-refund-and-exchange-lite' ),
				'desc_tip'		=> __( 'As soon as the refund request for a product is approved the Manage stock button is shown on order edit page.' , 'woo-refund-and-exchange-lite'),
				'type'          => 'checkbox',
				'id' 			=> 'mwb_rma_return_request_manage_stock',
			),
			array(
				'title'    		=> __( 'Select the orderstatus in which the order can be Refunded', 'woo-refund-and-exchange-lite' ),
				'desc_tip'    	=> __( 'Select Order status on which you want to allow the user to initiate Refund request user can submit.', 'woo-refund-and-exchange-lite' ),
				'custom_attribute' => array(
					'style' 			=> '"width:50%;"',
					'data-placeholder' 	=> __( 'Select', 'woo-refund-and-exchange-lite' )
				),
				'type'     		=> 'multiselect',
				'multiple'		=> 'multiple',
				'options'  		=> $status,
				'id' 			=> 'mwb_rma_return_order_status',
			),
			array(
				'title'         => __( 'Enable Refund Guidelines', 'woo-refund-and-exchange-lite' ),
				'desc'          => __( 'Enable, if you want to show custom Refund Policy Guidelines on Refund Request Form.', 'woo-refund-and-exchange-lite' ),
				'desc_tip'		=> __( 'When enabled it shows custom guidelines given in Refund Rules editor on the Refund Request Form' , 'woo-refund-and-exchange-lite'),
				'type'          => 'checkbox',
				'id' 			=> 'mwb_rma_refund_rules_editor_enable',
			),
			array(

				'title'         => __( 'Refund Guidelines Editor ', 'woo-refund-and-exchange-lite' ),
				'type'          => 'wp_editor',
				'desc_tip' 		=>  __( 'Custom Refund Guidelines Editor .Put your custom Refund rules here.', 'woo-refund-and-exchange-lite' ),
				'id' 			=> 'mwb_rma_return_request_rules_editor',
				'class'      	=> 'mwb_rma_return_request_rules_editor'
			),
		),
	),		
);

$refund_setting_array = apply_filters('refund_setting_array',$refund_setting_array);
$flag = false;
if( isset( $_POST['mwb_rma_refund_settings_save'] ) )
{
	if( wp_verify_nonce( $_REQUEST['mwb-rma-refund-nonce'] ,'mwb-rma-refund-nonce') ){
		 
		$postdata = $_POST;
		$refund_setting_arr = $mwb_rma_admin_settings->mwb_rma_save_tab_settings($postdata,$refund_setting_array);
		if (is_array($refund_setting_arr) && !empty($refund_setting_arr)) {	
			update_option('mwb_rma_refund_settings',$refund_setting_arr);
		}
		$flag=true;
	}
}

if($flag){	
	$mwb_rma_admin_settings->mwb_rma_settings_saved();
}

$refund_settings_values = get_option('mwb_rma_refund_settings',array());

?>
<div>
	<form enctype="multipart/form-data" action="" method="post">
		<div>
			<table>
				<?php foreach ($refund_setting_array as $rs_key => $rs_value) {
					foreach ($rs_value as $rsv_key => $rsv_value) {
						?><tr><?php if($rsv_key== 'label') { echo $rsv_value; }?></tr><?php
						if($rsv_key == 'data'){
							$mwb_rma_admin_settings->mwb_rma_generate_tab_settings_html($rsv_value,$refund_settings_values);
						} 
					}
				} ?>
			</table>
		</div>
		<?php  	$mwb_rma_admin_settings->mwb_rma_save_button_html('mwb_rma_refund_settings_save');  ?>
		<input 	type="hidden" name="mwb-rma-refund-nonce" value="<?php echo wp_create_nonce('mwb-rma-refund-nonce'); ?>"> 
	</form>
</div>