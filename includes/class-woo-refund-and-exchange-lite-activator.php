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
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Woo_Refund_And_Exchange_Lite_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

	}


	/**
	 * This function is used to restore the setting values of plugin
	 * @name mwb_rma_update_data_values
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public static function mwb_rma_update_data_values() {

		$mwb_rma_check_enable = false;
		$refund_enable = get_option("mwb_wrma_return_enable", false);
		if( isset( $refund_enable ) && $refund_enable == 'yes' ) {
			$mwb_rma_check_enable = true;
		}
		if( $mwb_rma_check_enable ) {
	       $refund_setting_flag = false;
	       $refund_update = false;
	       $refund_update = Woo_Refund_And_Exchange_Lite_Activator::update_refund_setting_data( $refund_setting_flag );
		}
		if( $refund_update ) {
			$mail_basic_setting_flag = false;
			$mail_basic_update = Woo_Refund_And_Exchange_Lite_Activator::update_mail_basic_setting_data( $mail_basic_setting_flag );
		}
		if( $mail_basic_update ) {
			$mail_refund_setting_flag=false;
			$mail_refund_update = Woo_Refund_And_Exchange_Lite_Activator::update_mail_refund_setting_data( $mail_refund_setting_flag );
		}
	}

	/**
	 * This function is used to restore the refund setting values
	 * @name update_refund_setting_data
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public static function update_refund_setting_data( $refund_setting_flag ) {
		$mwb_refund_settings = get_option('mwb_rma_refund_settings',array());
		$refund_process_comp = false;
		if( empty( $mwb_refund_settings ) ) {
			$mwb_return_enable = get_option( 'mwb_wrma_return_enable',false);
			if( $mwb_return_enable == 'yes') {
				$mwb_return_enable = 'on';
			}
			$return_tax_enable = get_option( 'mwb_wrma_return_tax_enable',false);
			if( $return_tax_enable == 'yes') {
				$return_tax_enable = 'on';
			}
			$return_days = get_option( 'mwb_wrma_return_days',false);
			$return_attach_enable = get_option( 'mwb_wrma_return_attach_enable',false);
			if( $return_attach_enable == 'yes') {
				$return_attach_enable = 'on';
			}
			$return_request_description = get_option( 'mwb_wrma_return_request_description',false);
			if( $return_request_description == 'yes') {
				$return_request_description = 'on';
			}
			$return_request_manage_stock = get_option( 'mwb_wrma_return_request_manage_stock',false);
			if( $return_request_manage_stock == 'yes') {
				$return_request_manage_stock = 'on';
			}
			$mwb_wrma_return_order_status = get_option( 'mwb_wrma_return_order_status',array());
			$refund_rules_editor_enable = get_option( 'mwb_wrma_refund_rules_editor_enable',false);
			if( $refund_rules_editor_enable == 'yes') {
				$refund_rules_editor_enable = 'on';
			}
			$mwb_wrma_return_request_rules_editor = get_option( 'mwb_wrma_return_request_rules_editor',false);
			$mwb_rma_refund_settings = array(
				'mwb_rma_return_enable'					=> $mwb_return_enable,
				'mwb_rma_return_tax_enable' 			=> $return_tax_enable,
				'mwb_rma_return_days'					=> $return_days,
				'mwb_rma_return_attach_enable'			=> $return_attach_enable,
				'mwb_rma_return_request_description'	=> $return_request_description,
				'mwb_rma_return_request_manage_stock' 	=> $return_request_manage_stock,
				'mwb_rma_return_order_status'			=> $mwb_wrma_return_order_status,
				'mwb_rma_refund_rules_editor_enable'	=> $refund_rules_editor_enable,
				'mwb_rma_return_request_rules_editor'	=> $mwb_wrma_return_request_rules_editor
			);
			update_option('mwb_rma_refund_settings' , $mwb_rma_refund_settings);
			$refund_process_comp = true;
		}
		if( $refund_process_comp ) {
			delete_option( 'mwb_wrma_return_enable' );
			delete_option( 'mwb_wrma_return_tax_enable' );
			delete_option( 'mwb_wrma_return_attach_enable' );
			delete_option( 'mwb_wrma_return_days' );
			delete_option( 'mwb_wrma_return_attach_enable' );
			delete_option( 'mwb_wrma_return_request_description' );
			delete_option( 'mwb_wrma_return_request_manage_stock' );
			delete_option( 'mwb_wrma_return_order_status' );
			delete_option( 'mwb_wrma_refund_rules_editor_enable' );
			delete_option( 'mwb_wrma_return_request_rules_editor' );

			$refund_setting_flag = true;
		}
		return $refund_setting_flag;
	}

	/**
	 * This function is used to restore the basic mail setting values
	 * @name update_mail_basic_setting_data
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public static function update_mail_basic_setting_data( $mail_basic_setting_flag ) {
		$mwb_mail_basic_settings = get_option('mwb_rma_mail_basic_settings',array());
		$mail_basic_process_comp = false;
		if( empty( $mwb_mail_basic_settings ) ) {
			$mwb_rma_mail_from_name = get_option( 'ced_rnx_notification_from_name', '' );
			$mwb_rma_mail_from_name = sanitize_text_field($mwb_rma_mail_from_name);
			$mwb_rma_mail_from_mail = get_option( 'ced_rnx_notification_from_mail', '' );
			$mwb_rma_mail_from_mail = sanitize_text_field($mwb_rma_mail_from_mail);
			$mwb_rma_mail_header = get_option( 'ced_rnx_notification_mail_header' , '' );
			$mwb_rma_mail_footer =get_option( 'ced_rnx_notification_mail_footer' , '' );
			$mwb_rma_predefine_refund_reason = get_option( 'ced_rnx_return_predefined_reason' , array());
			$mwb_rma_mail_basic_settings=array(
				'mwb_rma_mail_from_name'  			=> $mwb_rma_mail_from_name,
				'mwb_rma_mail_from_email' 			=> $mwb_rma_mail_from_mail,
				'mwb_rma_mail_header'				=> $mwb_rma_mail_header,
				'mwb_rma_mail_footer'				=> $mwb_rma_mail_footer,
				'mwb_rma_return_predefined_reason' 	=> $mwb_rma_predefine_refund_reason,
			);
			update_option('mwb_rma_mail_basic_settings' , $mwb_rma_mail_basic_settings);
			$mail_basic_process_comp = true;
		}
		if( $mail_basic_process_comp ) {
			delete_option( 'ced_rnx_notification_from_name' );
			delete_option( 'ced_rnx_notification_from_mail' );
			delete_option( 'ced_rnx_notification_mail_header' );
			delete_option( 'ced_rnx_notification_mail_footer' );
			delete_option( 'ced_rnx_return_predefined_reason' );
			$mail_basic_setting_flag = true;
		}
		return $mail_basic_setting_flag;
	}

	/**
	 * This function is used to restore the refund mail setting values
	 * @name update_mail_refund_setting_data
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public static function update_mail_refund_setting_data( $mail_refund_setting_flag ) {
		$mwb_mail_refund_settings = get_option('mwb_rma_mail_refund_settings',array());
		$mail_refund_process_comp = false;
		if( empty( $mwb_mail_refund_settings ) ) {
			$mwb_rma_mail_merchant_return_subject = get_option( 'ced_rnx_notification_merchant_return_subject' ,'');
			$mwb_rma_mail_merchant_return_subject = sanitize_text_field( $mwb_rma_mail_merchant_return_subject);
			$mwb_rma_mail_return_subject = get_option( 'ced_rnx_notification_return_subject' , '');
			$mwb_rma_mail_return_subject = sanitize_text_field( $mwb_rma_mail_return_subject);
			$mwb_rma_mail_return_message = get_option( 'ced_rnx_notification_return_rcv' , '');
			$mwb_rma_mail_return_approve_subject = get_option( 'ced_rnx_notification_return_approve_subject' , '');
			$mwb_rma_mail_return_approve_subject = sanitize_text_field( $mwb_rma_mail_return_approve_subject);
			$mwb_rma_mail_return_approve_message = get_option( 'ced_rnx_notification_return_approve' , '');
			$mwb_rma_mail_return_cancel_subject = get_option( 'ced_rnx_notification_return_cancel_subject' , '');
			$mwb_rma_mail_return_cancel_subject = sanitize_text_field( $mwb_rma_mail_return_cancel_subject);
			$mwb_rma_mail_return_cancel_message = get_option( 'ced_rnx_notification_return_cancel' , '');
			$mwb_rma_mail_refund_settings = array(
				'mwb_rma_mail_merchant_return_subject' 	=> $mwb_rma_mail_merchant_return_subject,
				'mwb_rma_mail_return_subject' 			=> $mwb_rma_mail_return_subject,
				'mwb_rma_mail_return_message' 			=> $mwb_rma_mail_return_message,
				'mwb_rma_mail_return_approve_subject' 	=> $mwb_rma_mail_return_approve_subject,
				'mwb_rma_mail_return_approve_message' 	=> $mwb_rma_mail_return_approve_message,
				'mwb_rma_mail_return_cancel_subject'	=> $mwb_rma_mail_return_cancel_subject,
				'mwb_rma_mail_return_cancel_message' 	=> $mwb_rma_mail_return_cancel_message,
			);
			update_option('mwb_rma_mail_refund_settings' , $mwb_rma_mail_refund_settings);
			$mail_refund_process_comp = true;
		}
		if( $mail_refund_process_comp ) {
			delete_option( 'ced_rnx_notification_merchant_return_subject' );
			delete_option( 'ced_rnx_notification_return_subject' );
			delete_option( 'ced_rnx_notification_return_rcv' );
			delete_option( 'ced_rnx_notification_return_approve_subject' );
			delete_option( 'ced_rnx_notification_return_approve' );
			delete_option( 'ced_rnx_notification_return_cancel_subject' );
			delete_option( 'ced_rnx_notification_return_cancel' );
			$mail_refund_setting_flag = true;
		}
		return $mail_refund_setting_flag;
	}

}
