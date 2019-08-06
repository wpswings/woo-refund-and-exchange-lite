<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       www.makewebbetter.com
 * @since      1.0.0
 *
 * @package    Mwb_Rma
 * @subpackage Mwb_Rma/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php  
	include_once MWB_RMA_DIR_PATH.'admin/partials/templates/settings/setting-tab-array.php';

	
?>

<div class="wrap woocommerce" id="mwb_rma_setting_wrapper">
	<form enctype="multipart/form-data" action="" id="mainform" method="post">
		<div class="mwb_rma_header">
			<div class="mwb_rma_header_content_left">
				<div>
					<h3 class="mwb_rma_setting_title"><?php  _e('RMA SETTING' , 'mwb-rma'); ?></h3>
				</div>
			</div>
			<div class="mwb_rma_header_content_right">
				<ul>
					<li><a href="https://makewebbetter.com/contact-us/" target="_blank">
						<span class="dashicons dashicons-phone"></span>
					</a>
				</li>
				<li><a href="https://docs.makewebbetter.com/woocommerce-refund-and-exchange-lite/" target="_blank">
					<span class="dashicons dashicons-media-document"></span>
				</a>
			</li>
			<li class="mwb_rma_header_menu_button"><a  href="https://makewebbetter.com/product/woocommerce-rma-return-refund-exchange/" class="" title="" target="_blank">GO PRO NOW</a></li>
		</ul>
	</div>
</div>
<div class="mwb_rma_main_template">
	<div class="mwb_rma_body_template">
		<div class="mwb_rma_mobile_nav">
			<span class="dashicons dashicons-menu"></span>
		</div>
		<div class="mwb_rma_navigator_template">
			<div class="hubwoo-navigations">
				<?php 
				if(isset($mwb_rma_setting_tabs) && !empty($mwb_rma_setting_tabs) && is_array($mwb_rma_setting_tabs)){

					foreach($mwb_rma_setting_tabs as $tab_key => $tab_arr){
						foreach ($tab_arr as $tab_arr_key => $tab_data) {
							if (isset($_GET['tab']) &&  $_GET['tab'] == $tab_arr_key ) { 
								?>
								<div class="mwb_rma_tabs">
									<a class="mwb_rma_nav_tab nav-tab nav-tab-active " href="?page=mwb-rma-setting&tab=<?php echo $tab_arr_key; ?>"><?php _e($tab_data['title'], 'mwb-rma');?></a>
								</div>
								<?php 
							}
							else  { 
								if(empty($_GET['tab']) && $tab_arr_key =='refund'){  
									?>
									<div class="mwb_rma_tabs">
										<a class="mwb_rma_nav_tab nav-tab nav-tab-active " href="?page=mwb-rma-setting&tab=<?php echo $tab_arr_key; ?>"><?php _e($tab_data['title'], 'mwb-rma');?></a>
									</div>
									<?php	
								}
								else{ 
									?>			
									<div class="mwb_rma_tabs">
										<a class="mwb_rma_nav_tab nav-tab" href="?page=mwb-rma-setting&tab=<?php echo $tab_arr_key; ?>"><?php _e($tab_data['title'], 'mwb-rma');?></a>
									</div>
									<?php
								}
							}
						}
					}

				}?>
			</div>
		</div>
		<div class="mwb_rma_content_template">
			<?php 
			if (isset($mwb_rma_setting_tabs) && !empty($mwb_rma_setting_tabs) && is_array($mwb_rma_setting_tabs)) {
				if(isset($_GET['tab']) && !empty($_GET['tab'])){
					$tab = sanitize_text_field( $_GET['tab'] );
					foreach ($mwb_rma_setting_tabs as $s_tab_value) {
						foreach ($s_tab_value as $s_tab_arr_key => $s_tab_data) {
							if($s_tab_arr_key == $tab ){
								include_once $s_tab_data['file_path'];
							}
						}
					}
				}else{
					include_once MWB_RMA_DIR_PATH.'admin/partials/templates/mwb-rma-refund.php';
				}
			}
			?>
		</div>
	</div>
</div>