<?php
/**
 * Fired during plugin activation
 *
 * @link       https://makewebbetter.com/
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
 */
class Woo_Refund_And_Exchange_Lite_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function woo_refund_and_exchange_lite_activate() {

		$timestamp = get_option( 'mwb_rma_activated_timestamp', 'not_set' );

		if ( 'not_set' === $timestamp ) {

			$current_time = current_time( 'timestamp' );

			$thirty_days = strtotime( '+30 days', $current_time );

			update_option( 'mwb_rma_activated_timestamp', $thirty_days );
		}

		// Pages will create.
		$email                       = get_option( 'admin_email', false );
		$admin                       = get_user_by( 'email', $email );
		$admin_id                    = $admin->ID;
		$mwb_rma_return_request_form = array(
			'post_author' => $admin_id,
			'post_name'   => 'refund-request-form',
			'post_title'  => 'Refund Request Form',
			'post_type'   => 'page',
			'post_status' => 'publish',

		);

		$page_id = wp_insert_post( $mwb_rma_return_request_form );

		if ( $page_id ) {
			update_option( 'mwb_rma_return_request_form_page_id', $page_id );
			self::mwb_rma_wpml_translate_post( $page_id ); // Insert Translated Pages.
		}

		$mwb_rma_view_order_msg_form = array(
			'post_author' => $admin_id,
			'post_name'   => 'view-order-msg',
			'post_title'  => 'View Order Messages',
			'post_type'   => 'page',
			'post_status' => 'publish',

		);

		$page_id = wp_insert_post( $mwb_rma_view_order_msg_form );

		if ( $page_id ) {
			update_option( 'mwb_rma_view_order_msg_page_id', $page_id );
			self::mwb_rma_wpml_translate_post( $page_id ); // Insert Translated Pages.
		}
	}

	/**
	 * Creates a translation of a post (to be used with WPML)
	 *
	 * @param int $page_id The ID of the post to be translated.
	 **/
	public static function mwb_rma_wpml_translate_post( $page_id ) {
		if ( has_filter( 'wpml_object_id' ) ) {
			// If the translated page doesn't exist, now create it.
			do_action( 'wpml_admin_make_post_duplicates', $page_id );
		}
	}
}
