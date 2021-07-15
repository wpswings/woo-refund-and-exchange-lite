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
			global $wpdb;
			// $lang The language of the translated post (ie 'fr', 'de', etc.).
			$langs = wpml_get_active_languages();
			foreach ( $langs as $lang ) {
				// If the translated page doesn't exist, now create it.
				if ( apply_filters( 'wpml_object_id', $page_id, 'page', false, $lang['code'] ) === null ) {
					$page_translated_name  = get_post( $page_id )->post_name . ' (-' . $lang['code'] . ')';
					$page_translated_title = get_post( $page_id )->post_title;
					// All page stuff.
					$my_page                = array();
					$my_page['post_name']   = $page_translated_name;
					$my_page['post_title']  = $page_translated_title;
					$my_page['post_author'] = 1;
					$my_page['post_type']   = 'page';
					$my_page['post_status'] = 'publish';
					// Insert translated post.
					$post_translated_id = wp_insert_post( $my_page );

					// Get trid of original post.
					$trid = wpml_get_content_trid( 'post_page', $page_id );
					// Get default language.
					$default_lang = wpml_get_default_language();
					// Associate original post and translated post.
					$wpdb->update(
						$wpdb->prefix . 'icl_translations',
						array(
							'trid'                 => $trid,
							'language_code'        => $lang['code'],
							'source_language_code' => $default_lang,
						),
						array( 'element_id' => $post_translated_id )
					);

				}
			}
		}
	}

}
