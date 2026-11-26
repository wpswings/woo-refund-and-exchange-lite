<?php
/**
 * EU Withdrawal acknowledgement email (customer-facing, durable-medium requirement).
 *
 * @package woo-refund-and-exchange-lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer acknowledgement email sent on EU withdrawal submission.
 *
 * @since 1.0.0
 * @extends \WC_Email
 */
class Wps_Rma_Eu_Withdrawal_Email extends WC_Email {

	/**
	 * Order ID for the withdrawal.
	 *
	 * @var int
	 */
	public $order_id = 0;

	/**
	 * Audit log entry for this submission.
	 *
	 * @var array
	 */
	public $log_entry = array();

	/**
	 * Construct email defaults.
	 */
	public function __construct() {
		$this->id             = 'wps_rma_eu_withdrawal_email';
		$this->customer_email = true;
		$this->title          = esc_html__( 'RMA EU Withdrawal Acknowledgement', 'woo-refund-and-exchange-lite' );
		$this->description    = esc_html__( 'Sent to the customer when they submit an EU right-of-withdrawal request (Directive 2023/2673). Required on a durable medium.', 'woo-refund-and-exchange-lite' );
		$this->heading        = esc_html__( 'Withdrawal from Contract – Acknowledgement', 'woo-refund-and-exchange-lite' );
		$this->subject        = esc_html__( '[{site_title}] EU Withdrawal Acknowledgement for Order #{order_id}', 'woo-refund-and-exchange-lite' );
		$this->template_html  = 'wps-rma-eu-withdrawal-email-template.php';
		$this->template_plain = 'wps-rma-eu-withdrawal-email-template.php';
		$this->template_base  = WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'emails/templates/';
		$this->placeholders   = array(
			'{site_title}' => $this->get_blogname(),
			'{order_id}'   => '',
		);

		parent::__construct();
	}

	/**
	 * Send the customer acknowledgement email.
	 *
	 * @param int   $order_id  WooCommerce order ID.
	 * @param array $log_entry Audit log entry containing items, timestamp, etc.
	 */
	public function trigger( $order_id, $log_entry = array() ) {
		$this->order_id              = absint( $order_id );
		$this->log_entry             = $log_entry;
		$this->placeholders['{order_id}'] = $this->order_id;

		$order = wc_get_order( $this->order_id );
		if ( ! $order ) {
			return;
		}

		$this->recipient = $order->get_billing_email();
		if ( ! $this->recipient ) {
			return;
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * Get email content HTML.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'order_id'           => $this->order_id,
				'log_entry'          => $this->log_entry,
				'email'              => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Get email content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return $this->get_content_html();
	}
}
