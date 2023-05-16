<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://wpswings.com/
 * @since 1.0.0
 *
 * @package    woocommerce-rma-for-return-refund-and-exchange
 * @subpackage woocommerce-rma-for-return-refund-and-exchange/admin/partials
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Returnship label setting
 *
 * @package    woocommerce-rma-for-return-refund-and-exchange
 * @subpackage woocommerce-rma-for-return-refund-and-exchange/admin/partials
 */
class Wps_Rma_Returnship_Html {
	/**
	 * Returnship related html on order edit page.
	 *
	 * @param [type] $order_id .
	 * @return void
	 */
	public function wps_rma_return_ship_attach_upload_html_set( $order_id ) {
		$wps_wrma_enable_return_ship_label = get_option( 'wps_wrma_enable_return_ship_label', 'no' );
		if ( 'on' === $wps_wrma_enable_return_ship_label ) {
			?>
			<p><b><?php esc_html_e( 'Return Ship Label Attachment :', 'woocommerce-rma-for-return-refund-and-exchange' ); ?></b></p> 
			<?php
			$wps_wrma_return_upload_button = esc_html__( 'Upload', 'woocommerce-rma-for-return-refund-and-exchange' );
			$return_label_name             = get_post_meta( $order_id, 'wps_return_label_attachment_name', true );
			if ( '' != $return_label_name ) {
				?>
				<span><b>Uploaded Attachment :</b></span>
				<a href="<?php echo esc_html( content_url() . '/return-label-attachments/' . $return_label_name ); ?>" target="_blank"><?php esc_html_e( 'Return Label Attachment', 'woocommerce-rma-for-return-refund-and-exchange' ); ?></a><br><br>
				<?php
				$wps_wrma_return_upload_button = esc_html__( 'Change Attachment', 'woocommerce-rma-for-return-refund-and-exchange' );
			}
			$wps_wrma_enable_return_ship_station_label    = get_option( 'wps_wrma_enable_return_ship_station_label', true );
			$wps_wrma_enable_ss_return_ship_station_label = get_option( 'wps_wrma_enable_ss_return_ship_station_label', true );
			if ( 'on' === $wps_wrma_enable_return_ship_station_label || 'on' === $wps_wrma_enable_ss_return_ship_station_label ) {
				if ( ! empty( get_option( ' wps_wrma_saved_carriers ' ) ) || ! empty( get_option( ' wps_wrma_ship_saved_carriers ' ) ) ) :
					?>
					<div id="wps_wrma_return_ship_attchment_div">      
						<p class="description"><?php esc_html_e( 'Validate your Return label attachment here.', 'woocommerce-rma-for-return-refund-and-exchange' ); ?>
						</p>
						<input type="hidden" name="return_type" value="return" />
						<?php $nonce = wp_create_nonce( 'save_button_nonce' ); ?>
						<input type="hidden" name="message-save_button_nonce" value="<?php echo esc_attr( $nonce ); ?>" />
						<input type="submit" name="wps_wrma_save_button" class="button save_order button-primary" value="<?php esc_html_e( 'Create Return Label', 'woocommerce-rma-for-return-refund-and-exchange' ); ?>" >
					</div>
					<?php
				endif;
			} else {
				$attach_message = get_post_meta( $order_id, 'wps_wrma_return_attachment_error', true );
				if ( isset( $attach_message ) && ! empty( $attach_message ) ) {

					?>
				<p class="wps_wrma_retrn_attac_error">  <span class="wps_wrma_retrn_close">X</span> <?php echo esc_html( $attach_message ); ?></p> <?php } ?>
				<div id="wps_wrma_return_ship_attchment_div">      
					<p class="description"><?php esc_html_e( 'Upload your Return label attachment here.', 'woocommerce-rma-for-return-refund-and-exchange' ); ?></p>
					<input type="file" id="wps_return_label_attachment" name="wps_return_label_attachment" value="" size="25" />
					<input type="submit" name="save" class="button save_order button-primary" value="<?php echo esc_html( $wps_wrma_return_upload_button ); ?>" id='wps_wrma_save_button'> 
				</div>
				<?php
			}
		}
	}
}
