<?php
/**
 * SLA deadline alert email — sent to the store admin when an RMA request
 * is approaching or has passed its configured SLA deadline.
 *
 * @package woo-refund-and-exchange-lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin SLA alert email.
 *
 * @since 1.0.0
 * @extends WC_Email
 */
class Wps_Rma_Sla_Alert_Email extends WC_Email {

	/**
	 * Order id set.
	 *
	 * @var int WooCommerce order ID.
	 */
	public $order_id = 0;

	/**
	 * Request type.
	 *
	 * @var string 'Return' or 'Exchange'
	 */
	public $request_type = '';

	/**
	 * Hours remaining until deadline (negative = overdue).
	 *
	 * @var float
	 */
	public $hours_remaining = 0;

	/**
	 * Unix timestamp of the SLA deadline.
	 *
	 * @var int
	 */
	public $deadline = 0;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = 'wps_rma_sla_alert_email';
		$this->title          = 'RMA Deadline Alert';
		$this->description    = 'Sent to admin when an RMA request is approaching or has passed its resolution deadline.';
		$this->heading        = 'RMA Resolution Deadline Alert';
		$this->subject        = 'Deadline Alert: Order #{order_id} — {request_type} request deadline';
		$this->template_html  = 'wps-rma-sla-alert-email-template.php';
		$this->template_plain = 'wps-rma-sla-alert-email-template.php';
		$this->template_base  = WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'emails/templates/';
		$this->placeholders   = array(
			'{site_title}'    => $this->get_blogname(),
			'{order_id}'      => '',
			'{request_type}'  => '',
		);

		parent::__construct();
	}

	/**
	 * Trigger the email.
	 *
	 * @param int    $order_id        WooCommerce order ID.
	 * @param string $request_type    'Return' or 'Exchange'.
	 * @param float  $hours_remaining Hours until deadline (negative = overdue).
	 * @param int    $deadline        Unix timestamp of the SLA deadline.
	 */
	public function trigger( $order_id, $request_type, $hours_remaining, $deadline ) {
		$admin_email = get_option( 'admin_email' );
		if ( ! $admin_email ) {
			return;
		}

		$this->setup_locale();

		$this->order_id        = (int) $order_id;
		$this->request_type    = sanitize_text_field( $request_type );
		$this->hours_remaining = (float) $hours_remaining;
		$this->deadline        = (int) $deadline;

		$this->placeholders['{order_id}']     = '#' . $this->order_id;
		$this->placeholders['{request_type}'] = $this->request_type;

		if ( $this->is_enabled() ) {
			$this->send(
				$admin_email,
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				array()
			);
		}

		$this->restore_locale();
	}

	/**
	 * Get email HTML content.
	 *
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		wc_get_template(
			$this->template_html,
			array(
				'order_id'        => $this->order_id,
				'request_type'    => $this->request_type,
				'hours_remaining' => $this->hours_remaining,
				'deadline'        => $this->deadline,
				'email_heading'   => $this->get_heading(),
				'sent_to_admin'   => true,
				'plain_text'      => false,
				'email'           => $this,
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}

	/**
	 * Get plain-text email content.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template(
			$this->template_plain,
			array(
				'order_id'        => $this->order_id,
				'request_type'    => $this->request_type,
				'hours_remaining' => $this->hours_remaining,
				'deadline'        => $this->deadline,
				'email_heading'   => $this->get_heading(),
				'sent_to_admin'   => true,
				'plain_text'      => true,
				'email'           => $this,
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}

	/**
	 * Default email subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return esc_html__( 'Deadline Alert: Order #{order_id} — {request_type} request deadline', 'woo-refund-and-exchange-lite' );
	}

	/**
	 * Default email heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return esc_html__( 'RMA Resolution Deadline Alert', 'woo-refund-and-exchange-lite' );
	}

	/**
	 * Settings fields shown in WooCommerce → Settings → Emails.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => 'Enable/Disable',
				'type'    => 'checkbox',
				'label'   => 'Enable resolution deadline alert email to admin',
				'default' => 'yes',
			),
			'subject' => array(
				'title'       => esc_html__( 'Subject', 'woo-refund-and-exchange-lite' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => esc_html__( 'Available placeholders: {order_id}, {request_type}', 'woo-refund-and-exchange-lite' ),
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading'  => array(
				'title'       => esc_html__( 'Heading', 'woo-refund-and-exchange-lite' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => esc_html__( 'Available placeholders: {order_id}, {request_type}', 'woo-refund-and-exchange-lite' ),
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'email_type' => array(
				'title'       => 'Email type',
				'type'        => 'select',
				'description' => 'Choose which format of email to send.',
				'default'     => 'html',
				'class'       => 'email_type',
				'options'     => array(
					'plain'     => esc_html__( 'Plain text', 'woo-refund-and-exchange-lite' ),
					'html'      => esc_html__( 'HTML', 'woo-refund-and-exchange-lite' ),
					'multipart' => esc_html__( 'Multipart', 'woo-refund-and-exchange-lite' ),
				),
			),
		);
	}
}
