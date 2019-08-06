<?php 
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$mail_config_tabs = array(
	array(
		'basic'	=>  array(
			'title' =>  __('Basic' , 'mwb-rma'),
			'file_path' => MWB_RMA_DIR_PATH.'admin/partials/templates/mwb-rma-basic.php',
		),
	),
	array(
		'refund_mail'	=>  array(
			'title' =>  __('Refund' , 'mwb-rma'),
			'file_path' => MWB_RMA_DIR_PATH.'admin/partials/templates/mwb-rma-refund-mail.php',
		),
	),
);

$mail_config_tabs = apply_filters( 'mail_config_tabs' ,$mail_config_tabs);

?>

<div>
	<div>
		<?php
		if(isset($mail_config_tabs) && !empty($mail_config_tabs) && is_array($mail_config_tabs)){
			
			foreach($mail_config_tabs as $mc_key => $mc_arr){
				foreach ($mc_arr as $mc_arr_key => $mc_data) {
					if (isset($_GET['section']) &&  $_GET['section'] == $mc_arr_key ) { 
								?>
								<div class="mwb_rma_section">
									<a class=" " href="?page=mwb-rma-setting&tab=<?php echo $_GET['tab']; ?>&section=<?php echo $mc_arr_key; ?> "><?php _e($mc_data['title'], 'mwb-rma');?></a>
								</div>
								<?php 
							}
							else  { 
								if(empty($_GET['section']) && $mc_arr_key =='basic'){  
									?>
									<div class="mwb_rma_section">
										<a class=" " href="?page=mwb-rma-setting&tab=<?php echo $_GET['tab']; ?>&section=<?php echo $mc_arr_key; ?>"><?php _e($mc_data['title'], 'mwb-rma');?></a>
									</div>
									<?php	
								}
								else{ 
									?>			
									<div class="mwb_rma_section">
										<a class="" href="?page=mwb-rma-setting&tab=<?php echo $_GET['tab']; ?>&section=<?php echo $mc_arr_key; ?>"><?php _e($mc_data['title'], 'mwb-rma');?></a>
									</div>
									<?php
								}
							}
				
				}
				
			}

		}?>
	</div>
	<div>
		<?php 
		if (isset($mail_config_tabs) && !empty($mail_config_tabs) && is_array($mail_config_tabs)) {
			if(isset($_GET['tab']) && !empty($_GET['tab']) && isset($_GET['section']) && !empty($_GET['section'])){
				$tab = sanitize_text_field( $_GET['tab'] );
				$section = sanitize_text_field( $_GET['section'] );
				foreach ($mail_config_tabs as $m_tab_value) {
					foreach ($m_tab_value as $m_tab_arr_key => $m_tab_data) {
						if($tab == 'mail_configuration' && $m_tab_arr_key == $section){
							include_once $m_tab_data['file_path'];
						}
					}
				}
			}elseif(isset($_GET['tab']) && $_GET['tab'] == 'mail_configuration'){
				include_once MWB_RMA_DIR_PATH.'admin/partials/templates/mwb-rma-basic.php';
			}
		}
		?>
	</div>
</div>