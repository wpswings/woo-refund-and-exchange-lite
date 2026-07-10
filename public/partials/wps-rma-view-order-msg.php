<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/public/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header( 'shop' );

if ( apply_filters( 'wps_rma_refund_form_sidebar', true ) ) {
	do_action( 'woocommerce_before_main_content' );
}
if ( isset( $_GET['wps_rma_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wps_rma_nonce'] ) ), 'wps_rma_nonce' ) && isset( $_GET['order_id'] ) ) {
	$order_id = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
	$order_obj    = wc_get_order( $order_id );
	if ( ! empty( $order_id ) && ! empty( $order_obj ) ) {
		$user_id = $order_obj->get_user_id();
		if ( function_exists( 'get_current_user_id' ) && ! empty( get_current_user_id() ) && ( 1 === get_current_user_id() || get_current_user_id() === $user_id ) && 'yes' !== wps_rma_order_message_role_allowed() ) {
			?>
			<p class="wps_rma_order_msg_restricted"><?php echo esc_html( wps_rma_order_message_role_allowed() ); ?></p>
			<?php
		} elseif ( function_exists( 'get_current_user_id' ) && ! empty( get_current_user_id() ) && ( 1 === get_current_user_id() || get_current_user_id() === $user_id ) ) {
			$order_msg_template_class = get_option( 'wps_rma_order_msg_template_css', '' );
			$wps_order_msg_visual_css = '';
			if ( 'template2' === $order_msg_template_class ) {
				$wps_order_msg_visual_template = <<<'CSS'
#%1$s.wps_rma_template2{background:%2$s!important;border-color:%4$s!important;color:%5$s!important;}
#%1$s.wps_rma_template2::before{display:none!important;}
#%1$s.wps_rma_template2 .wps-order-msg_column,
#%1$s.wps_rma_template2 .wps_order_msg_sub_container,
#%1$s.wps_rma_template2 .wps-rma-order-msg-wrapper,
#%1$s.wps_rma_template2 #wps_rma_notification_div,
#%1$s.wps_rma_template2 .wps_order_msg_att-wrap{background:%3$s!important;border-color:%4$s!important;color:%5$s!important;}
#%1$s.wps_rma_template2 .wps-order-msg-back,
#%1$s.wps_rma_template2 .wps-order-msg-btn{background:%4$s!important;color:%6$s!important;box-shadow:none!important;}
#%1$s.wps_rma_template2 .wps-order-msg_column_name,
#%1$s.wps_rma_template2 .wps-order-msg_column .shop_man-title,
#%1$s.wps_rma_template2 .wps_order_msg_sender_details,
#%1$s.wps_rma_template2 .wps_o_m-label-wrap label,
#%1$s.wps_rma_template2 #wps_rma_notification_div label,
#%1$s.wps_rma_template2 #wps_rma_notification_div .wps_rma_notification_label{color:%5$s!important;}
#%1$s.wps_rma_template2 .wps-order-msg_row_shopmanager .wps_order_msg_detail_container{background:%3$s!important;border-color:%4$s!important;color:%5$s!important;}
#%1$s.wps_rma_template2 .wps-order-msg_row_customer .wps_order_msg_detail_container{background:%4$s!important;border-color:%4$s!important;color:%6$s!important;}
#%1$s.wps_rma_template2 #wps_order_new_msg,
#%1$s.wps_rma_template2 #wps_rma_notification_div label input[type="tel"]{background:%3$s!important;border-color:%4$s!important;color:%5$s!important;}
#%1$s.wps_rma_template2 .wps_order_msg_single_attachment img{background:%3$s!important;border-color:%4$s!important;}
#%1$s.wps_rma_template2 .wps_order_msg_att-wrap svg path{stroke:%5$s!important;}
#%1$s.wps_rma_template2 .wps-order-msg-btn svg path{stroke:%6$s!important;}
CSS;
				$wps_order_msg_visual_css = sprintf(
					$wps_order_msg_visual_template,
					esc_attr( 'wps_rma_order_msg_react' ),
					esc_attr( sanitize_hex_color( get_option( 'wps_rma_order_msg_background_color' ) ) ? get_option( 'wps_rma_order_msg_background_color' ) : '#fffdf7' ),
					esc_attr( sanitize_hex_color( get_option( 'wps_rma_order_msg_surface_color' ) ) ? get_option( 'wps_rma_order_msg_surface_color' ) : '#ffffff' ),
					esc_attr( sanitize_hex_color( get_option( 'wps_rma_order_msg_accent_color' ) ) ? get_option( 'wps_rma_order_msg_accent_color' ) : '#ff9800' ),
					esc_attr( sanitize_hex_color( get_option( 'wps_rma_order_msg_text_color' ) ) ? get_option( 'wps_rma_order_msg_text_color' ) : '#18120b' ),
					esc_attr( sanitize_hex_color( get_option( 'wps_rma_order_msg_button_text_color' ) ) ? get_option( 'wps_rma_order_msg_button_text_color' ) : '#18120b' )
				);
			}
			?>
				<style><?php echo wp_kses_post( $wps_order_msg_visual_css ); ?></style>
				<div id="wps_rma_order_msg_react" class="wps_rma_order_msg_react_wrapper wps_rma_<?php echo esc_attr( $order_msg_template_class ); ?>" data-order_id="<?php echo esc_attr( $order_id ); ?>"></div>
			<?php
		}
	}
}

if ( apply_filters( 'wps_rma_refund_form_sidebar', true ) ) {
	do_action( 'woocommerce_after_main_content' );
}

get_footer( 'shop' );
