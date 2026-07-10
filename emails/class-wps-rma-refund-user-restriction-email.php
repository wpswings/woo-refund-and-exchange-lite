<?php
/**
 * Refund access restriction notice — sent to a customer when the admin
 * blocks or unblocks their email from raising Refund requests.
 *
 * @package woo-refund-and-exchange-lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer refund restriction notice email.
 *
 * @since 1.0.0
 * @extends WC_Email
 */
class Wps_Rma_Refund_User_Restriction_Email extends WC_Email {

	/**
	 * Whether the customer was just blocked or unblocked.
	 *
	 * @var string 'blocked' or 'unblocked'
	 */
	public $action = 'blocked';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = 'wps_rma_refund_user_restriction_email';
		$this->title          = 'Refund Access Restriction Notice';
		$this->description    = 'Sent to a customer when the admin blocks or unblocks them from raising Refund requests.';
		$this->heading        = 'Refund Request Access Restricted';
		$this->subject        = '{site_title} - Your Refund Request Access Has Been {status}';
		$this->template_html  = 'wps-rma-user-restriction-email-template.php';
		$this->template_plain = 'wps-rma-user-restriction-email-template.php';
		$this->template_base  = WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'emails/templates/';
		$this->placeholders   = array(
			'{site_title}' => $this->get_blogname(),
			'{status}'     => '',
		);

		parent::__construct();
	}

	/**
	 * Trigger the email.
	 *
	 * @param string $user_email Customer email address.
	 * @param string $action     'blocked' or 'unblocked'.
	 */
	public function trigger( $user_email, $action ) {
		$user_email = sanitize_email( $user_email );
		if ( ! $user_email || ! is_email( $user_email ) ) {
			return;
		}

		$this->setup_locale();

		$this->action    = 'unblocked' === $action ? 'unblocked' : 'blocked';
		$this->recipient = $user_email;

		$this->heading                = 'unblocked' === $this->action
			? esc_html__( 'Refund Request Access Restored', 'woo-refund-and-exchange-lite' )
			: esc_html__( 'Refund Request Access Restricted', 'woo-refund-and-exchange-lite' );
		$this->placeholders['{status}'] = 'unblocked' === $this->action
			? esc_html__( 'Restored', 'woo-refund-and-exchange-lite' )
			: esc_html__( 'Restricted', 'woo-refund-and-exchange-lite' );

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send(
				$this->get_recipient(),
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
				'action'         => $this->action,
				'request_type'   => esc_html__( 'Refund', 'woo-refund-and-exchange-lite' ),
				'email_heading'  => $this->get_heading(),
				'sent_to_admin'  => false,
				'plain_text'     => false,
				'email'          => $this,
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
				'action'         => $this->action,
				'request_type'   => esc_html__( 'Refund', 'woo-refund-and-exchange-lite' ),
				'email_heading'  => $this->get_heading(),
				'sent_to_admin'  => false,
				'plain_text'     => true,
				'email'          => $this,
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
		return esc_html__( '{site_title} - Your Refund Request Access Has Been {status}', 'woo-refund-and-exchange-lite' );
	}

	/**
	 * Default email heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return esc_html__( 'Refund Request Access Restricted', 'woo-refund-and-exchange-lite' );
	}

	/**
	 * Settings fields shown in WooCommerce → Settings → Emails.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => 'Enable/Disable',
				'type'    => 'checkbox',
				'label'   => 'Enable this email notification',
				'default' => 'yes',
			),
			'subject' => array(
				'title'       => esc_html__( 'Subject', 'woo-refund-and-exchange-lite' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => esc_html__( 'Available placeholders: {site_title}, {status}', 'woo-refund-and-exchange-lite' ),
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading' => array(
				'title'       => esc_html__( 'Heading', 'woo-refund-and-exchange-lite' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => esc_html__( 'Available placeholders: {site_title}, {status}', 'woo-refund-and-exchange-lite' ),
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
