<?php 
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once MWB_RMA_DIR_PATH.'admin/partials/class-mwb-rma-settings.php';

$mwb_rma_admin_settings = new mwb_rma_admin_settings();

$mail_config_refund = array(
	array(
		'label' => __( 'Merchant Setting' , 'mwb-rma'),
		'data'	=> array(
			array(
				'title'         => __( 'Merchant Refund Request Subject', 'mwb-rma' ),
				'type'          => 'text',
				'id' 			=> 'mwb_rma_mail_merchant_return_subject',
				'desc_tip'		=> __( "Site title which will be shown when the mail is send to the user.", 'mwb-rma' ),
				'class' 		=> 'input-text ',
				'style' 		=> 'width:400px',
			),
		),
	), 
	array(
		'label' => __( 'Short-Codes' , 'mwb-rma'),
		'data'	=> array(
			array(
				'title'         => __( '', 'mwb-rma' ),
				'type'          => 'display_text',
				'str'			=> __( "These are order shortcode that you can use in EMAIL MESSESGES. It will be changed with order's dynamic values.", 'mwb-rma' ),
				'ss_both'		=> __('%s Note :%s Use %s [order] %s for Order Number, %s [siteurl] %s for home page url and %s [username] %s for user name.','mwb-woocommerce-rma'),
			),
		),
	), 
	array(
		'label' => __( 'Refund Request' , 'mwb-rma'),
		'data'	=> array(
			array(
				'title'         => __( 'Refund Request Subject', 'mwb-rma' ),
				'type'          => 'text',
				'id' 			=> 'mwb_rma_mail_return_subject',
				'desc_tip'		=> __( "Site title which will be shown when the mail is send to the user.", 'mwb-rma' ),
				'class' 		=> 'input-text ',
				'style' 		=> 'width:400px',
			),
			array(
				'title'         => __( 'Recieved Refund Request Message', 'mwb-rma' ),
				'type'          => 'wp_editor',
				'id' 			=> 'mwb_rma_mail_return_message',
				'desc_tip'		=> __( "Site title which will be shown when the mail is send to the user.", 'mwb-rma' ),
			),
		),
	), 
	array(
		'label' => __( 'Refund Approved' , 'mwb-rma'),
		'data'	=> array(
			array(
				'title'         => __( 'Approved Refund Request Subject', 'mwb-rma' ),
				'type'          => 'text',
				'id' 			=> 'mwb_rma_mail_return_approve_subject',
				'desc_tip'		=> __( "Site title which will be shown when the mail is send to the user.", 'mwb-rma' ),
				'class' 		=> 'input-text ',
				'style' 		=> 'width:400px',
			),
			array(
				'title'         => __( 'Approved Refund Request Message', 'mwb-rma' ),
				'type'          => 'wp_editor',
				'id' 			=> 'mwb_rma_mail_return_approve_message',
				'desc_tip'		=> __( "Site title which will be shown when the mail is send to the user.", 'mwb-rma' ),
			),
		),
	), 
	array(
		'label' => __( 'Refund Cancel' , 'mwb-rma'),
		'data'	=> array(
			array(
				'title'         => __( 'Cancelled Refund Request Subject', 'mwb-rma' ),
				'type'          => 'text',
				'id' 			=> 'mwb_rma_mail_return_cancel_subject',
				'desc_tip'		=> __( "Site title which will be shown when the mail is send to the user.", 'mwb-rma' ),
				'class' 		=> 'input-text ',
				'style' 		=> 'width:400px',
			),
			array(
				'title'         => __( 'Cancelled Refund Request Message', 'mwb-rma' ),
				'type'          => 'wp_editor',
				'id' 			=> 'mwb_rma_mail_return_cancel_message',
				'desc_tip'		=> __( "Site title which will be shown when the mail is send to the user.", 'mwb-rma' ),
			),
		),
	), 
);

$mail_config_refund = apply_filters( 'mail_config_refund' , $mail_config_refund);
$flag = false;
if( isset( $_POST['mwb_rma_mail_refund_save'] ) )
{
	if( wp_verify_nonce( $_REQUEST['mwb-rma-mail-refund-nonce'] ,'mwb-rma-mail-refund-nonce') ){
		unset($_POST['mwb_rma_mail_refund_save']);

		$postdata = $_POST;

		$refund_mail_setting_arr = $mwb_rma_admin_settings->mwb_rma_save_tab_settings($postdata,$mail_config_refund);
		if (is_array($refund_mail_setting_arr) && !empty($refund_mail_setting_arr)) {
			update_option('mwb_rma_mail_refund_settings',$refund_mail_setting_arr);
			$flag = true;
		}
	}
}
if($flag){	
	$mwb_rma_admin_settings->mwb_rma_settings_saved();
}
$mail_refund_settings_values = get_option('mwb_rma_mail_refund_settings',array());


?>
<div>
	<form enctype="multipart/form-data" action="" method="post">
		<div>
			<?php foreach ($mail_config_refund as $mcr_key => $mcr_value) {
				foreach ($mcr_value as $mcrv_key => $mcrv_value) {
					if($mcrv_key == 'label'){ 
						?> <div><?php echo $mcrv_value; ?></div><?php
					}
					if($mcrv_key == 'data'){?>
						<table>
							<?php 
							$mwb_rma_admin_settings->mwb_rma_generate_tab_settings_html($mcrv_value,$mail_refund_settings_values);
							?>
						</table><?php
					} 
				}
			} ?>
			</div>
		<?php 	do_action( 'after_refund_setting_array'); ?>
		<?php  	$mwb_rma_admin_settings->mwb_rma_save_button_html('mwb_rma_mail_refund_save');  ?>
		<input 	type="hidden" name="mwb-rma-mail-refund-nonce" value="<?php echo wp_create_nonce('mwb-rma-mail-refund-nonce'); ?>"> 
	</form>
</div>