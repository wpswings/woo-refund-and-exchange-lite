<?php
/**
 * Guest return magic-link email (WPS-8379).
 *
 * @package woo-refund-and-exchange-lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sends the tokenised return link to the customer after a successful guest lookup.
 *
 * @since 1.0.0
 * @extends \WC_Email
 */
class Wps_Rma_Guest_Return_Link_Email extends WC_Email {

	/**
	 * WooCommerce order ID.
	 *
	 * @var int
	 */
	public $order_id = 0;

	/**
	 * One-time return link URL (token already embedded).
	 *
	 * @var string
	 */
	public $return_link = '';

	/**
	 * Construct email defaults.
	 */
	public function __construct() {
		$this->id             = 'wps_rma_guest_return_link_email';
		$this->customer_email = true;
		$this->title          = esc_html__( 'RMA Guest Return Link', 'woo-refund-and-exchange-lite' );
		$this->description    = esc_html__( 'Sent to the customer when they request a guest return link. Contains a one-time tokenised URL valid for 24 hours.', 'woo-refund-and-exchange-lite' );
		$this->heading        = esc_html__( 'Your Return Request Link', 'woo-refund-and-exchange-lite' );
		$this->subject        = esc_html__( '[{site_title}] Return Request Link for Order #{order_number}', 'woo-refund-and-exchange-lite' );
		$this->template_html  = 'wps-rma-guest-return-link-email.php';
		$this->template_plain = 'wps-rma-guest-return-link-email.php';
		$this->template_base  = WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'emails/templates/';
		$this->placeholders   = array(
			'{site_title}'    => $this->get_blogname(),
			'{order_number}'  => '',
		);

		parent::__construct();
	}

	/**
	 * Send the return link email.
	 *
	 * @param int    $order_id    WooCommerce order ID.
	 * @param string $return_link Tokenised URL to the return form.
	 */
	public function trigger( $order_id, $return_link ) {
		$this->order_id              = absint( $order_id );
		$this->return_link           = esc_url_raw( $return_link );
		$this->placeholders['{order_number}'] = '';

		$order = wc_get_order( $this->order_id );
		if ( ! $order ) {
			return;
		}

		$this->placeholders['{order_number}'] = $order->get_order_number();
		$this->recipient = $order->get_billing_email();

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
				'return_link'        => $this->return_link,
				'email'              => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Get email content plain text.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return $this->get_content_html();
	}
}
