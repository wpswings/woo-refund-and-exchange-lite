<?php

/**
 * This is class to restore the saved data on particular keys.
 *
 * @name    Woocommerce_Rma_Lite_Restore_Settings_Updation
 * @category Class
 * @author   makewebbetter <webmaster@makewebbetter.com>
 */
class Woocommerce_Rma_Lite_Restore_Settings_Updation {
	/**
	 * Function to migrate the settings
	 */
	public function mwb_rma_migrate_re_settings() {
		$enable_refund = get_option( 'mwb_wrma_return_enable', false );
		if ( 'yes' === $enable_refund ) {
			update_option( 'mwb_rma_refund_enable', 'on' );
		}
		$attach_enable = get_option( 'mwb_wrma_return_attach_enable', false );
		if ( 'yes' === $attach_enable ) {
			update_option( 'mwb_rma_refund_attachment', 'on' );
		}
		$attach_limit = get_option( 'mwb_wrma_refund_attachment_limit', false );
		if ( ! empty( $attach_limit ) && $attach_limit > 0 ) {
			update_option( 'mwb_rma_attachment_limit', $attach_limit );
		}
		$manage_stock = get_option( 'mwb_wrma_return_request_manage_stock', false );
		if ( 'yes' === $manage_stock ) {
			update_option( 'mwb_rma_refund_manage_stock', 'on' );
		}
		$show_pages = get_option( 'mwb_wrma_refund_button_view', false );
		if ( ! empty( $show_pages ) ) {
			$button_hide = array();
			if ( ! in_array( 'order-page', $show_pages ) ) {
				$button_hide[] = 'order-page';
			}
			if ( ! in_array( 'My account', $show_pages ) ) {
				$button_hide[] = 'My account';
			}
			if ( ! in_array( 'thank-you-page', $show_pages ) ) {
				$button_hide[] = 'Checkout';
			}
			update_option( 'mwb_rma_refund_button_pages', $button_hide );
		}
		$refund_rule_enable = get_option( 'mwb_wrma_refund_rules_editor_enable', false );
		if ( 'yes' === $refund_rule_enable ) {
			update_option( 'mwb_rma_refund_rules', 'on' );
		}
		$refund_editor = get_option( 'mwb_wrma_return_request_rules_editor', false );
		if ( ! empty( $refund_editor ) ) {
			update_option( 'mwb_rma_refund_rules_editor', $refund_editor );
		}
		$refund_text = get_option( 'mwb_wrma_return_button_text', false );
		if ( ! empty( $refund_text ) ) {
			update_option( 'mwb_rma_refund_button_text', $refund_text );
		}
		$refund_desc = get_option( 'mwb_wrma_return_request_description', false );
		if ( 'yes' === $refund_desc ) {
			update_option( 'mwb_rma_refund_description', 'on' );
		}
		$refund_reason  = get_option( 'ced_rnx_return_predefined_reason', false );
		$refund_reason1 = get_option( 'mwb_wrma_return_predefined_reason', false );
		if ( ! empty( $refund_reason1 ) ) {
			$refund_reason = $refund_reason1;
		}
		if ( ! empty( $refund_reason ) ) {
			$refund_reason = implode( ',', $refund_reason );
			update_option( 'mwb_rma_refund_reasons', $refund_reason );
		}
		$order_msg_enable = get_option( 'mwb_wrma_order_message_view', false );
		if ( 'yes' === $order_msg_enable ) {
			update_option( 'mwb_rma_general_om', 'on' );
		}
		$order_attach = get_option( 'mwb_wrma_order_message_attachment', false );
		if ( 'yes' === $order_attach ) {
			update_option( 'mwb_rma_general_enable_om_attachment', 'on' );
		}
		$order_text = get_option( 'mwb_wrma_order_msg_text', false );
		if ( ! empty( $order_text ) ) {
			update_option( 'mwb_rma_order_message_button_text', $order_text );
		}

		// RMA Policies Setting Save.
		$tax_enable          = get_option( 'mwb_wrma_return_tax_enable', false );
		$refund_order_status = get_option( 'mwb_wrma_return_order_status', false );
		$return_days         = get_option( 'mwb_wrma_return_days', false );
		$refund_order_status = ! empty( $refund_order_status ) ? $refund_order_status : array();
		$set_policies_arr = array(
			'mwb_rma_setting' =>
			array(
				0 => array(
					'row_policy'           => 'mwb_rma_maximum_days',
					'row_functionality'    => 'refund',
					'row_conditions1'      => 'mwb_rma_less_than',
					'row_value'            => $return_days,
					'incase_functionality' => 'incase',
				),
				1 => array(
					'row_functionality'    => 'refund',
					'row_policy'           => 'mwb_rma_order_status',
					'row_conditions2'      => 'mwb_rma_equal_to',
					'row_statuses'         => $refund_order_status,
					'incase_functionality' => 'incase',
				),
			),
		);

		if ( 'yes' !== $tax_enable ) {
			unset( $set_policies_arr['mwb_rma_setting'][2] );
		}
		update_option( 'policies_setting_option', $set_policies_arr );

		// Refund Request Subject And Content Updation.
		$subject  = get_option( 'ced_rnx_notification_return_subject', false );
		$subject1 = get_option( 'mwb_wrma_notification_return_subject', false );
		if ( ! empty( $subject1 ) ) {
			$subject = $subject1;
		}
		if ( empty( $subject ) ) {
			$subject = '';
		}
		$content  = get_option( 'ced_rnx_notification_return_rcv', false );
		$content1 = get_option( 'mwb_wrma_notification_return_rcv', false );
		if ( ! empty( $content1 ) ) {
			$content = $content1;
		}
		if ( empty( $content ) ) {
			$content = '';
		}
		$refund_request_add = array(
			'enabled'            => 'yes',
			'subject'            => $subject,
			'heading'            => '',
			'additional_content' => $content,
			'email_type'         => 'html',
		);
		update_option( 'woocommerce_mwb_rma_refund_request_email_settings', $refund_request_add );

		// Refund Request Accept Subject And Content Updation.
		$subject  = get_option( 'ced_rnx_notification_return_approve_subject', false );
		$subject1 = get_option( 'mwb_wrma_notification_return_approve_subject', false );
		if ( ! empty( $subject1 ) ) {
			$subject = $subject1;
		}
		if ( empty( $subject ) ) {
			$subject = '';
		}
		$content  = get_option( 'ced_rnx_notification_return_approve', false );
		$content1 = get_option( 'mwb_wrma_notification_return_approve', false );
		if ( ! empty( $content1 ) ) {
			$content = $content1;
		}
		if ( empty( $content ) ) {
			$content = '';
		}
		$refund_request_accept_add = array(
			'enabled'            => 'yes',
			'subject'            => $subject,
			'heading'            => '',
			'additional_content' => $content,
			'email_type'         => 'html',
		);
		update_option( 'woocommerce_mwb_rma_refund_request_accept_email_settings', $refund_request_accept_add );

		// Refund Request Cancel Subject And Content Updation.

		$subject  = get_option( 'ced_rnx_notification_return_cancel_subject', false );
		$subject1 = get_option( 'mwb_wrma_notification_return_cancel_subject', false );
		if ( ! empty( $subject1 ) ) {
			$subject = $subject1;
		}
		if ( empty( $subject ) ) {
			$subject = '';
		}
		$content  = get_option( 'ced_rnx_notification_return_cancel', false );
		$content1 = get_option( 'mwb_wrma_notification_return_cancel', false );
		if ( ! empty( $content1 ) ) {
			$content = $content1;
		}
		if ( empty( $content ) ) {
			$content = '';
		}
		$refund_request_cancel_add = array(
			'enabled'            => 'yes',
			'subject'            => $subject,
			'heading'            => '',
			'additional_content' => $content,
			'email_type'         => 'html',
		);
		update_option( 'woocommerce_mwb_rma_refund_request_cancel_email_settings', $refund_request_cancel_add );
	}
}
