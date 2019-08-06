<?php 
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once MWB_RMA_DIR_PATH.'admin/partials/templates/settings/refund-setting-array.php';
include_once MWB_RMA_DIR_PATH.'admin/partials/class-mwb-rma-settings.php';

$mwb_rma_admin_settings = new mwb_rma_admin_settings();
$flag = false;
if( isset( $_POST['mwb_rma_refund_settings_save'] ) )
{
	if( wp_verify_nonce( $_REQUEST['mwb-rma-refund-nonce'] ,'mwb-rma-refund-nonce') ){
		unset($_POST['mwb_rma_refund_settings_save']);

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
		<?php 	do_action( 'after_refund_setting_array'); ?>
		<?php  	$mwb_rma_admin_settings->mwb_rma_save_button_html('mwb_rma_refund_settings_save');  ?>
		<input 	type="hidden" name="mwb-rma-refund-nonce" value="<?php echo wp_create_nonce('mwb-rma-refund-nonce'); ?>"> 
	</form>
</div>