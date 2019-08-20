<?php 
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
include_once MWB_RMA_DIR_PATH.'admin/partials/class-woo-refund-and-exchange-lite-settings.php';

$mwb_rma_admin_settings = new mwb_rma_admin_settings();

$mail_config_basic = array(
	array(
		'label' => __( 'Mail Setting' , 'woo-refund-and-exchange-lite'),
		'data'	=> array(
			array(
				'title'         => __( 'From Name', 'woo-refund-and-exchange-lite' ),
				'type'          => 'text',
				'id' 			=> 'mwb_rma_mail_from_name',
				'desc_tip'		=> __( "Site title which will be shown when the mail is send to the user.", 'woo-refund-and-exchange-lite' ),
				'class' 		=> 'input-text ',
				'style' 		=> 'width:160px',
			),
			array(
				'title'         => __( 'From Email', 'woo-refund-and-exchange-lite' ),
				'type'          => 'text',
				'id' 			=> 'mwb_rma_mail_from_email',
				'desc_tip'		=> __( "Site e-mail which will be shown when the mail is send to the user.", 'woo-refund-and-exchange-lite' ),
				'class'			=> 'input-text ',
				'style' 		=> 'width:160px',
			),
			array(
				'title'         => __( 'Mail Header', 'woo-refund-and-exchange-lite' ),
				'type'          => 'wp_editor',
				'id' 			=> 'mwb_rma_mail_header',
				'desc_tip'		=> __( "Custom mail header that will be shown on the mails as mail header.", 'woo-refund-and-exchange-lite' ),
			),
			array(
				'title'         => __( 'Mail Footer', 'woo-refund-and-exchange-lite' ),
				'type'          => 'wp_editor',
				'id' 			=> 'mwb_rma_mail_footer',
				'desc_tip'		=> __( "Custom mail footer that will be shown on the mails as mail footer.", 'woo-refund-and-exchange-lite' ),
			),
		),
	), 
	array(
		'label' => __( 'Predefined Refund Reason' , 'woo-refund-and-exchange-lite'),
		'data'	=> array(
			array(
				'title'         => '',
				'type'          => 'add_more_text',
				'id' 			=> 'mwb_rma_return_predefined_reason',
				'class' 		=> 'input-text ',
				'style' 		=> 'width:160px',
			),
			array(
				'title'         => '',
				'type'          => 'add_more_button',
				'label'			=> __('ADD MORE' , 'woo-refund-and-exchange-lite'),
				'class'			=> 'add_more_button',
				'id' 			=> 'mwb_rma_rpr_add_more_button',
				'desc_tip'		=> __( "Add text boxes to enter Predefine Return reason which will be displayed on the Return Request Form", 'woo-refund-and-exchange-lite' ),
			),

		),
	), 
);

$mail_config_basic = apply_filters( 'mail_config_basic' , $mail_config_basic);
$flag =false;

if( isset( $_POST['mwb_rma_mail_basic_save'] ) )
{
	if( wp_verify_nonce( $_REQUEST['mwb-rma-mail-basic-nonce'] ,'mwb-rma-mail-basic-nonce') ){
		unset($_POST['mwb_rma_mail_basic_save']);

		$postdata = $_POST;

		$basic_mail_setting_arr = $mwb_rma_admin_settings->mwb_rma_save_tab_settings($postdata,$mail_config_basic);
		if (is_array($basic_mail_setting_arr) && !empty($basic_mail_setting_arr)) {
			update_option('mwb_rma_mail_basic_settings',$basic_mail_setting_arr);
			$flag=true;
		}
	}
}

if($flag){	
	$mwb_rma_admin_settings->mwb_rma_settings_saved();
}

$mail_basic_settings_values = get_option('mwb_rma_mail_basic_settings',array());

?>
<div>
	<form enctype="multipart/form-data" action="" method="post">
		<div>
			<?php foreach ($mail_config_basic as $rs_key => $rs_value) {
				foreach ($rs_value as $rsv_key => $rsv_value) {
					if($rsv_key == 'label'){ 
						echo $rsv_value; 
					}
					if($rsv_key == 'data'){?>
						<table>
							<?php 
							$mwb_rma_admin_settings->mwb_rma_generate_tab_settings_html($rsv_value,$mail_basic_settings_values);
							?>
						</table><?php
					} 
				}
			} ?>
			</div>
		<?php  	$mwb_rma_admin_settings->mwb_rma_save_button_html('mwb_rma_mail_basic_save');  ?>
		<input 	type="hidden" name="mwb-rma-mail-basic-nonce" value="<?php echo wp_create_nonce('mwb-rma-mail-basic-nonce'); ?>"> 
	</form>
</div>