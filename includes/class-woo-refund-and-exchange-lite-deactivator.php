<?php
/**
 * Fired during plugin deactivation
 *
 * @link  https://makewebbetter.com/
 * @since 1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/includes
 */
class Woo_Refund_And_Exchange_Lite_Deactivator {


	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since 1.0.0
	 */
	public static function woo_refund_and_exchange_lite_deactivate() {
		// Delete created pages.
		$page_id = get_option( 'mwb_rma_return_request_form_page_id' );
		self::mwb_rma_delete_wpml_translate_post( $page_id );  // Delete tranlated pages.
		wp_delete_post( $page_id );
		delete_option( 'mwb_rma_return_request_form_page_id' );

		$page_id = get_option( 'mwb_rma_view_order_msg_page_id' );
		self::mwb_rma_delete_wpml_translate_post( $page_id ); // Delete tranlated pages.
		wp_delete_post( $page_id );
		delete_option( 'mwb_rma_view_order_msg_page_id' );

	}

	/**
	 * Function to delete translated pages.
	 *
	 * @param int $page_id The ID of the post to be deleted.
	 */
	public static function mwb_rma_delete_wpml_translate_post( $page_id ) {
		if ( has_filter( 'wpml_object_id' ) ) {
			$langs = wpml_get_active_languages();
			foreach ( $langs as $lang ) {
				if ( apply_filters( 'wpml_object_id', $page_id, 'page', false, $lang['code'] ) ) {
					$pageid = apply_filters( 'wpml_object_id', $page_id, 'page', false, $lang['code'] );
					wp_delete_post( $pageid );

				}
			}
		}
	}

}
