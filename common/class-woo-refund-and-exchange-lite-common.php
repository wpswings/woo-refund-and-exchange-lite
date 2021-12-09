<?php
/**
 * The common functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/common
 */

/**
 * The common functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the common stylesheet and JavaScript.
 * namespace woo_refund_and_exchange_lite_common.
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/common
 */
class Woo_Refund_And_Exchange_Lite_Common {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the common side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wrael_common_enqueue_styles() {
		wp_enqueue_style( $this->plugin_name . 'common', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'common/css/woo-refund-and-exchange-lite-common.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the common side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wrael_common_enqueue_scripts() {
		$pro_active = mwb_rma_pro_active();
		if ( get_current_user_id() > 0 ) {
			$myaccount_page     = get_option( 'woocommerce_myaccount_page_id' );
			$myaccount_page_url = get_permalink( $myaccount_page );
		} else {
			$myaccount_page_url = '';
			$myaccount_page_url = apply_filters( 'myaccount_page_url', $myaccount_page_url );
		}
		wp_register_script( $this->plugin_name . 'common', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'common/js/woo-refund-and-exchange-lite-common.min.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->plugin_name . 'common',
			'wrael_common_param',
			array(
				'ajaxurl'               => admin_url( 'admin-ajax.php' ),
				'mwb_rma_nonce'         => wp_create_nonce( 'mwb_rma_ajax_security' ),
				'return_subject_msg'    => esc_html__( 'Please Enter Refund Subject.', 'woo-refund-and-exchange-lite' ),
				'return_reason_msg'     => esc_html__( 'Please Enter Refund Reason.', 'woo-refund-and-exchange-lite' ),
				'return_select_product' => esc_html__( 'Please Select Product to refund.', 'woo-refund-and-exchange-lite' ),
				'check_pro_active'      => esc_html( $pro_active ),
				'message_sent'          => esc_html__( 'The message has been sent successfully', 'woo-refund-and-exchange-lite' ),
				'message_empty'         => esc_html__( 'Please Enter a Message.', 'woo-refund-and-exchange-lite' ),
				'myaccount_url'         => esc_attr( $myaccount_page_url ),
			)
		);
		wp_enqueue_script( $this->plugin_name . 'common' );
	}

	/**
	 * Add the email classes.
	 *
	 * @param array $email_classes email classes.
	 */
	public function mwb_rma_woocommerce_emails( $email_classes ) {
		// include our order message email class.
		require_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'emails/class-mwb-rma-order-messages-email.php';
		require_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'emails/class-mwb-rma-refund-request-email.php';
		require_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'emails/class-mwb-rma-refund-request-accept-email.php';
		require_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'emails/class-mwb-rma-refund-request-cancel-email.php';
		// add the email class to the list of email classes that WooCommerce loads.

		$email_classes['mwb_rma_order_messages_email']        = new Mwb_Rma_Order_Messages_Email();
		$email_classes['mwb_rma_refund_request_email']        = new Mwb_Rma_Refund_Request_Email();
		$email_classes['mwb_rma_refund_request_accept_email'] = new Mwb_Rma_Refund_Request_Accept_Email();
		$email_classes['mwb_rma_refund_request_cancel_email'] = new Mwb_Rma_Refund_Request_Cancel_Email();
		return $email_classes;
	}

	/**
	 * This function is to save return request Attachment
	 */
	public function mwb_rma_order_return_attach_files() {
		$check_ajax = check_ajax_referer( 'mwb_rma_ajax_security', 'security_check' );

		if ( $check_ajax ) {
			if ( isset( $_FILES['mwb_rma_return_request_files'] ) && isset( $_FILES['mwb_rma_return_request_files']['tmp_name'] ) && isset( $_FILES['mwb_rma_return_request_files']['name'] ) ) {
				$filename = array();
				$order_id = isset( $_POST['mwb_rma_return_request_order'] ) ? sanitize_text_field( wp_unslash( $_POST['mwb_rma_return_request_order'] ) ) : sanitize_text_field( wp_unslash( $_POST['mwb_rma_return_request_order'] ) );
				$count    = count( $_FILES['mwb_rma_return_request_files']['tmp_name'] );
				for ( $i = 0; $i < $count; $i++ ) {
					if ( isset( $_FILES['mwb_rma_return_request_files']['tmp_name'][ $i ] ) ) {
						$directory = ABSPATH . 'wp-content/attachment';
						if ( ! file_exists( $directory ) ) {
							mkdir( $directory, 0755, true );
						}

						$source_path = sanitize_text_field( wp_unslash( $_FILES['mwb_rma_return_request_files']['tmp_name'][ $i ] ) );
						$target_path = $directory . '/' . $order_id . '-' . sanitize_text_field( wp_unslash( $_FILES['mwb_rma_return_request_files']['name'][ $i ] ) );

						$filename[] = $order_id . '-' . sanitize_text_field( wp_unslash( $_FILES['mwb_rma_return_request_files']['name'][ $i ] ) );
						move_uploaded_file( $source_path, $target_path );
					}
				}

				$request_files = get_post_meta( $order_id, 'mwb_rma_return_attachment', true );

				$pending = true;
				if ( isset( $request_files ) && ! empty( $request_files ) ) {
					foreach ( $request_files as $date => $request_file ) {
						if ( 'pending' === $request_file['status'] ) {
							unset( $request_files[ $date ][0] );
							$request_files[ $date ]['files']  = $filename;
							$request_files[ $date ]['status'] = 'pending';
							$pending                          = false;
							break;
						}
					}
				}

				if ( $pending ) {
					$request_files                    = array();
					$date                             = gmdate( 'd-m-Y' );
					$request_files[ $date ]['files']  = $filename;
					$request_files[ $date ]['status'] = 'pending';
				}

				update_post_meta( $order_id, 'mwb_rma_return_attachment', $request_files );
				echo 'success';
			}
			wp_die();
		}
	}

	/**
	 * This function is to save return request.
	 */
	public function mwb_rma_save_return_request() {
		$check_ajax = check_ajax_referer( 'mwb_rma_ajax_security', 'security_check' );
		if ( $check_ajax && current_user_can( 'mwb-rma-refund-request' ) ) {
			$order_id = isset( $_POST['orderid'] ) ? sanitize_text_field( wp_unslash( $_POST['orderid'] ) ) : '';
			$re_bank  = get_option( 'mwb_rma_refund_manually_de', false );
			if ( 'on' === $re_bank && ! empty( $_POST['bankdetails'] ) ) {
				update_post_meta( $order_id, 'mwb_rma_bank_details', $_POST['bankdetails'] );
			}
			$refund_method  = isset( $_POST['refund_method'] ) ? sanitize_text_field( wp_unslash( $_POST['refund_method'] ) ) : '';
			$refund_method  = apply_filters( 'mwb_rma_refund_method_wallet', $refund_method );
			$wallet_enabled = get_option( 'mwb_rma_wallet_enable', 'no' );
			$refund_method  = get_option( 'mwb_rma_refund_method', 'no' );
			if ( mwb_rma_pro_active() && 'on' === $wallet_enabled && ( 'on' !== $refund_method || empty( $refund_method ) ) ) {
				$refund_method = 'wallet_method';
			}
			$products1 = $_POST;
			$response  = mwb_rma_save_return_request_callback( $order_id, $refund_method, $products1 );
			echo wp_json_encode( $response );
			wp_die();
		}
	}

	/**
	 * This function is to add custom order status for return
	 */
	public function mwb_rma_register_custom_order_status() {
		register_post_status(
			'wc-return-requested',
			array(
				'label'                     => 'Refund Requested',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true, /* translators: %s: search term */
				'label_count'               => _n_noop( 'Refund Requested <span class="count">(%s)</span>', 'Refund Requested <span class="count">(%s)</span>', 'woo-refund-and-exchange-lite' ),
			)
		);
		register_post_status(
			'wc-return-approved',
			array(
				'label'                     => 'Refund Approved',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true, /* translators: %s: search term */
				'label_count'               => _n_noop( 'Refund Approved <span class="count">(%s)</span>', 'Refund Approved <span class="count">(%s)</span>', 'woo-refund-and-exchange-lite' ),
			)
		);
		register_post_status(
			'wc-return-cancelled',
			array(
				'label'                     => 'Refund Cancelled',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true, /* translators: %s: search term */
				'label_count'               => _n_noop( 'Refund Cancelled <span class="count">(%s)</span>', 'Refund Cancelled <span class="count">(%s)</span>', 'woo-refund-and-exchange-lite' ),
			)
		);
		do_action( 'mwb_rma_register_custom_order_status' );
	}

	/**
	 * This function is to register custom order status
	 *
	 * @param array $mwb_rma_order_statuses .
	 */
	public function mwb_rma_add_custom_order_status( $mwb_rma_order_statuses ) {
		$mwb_rma_new_order_statuses = array();
		foreach ( $mwb_rma_order_statuses as $mwb_rma_key => $mwb_rma_status ) {

			$mwb_rma_new_order_statuses[ $mwb_rma_key ] = $mwb_rma_status;

			if ( 'wc-completed' === $mwb_rma_key ) {
				$mwb_rma_new_order_statuses['wc-return-requested'] = esc_html__( 'Refund Requested', 'woo-refund-and-exchange-lite' );
				$mwb_rma_new_order_statuses['wc-return-approved']  = esc_html__( 'Refund Approved', 'woo-refund-and-exchange-lite' );
				$mwb_rma_new_order_statuses['wc-return-cancelled'] = esc_html__( 'Refund Cancelled', 'woo-refund-and-exchange-lite' );
				$mwb_rma_new_order_statuses                        = apply_filters( 'mwb_rma_add_custom_order_status', $mwb_rma_new_order_statuses );
			}
		}
		return $mwb_rma_new_order_statuses;
	}

	/**
	 * Add capabilities for userrole
	 */
	public function mwb_rma_role_capability() {
		$mwb_rma_customer_role = get_role( 'customer' );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-request', true );

		$mwb_rma_customer_role = get_role( 'administrator' );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-request', true );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-approve', true );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-cancel', true );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-manage-stock', true );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-amount', true );

		$mwb_rma_customer_role = get_role( 'editor' );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-request', true );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-approve', true );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-cancel', true );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-manage-stock', true );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-amount', true );

		$mwb_rma_customer_role = get_role( 'shop_manager' );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-request', true );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-approve', true );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-cancel', true );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-manage-stock', true );
		$mwb_rma_customer_role->add_cap( 'mwb-rma-refund-amount', true );
	}

	/**
	 * Include the refund request temail template.
	 *
	 * @param string $order_id .
	 */
	public function mwb_rma_refund_req_email( $order_id ) {
		include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'common/partials/email_template/woo-refund-and-exchange-lite-refund-request-email.php';
	}

	/**
	 *  Multisite compatibility .
	 *
	 * @param array $new_site .
	 * @return void
	 */
	public function mwb_rma_plugin_on_create_blog( $new_site ) {
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}
		// Check if the plugin has been activated on the network .
		if ( is_plugin_active_for_network( 'woo-refund-and-exchange-lite/woocommerce-refund-and-exchange-lite.php' ) ) {
			$blog_id = $new_site->blog_id;
			// Switch to newly created site .
			switch_to_blog( $blog_id );
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-refund-and-exchange-lite-activator.php';
			Woo_Refund_And_Exchange_Lite_Activator::mwb_rma_create_pages();
			update_option( 'wrael_plugin_standard_multistep_done', 'yes' );
			restore_current_blog();
		}

	}

	/**
	 * Send refund request accept email to customer
	 *
	 * @param string $order_id .
	 */
	public function mwb_rma_refund_req_accept_email( $order_id ) {
		include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/email_template/woo-refund-and-exchange-lite-refund-request-accept-email.php';
	}

	/**
	 * Send refund request cancel email to customer.
	 *
	 * @param string $order_id .
	 */
	public function mwb_rma_refund_req_cancel_email( $order_id ) {
		include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/email_template/woo-refund-and-exchange-lite-refund-request-cancel-email.php';
	}
	/**
	 * Save order message from admin side.
	 */
	public function mwb_rma_order_messages_save() {
		$check_ajax = check_ajax_referer( 'mwb_rma_ajax_security', 'security_check' );
		if ( $check_ajax ) {
			$msg      = isset( $_POST['msg'] ) ? filter_input( INPUT_POST, 'msg' ) : '';
			$msg_type = isset( $_POST['order_msg_type'] ) ? filter_input( INPUT_POST, 'order_msg_type' ) : '';
			$order_id = isset( $_POST['order_id'] ) ? filter_input( INPUT_POST, 'order_id' ) : '';
			$order    = wc_get_order( $order_id );
			$to       = $order->get_billing_email();
			if ( 'admin' === $msg_type ) {
				$sender = 'Shop Manager';
			} else {
				$sender = 'Customer';
			}
			$flag = mwb_rma_lite_send_order_msg_callback( $order_id, $msg, $sender, $to );
			echo esc_html( $flag );
			wp_die();
		}
	}

	/**
	 * Function is used for the sending the track data.
	 *
	 * @param boolean $override .
	 */
	public function wrael_makewebbetter_tracker_send_event( $override = false ) {
		require WC()->plugin_path() . '/includes/class-wc-tracker.php';

		$last_send = get_option( 'makewebbetter_tracker_last_send' );
		if ( ! apply_filters( 'makewebbetter_tracker_send_override', $override ) ) {
			// Send a maximum of once per week by default.
			$last_send = $this->mwb_wrael_last_send_time();
			if ( $last_send && $last_send > apply_filters( 'makewebbetter_tracker_last_send_interval', strtotime( '-1 week' ) ) ) {
				return;
			}
		} else {
			// Make sure there is at least a 1 hour delay between override sends, we don't want duplicate calls due to double clicking links.
			$last_send = $this->mwb_wrael_last_send_time();
			if ( $last_send && $last_send > strtotime( '-1 hours' ) ) {
				return;
			}
		}
		// Update time first before sending to ensure it is set.
		update_option( 'makewebbetter_tracker_last_send', time() );
		$params  = WC_Tracker::get_tracking_data();
		$params  = apply_filters( 'makewebbetter_tracker_params', $params );
		$api_url = 'https://tracking.makewebbetter.com/wp-json/wrael-route/v1/wrael-testing-data/';
		$sucess  = wp_safe_remote_post(
			$api_url,
			array(
				'method' => 'POST',
				'body'   => wp_json_encode( $params ),
			)
		);
	}

	/**
	 * Get the updated time.
	 *
	 * @name mwb_wrael_last_send_time
	 *
	 * @since 1.0.0
	 */
	public function mwb_wrael_last_send_time() {
		return apply_filters( 'makewebbetter_tracker_last_send_time', get_option( 'makewebbetter_tracker_last_send', false ) );
	}

	/**
	 * Update the option for settings from the multistep form.
	 */
	public function mwb_rma_standard_save_settings_filter() {
		check_ajax_referer( 'ajax-nonce', 'nonce' );
		unset($_POST['action']);
		unset($_POST['nonce']);
		$checked_refund          = isset( $_POST['checkedRefund'] ) ? sanitize_text_field( wp_unslash( $_POST['checkedRefund'] ) ) : false;
		$checked_order_msg       = isset( $_POST['checkedOrderMsg'] ) ? sanitize_text_field( wp_unslash( $_POST['checkedOrderMsg'] ) ) : false;
		$checked_order_msg_email = isset( $_POST['checkedOrderMsgEmail'] ) ? sanitize_text_field( wp_unslash( $_POST['checkedOrderMsgEmail'] ) ) : false;
		$checked_exchange        = isset( $_POST['checkedExchange'] ) ? sanitize_text_field( wp_unslash( $_POST['checkedExchange'] ) ) : false;
		$checked_cancel          = isset( $_POST['checkedCancel'] ) ? sanitize_text_field( wp_unslash( $_POST['checkedCancel'] ) ) : false;
		$checked_cancel_prod     = isset( $_POST['checkedCancelProd'] ) ? sanitize_text_field( wp_unslash( $_POST['checkedCancelProd'] ) ) : false;
		$checked_wallet          = isset( $_POST['checkedWallet'] ) ? sanitize_text_field( wp_unslash( $_POST['checkedWallet'] ) ) : false;
		$checked_cod             = isset( $_POST['checkedCOD'] ) ? sanitize_text_field( wp_unslash( $_POST['checkedCOD'] ) ) : false;
		$checked_conset          = isset( $_POST['consetCheck'] ) ? sanitize_text_field( wp_unslash( $_POST['consetCheck'] ) ) : false;
		$checked_reset_license   = isset( $_POST['checkedResetLicense'] ) ? sanitize_text_field( wp_unslash( $_POST['checkedResetLicense'] ) ) : false;
		$license_code            = isset( $_POST['licenseCode'] ) ? sanitize_text_field( wp_unslash( $_POST['licenseCode'] ) ) : '';
		if ( $checked_refund ) {
			update_option( 'mwb_rma_refund_enable', 'on' );
		}
		if ( $checked_order_msg ) {
			update_option( 'mwb_rma_general_om', 'on' );
		}
		if ( $checked_order_msg_email ) {
			update_option( 'mwb_rma_order_email', 'on' );
		}
		if ( $checked_exchange ) {
			update_option( 'mwb_rma_exchange_enable', 'on' );
		}
		if ( $checked_cancel ) {
			update_option( 'mwb_rma_cancel_enable', 'on' );
		}
		if ( $checked_cancel_prod ) {
			update_option( 'mwb_rma_cancel_product', 'on' );
		}
		if ( $checked_wallet ) {
			update_option( 'mwb_rma_wallet_enable', 'on' );
		}
		if ( $checked_cod ) {
			update_option( 'mwb_rma_hide_rec', 'on' );
		}
		if ( $checked_conset ) {
			update_option( 'wrael_enable_tracking', 'on' );
		}
		if ( $checked_reset_license ) {
			update_option( 'mwr_radio_reset_license', 'on' );
		}
		if ( ! empty( $license_code ) && function_exists( 'mwb_rma_license_activate' ) ) {
			mwb_rma_license_activate( $license_code );
		}
		update_option( 'wrael_plugin_standard_multistep_done', 'yes' );
		wp_send_json( 'yes' );
	}

	/**
	 * Hide refund label from order edit page.
	 */
	public function mwb_rma_refund_info() {
		$check_ajax = check_ajax_referer( 'mwb_rma_ajax_security', 'security_check' );
		if ( $check_ajax ) {
			if( isset( $_POST['refund_id'] ) && ! empty( $_POST['refund_id'] ) ) {
				$refund_id  = $_POST['refund_id'];
				$mwb_refund = get_option( 'mwb_rma_refund_info', array() );
				if ( in_array( $refund_id, $mwb_refund, false ) ) {
					echo false;
				} else {
					echo true;
				}
			}
		}
		wp_die();
	}
}
