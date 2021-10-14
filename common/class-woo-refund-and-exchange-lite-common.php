<?php
/**
 * The common functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/common
 */

/**
 * The common functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the common stylesheet and JavaScript.
 * namespace woo_refund_and_exchange_lite_common.
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/common
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
		wp_register_script( $this->plugin_name . 'common', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'common/js/woo-refund-and-exchange-lite-common.js', array( 'jquery' ), $this->version, false );
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
			if ( mwb_rma_pro_active() && 'on' === $wallet_enabled && 'on' !== $refund_method ) {
				$refund_method = 'wallet_method';
			}
			$products1 = $_POST;
			$response  = $this->mwb_rma_save_return_request_callback( $order_id, $refund_method, $products1 );
			echo wp_json_encode( $response );
			wp_die();
		}
	}

	/**
	 * This function is a callback function to save return request.
	 *
	 * @param int    $order_id .
	 * @param string $refund_method .
	 * @param array  $products1 .
	 */
	public function mwb_rma_save_return_request_callback( $order_id, $refund_method, $products1 ) {
		update_option( $order_id . 'mwb_rma_refund_method', $refund_method );
		$order = wc_get_order( $order_id );
		if ( empty( get_post_meta( $order_id, 'mwb_rma_request_made', true ) ) ) {
			$item_id = array();
		} else {
			$item_id = get_post_meta( $order_id, 'mwb_rma_request_made', true );
		}
		$item_ids = array();
		if ( isset( $products1 ) && ! empty( $products1 ) && is_array( $products1 ) ) {
			foreach ( $products1 as $post_key => $post_value ) {
				if ( is_array( $post_value ) && ! empty( $post_value ) ) {
					foreach ( $post_value as $post_val_key => $post_val_value ) {
						$item_id[ $post_val_value['item_id'] ] = 'pending';
						$item_ids[]                            = $post_val_value['item_id'];
					}
				}
			}
		}
		update_post_meta( $order_id, 'mwb_rma_request_made', $item_id );
		$products = get_post_meta( $order_id, 'mwb_rma_return_product', true );
		$pending  = true;
		if ( isset( $products ) && ! empty( $products ) ) {
			foreach ( $products as $date => $product ) {
				if ( 'pending' === $product['status'] ) {
						$products[ $date ]           = $products1;
						$products[ $date ]['status'] = 'pending'; // update requested products.
						$pending                     = false;
						break;
				}
			}
		}
		if ( $pending ) {
			if ( ! is_array( $products ) ) {
				$products = array();
			}
			$products                    = array();
			$date                        = date_i18n( wc_date_format(), time() );
			$products[ $date ]           = $products1;
			$products[ $date ]['status'] = 'pending';

		}

		update_post_meta( $order_id, 'mwb_rma_return_product', $products );

		// Send refund request email to admin.

		$restrict_mail = apply_filters( 'mwb_rma_restrict_refund_request_mails', false );
		if ( ! $restrict_mail ) {
			do_action( 'mwb_rma_refund_req_email', $order_id );
		}
		do_action( 'mwb_rma_do_something_on_refund', $order_id, $item_ids );
		$order->update_status( 'wc-return-requested', esc_html__( 'User Request to refund product', 'woo-refund-and-exchange-lite' ) );
		$response['auto_accept'] = apply_filters( 'mwb_rma_auto_accept_refund', false );
		$response['flag']        = true;
		$response['msg']         = esc_html__( 'Refund request placed successfully. You have received a notification mail regarding this. You will redirect to the My Account Page', 'woo-refund-and-exchange-lite' );
		return $response;
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
				'label_count'               => _n_noop( 'Refund Requested <span class="count">(%s)</span>', 'Refund Requested <span class="count">(%s)</span>' ),
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
				'label_count'               => _n_noop( 'Refund Approved <span class="count">(%s)</span>', 'Refund Approved <span class="count">(%s)</span>' ),
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
				'label_count'               => _n_noop( 'Refund Cancelled <span class="count">(%s)</span>', 'Refund Cancelled <span class="count">(%s)</span>' ),
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
	 *  Multisite compatibility 
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
			restore_current_blog();
		}

	}
}
