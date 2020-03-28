<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_int( $thepostid ) ) {
	$thepostid = $post->ID;
}
if ( ! is_object( $theorder ) ) {
	$theorder = wc_get_order( $thepostid );
}

$order = $theorder;
if( WC()->version < "3.0.0" ) {
	$order_id = $order->id;
} else {
	$order_id = $order->get_id();
}

?>
<div class="mwb_admin_order_msg_wrapper">
	<div class="mwb_admin_order_msg_history_container">
		<div class="mwb_order_msg_history_title">
			<h4>
				<?php esc_html_e( 'Message History', 'woocommerce-refund-and-exchange-lite' ); ?>
			</h4>
			<a href="" class="mwb_wrma_reload_messages">Reload</a>
		</div>
		<div  class="mwb_admin_order_msg_sub_container">
			<?php 
			$mwb_order_messages = get_option( $order_id.'-mwb_cutomer_order_msg', array() );
			if ( isset( $mwb_order_messages ) && is_array( $mwb_order_messages ) && ! empty( $mwb_order_messages ) ) {
				foreach ( array_reverse( $mwb_order_messages ) as $o_key => $o_val ) {
					foreach ( $o_val as $om_key => $om_val ) {
						?>
						<div class="mwb_order_msg_main_container mwb_order_messages">
							<div>
								<div class="mwb_order_msg_sender"><?php echo esc_html__( $om_val['sender'], 'woocommerce-refund-and-exchange-lite' ); ?></div>
								<span class="mwb_order_msg_date"><?php echo $om_key;?></span>
							</div>
							<div class="mwb_order_msg_detail_container">
								<span><?php echo esc_html__( $om_val['msg'], 'woocommerce-refund-and-exchange-lite' ); ?></span>
							</div>
							<?php if ( isset( $om_val['files'] ) && ! empty( $om_val['files'] ) ) { ?>
								<hr>
								<div class="mwb_order_msg_attach_container">
									<div class="mwb_order_msg_attachments_title"><?php esc_html_e( 'Message attachments:', 'woocommerce-refund-and-exchange-lite' ); ?></div>
									<?php foreach( $om_val['files'] as $fkey => $fval ) { 
										if ( ! empty( $fval['name'] ) ) { 
											$is_image = $fval['img'];
											?>
											<div class="mwb_order_msg_single_attachment">
												<a target="_blank" href="<?php echo get_home_url().'/wp-content/attachment/'.$order_id.'-'.$fval['name']; ?>">
													<img class="mwb_order_msg_attachment_thumbnail" src="<?php echo $is_image ? get_home_url().'/wp-content/attachment/'.$order_id.'-'.$fval['name'] : get_home_url() . '/wp-content/plugins/woo-refund-and-exchange-lite/admin/images/attachment.png'; ?>">
													<span class="mwb_order_msg_attachment_file_name"><?php echo esc_html__( $fval['name'], 'woocommerce-refund-and-exchange-lite' ); ?></span>
												</a>
											</div>
										<?php } ?>
									<?php } ?>
								</div>
							<?php } ?>
						</div>
						<?php
					}
				}
			}
			?>
		</div>
	</div>
	<div class="mwb_order_msg_notice_wrapper">
	</div>
	<div class="mwb_admin_order_msg_container">
		<form id="mwb_order_new_msg_form" method="post" enctype="multipart/form-data" action="">
			<div class="mwb_order_msg_title"><h4><?php esc_html_e( 'Add a message', 'woocommerce-refund-and-exchange-lite' ); ?></h4></div>
			<textarea id="mwb_order_new_msg" name="mwb_order_new_msg" title="<?php esc_html_e( 'Write a message you want to sent to the Shop Manager.', 'woocommerce-refund-and-exchange-lite' ); ?>" rows="5"></textarea>
			<div>
	            <label for="mwb_order_msg_attachment"> <?php esc_html_e( 'Attach files ', 'woocommerce-refund-and-exchange-lite' ); ?></label>
	        </div>
	        <p><input type="file" id="mwb_order_msg_attachment" name="mwb_order_msg_attachment[]" multiple ></p>
			<input type="button" class="button button-primary" id="mwb_admin_order_msg_submit" name="mwb_admin_order_msg_submit" value="<?php esc_html_e( 'Send', 'woocommerce-refund-and-exchange-lite' ); ?>" data-id="<?php echo esc_attr( $order_id ); ?>">
		</form>
	</div>
</div>