<?php 

$mwb_rma_setting_tabs = array(
	array(
		'refund'        	=> array(
			'title' 			=> 	__( 'Refund', 'mwb-rma' ),
			'file_path'			=> 	MWB_RMA_DIR_PATH.'admin/partials/templates/mwb-rma-refund.php',
		),
	),
	array(
		'mail_configuration' => array(
			'title'				=>  __( 'Mail Configuration' ,'mwb-rma'),
			'file_path' 		=> 	MWB_RMA_DIR_PATH.'admin/partials/templates/mwb-rma-mail-configuration.php',
		),
	),
	array(
		'overview' 			=> array(
			'title'				=>  __( 'Overview' , 'mwb-rma'),
			'file_path' 		=> 	MWB_RMA_DIR_PATH.'admin/partials/templates/mwb-rma-overview.php',
		),
	),

);

$mwb_rma_setting_tabs = apply_filters('mwb_rma_setting_tabs' ,$mwb_rma_setting_tabs);