<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link  https://makewebbetter.com/
 * @since 1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit(); // Exit if accessed directly.
}
global $wrael_mwb_rma_obj;
$wrael_active_tab   = isset( $_GET['wrael_tab'] ) ? sanitize_key( $_GET['wrael_tab'] ) : 'woo-refund-and-exchange-lite-general';
$wrael_default_tabs = $wrael_mwb_rma_obj->mwb_rma_plug_default_tabs();
?>
<header>
	<?php
		// Used to get the settings during saving.
		do_action( 'mwb_rma_settings_saved_notice' );
	?>
	<div class="mwb-header-container mwb-bg-white mwb-r-8">
		<h1 class="mwb-header-title"><?php echo esc_attr( strtoupper( str_replace( '-', ' ', $wrael_mwb_rma_obj->wrael_get_plugin_name() ) ) ); ?></h1>
		<a href="https://docs.makewebbetter.com/woocommerce-refund-and-exchange-lite" target="_blank" class="mwb-link"><?php esc_html_e( 'Documentation', 'woo-refund-and-exchange-lite' ); ?></a>
		<span>|</span>
		<a href="https://makewebbetter.com/contact-us/" target="_blank" class="mwb-link"><?php esc_html_e( 'Support', 'woo-refund-and-exchange-lite' ); ?></a>
	</div>
</header>
<main class="mwb-main mwb-bg-white mwb-r-8">
	<nav class="mwb-navbar">
		<ul class="mwb-navbar__items">
			<?php
			if ( is_array( $wrael_default_tabs ) && ! empty( $wrael_default_tabs ) ) {
				foreach ( $wrael_default_tabs as $wrael_tab_key => $wrael_default_tabs ) {

					$wrael_tab_classes = 'mwb-link ';
					if ( ! empty( $wrael_active_tab ) && $wrael_active_tab === $wrael_tab_key ) {
						$wrael_tab_classes .= 'active';
					}
					?>
					<li>
						<a id="<?php echo esc_attr( $wrael_tab_key ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' ) . '&wrael_tab=' . esc_attr( $wrael_tab_key ) ); ?>" class="<?php echo esc_attr( $wrael_tab_classes ); ?>"><?php echo esc_html( $wrael_default_tabs['title'] ); ?></a>
					</li>
					<?php
				}
			}
			?>
		</ul>
	</nav>
	<section class="mwb-section">
		<div>
			<?php
				// desc - This hook is used for trial.
				do_action( 'mwb_rma_before_general_settings_form' );
				// if submenu is directly clicked on woocommerce.
			if ( empty( $wrael_active_tab ) ) {
				$wrael_active_tab = 'mwb_rma_plug_general';
			}

				// look for the path based on the tab id in the admin templates.
				$wrael_default_tabs     = $wrael_mwb_rma_obj->mwb_rma_plug_default_tabs();
				$wrael_tab_content_path = $wrael_default_tabs[ $wrael_active_tab ]['file_path'];
				$wrael_mwb_rma_obj->mwb_rma_plug_load_template( $wrael_tab_content_path );
				// desc - This hook is used for trial.
				do_action( 'mwb_rma_after_general_settings_form' );
			?>
		</div>
	</section>
